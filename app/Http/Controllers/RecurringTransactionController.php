<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRecurringTransactionRequest;
use App\Http\Requests\UpdateRecurringTransactionRequest;
use App\Models\RecurringTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class RecurringTransactionController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('profile');
    }

    public function store(StoreRecurringTransactionRequest $request): RedirectResponse
    {
        RecurringTransaction::create([
            ...$request->validated(),
            'user_id' => Auth::id(),
            'type' => 'expense',
            'payment_method' => 'cash',
            'is_active' => true,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Recurring transaction created.')]);

        return redirect()->route('profile');
    }

    public function update(UpdateRecurringTransactionRequest $request, RecurringTransaction $recurringTransaction): RedirectResponse
    {
        if ($recurringTransaction->user_id !== Auth::id()) {
            abort(403);
        }

        $recurringTransaction->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Recurring transaction updated.')]);

        return redirect()->route('profile');
    }

    public function destroy(RecurringTransaction $recurringTransaction): RedirectResponse
    {
        if ($recurringTransaction->user_id !== Auth::id()) {
            abort(403);
        }

        $recurringTransaction->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Recurring transaction deleted.')]);

        return redirect()->route('profile');
    }
}
