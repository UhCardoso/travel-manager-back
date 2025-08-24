<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => UserRole::USER->value,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Define the model's state for an admin user.
     *
     * @return array<string, mixed>
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::ADMIN->value,
        ]);
    }

    /**
     * Define the model's state for a user.
     *
     * @return array<string, mixed>
     */
    public function user(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::USER->value,
        ]);
    }
}
