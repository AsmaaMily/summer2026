<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Laravel\Ai\Tools\Request;
use Stringable;

class UpdateTransactions extends FinanceTool
{
    public const MAX_ITEMS = 50;

    public function description(): Stringable|string
    {
        return <<<'TXT'
        Use this tool to PARTIALLY update one or more of the current user's existing transactions. Each item must contain the numeric "id" of a transaction plus only the fields to change (amount, date, type, category, notes); fields that are not sent remain untouched. Resolve exact IDs first by calling ListTransactions whenever the user refers to records vaguely (e.g. "the last grocery one") or by notes — never guess IDs. IDs that do not belong to the current user or no longer exist are ignored and reported in "not_found" rather than failing the whole call. All updates are applied atomically.
        TXT;
    }

    public function handle(Request $request): Stringable|string
    {
        $updates = $request['updates'];

        if (! is_array($updates) || $updates === []) {
            return $this->result(false, 'مدخلات غير صالحة: updates مطلوبة كمصفوفة تحتوي عنصراً واحداً على الأقل');
        }

        if (count($updates) > self::MAX_ITEMS) {
            return $this->result(false, sprintf('الحد الأقصى %d عملية في الاستدعاء الواحد. قسّم الطلب إلى دفعات', self::MAX_ITEMS));
        }

        $prepared = [];

        foreach (array_values($updates) as $index => $update) {
            if (! is_array($update)) {
                return $this->result(false, sprintf('العنصر رقم %d غير صالح', $index + 1));
            }

            $id = $update['id'] ?? null;

            if (! is_numeric($id)) {
                return $this->result(false, sprintf('العنصر رقم %d: id مطلوب ويجب أن يكون رقماً', $index + 1));
            }

            $hasCategoryKey = array_key_exists('category', $update) || array_key_exists('category_id', $update);

            if ($hasCategoryKey) {
                [$categoryId, $categoryError] = $this->normalizeCategory($update);

                if ($categoryError !== null) {
                    return $this->result(false, sprintf('العنصر رقم %d: %s', $index + 1, $categoryError));
                }

                $update['category_id'] = $categoryId;
            }

            $fields = array_intersect_key(
                Arr::except($update, 'category'),
                array_flip(['amount', 'notes', 'date', 'type', 'category_id']),
            );

            if ($fields === []) {
                return $this->result(false, sprintf('العنصر رقم %d: لا توجد حقول للتحديث', $index + 1));
            }

            [$valid, $errors, $validated] = $this->validateTransaction($fields, array_keys($fields));

            if (! $valid) {
                return $this->result(false, sprintf('فشل التحقق في العنصر رقم %d (id=%s): %s', $index + 1, $id, implode('؛ ', $errors)));
            }

            $prepared[(int) $id] = $validated;
        }

        $owned = $this->user->transactions()
            ->whereKey(array_keys($prepared))
            ->get()
            ->keyBy('id');

        $updated = [];
        $notFound = [];

        DB::transaction(function () use (&$updated, &$notFound, $prepared, $owned) {
            foreach ($prepared as $id => $fields) {
                $transaction = $owned->get($id);

                if ($transaction === null) {
                    $notFound[] = $id;

                    continue;
                }

                $transaction->update($fields);

                $updated[] = ['id' => $id, 'changes' => $fields];
            }
        });

        if ($updated === []) {
            return $this->result(false, 'لم يتم العثور على أي من العمليات المطلوبة', [
                'data' => ['not_found' => $notFound],
            ]);
        }

        $summary = sprintf('تم تحديث %d عملية', count($updated));

        if ($notFound !== []) {
            $summary .= sprintf(' — وتجاهل %d غير موجودة', count($notFound));
        }

        return $this->result(true, $summary, [
            'data' => [
                'updated' => $updated,
                'updated_count' => count($updated),
                'not_found' => $notFound,
            ],
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'updates' => $schema->array()
                ->min(1)
                ->max(self::MAX_ITEMS)
                ->items($schema->object([
                    'id' => $schema->integer()->required()->description('رقم العملية الموجودة'),
                    'amount' => $schema->integer()->min(1)->description('المبلغ الجديد بالريال السعودي، رقم صحيح'),
                    'date' => $schema->string()->description('التاريخ الجديد بصيغة Y-m-d'),
                    'type' => $schema->string()->enum(['expense', 'income'])->description('النوع الجديد'),
                    'category' => $schema->string()->description('اسم التصنيف الجديد كما يظهر في قائمة التصنيفات'),
                    'notes' => $schema->string()->max(500)->description('الملاحظة الجديدة'),
                ]))
                ->required()
                ->description('التحديثات المطلوبة — أرسل الحقول المراد تغييرها فقط'),
        ];
    }
}
