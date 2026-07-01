<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name'              => fake()->name(),
            'email'             => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => Hash::make('password'),
            'phone'             => fake()->phoneNumber(),
            'address'           => fake()->streetAddress() . ', ' . fake()->city() . ', ' . fake()->country(),
            'role'              => User::ROLE_CUSTOMER,
            'status'            => User::STATUS_ACTIVE,
            'remember_token'    => Str::random(10),
        ];
    }

    public function admin(): self
    {
        return $this->state(fn () => [
            'role'   => User::ROLE_ADMIN,
            'email'  => 'admin@shopsphere.test',
        ]);
    }

    public function unverified(): self
    {
        return $this->state(fn () => [
            'email_verified_at' => null,
        ]);
    }
}
