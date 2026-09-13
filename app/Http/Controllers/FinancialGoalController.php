<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFinancialGoalRequest;
use App\Models\FinancialGoal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class FinancialGoalController extends Controller
{
    public function store(StoreFinancialGoalRequest $request): RedirectResponse
    {
        FinancialGoal::create([
            ...$request->validated(),
            'title' => $request->input('title_ar'),
            'user_id' => Auth::id(),
            'current_amount' => 0,
            'status' => 'active',
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Goal created.')]);

        return redirect()->route('budget');
    }

    public function update(Request $request, FinancialGoal $financialGoal): RedirectResponse
    {
        if ($financialGoal->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'title_ar' => ['required', 'string', 'max:200'],
            'target_amount' => ['required', 'integer', 'min:1'],
            'deadline' => ['nullable', 'date'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,completed,abandoned'],
        ]);

        $validated['title'] = $validated['title_ar'];

        $financialGoal->update($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Goal updated.')]);

        return redirect()->route('budget');
    }

    public function destroy(FinancialGoal $financialGoal): RedirectResponse
    {
        if ($financialGoal->user_id !== Auth::id()) {
            abort(403);
        }

        $financialGoal->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Goal deleted.')]);

        return redirect()->route('budget');
    }

    public function contribute(Request $request, FinancialGoal $financialGoal): RedirectResponse
    {
        if ($financialGoal->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
        ]);

        $financialGoal->increment('current_amount', $request->integer('amount'));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Contribution added.')]);

        return redirect()->route('budget');
    }
}
