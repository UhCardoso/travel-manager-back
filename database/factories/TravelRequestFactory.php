<?php

namespace Database\Factories;

use App\Enums\TravelRequestStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TravelRequest>
 */
class TravelRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $departureDate = $this->faker->dateTimeBetween('+1 month', '+6 months');
        $returnDate = $this->faker->dateTimeBetween($departureDate, '+1 year');

        return [
            'user_id' => User::factory(),
            'name' => $this->faker->sentence(3, false),
            'country' => $this->faker->country(),
            'town' => $this->faker->optional(0.8)->city(),
            'state' => $this->faker->optional(0.7)->state(),
            'region' => $this->faker->optional(0.6)->word(),
            'departure_date' => $departureDate->format('Y-m-d'),
            'return_date' => $returnDate->format('Y-m-d'),
            'status' => TravelRequestStatus::PENDING,
        ];
    }

    /**
     * Indicate that the travel request is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TravelRequestStatus::PENDING,
        ]);
    }

    /**
     * Indicate that the travel request is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TravelRequestStatus::APPROVED,
        ]);
    }

    /**
     * Indicate that the travel request is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TravelRequestStatus::CANCELLED,
        ]);
    }

    /**
     * Indicate that the travel request has minimal data.
     */
    public function minimal(): static
    {
        return $this->state(fn (array $attributes) => [
            'town' => null,
            'state' => null,
            'region' => null,
        ]);
    }
}
