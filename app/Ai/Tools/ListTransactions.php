<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Carbon;
use Laravel\Ai\Tools\Request;
use Stringable;

class ListTransactions extends FinanceTool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'List the current user\'s transactions with optional filters (date range, type, category, amount range, text search). '
            .'Use this whenever you need to inspect, count, or sum the user\'s transactions, including before editing or deleting '
            .'records described vaguely by the user. The result includes the matching total count and amount sum, even when only '
            .'a limited page of rows is returned. Do not use this tool to change data.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate(
            [
                'date_from' => ['nullable', 'date_format:Y-m-d'],
                'date_to' => ['nullable', 'date_format:Y-m-d'],
                'type' => ['nullable', 'in:income,expense'],
                'category' => ['nullable'],
                'min_amount' => ['nullable', 'numeric', 'min:0'],
                'max_amount' => ['nullable', 'numeric', 'min:0'],
                'search' => ['nullable', 'string', 'max:255'],
                'sort' => ['nullable', 'in:date_desc,date_asc,amount_desc,amount_asc'],
                'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
            ],
            [
                'date_from.date_format' => 'The date_from must be a real calendar date in exact YYYY-MM-DD format (e.g. 2026-09-12). Relative words like "today" or "last week" are not accepted.',
                'date_to.date_format' => 'The date_to must be a real calendar date in exact YYYY-MM-DD format (e.g. 2026-09-12). Relative words like "today" or "last week" are not accepted.',
            ],
        );

        $categoryInput = $validated['category'] ?? null;

        if ($categoryInput !== null && ! is_string($categoryInput) && ! is_array($categoryInput)) {
            return $this->failure('The category filter must be a category name (string) or an array of category names.');
        }

        $categoryNames = collect(is_array($categoryInput) ? $categoryInput : [$categoryInput])
            ->filter()
            ->map(fn (string $value): string => trim($value))
            ->values();

        $categories = $this->availableCategories();
        $categoryIds = $categoryNames
            ->map(fn (string $name): ?int => $this->resolveCategory($name, $categories)?->id)
            ->filter()
            ->values();

        if ($categoryNames->count() !== $categoryIds->count()) {
            $unresolved = $categoryNames->diff($categories->flatMap(
                fn ($category): array => [$category->name, $category->name_ar],
            ))->implode(', ');

            return $this->failure(sprintf(
                'Unknown category: [%s]. Available categories are: %s.',
                $unresolved,
                $this->categoryOptions($categories),
            ));
        }

        $minAmount = $validated['min_amount'] ?? null;
        $maxAmount = $validated['max_amount'] ?? null;

        if ($minAmount !== null && $maxAmount !== null && (float) $minAmount > (float) $maxAmount) {
            return $this->failure('The min_amount must be less than or equal to max_amount.');
        }

        $query = $this->user->transactions()->with('category');

        if ($validated['date_from'] ?? null) {
            $query->where('date', '>=', $validated['date_from']);
        }

        if ($validated['date_to'] ?? null) {
            // Exclusive upper bound: SQLite stores dates as "Y-m-d 00:00:00", so an
            // inclusive "<= Y-m-d" comparison would miss the boundary day itself.
            $query->where('date', '<', Carbon::parse($validated['date_to'])->addDay()->toDateString());
        }

        if ($validated['type'] ?? null) {
            $query->where('type', $validated['type']);
        }

        if ($categoryIds->isNotEmpty()) {
            $query->whereIn('category_id', $categoryIds->all());
        }

        if ($minAmount !== null) {
            $query->where('amount', '>=', (int) $minAmount);
        }

        if ($maxAmount !== null) {
            $query->where('amount', '<=', (int) $maxAmount);
        }

        if ($validated['search'] ?? null) {
            $query->where('notes', 'like', '%'.str_replace('%', '\%', $validated['search']).'%');
        }

        $totalCount = (clone $query)->count();
        $sumAmount = (int) (clone $query)->sum('amount');

        $query->orderBy(match ($validated['sort'] ?? 'date_desc') {
            'date_asc' => 'date',
            'amount_desc' => 'amount',
            'amount_asc' => 'amount',
            default => 'date',
        }, in_array($validated['sort'] ?? 'date_desc', ['date_asc', 'amount_asc'], true) ? 'asc' : 'desc');

        $limit = (int) ($validated['limit'] ?? 25);
        $transactions = $query->limit($limit)->get();

        $truncated = $totalCount > $transactions->count();

        $summary = sprintf(
            'Found %d matching transaction(s), total amount %d SAR. Returned %d row(s)%s.',
            $totalCount,
            $sumAmount,
            $transactions->count(),
            $truncated ? sprintf(' (more available beyond the limit of %d)', $limit) : '',
        );

        return $this->result(true, $summary, [
            'transactions' => $this->transactionRows($transactions),
            'total_count' => $totalCount,
            'sum_amount' => $sumAmount,
            'limit' => $limit,
            'truncated' => $truncated,
        ]);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'date_from' => $schema->string()->pattern('^\d{4}-\d{2}-\d{2}$')->nullable()
                ->description('Inclusive lower date bound in exact YYYY-MM-DD format. Never use relative words like "today".'),
            'date_to' => $schema->string()->pattern('^\d{4}-\d{2}-\d{2}$')->nullable()
                ->description('Inclusive upper date bound in exact YYYY-MM-DD format. Never use relative words like "today".'),
            'type' => $schema->string()->enum(['income', 'expense'])->nullable()
                ->description('Filter by transaction type.'),
            'category' => $schema->union(['string', 'array'])->nullable()
                ->description('Filter by a single category name or an array of category names (English or Arabic names from the category list, or a category id).'),
            'min_amount' => $schema->integer()->min(0)->nullable()
                ->description('Inclusive minimum amount in SAR (whole riyals).'),
            'max_amount' => $schema->integer()->min(0)->nullable()
                ->description('Inclusive maximum amount in SAR (whole riyals).'),
            'search' => $schema->string()->max(255)->nullable()
                ->description('Free-text search over transaction notes.'),
            'sort' => $schema->string()->enum(['date_desc', 'date_asc', 'amount_desc', 'amount_asc'])->nullable()
                ->description('Result ordering. Defaults to date_desc (newest first).'),
            'limit' => $schema->integer()->min(1)->max(100)->nullable()
                ->description('Maximum rows to return (1-100). Defaults to 25. The totals always cover every matching row.'),
        ];
    }
}
