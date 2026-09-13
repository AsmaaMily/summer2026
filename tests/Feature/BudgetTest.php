<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class BudgetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::create(2026, 9, 13));
    }

    public function test_user_can_view_budget_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('budget'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('BudgetGoals')
            ->has('budgets')
            ->has('goals')
            ->has('categories')
            ->has('totalBudget')
            ->has('totalSpent')
            ->has('currentMonth')
        );
    }

    public function test_user_can_create_budget(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->expense()->create();

        $response = $this->actingAs($user)->post(route('budgets.store'), [
            'category_id' => $category->id,
            'amount' => 500000,
            'month' => '2026-09',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('budgets', [
            'user_id' => $user->id,
            'category_id' => $category->id,
            'amount' => 500000,
        ]);
    }

    public function test_user_can_update_own_budget(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->expense()->create();
        $budget = Budget::factory()->create(['user_id' => $user->id, 'category_id' => $category->id]);

        $response = $this->actingAs($user)->put(route('budgets.update', $budget), [
            'amount' => 750000,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('budgets', [
            'id' => $budget->id,
            'amount' => 750000,
        ]);
    }

    public function test_user_cannot_update_others_budget(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $category = Category::factory()->expense()->create();
        $budget = Budget::factory()->create(['user_id' => $otherUser->id, 'category_id' => $category->id]);

        $response = $this->actingAs($user)->put(route('budgets.update', $budget), [
            'amount' => 999999,
        ]);

        $response->assertForbidden();
    }

    public function test_user_can_delete_own_budget(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->expense()->create();
        $budget = Budget::factory()->create(['user_id' => $user->id, 'category_id' => $category->id]);

        $response = $this->actingAs($user)->delete(route('budgets.destroy', $budget));

        $response->assertRedirect();
        $this->assertDatabaseMissing('budgets', ['id' => $budget->id]);
    }
}
