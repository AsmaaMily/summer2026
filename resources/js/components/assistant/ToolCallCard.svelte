<script module lang="ts">
    export interface ToolCallView {
        id: string;
        name: string;
        arguments?: Record<string, unknown>;
        status: 'running' | 'success' | 'failed';
        summary?: string;
        data?: unknown;
    }
</script>

<script lang="ts">
    import Check from 'lucide-svelte/icons/check';
    import ChevronDown from 'lucide-svelte/icons/chevron-down';
    import Eye from 'lucide-svelte/icons/eye';
    import Pencil from 'lucide-svelte/icons/pencil';
    import Plus from 'lucide-svelte/icons/plus';
    import Trash2 from 'lucide-svelte/icons/trash-2';
    import X from 'lucide-svelte/icons/x';
    import { Badge } from '@/components/ui/badge';
    import * as Collapsible from '@/components/ui/collapsible';
    import { Spinner } from '@/components/ui/spinner';

    let { call }: { call: ToolCallView } = $props();

    const META: Record<string, { label: string; icon: typeof Eye }> = {
        ListTransactions: { label: 'عرض العمليات', icon: Eye },
        CreateTransactions: { label: 'إضافة عمليات', icon: Plus },
        UpdateTransactions: { label: 'تعديل عمليات', icon: Pencil },
        DeleteTransactions: { label: 'حذف عمليات', icon: Trash2 },
    };

    let meta = $derived(META[call.name] ?? { label: call.name, icon: Eye });

    let statusLabel = $derived(
        call.status === 'running'
            ? 'جارٍ التنفيذ'
            : call.status === 'success'
              ? 'تم'
              : 'فشل',
    );

    const FILTER_LABELS: Record<string, string> = {
        date_from: 'من تاريخ',
        date_to: 'إلى تاريخ',
        type: 'النوع',
        category: 'التصنيف',
        min_amount: 'أدنى مبلغ',
        max_amount: 'أعلى مبلغ',
        search: 'بحث',
        sort: 'الترتيب',
        limit: 'الحد',
        amount: 'المبلغ',
        date: 'التاريخ',
        notes: 'الملاحظة',
    };

    const TYPE_LABELS: Record<string, string> = {
        expense: 'مصروف',
        income: 'دخل',
    };

    function formatValue(key: string, value: unknown): string {
        if (value === null || value === undefined || value === '') {
            return '—';
        }

        if (Array.isArray(value)) {
            return value.map((item) => formatValue(key, item)).join('، ');
        }

        if (typeof value === 'number') {
            return key.includes('amount') ? `${value} ر.س` : String(value);
        }

        const text = String(value);

        return TYPE_LABELS[text] ?? text;
    }

    let argumentLines = $derived.by(() => {
        const args = call.arguments ?? {};

        if (Array.isArray(args.transactions)) {
            return args.transactions.map((item) => {
                const tx = item as Record<string, unknown>;

                return [
                    `${formatValue('amount', tx.amount)}`,
                    tx.category ? String(tx.category) : null,
                    tx.date ? String(tx.date) : null,
                    tx.type
                        ? (TYPE_LABELS[String(tx.type)] ?? String(tx.type))
                        : null,
                    tx.notes ? String(tx.notes) : null,
                ]
                    .filter(Boolean)
                    .join(' · ');
            });
        }

        if (Array.isArray(args.updates)) {
            return args.updates.map((item) => {
                const update = item as Record<string, unknown>;
                const changes = Object.entries(update)
                    .filter(([key]) => key !== 'id')
                    .map(
                        ([key, value]) =>
                            `${FILTER_LABELS[key] ?? key}: ${formatValue(key, value)}`,
                    )
                    .join('، ');

                return `#${update.id} — ${changes || '—'}`;
            });
        }

        if (Array.isArray(args.ids)) {
            return [`العمليات: ${args.ids.map((id) => `#${id}`).join('، ')}`];
        }

        return Object.entries(args)
            .filter(
                ([, value]) =>
                    value !== null && value !== undefined && value !== '',
            )
            .map(
                ([key, value]) =>
                    `${FILTER_LABELS[key] ?? key}: ${formatValue(key, value)}`,
            );
    });

    interface ResultRow {
        label: string;
        value: string;
    }

    const RESULT_LABELS: Record<string, string> = {
        total_count: 'إجمالي النتائج',
        returned_count: 'النتائج المعروضة',
        sum_amount: 'المجموع',
        truncated: 'مقطوعة',
        created_count: 'عدد المُضافة',
        created_ids: 'المعرّفات المُضافة',
        updated_count: 'عدد المُحدّثة',
        deleted_count: 'عدد المحذوفة',
        deleted_ids: 'المعرّفات المحذوفة',
        not_found: 'غير موجودة',
    };

    let resultRows = $derived.by(() => {
        const data = call.data;

        if (!data || typeof data !== 'object') {
            return [];
        }

        const entries = Object.entries(data as Record<string, unknown>);

        const rows: ResultRow[] = [];

        for (const [key, value] of entries) {
            if (key === 'transactions' && Array.isArray(value)) {
                for (const raw of value) {
                    const tx = raw as Record<string, unknown>;

                    rows.push({
                        label: String(tx.date ?? ''),
                        value: [
                            `${formatValue('amount', tx.amount)}`,
                            tx.category ? String(tx.category) : 'بدون تصنيف',
                            tx.notes ? String(tx.notes) : null,
                        ]
                            .filter(Boolean)
                            .join(' · '),
                    });
                }

                continue;
            }

            if (key === 'updated' && Array.isArray(value)) {
                for (const raw of value) {
                    const update = raw as Record<string, unknown>;

                    rows.push({
                        label: `#${update.id}`,
                        value:
                            call.name === 'UpdateTransactions'
                                ? 'تم التحديث'
                                : '',
                    });
                }

                continue;
            }

            if (value === null || value === undefined) {
                continue;
            }

            if (key === 'truncated') {
                if (value === true) {
                    rows.push({ label: RESULT_LABELS[key], value: 'نعم' });
                }

                continue;
            }

            rows.push({
                label: RESULT_LABELS[key] ?? key,
                value: formatValue(key, value),
            });
        }

        return rows;
    });
</script>

<Collapsible.Root
    class="group/collapsible overflow-hidden rounded-xl border border-border/70 bg-card shadow-soft transition-colors
        {call.status === 'failed' ? 'border-destructive/30' : ''}"
>
    <Collapsible.Trigger
        class="flex w-full items-center gap-2.5 px-3 py-2.5 text-start text-sm transition-colors hover:bg-accent/40"
    >
        <span
            class="flex size-8 shrink-0 items-center justify-center rounded-lg transition-colors
                {call.status === 'running'
                ? 'bg-primary/10 text-primary'
                : call.status === 'success'
                  ? 'bg-income-muted text-income'
                  : 'bg-destructive/10 text-destructive'}"
            aria-hidden="true"
        >
            <meta.icon class="size-4" />
        </span>

        <span class="flex min-w-0 flex-1 flex-col gap-0.5">
            <span class="flex flex-wrap items-center gap-1.5">
                <span class="font-medium">{meta.label}</span>
                {#if call.status === 'running'}
                    <Badge
                        variant="outline"
                        class="gap-1 border-primary/30 text-primary"
                    >
                        <Spinner class="size-2.5" />
                        {statusLabel}
                    </Badge>
                {:else if call.status === 'success'}
                    <Badge
                        variant="secondary"
                        class="gap-1 bg-income-muted text-income"
                    >
                        <Check class="size-2.5" />
                        {statusLabel}
                    </Badge>
                {:else}
                    <Badge variant="destructive" class="gap-1">
                        <X class="size-2.5" />
                        {statusLabel}
                    </Badge>
                {/if}
            </span>
            {#if call.summary}
                <span class="truncate text-xs text-muted-foreground" dir="auto"
                    >{call.summary}</span
                >
            {/if}
        </span>

        <ChevronDown
            class="size-4 shrink-0 text-muted-foreground transition-transform duration-200 group-data-[state=open]/collapsible:rotate-180"
        />
    </Collapsible.Trigger>

    <Collapsible.Content>
        <div class="space-y-3 border-t border-border/70 px-3 py-3 text-xs">
            {#if argumentLines.length > 0}
                <div>
                    <p
                        class="mb-1.5 text-[0.7rem] font-semibold uppercase tracking-wide text-muted-foreground"
                    >
                        المعطيات
                    </p>
                    <ul class="space-y-1 tabular-nums" dir="auto">
                        {#each argumentLines as line, index (index)}
                            <li
                                class="rounded-lg bg-muted/60 px-2.5 py-1.5"
                                dir="auto"
                            >
                                {line}
                            </li>
                        {/each}
                    </ul>
                </div>
            {/if}

            {#if resultRows.length > 0}
                <div>
                    <p
                        class="mb-1.5 text-[0.7rem] font-semibold uppercase tracking-wide text-muted-foreground"
                    >
                        النتيجة
                    </p>
                    <dl class="space-y-1 tabular-nums">
                        {#each resultRows as row (row.label + '|' + row.value)}
                            <div
                                class="flex items-baseline justify-between gap-3 rounded-lg bg-muted/60 px-2.5 py-1.5"
                            >
                                <dt
                                    class="shrink-0 text-muted-foreground"
                                    dir="auto"
                                >
                                    {row.label}
                                </dt>
                                <dd class="text-end font-medium" dir="auto">
                                    {row.value}
                                </dd>
                            </div>
                        {/each}
                    </dl>
                </div>
            {/if}
        </div>
    </Collapsible.Content>
</Collapsible.Root>
