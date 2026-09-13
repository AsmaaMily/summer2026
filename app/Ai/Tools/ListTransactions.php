<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Laravel\Ai\Tools\Request;
use Stringable;

class ListTransactions extends FinanceTool
{
    public function description(): Stringable|string
    {
        return <<<'TXT'
        Use this tool to READ the current user's transactions before answering any question about spending, income, or specific records. It supports filtering by date range, type, category name(s), amount range, and free-text search in the notes, plus sorting and a result limit. It returns compact rows (id, date, type, category, amount, notes) along with total_count and sum_amount, which is enough to answer questions like "how much did I spend on food last month" directly. Do NOT use this tool to create, update, or delete transactions. ALWAYS call this tool to resolve exact transaction IDs before updating or deleting records described vaguely by the user (e.g. "the coffee one").
        TXT;
    }

    public function handle(Request $request): Stringable|string
    {
        $validator = Validator::make($request->toArray(), [
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'type' => ['nullable', 'string', 'in:expense,income'],
            'category' => ['nullable'],
            'min_amount' => ['nullable', 'numeric', 'min:0'],
            'max_amount' => ['nullable', 'numeric', 'min:0'],
            'search' => ['nullable', 'string', 'max:200'],
            'sort' => ['nullable', 'string', 'in:date_desc,date_asc,amount_desc,amount_asc'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        if ($validator->fails()) {
            return $this->result(false, 'مدخلات غير صالحة: '.implode('؛ ', $validator->errors()->all()));
        }

        $filters = $validator->validated();

        $categoryIds = null;

        if (array_key_exists('category', $filters) && $filters['category'] !== null) {
            $names = is_array($filters['category']) ? $filters['category'] : [$filters['category']];
            $categoryIds = [];

            foreach ($names as $name) {
                $resolved = is_numeric($name)
                    ? $this->resolveCategoryId((int) $name)
                    : $this->resolveCategoryId(name: (string) $name);

                if ($resolved === null) {
                    return $this->result(false, "تصنيف غير معروف: {$name}. استخدم أسماء التصنيفات المتاحة فقط");
                }

                $categoryIds[] = $resolved;
            }
        }

        $query = $this->user->transactions()->with('category');

        if (! empty($filters['date_from'])) {
            $query->where('date', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            // Exclusive upper bound: SQLite stores dates as "Y-m-d 00:00:00", so an
            // inclusive "<= Y-m-d" comparison would miss the boundary day itself.
            $query->where('date', '<', Carbon::parse($filters['date_to'])->addDay()->toDateString());
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if ($categoryIds !== null) {
            $query->whereIn('category_id', $categoryIds);
        }

        if (isset($filters['min_amount'])) {
            $query->where('amount', '>=', $filters['min_amount']);
        }

        if (isset($filters['max_amount'])) {
            $query->where('amount', '<=', $filters['max_amount']);
        }

        if (! empty($filters['search'])) {
            $query->where('notes', 'like', '%'.str_replace(['%', '_'], ['\%', '\_'], $filters['search']).'%');
        }

        [$column, $direction] = match ($filters['sort'] ?? 'date_desc') {
            'date_asc' => ['date', 'asc'],
            'amount_desc' => ['amount', 'desc'],
            'amount_asc' => ['amount', 'asc'],
            default => ['date', 'desc'],
        };

        $limit = (int) ($filters['limit'] ?? 25);

        $totalCount = (clone $query)->count();
        $sumAmount = (clone $query)->sum('amount');

        $transactions = $query
            ->orderBy($column, $direction)
            ->orderBy('id', $direction)
            ->limit($limit)
            ->get()
            ->map(fn ($transaction) => [
                'id' => $transaction->id,
                'date' => $transaction->date->format('Y-m-d'),
                'type' => $transaction->type,
                'category' => $transaction->category?->name,
                'amount' => (float) $transaction->amount,
                'notes' => $transaction->notes,
            ])
            ->values();

        $truncated = $totalCount > $transactions->count();

        $summary = "عدد النتائج المطابقة: {$totalCount}";

        if (! empty($filters['type'])) {
            $typeLabel = $filters['type'] === 'expense' ? 'المصروفات' : 'الدخل';
            $summary .= sprintf(' — مجموع %s: %s ر.س', $typeLabel, number_format((float) $sumAmount, 2));
        }

        if ($truncated) {
            $summary .= sprintf(' — تُعرض أول %d نتيجة فقط، النتائج مقطوعة', $transactions->count());
        }

        return $this->result(true, $summary, [
            'data' => [
                'transactions' => $transactions,
                'total_count' => $totalCount,
                'returned_count' => $transactions->count(),
                'sum_amount' => (float) $sumAmount,
                'truncated' => $truncated,
            ],
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'date_from' => $schema->string()->description('تاريخ البداية شامل، بصيغة Y-m-d')->format('date'),
            'date_to' => $schema->string()->description('تاريخ النهاية شامل، بصيغة Y-m-d')->format('date'),
            'type' => $schema->string()->enum(['expense', 'income'])->description('نوع العملية'),
            'category' => $schema->anyOf([
                $schema->string(),
                $schema->array()->items($schema->string()),
            ])->description('اسم تصنيف أو أكثر بالضبط كما يظهر في قائمة التصنيفات'),
            'min_amount' => $schema->number()->min(0)->description('أدنى مبلغ'),
            'max_amount' => $schema->number()->min(0)->description('أعلى مبلغ'),
            'search' => $schema->string()->max(200)->description('بحث نصي في وصف العملية'),
            'sort' => $schema->string()->enum(['date_desc', 'date_asc', 'amount_desc', 'amount_asc'])->description('الترتيب، الافتراضي date_desc'),
            'limit' => $schema->integer()->min(1)->max(100)->description('عدد النتائج المعروضة، الافتراضي 25'),
        ];
    }
}
