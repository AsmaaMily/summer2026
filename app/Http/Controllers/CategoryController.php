<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $maxOrder = Category::max('sort_order') ?? 0;

        Category::create([
            ...$request->validated(),
            'user_id' => Auth::id(),
            'sort_order' => $maxOrder + 1,
            'is_default' => false,
            'is_archived' => false,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Category created.')]);

        return redirect()->route('profile');
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        if ($category->is_default || ($category->user_id !== Auth::id() && $category->user_id !== null)) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'name_ar' => ['required', 'string', 'max:100'],
            'icon' => ['nullable', 'string'],
            'color' => ['nullable', 'string'],
        ]);

        $category->update($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Category updated.')]);

        return redirect()->route('profile');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->is_default || $category->user_id !== Auth::id()) {
            abort(403);
        }

        $category->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Category deleted.')]);

        return redirect()->route('profile');
    }
}
