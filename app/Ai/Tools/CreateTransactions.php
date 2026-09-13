<?php

namespace App\Ai\Tools;

use App\TransactionSource;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Ai\Tools\Request;
use Stringable;

class CreateTransactions extends FinanceTool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Create one or more transactions for the current user (bulk). Accepts up to 50 transactions in a single call. '
            .'Dates must be exact YYYY-MM-DD strings; never send relative words like "today". Categories must come from the '
            .'user\'s available category list, given as category_id or the exact category name. Only use this tool when the '
            .'user has provided all essential details (amount, category, and a resolvable date); otherwise ask first.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate(
            [
                'transactions' => ['required', 'array', 'min:1', 'max:50'],
                'transactions.*.amount' => ['required', 'integer', 'min:1'],
                'transactions.*.type' => ['required', 'in:income,expense'],
                'transactions.*.date' => ['required', 'date_format:Y-m-d'],
                'transactions.*.category_id' => ['nullable', 'integer'],
                'transactions.*.category' => ['nullable', 'string', 'max:100'],
                'transactions.*.notes' => ['nullable', 'string', 'max:500'],
                'transactions.*.payment_method' => ['nullable', 'in:cash,card'],
                'transactions.*.is_recurring' => ['nullable', 'boolean'],
            ],
            [
                'transactions.max' => 'A single call may create at most 50 transactions. Split larger requests into multiple calls.',
                'transactions.*.date.date_format' => 'Every transaction date must be a real calendar date in exact YYYY-MM-DD format (e.g. 2026-09-12). Relative words like "today" or "yesterday" are not accepted — resolve them to actual dates first.',
            ],
        );

        $categories = $this->availableCategories();

        $prepared = [];

        foreach ($validated['transactions'] as $index => $item) {
            $category = null;

            if (array_key_exists('category_id', $item) && $item['category_id'] !== null) {
                $category = $categories->first(
                    fn ($candidate): bool => $candidate->id === (int) $item['category_id'],
                );

                if ($category === null) {
                    return $this->failure(sprintf(
                        'transactions.%d.category_id [%s] does not exist or is not available to this user. Available categories are: %s.',
                        $index,
                        $item['category_id'],
                        $this->categoryOptions($categories),
                    ));
                }
            } elseif (array_key_exists('category', $item) && $item['category'] !== null) {
                $category = $this->resolveCategory((string) $item['category'], $categories);

                if ($category === null) {
                    return $this->failure(sprintf(
                        'transactions.%d.category [%s] does not match any available category. Available categories are: %s.',
                        $index,
                        $item['category'],
                        $this->categoryOptions($categories),
                    ));
                }
            } else {
                return $this->failure(sprintf(
                    'transactions.%d is missing a category. Provide category_id or category. Available categories are: %s.',
                    $index,
                    $this->categoryOptions($categories),
                ));
            }

            if ($category->type->value !== $item['type']) {
                return $this->failure(sprintf(
                    'transactions.%d uses category "%s" which is a %s category, but the transaction type is "%s". Choose a category that matches the transaction type. Available categories are: %s.',
                    $index,
                    $category->name,
                    $category->type->value,
                    $item['type'],
                    $this->categoryOptions($categories),
                ));
            }

            $prepared[] = [
                'category_id' => $category->id,
                'amount' => (int) $item['amount'],
                'type' => $item['type'],
                'date' => $item['date'],
                'notes' => $item['notes'] ?? null,
                'payment_method' => $item['payment_method'] ?? 'cash',
                'is_recurring' => (bool) ($item['is_recurring'] ?? false),
                'source' => TransactionSource::Ai->value,
            ];
        }

        $created = DB::transaction(function () use ($prepared) {
            return collect($prepared)
                ->map(fn (array $attributes) => $this->user->transactions()->create($attributes))
                ->values();
        });

        $ids = $created->map(fn ($transaction): int => $transaction->id)->all();

        $summary = sprintf('Created %d transaction(s) with id(s): %s.', count($ids), implode(', ', $ids));

        return $this->result(true, $summary, [
            'created_ids' => $ids,
            'transactions' => $this->transactionRows($created),
        ]);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'transactions' => $schema->array()->min(1)->max(50)
                ->items($schema->object([
                    'amount' => $schema->integer()->min(1)->required()
                        ->description('Amount in whole Saudi Riyals (integer, no decimals).'),
                    'type' => $schema->string()->enum(['income', 'expense'])->required()
                        ->description('Transaction type.'),
                    'date' => $schema->string()->pattern('^\d{4}-\d{2}-\d{2}$')->required()
                        ->description('Transaction date in exact YYYY-MM-DD format (e.g. 2026-09-12). Never send relative words like "today" — resolve them to real dates first.'),
                    'category_id' => $schema->integer()->nullable()
                        ->description('Category id from the user\'s available category list.'),
                    'category' => $schema->string()->max(100)->nullable()
                        ->description('Exact category name (English or Arabic) from the user\'s available category list. Used only when category_id is not given.'),
                    'notes' => $schema->string()->max(500)->nullable()
                        ->description('Optional note describing the transaction.'),
                    'payment_method' => $schema->string()->enum(['cash', 'card'])->nullable()
                        ->description('Payment method. Defaults to cash when omitted.'),
                    'is_recurring' => $schema->boolean()->nullable()
                        ->description('Whether the transaction repeats regularly. Defaults to false.'),
                ]))
                ->required()
                ->description('The transactions to create (1 to 50 per call).'),
        ];
    }
}
