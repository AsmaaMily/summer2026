<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Ai\Tools\Request;
use Stringable;

class UpdateTransactions extends FinanceTool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Partially update one or more of the current user\'s existing transactions (bulk, up to 50 per call). '
            .'Only the fields present in each update change; omitted fields keep their current values. Transactions that '
            .'do not exist or belong to someone else are ignored and listed as not_found. When the user describes records '
            .'vaguely (e.g. "the coffee one"), call ListTransactions first to determine the exact ids, then update.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate(
            [
                'updates' => ['required', 'array', 'min:1', 'max:50'],
                'updates.*.id' => ['required', 'integer'],
                'updates.*.amount' => ['sometimes', 'integer', 'min:1'],
                'updates.*.type' => ['sometimes', 'in:income,expense'],
                'updates.*.date' => ['sometimes', 'date_format:Y-m-d'],
                'updates.*.category_id' => ['sometimes', 'integer'],
                'updates.*.category' => ['sometimes', 'string', 'max:100'],
                'updates.*.notes' => ['sometimes', 'nullable', 'string', 'max:500'],
                'updates.*.payment_method' => ['sometimes', 'in:cash,card'],
                'updates.*.is_recurring' => ['sometimes', 'boolean'],
            ],
            [
                'updates.max' => 'A single call may update at most 50 transactions. Split larger requests into multiple calls.',
                'updates.*.date.date_format' => 'Every date must be a real calendar date in exact YYYY-MM-DD format (e.g. 2026-09-12). Relative words like "today" are not accepted — resolve them to actual dates first.',
            ],
        );

        $categories = $this->availableCategories();

        $requestedIds = collect($validated['updates'])->pluck('id')->unique()->values();

        // Ownership is enforced inside the query itself; other users' ids are never visible.
        $existing = $this->user->transactions()
            ->with('category')
            ->whereIn('id', $requestedIds->all())
            ->get()
            ->keyBy('id');

        $notFound = $requestedIds->diff($existing->keys())->values()->all();

        $changesByTransaction = [];

        foreach ($validated['updates'] as $index => $update) {
            $transaction = $existing->get($update['id']);

            if ($transaction === null) {
                continue;
            }

            $effectiveType = $update['type'] ?? $transaction->type->value;
            $category = $transaction->category;
            $categoryChanged = false;

            if (array_key_exists('category_id', $update) && $update['category_id'] !== null) {
                $category = $categories->first(
                    fn ($candidate): bool => $candidate->id === (int) $update['category_id'],
                );

                if ($category === null) {
                    return $this->failure(sprintf(
                        'updates.%d.category_id [%s] does not exist or is not available to this user. Available categories are: %s.',
                        $index,
                        $update['category_id'],
                        $this->categoryOptions($categories),
                    ));
                }

                $categoryChanged = true;
            } elseif (array_key_exists('category', $update) && $update['category'] !== null) {
                $category = $this->resolveCategory((string) $update['category'], $categories);

                if ($category === null) {
                    return $this->failure(sprintf(
                        'updates.%d.category [%s] does not match any available category. Available categories are: %s.',
                        $index,
                        $update['category'],
                        $this->categoryOptions($categories),
                    ));
                }

                $categoryChanged = true;
            }

            if ($category !== null && $category->type->value !== $effectiveType) {
                return $this->failure(sprintf(
                    'updates.%d (transaction id %d) would use category "%s" (a %s category) with type "%s". Choose a category that matches the transaction type. Available categories are: %s.',
                    $index,
                    $transaction->id,
                    $category->name,
                    $category->type->value,
                    $effectiveType,
                    $this->categoryOptions($categories),
                ));
            }

            $changes = [];

            if (array_key_exists('amount', $update)) {
                $changes['amount'] = (int) $update['amount'];
            }

            if (array_key_exists('type', $update)) {
                $changes['type'] = $update['type'];
            }

            if (array_key_exists('date', $update)) {
                $changes['date'] = $update['date'];
            }

            if ($categoryChanged) {
                $changes['category_id'] = $category->id;
            }

            if (array_key_exists('notes', $update) && $update['notes'] !== null) {
                $changes['notes'] = $update['notes'];
            }

            if (array_key_exists('payment_method', $update)) {
                $changes['payment_method'] = $update['payment_method'];
            }

            if (array_key_exists('is_recurring', $update)) {
                $changes['is_recurring'] = (bool) $update['is_recurring'];
            }

            if ($changes === []) {
                return $this->failure(sprintf(
                    'updates.%d (transaction id %d) contains no updatable fields. Provide at least one of: amount, type, date, category_id/category, notes, payment_method, is_recurring.',
                    $index,
                    $transaction->id,
                ));
            }

            $changesByTransaction[$transaction->id] = $changes;
        }

        $updatedIds = array_keys($changesByTransaction);

        $updated = DB::transaction(function () use ($existing, $changesByTransaction) {
            return collect($changesByTransaction)
                ->map(function (array $changes, int $id) use ($existing) {
                    $transaction = $existing->get($id);
                    $transaction->update($changes);

                    return $transaction->refresh();
                })
                ->values();
        });

        $summary = sprintf(
            'Updated %d transaction(s): %s.%s',
            count($updatedIds),
            implode(', ', $updatedIds),
            $notFound === [] ? '' : sprintf(' Ignored %d id(s) not found for this user: %s.', count($notFound), implode(', ', $notFound)),
        );

        return $this->result(true, $summary, [
            'updated_ids' => $updatedIds,
            'transactions' => $this->transactionRows($updated),
            'not_found' => $notFound,
        ]);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'updates' => $schema->array()->min(1)->max(50)
                ->items($schema->object([
                    'id' => $schema->integer()->required()
                        ->description('Id of the transaction to update. Determine ids via ListTransactions when unsure.'),
                    'amount' => $schema->integer()->min(1)->nullable()
                        ->description('New amount in whole Saudi Riyals. Omit to keep unchanged.'),
                    'type' => $schema->string()->enum(['income', 'expense'])->nullable()
                        ->description('New transaction type. Omit to keep unchanged. The category must match the effective type.'),
                    'date' => $schema->string()->pattern('^\d{4}-\d{2}-\d{2}$')->nullable()
                        ->description('New date in exact YYYY-MM-DD format. Never send relative words like "today". Omit to keep unchanged.'),
                    'category_id' => $schema->integer()->nullable()
                        ->description('New category id from the user\'s available category list. Omit to keep unchanged.'),
                    'category' => $schema->string()->max(100)->nullable()
                        ->description('New exact category name (English or Arabic). Omit to keep unchanged.'),
                    'notes' => $schema->string()->max(500)->nullable()
                        ->description('New note. Omit to keep unchanged.'),
                    'payment_method' => $schema->string()->enum(['cash', 'card'])->nullable()
                        ->description('New payment method. Omit to keep unchanged.'),
                    'is_recurring' => $schema->boolean()->nullable()
                        ->description('New recurring flag. Omit to keep unchanged.'),
                ]))
                ->required()
                ->description('Partial updates to apply (1 to 50 per call). Only provided fields change.'),
        ];
    }
}
