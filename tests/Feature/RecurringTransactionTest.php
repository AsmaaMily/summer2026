<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\RecurringTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class RecurringTransactionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::create(2026, 9, 13));
    }

    public function test_profile_page_includes_recurring_transactions(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->expense()->create(['user_id' => $user->id]);
        RecurringTransaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'frequency' => 'monthly',
            'day_of_month' => 20,
            'start_date' => '2026-01-01',
            'end_date' => null,
        ]);

        $response = $this->actingAs($user)->get(route('profile'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Profile')
            ->has('recurringTransactions', 1)
            ->has('recurringTransactions.0.next_due_date')
        );
    }

    public function test_user_can_create_a_recurring_transaction(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->expense()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('recurring-transactions.store'), [
            'category_id' => $category->id,
            'amount' => 150000,
            'frequency' => 'monthly',
            'day_of_month' => 5,
            'start_date' => '2026-09-01',
        ]);

        $response->assertRedirect(route('profile'));
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('recurring_transactions', [
            'user_id' => $user->id,
            'category_id' => $category->id,
            'amount' => 150000,
            'frequency' => 'monthly',
            'day_of_month' => 5,
            'type' => 'expense',
            'payment_method' => 'cash',
            'is_active' => true,
        ]);
    }

    public function test_monthly_recurring_transaction_requires_day_of_month(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->expense()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('recurring-transactions.store'), [
            'category_id' => $category->id,
            'amount' => 150000,
            'frequency' => 'monthly',
            'start_date' => '2026-09-01',
        ]);

        $response->assertSessionHasErrors('day_of_month');
    }

    public function test_weekly_recurring_transaction_requires_day_of_week(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->expense()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('recurring-transactions.store'), [
            'category_id' => $category->id,
            'amount' => 150000,
            'frequency' => 'weekly',
            'start_date' => '2026-09-01',
        ]);

        $response->assertSessionHasErrors('day_of_week');
    }

    public function test_user_can_update_own_recurring_transaction(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->expense()->create(['user_id' => $user->id]);
        $recurring = RecurringTransaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->put(route('recurring-transactions.update', $recurring), [
            'category_id' => $category->id,
            'amount' => 250000,
            'frequency' => 'monthly',
            'day_of_month' => 15,
            'start_date' => '2026-09-01',
            'is_active' => false,
        ]);

        $response->assertRedirect(route('profile'));
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('recurring_transactions', [
            'id' => $recurring->id,
            'amount' => 250000,
            'is_active' => false,
        ]);
    }

    public function test_user_cannot_update_others_recurring_transaction(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $category = Category::factory()->expense()->create(['user_id' => $otherUser->id]);
        $recurring = RecurringTransaction::factory()->create([
            'user_id' => $otherUser->id,
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->put(route('recurring-transactions.update', $recurring), [
            'category_id' => $category->id,
            'amount' => 250000,
            'frequency' => 'monthly',
            'day_of_month' => 15,
            'start_date' => '2026-09-01',
        ]);

        $response->assertForbidden();
    }

    public function test_user_can_delete_own_recurring_transaction(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->expense()->create(['user_id' => $user->id]);
        $recurring = RecurringTransaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->delete(route('recurring-transactions.destroy', $recurring));

        $response->assertRedirect(route('profile'));
        $this->assertDatabaseMissing('recurring_transactions', ['id' => $recurring->id]);
    }

    public function test_dashboard_shows_next_due_date_for_active_recurring_transactions(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->expense()->create(['user_id' => $user->id]);
        RecurringTransaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'frequency' => 'monthly',
            'day_of_month' => 20,
            'start_date' => '2026-01-01',
            'end_date' => null,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('recurringUpcoming', 1)
            ->where('recurringUpcoming.0.next_due_date', '2026-09-20')
        );
    }

    public function test_dashboard_excludes_inactive_recurring_transactions(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->expense()->create(['user_id' => $user->id]);
        RecurringTransaction::factory()->inactive()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('recurringUpcoming', 0)
        );
    }
}
