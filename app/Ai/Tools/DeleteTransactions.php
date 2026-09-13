<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Ai\Tools\Request;
use Stringable;

class DeleteTransactions extends FinanceTool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Permanently delete one or more of the current user\'s transactions by explicit ids (bulk, up to 50 per call). '
            .'Deletion is irreversible. Ids that do not exist or belong to someone else are ignored and listed as not_found. '
            .'When the user describes records vaguely (e.g. "delete the coffee ones this week"), call ListTransactions first, '
            .'show or reason about the exact records, then delete the confirmed ids. There is no way to delete all transactions '
            .'in one call — explicit ids are always required.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate(
            [
                'ids' => ['required', 'array', 'min:1', 'max:50'],
                'ids.*' => ['required', 'integer'],
            ],
            [
                'ids.max' => 'A single call may delete at most 50 transactions. Split larger requests into multiple calls.',
                'ids.min' => 'At least one transaction id is required. There is no delete-all operation.',
            ],
        );

        $requestedIds = collect($validated['ids'])
            ->filter(fn ($id): bool => $id !== null)
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values();

        if ($requestedIds->isEmpty()) {
            return $this->failure('At least one valid transaction id is required.');
        }

        // Ownership is enforced inside the query itself; other users' ids are never visible.
        $existing = $this->user->transactions()
            ->with('category')
            ->whereIn('id', $requestedIds->all())
            ->get();

        $notFound = $requestedIds->diff($existing->modelKeys())->values()->all();

        $deletedRows = $this->transactionRows($existing);
        $deletedIds = $existing->modelKeys();
        $deletedTotal = (int) $existing->sum('amount');

        DB::transaction(function () use ($deletedIds): void {
            $this->user->transactions()->whereIn('id', $deletedIds)->delete();
        });

        $summary = sprintf(
            'Deleted %d transaction(s) (ids: %s, total %d SAR).%s',
            count($deletedIds),
            implode(', ', $deletedIds),
            $deletedTotal,
            $notFound === [] ? '' : sprintf(' Ignored %d id(s) not found for this user: %s.', count($notFound), implode(', ', $notFound)),
        );

        return $this->result(true, $summary, [
            'deleted_count' => count($deletedIds),
            'deleted_ids' => $deletedIds,
            'deleted_total' => $deletedTotal,
            'deleted' => $deletedRows,
            'not_found' => $notFound,
        ]);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'ids' => $schema->array()->min(1)->max(50)
                ->items($schema->integer())
                ->required()
                ->description('The ids of the transactions to delete (1 to 50 per call). Determine ids via ListTransactions when the user describes records vaguely.'),
        ];
    }
}
