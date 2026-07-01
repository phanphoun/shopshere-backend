<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TelegramBotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TelegramController extends Controller
{
    public function __construct(
        protected TelegramBotService $telegramBotService
    ) {}

    /**
     * Webhook endpoint for Telegram bot updates.
     * When a user sends /start to the bot, we capture their chat_id.
     */
    public function webhook(Request $request): JsonResponse
    {
        // Verify the request is from Telegram by checking the secret token header
        $secretToken = $request->header('X-Telegram-Bot-Api-Secret-Token');
        $expectedToken = config('services.telegram.token');

        if ($secretToken && $expectedToken && !hash_equals($expectedToken, $secretToken)) {
            Log::warning('Telegram webhook received with invalid secret token');
            return response()->json(['ok' => false], 403);
        }

        $payload = $request->all();
        Log::debug('Telegram webhook received', $payload);

        $message = $payload['message'] ?? null;
        if (!$message || !isset($message['chat']['id'])) {
            return response()->json(['ok' => true]);
        }

        $chatId = (string) $message['chat']['id'];
        $text = $message['text'] ?? '';
        $username = $message['from']['username'] ?? '';
        $firstName = $message['from']['first_name'] ?? '';

        // Handle /start command with a token parameter
        // Format: /start <token>
        if (Str::startsWith($text, '/start')) {
            $parts = explode(' ', $text, 2);
            $token = $parts[1] ?? null;

            if ($token) {
                // Find user by the verification token (stored temporarily)
                // The token is the user's Sanctum token or a custom verification code
                $user = $this->findUserByVerificationToken($token);

                if ($user) {
                    $user->telegram_chat_id = $chatId;
                    $user->save();

                    $this->telegramBotService->sendMessage(
                        $chatId,
                        "✅ *Telegram connected successfully!*\n\n"
                        . "Hi {$user->name}, you will now receive real-time notifications for:\n"
                        . "• New orders\n"
                        . "• Payment confirmations\n"
                        . "• Order status updates\n\n"
                        . "Use /status to check this connection."
                    );

                    return response()->json(['ok' => true]);
                }
            }

            // No token or invalid token - send instructions
            $botUsername = config('services.telegram.bot_username', 'ShopSphereBot');
            $this->telegramBotService->sendMessage(
                $chatId,
                "👋 *Welcome to ShopSphere Bot!*\n\n"
                . "To connect this chat to your ShopSphere admin account:\n\n"
                . "1. Go to your ShopSphere admin panel\n"
                . "2. Navigate to your profile settings\n"
                . "3. Click \"Connect Telegram\"\n"
                . "4. Copy the code and send it here:\n\n"
                . "`/start <your_code>`"
            );
        }

        // Handle /status command
        if (Str::startsWith($text, '/status')) {
            $user = User::where('telegram_chat_id', $chatId)->first();

            if ($user) {
                $this->telegramBotService->sendMessage(
                    $chatId,
                    "✅ *Connected as:* {$user->name}\n"
                    . "📧 *Email:* {$user->email}\n"
                    . "👤 *Role:* {$user->role}"
                );
            } else {
                $this->telegramBotService->sendMessage(
                    $chatId,
                    "❌ This chat is not connected to any ShopSphere account.\n"
                    . "Use /start to connect."
                );
            }
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Generate a verification token for a user to connect their Telegram.
     */
    public function generateToken(Request $request): JsonResponse
    {
        $user = $request->user();

        // Generate a simple verification token
        $token = Str::random(32);
        cache()->put("telegram_verify_{$token}", $user->id, now()->addMinutes(30));

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token,
                'bot_username' => config('services.telegram.bot_username', 'ShopSphereBot'),
                'instructions' => "Send this message to the bot:\n/start {$token}",
            ],
        ]);
    }

    /**
     * Connect the authenticated user's Telegram chat_id.
     */
    public function connect(Request $request): JsonResponse
    {
        $request->validate([
            'chat_id' => ['required', 'string'],
        ]);

        $user = $request->user();

        // Check if chat_id is already used by another user
        $existing = User::where('telegram_chat_id', $request->input('chat_id'))
            ->where('id', '!=', $user->id)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'This Telegram chat is already connected to another account.',
            ], 422);
        }

        $user->telegram_chat_id = $request->input('chat_id');
        $user->save();

        // Send a welcome message to the Telegram chat
        $this->telegramBotService->sendMessage(
            $request->input('chat_id'),
            "✅ *Telegram connected successfully!*\n\n"
            . "Hi {$user->name}, you will now receive real-time notifications for orders and payments."
        );

        return response()->json([
            'success' => true,
            'message' => 'Telegram connected successfully.',
        ]);
    }

    /**
     * Disconnect Telegram from the authenticated user's account.
     */
    public function disconnect(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->telegram_chat_id) {
            $this->telegramBotService->sendMessage(
                $user->telegram_chat_id,
                "👋 Telegram disconnected from ShopSphere.\n\n"
                . "You will no longer receive notifications here."
            );
        }

        $user->telegram_chat_id = null;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Telegram disconnected successfully.',
        ]);
    }

    /**
     * Get the connection status for the authenticated user.
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'connected' => !empty($user->telegram_chat_id),
                'telegram_chat_id' => $user->telegram_chat_id,
                'bot_username' => config('services.telegram.bot_username', 'ShopSphereBot'),
            ],
        ]);
    }

    /**
     * Find a user by a verification token from cache.
     */
    protected function findUserByVerificationToken(string $token): ?User
    {
        $userId = cache()->get("telegram_verify_{$token}");

        if ($userId) {
            cache()->forget("telegram_verify_{$token}");
            return User::find($userId);
        }

        return null;
    }
}
