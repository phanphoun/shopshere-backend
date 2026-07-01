<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {}

    /**
     * Register a new customer.
     */
    public function register(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $user = $this->userRepository->create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
                'phone'    => $data['phone'] ?? null,
                'address'  => $data['address'] ?? null,
                'role'     => User::ROLE_CUSTOMER,
                'status'   => User::STATUS_ACTIVE,
            ]);

            $token = $user->createToken('api', ['*'])->plainTextToken;

            return [
                'user'  => $user,
                'token' => $token,
            ];
        });
    }

    /**
     * Login a user and return the token + user.
     *
     * @throws ValidationException
     */
    public function login(string $email, string $password): array
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($user->status !== User::STATUS_ACTIVE) {
            throw ValidationException::withMessages([
                'email' => ['Your account is not active.'],
            ]);
        }

        // Revoke previous 'api' tokens to keep a clean list
        // but preserve tokens with other names (e.g. 'mobile') for multi-device support
        $user->tokens()->where('name', 'api')->delete();

        $token = $user->createToken('api', ['*'])->plainTextToken;

        return [
            'user'  => $user,
            'token' => $token,
        ];
    }

    /**
     * Logout the current user (revoke current token).
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }

    /**
     * Update the user's profile.
     */
    public function updateProfile(User $user, array $data): User
    {
        // Strip password fields if not provided
        if (empty($data['password'])) {
            unset($data['password']);
        }

        return $this->userRepository->update($user, $data);
    }

    /**
     * Change the user's password.
     *
     * @throws ValidationException
     */
    public function changePassword(User $user, string $current, string $new): void
    {
        if (!Hash::check($current, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Current password is incorrect.'],
            ]);
        }

        $user->password = Hash::make($new);
        $user->save();
    }
}
