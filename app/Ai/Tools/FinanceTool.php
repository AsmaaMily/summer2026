<?php

namespace App\Ai\Tools;

use App\Http\Requests\StoreTransactionRequest;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Laravel\Ai\Contracts\Tool;

abstract class FinanceTool implements Tool
{
    public function __construct(protected User $user) {}

    /**
     * Build the unified JSON payload returned by every finance tool.
     *
     * @param  array<string, mixed>  $data
     */
    protected function result(bool $ok, string $summary, array $data = []): string
    {
        return (string) json_encode(['ok' => $ok, 'summary' => $summary, ...$data], JSON_UNESCAPED_UNICODE);
    }

    /**
     * The transaction validation rules shared with the HTTP form requests.
     *
     * @return array<string, mixed>
     */
    protected function transactionRules(): array
    {
        return (new StoreTransactionRequest)->rules();
    }

    /**
     * Validate a single transaction payload using the shared form request rules.
     *
     * @param  array<int, string>|null  $only
     * @return array{0: bool, 1: array<int, string>, 2: array<string, mixed>}
     */
    protected function validateTransaction(array $item, ?array $only = null): array
    {
        $rules = $this->transactionRules();

        if ($only !== null) {
            $rules = array_intersect_key($rules, array_flip($only));
        }

        $validator = Validator::make($item, $rules);

        if ($validator->fails()) {
            return [false, $validator->errors()->all(), []];
        }

        return [true, [], $validator->validated()];
    }

    /**
     * Resolve the "category" / "category_id" arguments of a transaction payload
     * into a category ID owned by, or global to, the current user.
     *
     * @param  array<string, mixed>  $item
     * @return array{0: int|null, 1: string|null}
     */
    protected function normalizeCategory(array $item): array
    {
        $name = $item['category'] ?? null;
        $id = $item['category_id'] ?? null;

        if ($name !== null && ! is_string($name)) {
            return [null, 'قيمة category يجب أن تكون اسم تصنيف نصي'];
        }

        if ($id !== null && ! is_numeric($id)) {
            return [null, 'قيمة category_id يجب أن تكون رقماً'];
        }

        $resolved = $this->resolveCategoryId(
            $id !== null ? (int) $id : null,
            is_string($name) && $name !== '' ? $name : null,
        );

        if ($resolved === null) {
            return [null, 'تصنيف غير معروف. استخدم اسم تصنيف من القائمة المتاحة فقط'];
        }

        return [$resolved, null];
    }

    /**
     * Find a global or user-owned category by ID or exact (case-insensitive) name.
     */
    protected function resolveCategoryId(?int $id = null, ?string $name = null): ?int
    {
        $query = Category::query()->where(function ($query) {
            $query->whereNull('user_id')->orWhere('user_id', $this->user->id);
        });

        if ($id !== null) {
            return $query->where('id', $id)->value('id');
        }

        if ($name !== null) {
            $category = $query->where('name', $name)->first()
                ?? $query->whereRaw('lower(name) = ?', [mb_strtolower($name)])->first();

            return $category?->id;
        }

        return null;
    }

    /**
     * Strip the convenience keys from a payload after category normalization.
     *
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    protected function withoutCategoryKeys(array $item): array
    {
        return Arr::except($item, ['category', 'category_id']);
    }
}
