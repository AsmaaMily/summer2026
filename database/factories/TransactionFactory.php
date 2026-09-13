<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\RecurringTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['income', 'expense']);

        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(['type' => $type]),
            'recurring_transaction_id' => null,
            'amount' => fake()->numberBetween(1000, 100000),
            'type' => $type,
            'date' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'notes' => fake()->optional()->sentence(),
            'payment_method' => fake()->randomElement(['cash', 'card']),
            'is_recurring' => false,
            'source' => 'manual',
            'is_flagged_duplicate' => false,
            'flag_reason' => null,
        ];
    }

    public function income(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'income',
        ]);
    }

    public function expense(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'expense',
        ]);
    }

    public function ai(): static
    {
        return $this->state(fn (array $attributes) => [
            'source' => 'ai',
        ]);
    }

    public function recurring(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_recurring' => true,
            'recurring_transaction_id' => RecurringTransaction::factory(),
        ]);
    }
}
