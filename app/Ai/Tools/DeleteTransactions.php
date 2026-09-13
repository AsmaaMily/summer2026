<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laravel\Ai\Tools\Request;
use Stringable;

class DeleteTransactions extends FinanceTool
{
    public const MAX_ITEMS = 50;

    public function description(): Stringable|string
    {
        return <<<'TXT'
        Use this tool to DELETE one or more of the current user's existing transactions by their numeric IDs. There is no "delete all" capability — explicit IDs are always required. Whenever the user describes the targets vaguely (e.g. "delete all my coffee expenses this week"), first call ListTransactions to resolve the exact IDs, then pass those IDs here. IDs that do not belong to the current user or no longer exist are ignored and reported in "not_found" rather than failing the whole call. Deletion is soft, so records can be restored later. All deletions are applied atomically.
        TXT;
    }

    public function handle(Request $request): Stringable|string
    {
        $validator = Validator::make($request->toArray(), [
            'ids' => ['required', 'array', 'min:1', 'max:'.self::MAX_ITEMS],
            'ids.*' => ['required', 'integer'],
        ]);

        if ($validator->fails()) {
            return $this->result(false, 'مدخلات غير صالحة: '.implode('؛ ', $validator->errors()->all()));
        }

        $ids = array_values(array_unique(array_map('intval', $validator->validated()['ids'])));

        $ownedIds = $this->user->transactions()
            ->whereKey($ids)
            ->pluck('id')
            ->all();

        if ($ownedIds === []) {
            return $this->result(false, 'لم يتم العثور على أي من العمليات المطلوبة', [
                'data' => ['not_found' => $ids],
            ]);
        }

        DB::transaction(fn () => $this->user->transactions()->whereIn('id', $ownedIds)->delete());

        $notFound = array_values(array_diff($ids, $ownedIds));

        $summary = sprintf('حُذفت %d عملية', count($ownedIds));

        if ($notFound !== []) {
            $summary .= sprintf(' — وتجاهل %d غير موجودة', count($notFound));
        }

        return $this->result(true, $summary, [
            'data' => [
                'deleted_count' => count($ownedIds),
                'deleted_ids' => array_map('intval', $ownedIds),
                'not_found' => $notFound,
            ],
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'ids' => $schema->array()
                ->min(1)
                ->max(self::MAX_ITEMS)
                ->items($schema->integer())
                ->required()
                ->description('أرقام العمليات المطلوب حذفها — استخدم ListTransactions أولاً لتحديدها'),
        ];
    }
}
