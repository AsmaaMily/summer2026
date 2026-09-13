<?php

namespace App\Concerns;

use Illuminate\Contracts\Validation\ValidationRule;

trait TransactionValidationRules
{
    /**
     * Get the validation rules used to validate transactions.
     *
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function transactionRules(): array
    {
        return [
            'amount' => ['required', 'integer', 'min:1'],
            'category_id' => ['required', 'exists:categories,id'],
            'type' => ['required', 'in:income,expense'],
            'date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['nullable', 'in:cash,card'],
            'is_recurring' => ['boolean'],
        ];
    }
}
