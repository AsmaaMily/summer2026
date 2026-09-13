<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::create(2026, 9, 13));
    }

    public function test_authenticated_user_can_view_transactions(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('transactions'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Transactions')
            ->has('transactions')
            ->has('categories')
            ->has('filters')
        );
    }

    public function test_unauthenticated_user_cannot_view_transactions(): void
    {
        $response = $this->get(route('transactions'));

        $response->assertRedirect(route('login'));
    }

    public function test_user_can_create_transaction(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($user)->post(route('transactions.store'), [
            'amount' => 5000,
            'category_id' => $category->id,
            'type' => 'expense',
            'date' => '2026-09-01',
            'notes' => 'Test transaction',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'amount' => 5000,
            'category_id' => $category->id,
            'type' => 'expense',
            'payment_method' => 'cash',
        ]);
    }

    public function test_user_can_update_own_transaction(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $transaction = Transaction::factory()->create(['user_id' => $user->id, 'category_id' => $category->id]);

        $response = $this->actingAs($user)->put(route('transactions.update', $transaction), [
            'amount' => 7500,
            'category_id' => $category->id,
            'type' => 'expense',
            'date' => '2026-09-01',
            'notes' => 'Updated',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'amount' => 7500,
        ]);
    }

    public function test_user_cannot_update_others_transaction(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $category = Category::factory()->create();
        $transaction = Transaction::factory()->create(['user_id' => $otherUser->id, 'category_id' => $category->id]);

        $response = $this->actingAs($user)->put(route('transactions.update', $transaction), [
            'amount' => 9999,
            'category_id' => $category->id,
            'type' => 'expense',
            'date' => '2026-09-01',
        ]);

        $response->assertForbidden();
    }

    public function test_user_can_delete_own_transaction(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $transaction = Transaction::factory()->create(['user_id' => $user->id, 'category_id' => $category->id]);

        $response = $this->actingAs($user)->delete(route('transactions.destroy', $transaction));

        $response->assertRedirect();
        $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
    }

    public function test_user_cannot_delete_others_transaction(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $category = Category::factory()->create();
        $transaction = Transaction::factory()->create(['user_id' => $otherUser->id, 'category_id' => $category->id]);

        $response = $this->actingAs($user)->delete(route('transactions.destroy', $transaction));

        $response->assertForbidden();
        $this->assertDatabaseHas('transactions', ['id' => $transaction->id]);
    }

    public function test_user_can_bulk_delete_transactions(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $transactions = Transaction::factory(3)->create(['user_id' => $user->id, 'category_id' => $category->id]);

        $ids = $transactions->pluck('id')->toArray();

        $response = $this->actingAs($user)->post(route('transactions.bulk-destroy'), [
            'ids' => $ids,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        foreach ($ids as $id) {
            $this->assertDatabaseMissing('transactions', ['id' => $id]);
        }
    }

    public function test_transactions_are_filtered_by_type(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        Transaction::factory()->income()->create(['user_id' => $user->id, 'category_id' => $category->id]);
        Transaction::factory(3)->expense()->create(['user_id' => $user->id, 'category_id' => $category->id]);

        $response = $this->actingAs($user)->get(route('transactions', ['type' => 'income']));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Transactions')
            ->has('transactions.data', 1)
        );
    }

    public function test_transactions_are_filtered_by_category(): void
    {
        $user = User::factory()->create();
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();

        Transaction::factory(2)->create(['user_id' => $user->id, 'category_id' => $category1->id]);
        Transaction::factory(3)->create(['user_id' => $user->id, 'category_id' => $category2->id]);

        $response = $this->actingAs($user)->get(route('transactions', ['category_id' => $category1->id]));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Transactions')
            ->has('transactions.data', 2)
        );
    }

    public function test_transactions_are_paginated(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        Transaction::factory(20)->create(['user_id' => $user->id, 'category_id' => $category->id]);

        $response = $this->actingAs($user)->get(route('transactions'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Transactions')
            ->has('transactions.data', 15)
            ->where('transactions.total', 20)
        );
    }
}
