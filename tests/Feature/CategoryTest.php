<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::create(2026, 9, 13));
    }

    public function test_user_can_create_custom_category(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('categories.store'), [
            'name' => 'Custom Category',
            'name_ar' => 'فئة مخصصة',
            'type' => 'expense',
            'icon' => 'lucide:coffee',
            'color' => '#FF5733',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('categories', [
            'user_id' => $user->id,
            'name' => 'Custom Category',
            'name_ar' => 'فئة مخصصة',
            'type' => 'expense',
            'is_default' => false,
        ]);
    }

    public function test_user_can_update_own_category(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->put(route('categories.update', $category), [
            'name' => 'Updated Name',
            'name_ar' => 'اسم محدث',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Name',
            'name_ar' => 'اسم محدث',
        ]);
    }

    public function test_user_cannot_delete_default_category(): void
    {
        $user = User::factory()->create();
        $defaultCategory = Category::factory()->default()->create();

        $response = $this->actingAs($user)->delete(route('categories.destroy', $defaultCategory));

        $response->assertForbidden();
        $this->assertDatabaseHas('categories', ['id' => $defaultCategory->id]);
    }

    public function test_user_can_delete_own_category(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete(route('categories.destroy', $category));

        $response->assertRedirect();
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }
}
