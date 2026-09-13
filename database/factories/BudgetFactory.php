<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Budget>
 */
class BudgetFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(['type' => 'expense']),
            'amount' => fake()->numberBetween(50000, 500000),
            'month' => fake()->dateTimeBetween('-3 months', '+3 months')->format('Y-m-01'),
        ];
    }
}
