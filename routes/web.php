<?php

use App\Http\Controllers\AssistantStreamController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\FinancialGoalController;
use App\Http\Controllers\ProfileDataController;
use App\Http\Controllers\RecurringTransactionController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::inertia('/', 'hello')->name('hello');
});

Route::middleware(['auth'])->group(function () {
    Route::redirect('/', '/dashboard');

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('transactions', [TransactionController::class, 'index'])->name('transactions');
    Route::post('transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::put('transactions/{transaction}', [TransactionController::class, 'update'])->name('transactions.update');
    Route::delete('transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');
    Route::post('transactions/bulk-destroy', [TransactionController::class, 'bulkDestroy'])->name('transactions.bulk-destroy');

    Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    Route::get('budget', [BudgetController::class, 'index'])->name('budget');
    Route::post('budgets', [BudgetController::class, 'store'])->name('budgets.store');
    Route::put('budgets/{budget}', [BudgetController::class, 'update'])->name('budgets.update');
    Route::delete('budgets/{budget}', [BudgetController::class, 'destroy'])->name('budgets.destroy');

    Route::post('financial-goals', [FinancialGoalController::class, 'store'])->name('financial-goals.store');
    Route::put('financial-goals/{financialGoal}', [FinancialGoalController::class, 'update'])->name('financial-goals.update');
    Route::delete('financial-goals/{financialGoal}', [FinancialGoalController::class, 'destroy'])->name('financial-goals.destroy');
    Route::post('financial-goals/{financialGoal}/contribute', [FinancialGoalController::class, 'contribute'])->name('financial-goals.contribute');

    Route::get('recurring-transactions', [RecurringTransactionController::class, 'index'])->name('recurring-transactions.index');
    Route::post('recurring-transactions', [RecurringTransactionController::class, 'store'])->name('recurring-transactions.store');
    Route::put('recurring-transactions/{recurringTransaction}', [RecurringTransactionController::class, 'update'])->name('recurring-transactions.update');
    Route::delete('recurring-transactions/{recurringTransaction}', [RecurringTransactionController::class, 'destroy'])->name('recurring-transactions.destroy');

    Route::get('profile', [ProfileDataController::class, 'index'])->name('profile');
    Route::put('profile/salary', [ProfileDataController::class, 'updateSalary'])->name('profile.update-salary');

    Route::get('export/csv', [ExportController::class, 'csv'])->name('export.csv');

    Route::inertia('ai-assistant', 'AiAssistant')->name('ai-assistant');
    Route::post('ai-assistant/stream', AssistantStreamController::class)->name('ai-assistant.stream');
});

require __DIR__.'/settings.php';
