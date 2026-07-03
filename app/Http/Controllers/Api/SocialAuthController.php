<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirect(Request $request, string $driver): JsonResponse
    {
        abort_if($driver !== 'google', 404);

        if (!config('services.google.client_id') || !config('services.google.client_secret')) {
            return response()->json([
                'success' => false,
                'message' => 'Google login is not configured on the server.',
            ], 500);
        }

        $next = $request->query('next', '/');

        // We don't store 'next' in the session because API routes lack
        // session middleware. Instead, the frontend saves the redirect
        // URL in localStorage before opening the OAuth popup, and reads
        // it back in the SocialCallbackPage after auth completes.

        $url = Socialite::driver($driver)
            ->stateless()
            ->redirect()
            ->getTargetUrl();

        return response()->json([
            'success' => true,
            'data'    => ['url' => $url],
        ]);
    }

    public function callback(Request $request, string $driver): RedirectResponse|JsonResponse
    {
        abort_if($driver !== 'google', 404);

        if (!config('services.google.client_id') || !config('services.google.client_secret')) {
            return response()->json([
                'success' => false,
                'message' => 'Google login is not configured on the server.',
            ], 500);
        }

        try {
            $code = $request->query('code');

            if (!$code) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authorization code is missing.',
                ], 400);
            }

            $tokenResponse = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'code' => $code,
                'client_id' => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'redirect_uri' => config('services.google.redirect'),
                'grant_type' => 'authorization_code',
            ]);

            if ($tokenResponse->failed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to exchange authorization code.',
                    'error'   => $tokenResponse->body(),
                ], 401);
            }

            $googleAccessToken = $tokenResponse->json('access_token');

            if (!$googleAccessToken) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access token not returned by Google.',
                ], 401);
            }

            $userResponse = Http::withToken($googleAccessToken)->get('https://www.googleapis.com/oauth2/v2/userinfo');

            if ($userResponse->failed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch user profile from Google.',
                ], 401);
            }

            $googleUser = $userResponse->json();

            $user = User::where('google_id', $googleUser['id'])
                ->orWhere('email', $googleUser['email'])
                ->first();

            if (!$user) {
                $user = User::create([
                    'name'     => $googleUser['name'] ?? ($googleUser['given_name'] ?? 'Google User'),
                    'email'    => $googleUser['email'],
                    'password' => Hash::make('google-social-user'),
                    'google_id' => $googleUser['id'],
                    'role'     => User::ROLE_CUSTOMER,
                    'status'   => User::STATUS_ACTIVE,
                ]);
            } else {
                $user->update([
                    'name'       => $googleUser['name'] ?? $user->name,
                    'google_id'  => $googleUser['id'],
                ]);
            }

            $token = $user->createToken('api', ['*'])->plainTextToken;

            // The 'next' redirect URL is stored in localStorage by the frontend
            // before opening the popup, so the backend always defaults to '/'.
            $base = rtrim(config('services.frontend_url', url('/')), '/');

            return redirect()->away($base.'/auth/social/callback/google?token='.urlencode($token));
        } catch (\Throwable $e) {
            Log::error('Google social login failed', ['error' => $e]);

            $debugMessage = config('app.debug') ? $e->getMessage() : '';

            return response()->json([
                'success' => false,
                'message' => 'Unable to complete Google login.',
                'error'   => $debugMessage,
            ], 500);
        }
    }
}
