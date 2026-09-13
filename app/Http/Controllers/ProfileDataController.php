<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProfileDataController extends Controller
{
    public function index(Request $request): Response
    {
        $user = Auth::user();

        $expenseCategories = Category::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)->orWhereNull('user_id');
        })->where('type', 'expense')
            ->orderBy('sort_order')
            ->get();

        $incomeCategories = Category::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)->orWhereNull('user_id');
        })->where('type', 'income')
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Profile', [
            'expenseCategories' => $expenseCategories,
            'incomeCategories' => $incomeCategories,
            'monthlySalary' => $user->monthly_salary,
        ]);
    }

    public function updateSalary(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'monthly_salary' => ['required', 'integer', 'min:0'],
        ]);

        Auth::user()->update(['monthly_salary' => $validated['monthly_salary']]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Salary updated.')]);

        return redirect()->route('profile');
    }
}
