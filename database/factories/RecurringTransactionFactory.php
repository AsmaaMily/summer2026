<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecurringTransaction>
 */
class RecurringTransactionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['income', 'expense']);
        $frequency = fake()->randomElement(['daily', 'weekly', 'monthly', 'yearly']);

        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(['type' => $type]),
            'amount' => fake()->numberBetween(1000, 500000),
            'type' => $type,
            'frequency' => $frequency,
            'day_of_month' => $frequency === 'monthly' ? fake()->numberBetween(1, 28) : null,
            'day_of_week' => $frequency === 'weekly' ? fake()->numberBetween(0, 6) : null,
            'start_date' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'end_date' => fake()->optional(0.3)->dateTimeBetween('now', '+2 years')->format('Y-m-d'),
            'notes' => fake()->optional()->sentence(),
            'payment_method' => fake()->randomElement(['cash', 'card']),
            'is_active' => true,
            'last_reminded_at' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
