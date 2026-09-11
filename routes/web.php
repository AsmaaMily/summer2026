<?php

use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::inertia('/', 'Onboarding')->name('home');
});

Route::middleware(['auth'])->group(function () {
    Route::redirect('/', '/dashboard');
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    Route::inertia('transactions', 'Transactions')->name('transactions');
    Route::inertia('profile', 'Profile')->name('profile');
    Route::inertia('ai-assistant', 'AiAssistant')->name('ai-assistant');
    Route::inertia('budget', 'BudgetGoals')->name('budget');
    Route::inertia('hello', 'hello')->name('hello');
});

require __DIR__.'/settings.php';
