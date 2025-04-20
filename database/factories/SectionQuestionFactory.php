<?php

namespace Database\Factories;

use App\Models\SectionQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SectionQuestion>
 */
class SectionQuestionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SectionQuestion::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'section_id' => fake()->numberBetween(1, 10),
            'question_id' => fake()->numberBetween(1, 50),
        ];
    }
} 