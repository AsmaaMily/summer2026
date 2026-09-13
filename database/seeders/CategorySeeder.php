<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $expenseCategories = [
            ['name' => 'Food', 'name_ar' => 'طعام', 'icon' => 'utensils', 'color' => '#EF4444', 'sort_order' => 1],
            ['name' => 'Transport', 'name_ar' => 'مواصلات', 'icon' => 'car', 'color' => '#3B82F6', 'sort_order' => 2],
            ['name' => 'Housing', 'name_ar' => 'سكن', 'icon' => 'home', 'color' => '#8B5CF6', 'sort_order' => 3],
            ['name' => 'Entertainment', 'name_ar' => 'ترفيه', 'icon' => 'gamepad', 'color' => '#F59E0B', 'sort_order' => 4],
            ['name' => 'Health', 'name_ar' => 'صحة', 'icon' => 'heart-pulse', 'color' => '#EC4899', 'sort_order' => 5],
            ['name' => 'Education', 'name_ar' => 'تعليم', 'icon' => 'graduation-cap', 'color' => '#06B6D4', 'sort_order' => 6],
            ['name' => 'Shopping', 'name_ar' => 'تسوق', 'icon' => 'shopping-bag', 'color' => '#10B981', 'sort_order' => 7],
            ['name' => 'Utilities', 'name_ar' => 'فواتير', 'icon' => 'bolt', 'color' => '#6366F1', 'sort_order' => 8],
            ['name' => 'Other', 'name_ar' => 'أخرى', 'icon' => 'ellipsis', 'color' => '#6B7280', 'sort_order' => 9],
        ];

        $incomeCategories = [
            ['name' => 'Salary', 'name_ar' => 'راتب', 'icon' => 'wallet', 'color' => '#22C55E', 'sort_order' => 1],
            ['name' => 'Other', 'name_ar' => 'أخرى', 'icon' => 'ellipsis', 'color' => '#6B7280', 'sort_order' => 2],
        ];

        foreach ($expenseCategories as $category) {
            Category::create([
                'user_id' => null,
                'name' => $category['name'],
                'name_ar' => $category['name_ar'],
                'type' => 'expense',
                'icon' => $category['icon'],
                'color' => $category['color'],
                'sort_order' => $category['sort_order'],
                'is_default' => true,
                'is_archived' => false,
            ]);
        }

        foreach ($incomeCategories as $category) {
            Category::create([
                'user_id' => null,
                'name' => $category['name'],
                'name_ar' => $category['name_ar'],
                'type' => 'income',
                'icon' => $category['icon'],
                'color' => $category['color'],
                'sort_order' => $category['sort_order'],
                'is_default' => true,
                'is_archived' => false,
            ]);
        }
    }
}
