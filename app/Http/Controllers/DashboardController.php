<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = Auth::user();
        $selectedMonth = $request->get('month', Carbon::now()->format('Y-m'));
        $monthStart = Carbon::parse($selectedMonth)->startOfMonth()->format('Y-m-d');
        $monthEnd = Carbon::parse($selectedMonth)->endOfMonth()->format('Y-m-d');

        $transactionsQuery = Transaction::where('transactions.user_id', $user->id)
            ->whereBetween('transactions.date', [$monthStart, $monthEnd]);

        $totalIncome = (clone $transactionsQuery)
            ->where('transactions.type', 'income')
            ->sum('transactions.amount');

        $totalExpenses = (clone $transactionsQuery)
            ->where('transactions.type', 'expense')
            ->sum('transactions.amount');

        $categoryBreakdown = Transaction::where('transactions.user_id', $user->id)
            ->whereBetween('transactions.date', [$monthStart, $monthEnd])
            ->where('transactions.type', 'expense')
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->groupBy('transactions.category_id', 'categories.name', 'categories.name_ar', 'categories.color')
            ->select([
                'transactions.category_id',
                'categories.name as category_name',
                'categories.name_ar as category_name_ar',
                'categories.color',
                DB::raw('SUM(transactions.amount) as amount'),
            ])
            ->get()
            ->map(fn ($row) => [
                'category_id' => $row->category_id,
                'category_name' => $row->category_name,
                'category_name_ar' => $row->category_name_ar,
                'color' => $row->color,
                'amount' => (int) $row->amount,
            ])
            ->values()
            ->toArray();

        $recentTransactions = Transaction::where('user_id', $user->id)
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->with('category')
            ->orderByDesc('date')
            ->limit(10)
            ->get();

        $recurringUpcoming = RecurringTransaction::where('user_id', $user->id)
            ->where('is_active', true)
            ->with('category')
            ->get();

        $categories = Category::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)->orWhereNull('user_id');
        })->orderBy('sort_order')->get();

        return Inertia::render('Dashboard', [
            'netBalance' => $totalIncome - $totalExpenses,
            'totalIncome' => $totalIncome,
            'totalExpenses' => $totalExpenses,
            'categoryBreakdown' => $categoryBreakdown,
            'recentTransactions' => $recentTransactions,
            'recurringUpcoming' => $recurringUpcoming,
            'selectedMonth' => $selectedMonth,
            'categories' => $categories,
        ]);
    }
}
