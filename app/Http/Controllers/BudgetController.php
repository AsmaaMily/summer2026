<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBudgetRequest;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class BudgetController extends Controller
{
    public function index(Request $request): Response
    {
        $user = Auth::user();
        $currentMonth = $request->get('month', Carbon::now()->format('Y-m'));

        $budgets = Budget::where('user_id', $user->id)
            ->where('month', $currentMonth)
            ->with('category')
            ->get();

        $goals = $user->financialGoals()
            ->orderBy('deadline')
            ->get();

        $categories = Category::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
                ->orWhereNull('user_id');
        })->where('type', 'expense')
            ->orderBy('sort_order')
            ->get();

        $totalBudget = $budgets->sum('amount');

        $totalSpent = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereMonth('date', Carbon::parse($currentMonth)->month)
            ->whereYear('date', Carbon::parse($currentMonth)->year)
            ->sum('amount');

        return Inertia::render('BudgetGoals', [
            'budgets' => $budgets,
            'goals' => $goals,
            'categories' => $categories,
            'totalBudget' => $totalBudget,
            'totalSpent' => $totalSpent,
            'currentMonth' => $currentMonth,
        ]);
    }

    public function store(StoreBudgetRequest $request): RedirectResponse
    {
        $user = Auth::user();

        Budget::updateOrCreate(
            [
                'user_id' => $user->id,
                'category_id' => $request->validated('category_id'),
                'month' => $request->validated('month'),
            ],
            ['amount' => $request->validated('amount')],
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Budget saved.')]);

        return redirect()->route('budget');
    }

    public function update(Request $request, Budget $budget): RedirectResponse
    {
        if ($budget->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
        ]);

        $budget->update($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Budget updated.')]);

        return redirect()->route('budget');
    }

    public function destroy(Budget $budget): RedirectResponse
    {
        if ($budget->user_id !== Auth::id()) {
            abort(403);
        }

        $budget->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Budget deleted.')]);

        return redirect()->route('budget');
    }
}
