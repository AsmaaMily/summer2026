<?php

namespace App\Ai\Tools;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Ai\Contracts\Tool;

/**
 * Base class for the finance assistant tools.
 *
 * Every tool is hard-scoped to the user received via the constructor. Tools
 * must never query transactions outside the user's own relation, and IDs of
 * other users are silently ignored and reported as "not_found".
 */
abstract class FinanceTool implements Tool
{
    public function __construct(protected User $user) {}

    /**
     * Build the unified JSON result every tool returns to the model.
     *
     * @param  array<string, mixed>  $data
     */
    protected function result(bool $ok, string $summary, array $data = []): string
    {
        return (string) json_encode([
            'ok' => $ok,
            'summary' => $summary,
            'data' => $data,
        ]);
    }

    /**
     * Build the unified JSON error result for invalid or unresolvable input.
     */
    protected function failure(string $message): string
    {
        return $this->result(false, $message);
    }

    /**
     * The categories the user may assign: their own plus system defaults.
     *
     * @return Collection<int, Category>
     */
    protected function availableCategories(): Collection
    {
        return Category::query()
            ->where(function ($query): void {
                $query->where('user_id', $this->user->id)->orWhereNull('user_id');
            })
            ->where('is_archived', false)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Resolve a category given as an id, English name, or Arabic name.
     */
    protected function resolveCategory(string $value, Collection $categories): ?Category
    {
        if (ctype_digit($value)) {
            return $categories->first(fn (Category $category): bool => $category->id === (int) $value);
        }

        $normalized = Str::lower(trim($value));

        return $categories->first(fn (Category $category): bool => Str::lower($category->name) === $normalized
            || Str::lower($category->name_ar) === $normalized);
    }

    /**
     * A human-readable list of the available categories for error messages.
     */
    protected function categoryOptions(Collection $categories): string
    {
        return $categories
            ->map(fn (Category $category): string => sprintf('%d (%s / %s, %s)', $category->id, $category->name, $category->name_ar, $category->type->value))
            ->implode(', ');
    }

    /**
     * Compact, token-efficient representation of transactions for tool results.
     *
     * @param  Collection<int, Transaction>  $transactions
     * @return array<int, array<string, mixed>>
     */
    protected function transactionRows(Collection $transactions): array
    {
        return $transactions
            ->map(fn (Transaction $transaction): array => [
                'id' => $transaction->id,
                'date' => $transaction->date instanceof \DateTimeInterface
                    ? $transaction->date->format('Y-m-d')
                    : (string) $transaction->date,
                'type' => $transaction->type->value,
                'category' => $transaction->category?->name,
                'category_ar' => $transaction->category?->name_ar,
                'amount' => $transaction->amount,
                'notes' => $transaction->notes,
                'payment_method' => $transaction->payment_method->value,
            ])
            ->values()
            ->all();
    }
}
