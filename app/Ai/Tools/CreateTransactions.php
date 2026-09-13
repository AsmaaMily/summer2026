<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Ai\Tools\Request;
use Stringable;

class CreateTransactions extends FinanceTool
{
    public const MAX_ITEMS = 50;

    public function description(): Stringable|string
    {
        return <<<'TXT'
        Use this tool to CREATE one or more transactions (expenses or income) for the current user. The tool is bulk-native: when the user wants several transactions added, pass them all as items of a single "transactions" array instead of making multiple calls. Each item requires: type ("expense" or "income"), amount (a whole number of Saudi Riyals, at least 1), date (Y-m-d), and category (exact name from the category list); notes is optional. The whole batch is inserted atomically — if any item is invalid, nothing is created. Returns the IDs of the created records. Do NOT use this tool to modify existing transactions. If the user's request is missing a required value (most commonly the amount), ask them instead of guessing.
        TXT;
    }

    public function handle(Request $request): Stringable|string
    {
        $items = $request['transactions'];

        if (! is_array($items) || $items === []) {
            return $this->result(false, 'مدخلات غير صالحة: transactions مطلوبة كمصفوفة تحتوي عنصراً واحداً على الأقل');
        }

        if (count($items) > self::MAX_ITEMS) {
            return $this->result(false, sprintf('الحد الأقصى %d عملية في الاستدعاء الواحد. قسّم الطلب إلى دفعات', self::MAX_ITEMS));
        }

        $prepared = [];

        foreach (array_values($items) as $index => $item) {
            if (! is_array($item)) {
                return $this->result(false, sprintf('العنصر رقم %d غير صالح', $index + 1));
            }

            [$categoryId, $categoryError] = $this->normalizeCategory($item);

            if ($categoryError !== null) {
                return $this->result(false, sprintf('العنصر رقم %d: %s', $index + 1, $categoryError));
            }

            [$valid, $errors, $validated] = $this->validateTransaction([
                ...$this->withoutCategoryKeys($item),
                'category_id' => $categoryId,
            ]);

            if (! $valid) {
                return $this->result(false, sprintf('فشل التحقق في العنصر رقم %d: %s', $index + 1, implode('؛ ', $errors)));
            }

            $prepared[] = [
                ...$validated,
                'payment_method' => $validated['payment_method'] ?? 'cash',
                'source' => 'ai',
            ];
        }

        $created = DB::transaction(fn () => $this->user->transactions()->createMany($prepared));

        return $this->result(true, sprintf('أضيفت %d عملية بنجاح', $created->count()), [
            'data' => [
                'created_count' => $created->count(),
                'created_ids' => $created->pluck('id')->values(),
            ],
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'transactions' => $schema->array()
                ->min(1)
                ->max(self::MAX_ITEMS)
                ->items($schema->object([
                    'type' => $schema->string()->enum(['expense', 'income'])->required()->description('نوع العملية'),
                    'amount' => $schema->integer()->min(1)->required()->description('المبلغ بالريال السعودي، رقم صحيح بدون كسور'),
                    'date' => $schema->string()->required()->description('تاريخ العملية بصيغة Y-m-d'),
                    'category' => $schema->string()->required()->description('اسم التصنيف بالضبط كما يظهر في قائمة التصنيفات'),
                    'notes' => $schema->string()->max(500)->description('ملاحظة اختيارية للعملية'),
                ]))
                ->required()
                ->description('العمليات المطلوب إضافتها'),
        ];
    }
}
