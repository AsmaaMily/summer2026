<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_users_can_visit_the_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
    }

    public function test_dashboard_returns_correct_props(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->expense()->create(['user_id' => $user->id]);

        $today = date('Y-m-d');
        $currentMonth = date('Y-m');

        Transaction::factory()->income()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'amount' => 100000,
            'date' => $today,
        ]);

        Transaction::factory()->expense()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'amount' => 30000,
            'date' => $today,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('netBalance')
            ->has('totalIncome')
            ->has('totalExpenses')
            ->has('categoryBreakdown')
            ->has('recentTransactions')
            ->has('recurringUpcoming')
            ->has('selectedMonth')
            ->where('totalIncome', 100000)
            ->where('totalExpenses', 30000)
            ->where('netBalance', 70000)
            ->where('selectedMonth', $currentMonth)
        );
    }

    public function test_dashboard_returns_categories_for_the_transaction_form(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $ownCategory = Category::factory()->expense()->create(['user_id' => $user->id, 'sort_order' => 1]);
        $defaultCategory = Category::factory()->expense()->default()->create(['sort_order' => 2]);
        Category::factory()->expense()->create(['user_id' => $otherUser->id, 'sort_order' => 0]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('categories', 2)
            ->where('categories.0.id', $ownCategory->id)
            ->where('categories.1.id', $defaultCategory->id)
        );
    }
}
