<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ResetUserPassword extends Command
{
    protected $signature = 'auth:reset-password {email : The user\'s email address} {password? : The new password (will prompt if omitted)}';
    protected $description = 'Reset a user\'s password (fixes double-hashed passwords from a previous bug)';

    public function handle(): int
    {
        $email = $this->argument('email');

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email '{$email}' not found.");

            return self::FAILURE;
        }

        $password = $this->argument('password');

        if (!$password) {
            $password = $this->secret('Enter new password');
        }

        if (strlen($password) < 6) {
            $this->error('Password must be at least 6 characters.');

            return self::FAILURE;
        }

        $user->password = Hash::make($password);
        $user->save();

        $this->info("Password for {$user->name} ({$email}) has been reset successfully.");

        return self::SUCCESS;
    }
}
