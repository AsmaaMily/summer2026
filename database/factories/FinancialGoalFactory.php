<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FinancialGoal>
 */
class FinancialGoalFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $target = fake()->numberBetween(100000, 5000000);

        return [
            'user_id' => User::factory(),
            'title' => fake()->words(3, true),
            'title_ar' => fake()->words(3, true),
            'description' => fake()->optional()->paragraph(),
            'target_amount' => $target,
            'current_amount' => fake()->numberBetween(0, $target),
            'deadline' => fake()->optional(0.7)->dateTimeBetween('now', '+2 years')?->format('Y-m-d'),
            'category_id' => null,
            'status' => 'active',
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'current_amount' => $attributes['target_amount'],
        ]);
    }
}
