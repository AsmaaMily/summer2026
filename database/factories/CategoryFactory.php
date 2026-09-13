<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->word(),
            'name_ar' => fake()->word(),
            'type' => fake()->randomElement(['income', 'expense']),
            'icon' => null,
            'color' => '#'.fake()->hexColor(),
            'description' => fake()->sentence(),
            'sort_order' => fake()->numberBetween(0, 100),
            'is_default' => false,
            'is_archived' => false,
        ];
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
            'is_default' => true,
        ]);
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
}
