<?php

namespace App\Http\Controllers;

use App\Models\RecurringTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class RecurringTransactionController extends Controller
{
    public function index(): Response
    {
        $recurringTransactions = RecurringTransaction::where('user_id', Auth::id())
            ->with('category')
            ->orderByDesc('start_date')
            ->get();

        return Inertia::render('RecurringTransactions', [
            'recurringTransactions' => $recurringTransactions,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'amount' => ['required', 'integer', 'min:1'],
            'type' => ['required', 'in:income,expense'],
            'frequency' => ['required', 'in:daily,weekly,monthly,yearly'],
            'day_of_month' => ['nullable', 'integer', 'min:1', 'max:31'],
            'day_of_week' => ['nullable', 'integer', 'min:0', 'max:6'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'notes' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['required', 'in:cash,card'],
        ]);

        RecurringTransaction::create([
            ...$validated,
            'user_id' => Auth::id(),
            'is_active' => true,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Recurring transaction created.')]);

        return redirect()->route('budget');
    }

    public function update(Request $request, RecurringTransaction $recurringTransaction): RedirectResponse
    {
        if ($recurringTransaction->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'amount' => ['required', 'integer', 'min:1'],
            'type' => ['required', 'in:income,expense'],
            'frequency' => ['required', 'in:daily,weekly,monthly,yearly'],
            'day_of_month' => ['nullable', 'integer', 'min:1', 'max:31'],
            'day_of_week' => ['nullable', 'integer', 'min:0', 'max:6'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'notes' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['required', 'in:cash,card'],
            'is_active' => ['boolean'],
        ]);

        $recurringTransaction->update($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Recurring transaction updated.')]);

        return redirect()->route('budget');
    }

    public function destroy(RecurringTransaction $recurringTransaction): RedirectResponse
    {
        if ($recurringTransaction->user_id !== Auth::id()) {
            abort(403);
        }

        $recurringTransaction->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Recurring transaction deleted.')]);

        return redirect()->route('budget');
    }
}
