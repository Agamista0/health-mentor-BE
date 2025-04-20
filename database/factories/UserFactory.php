<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'username' => fake()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'date_of_birth' => fake()->date(),
            'age' => fake()->numberBetween(18, 80),
            'gender' => fake()->numberBetween(1, 2),
            'country_code' => fake()->randomElement(['US', 'UK', 'CA', 'AU']),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'image' => 'https://via.placeholder.com/640x480.png/00bbbb?text=ut',
            'health_status' => fake()->randomElement(['yes', 'no', 'not sure']),
            'description_disease' => fake()->randomElement(['none', 'chronic', 'acute']),
            'otp' => Str::random(6),
            'user_id' => '?',
            'remember_token' => Str::random(10),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
} 