<?php

namespace Tests\Feature;

use App\Models\FinancialGoal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class FinancialGoalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::create(2026, 9, 13));
    }

    public function test_user_can_create_goal(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('financial-goals.store'), [
            'title_ar' => 'صندوق الطوارئ',
            'target_amount' => 1000000,
            'deadline' => '2027-12-31',
            'description' => 'Save for emergencies',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('financial_goals', [
            'user_id' => $user->id,
            'title' => 'صندوق الطوارئ',
            'title_ar' => 'صندوق الطوارئ',
            'target_amount' => 1000000,
            'current_amount' => 0,
            'status' => 'active',
        ]);
    }

    public function test_user_can_update_own_goal(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 9, 13));

        $user = User::factory()->create();
        $goal = FinancialGoal::factory()->create(['user_id' => $user->id]);

        $this->withoutExceptionHandling();
        $response = $this->actingAs($user)->put(route('financial-goals.update', $goal), [
            'title_ar' => 'هدف محدث',
            'target_amount' => 2000000,
            'status' => 'active',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('financial_goals', [
            'id' => $goal->id,
            'title' => 'هدف محدث',
            'target_amount' => 2000000,
        ]);
    }

    public function test_user_can_delete_own_goal(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 9, 13));

        $user = User::factory()->create();
        $goal = FinancialGoal::factory()->create(['user_id' => $user->id]);

        $this->withoutExceptionHandling();
        $response = $this->actingAs($user)->delete(route('financial-goals.destroy', $goal));

        $response->assertRedirect();
        $this->assertDatabaseMissing('financial_goals', ['id' => $goal->id]);
    }

    public function test_user_can_contribute_to_goal(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 9, 13));

        $user = User::factory()->create();
        $goal = FinancialGoal::factory()->create([
            'user_id' => $user->id,
            'current_amount' => 10000,
        ]);

        $this->withoutExceptionHandling();
        $response = $this->actingAs($user)->post(route('financial-goals.contribute', $goal), [
            'amount' => 5000,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('financial_goals', [
            'id' => $goal->id,
            'current_amount' => 15000,
        ]);
    }
}
