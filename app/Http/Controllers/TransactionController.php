<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class TransactionController extends Controller
{
    public function index(Request $request): Response
    {
        $user = Auth::user();

        $query = Transaction::where('user_id', $user->id)->with('category');

        if ($search = $request->get('search')) {
            $query->where('notes', 'like', "%{$search}%");
        }

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        if ($categoryId = $request->get('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($paymentMethod = $request->get('payment_method')) {
            $query->where('payment_method', $paymentMethod);
        }

        if ($dateFrom = $request->get('date_from')) {
            $query->where('date', '>=', $dateFrom);
        }

        if ($dateTo = $request->get('date_to')) {
            $query->where('date', '<=', $dateTo);
        }

        $transactions = $query->orderByDesc('date')->paginate(15)->withQueryString();

        $categories = Category::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)->orWhereNull('user_id');
        })->orderBy('sort_order')->get();

        return Inertia::render('Transactions', [
            'transactions' => $transactions,
            'categories' => $categories,
            'filters' => $request->only(['search', 'type', 'category_id', 'payment_method', 'date_from', 'date_to']),
        ]);
    }

    public function store(StoreTransactionRequest $request): RedirectResponse
    {
        Transaction::create([
            ...$request->validated(),
            'user_id' => Auth::id(),
            'source' => 'manual',
            'payment_method' => $request->validated('payment_method') ?? 'cash',
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Transaction created.')]);

        return redirect()->route('transactions');
    }

    public function update(UpdateTransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        if ($transaction->user_id !== Auth::id()) {
            abort(403);
        }

        $data = $request->validated();
        $data['payment_method'] = $data['payment_method'] ?? 'cash';

        $transaction->update($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Transaction updated.')]);

        return redirect()->route('transactions');
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        if ($transaction->user_id !== Auth::id()) {
            abort(403);
        }

        $transaction->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Transaction deleted.')]);

        return redirect()->route('transactions');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:transactions,id'],
        ]);

        Transaction::where('user_id', Auth::id())
            ->whereIn('id', $request->ids)
            ->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Transactions deleted.')]);

        return redirect()->route('transactions');
    }
}
