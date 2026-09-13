<script module lang="ts">
    import BackgroundLayout from '@/layouts/BackgroundLayout.svelte';
    export const layout = BackgroundLayout;
</script>

<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import {
        Plus,
        Wallet,
        Lightbulb,
        ReceiptText,
        CalendarClock,
        ArrowLeftRight,
        CreditCard,
        Banknote,
    } from 'lucide-svelte';
    import AppHead from '@/components/AppHead.svelte';
    import TransactionFormModal from '@/components/TransactionFormModal.svelte';
    import { Button } from '@/components/ui/button';
    import {
        Select,
        SelectContent,
        SelectItem,
        SelectTrigger,
    } from '@/components/ui/select';
    import { formatAmount, formatDate } from '@/lib/utils';
    import type { DashboardProps, Category } from '@/types';

    let {
        netBalance = 0,
        totalIncome = 0,
        totalExpenses = 0,
        categoryBreakdown = [],
        recentTransactions = [],
        recurringUpcoming = [],
        selectedMonth = '',
        categories = [],
    }: DashboardProps & { categories: Category[] } = $props();

    let modalOpen = $state(false);

    const months = [
        { value: '2026-01', label: 'يناير 2026' },
        { value: '2026-02', label: 'فبراير 2026' },
        { value: '2026-03', label: 'مارس 2026' },
        { value: '2026-04', label: 'أبريل 2026' },
        { value: '2026-05', label: 'مايو 2026' },
        { value: '2026-06', label: 'يونيو 2026' },
        { value: '2026-07', label: 'يوليو 2026' },
        { value: '2026-08', label: 'أغسطس 2026' },
        { value: '2026-09', label: 'سبتمبر 2026' },
        { value: '2026-10', label: 'أكتوبر 2026' },
        { value: '2026-11', label: 'نوفمبر 2026' },
        { value: '2026-12', label: 'ديسمبر 2026' },
    ];

    const maxCategoryAmount = $derived(
        categoryBreakdown.length > 0
            ? Math.max(...categoryBreakdown.map((c) => c.amount))
            : 1,
    );

    function handleMonthChange(value: string) {
        router.visit('/dashboard', {
            data: { month: value },
            preserveState: true,
        });
    }

    const frequencyLabels: Record<string, string> = {
        daily: 'يومي',
        weekly: 'أسبوعي',
        monthly: 'شهري',
        yearly: 'سنوي',
    };
</script>

<AppHead title="الرئيسية" />

<div class="mx-auto w-full max-w-5xl p-4 sm:p-8">
    <div class="flex h-full flex-1 flex-col gap-6">

        <div class="flex items-center justify-between mb-2">
            <h1 class="text-xl font-bold md:text-2xl text-slate-800 dark:text-white drop-shadow-sm">لوحة التحكم</h1>
            <div class="flex items-center gap-3">
                <Select value={selectedMonth} onValueChange={handleMonthChange} type="single">
                    <SelectTrigger class="w-[150px] md:w-[180px] bg-white/50 backdrop-blur-md border-white/30 focus:ring-[#8C8DD8] dark:bg-slate-800/50">
                        <span>
                            {months.find((m) => m.value === selectedMonth)?.label ?? 'اختر الشهر'}
                        </span>
                    </SelectTrigger>
                    <SelectContent>
                        {#each months as month (month.value)}
                            <SelectItem value={month.value}>{month.label}</SelectItem>
                        {/each}
                    </SelectContent>
                </Select>
                <Button size="sm" onclick={() => modalOpen = true} class="md:size-default border-none bg-gradient-to-r from-[#3c3f98] via-[#6b6ec6] to-[#6b6ec6] text-white transition-opacity hover:opacity-90 shadow-lg">
                    <Plus class="ml-2 size-4" />
                    <span class="hidden md:inline">إضافة معاملة</span>
                </Button>
            </div>
        </div>

        <div class="flex items-center justify-between rounded-2xl border border-white/30 bg-white/50 p-6 shadow-2xl backdrop-blur-xl dark:border-slate-700/50 dark:bg-slate-900/60 sm:p-8">
            <div class="flex items-center gap-3">
                <Wallet class="size-6 text-[#8C8DD8]" />
                <h2 class="text-lg font-bold text-white drop-shadow-md">صافي الرصيد</h2>
            </div>
            <div class="text-2xl font-extrabold {netBalance >= 0 ? 'text-white' : 'text-rose-400'}">
                {formatAmount(netBalance)} ر.س
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

            <div class="flex flex-col gap-4 rounded-2xl border border-white/30 bg-white/50 p-6 shadow-2xl backdrop-blur-xl dark:border-slate-700/50 dark:bg-slate-900/60 sm:p-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <ReceiptText class="size-5 text-[#8C8DD8]" />
                        <h2 class="text-base font-bold text-slate-800 dark:text-white">المصروفات حسب الفئة</h2>
                    </div>
                </div>
                {#if categoryBreakdown.length > 0}
                    <div class="flex flex-col gap-3 max-h-[200px] overflow-y-auto">
                        {#each categoryBreakdown as item (item.category_id)}
                            <div class="flex flex-col gap-1">
                                <div class="flex items-center justify-between text-sm">
                                    <div class="flex items-center gap-2">
                                        {#if item.color}
                                            <span class="inline-block size-3 rounded-full" style="background-color: {item.color}"></span>
                                        {/if}
                                        <span class="text-slate-700 dark:text-slate-300">{item.category_name_ar}</span>
                                    </div>
                                    <span class="font-medium text-slate-800 dark:text-white">{formatAmount(item.amount)} ر.س</span>
                                </div>
                                <div class="h-2 w-full rounded-full bg-slate-200/50 dark:bg-slate-700/50">
                                    <div
                                        class="h-2 rounded-full transition-all"
                                        style="width: {(item.amount / maxCategoryAmount) * 100}%; background-color: {item.color ?? '#8C8DD8'}"
                                    ></div>
                                </div>
                            </div>
                        {/each}
                    </div>
                {:else}
                    <div class="flex h-[180px] items-center justify-center rounded-xl border border-dashed border-slate-400/50 bg-slate-100/30 dark:bg-slate-800/30">
                        <p class="text-sm text-slate-500">لا توجد مصروفات لهذا الشهر</p>
                    </div>
                {/if}
            </div>

            <div class="flex flex-col gap-4 rounded-2xl border border-white/30 bg-white/50 p-6 shadow-2xl backdrop-blur-xl dark:border-slate-700/50 dark:bg-slate-900/60 sm:p-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <CalendarClock class="size-5 text-[#8C8DD8]" />
                        <h2 class="text-base font-bold text-slate-800 dark:text-white">الفواتير الدورية</h2>
                    </div>
                </div>
                {#if recurringUpcoming.length > 0}
                    <div class="flex flex-col gap-3 max-h-[200px] overflow-y-auto">
                        {#each recurringUpcoming as item (item.id)}
                            <div class="flex items-center justify-between rounded-xl border border-white/20 bg-white/30 p-3 dark:border-slate-700/30 dark:bg-slate-800/30">
                                <div class="flex flex-col gap-1">
                                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{item.category.name_ar}</span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">
                                        {frequencyLabels[item.frequency] ?? item.frequency}
                                        {#if item.next_due_date}
                                            · الاستحقاق: {formatDate(item.next_due_date)}
                                        {/if}
                                    </span>
                                </div>
                                <span class="text-sm font-bold {item.type === 'income' ? 'text-emerald-300' : 'text-rose-400'}">
                                    {item.type === 'income' ? '+' : '-'}{formatAmount(item.amount)} ر.س
                                </span>
                            </div>
                        {/each}
                    </div>
                {:else}
                    <div class="flex h-[180px] items-center justify-center rounded-xl border border-dashed border-slate-400/50 bg-slate-100/30 dark:bg-slate-800/30">
                        <p class="text-sm text-slate-500">لا توجد فواتير قادمة</p>
                    </div>
                {/if}
            </div>

        </div>

        <div class="flex flex-col gap-4 rounded-2xl border border-white/30 bg-white/50 p-6 shadow-2xl backdrop-blur-xl dark:border-slate-700/50 dark:bg-slate-900/60 sm:p-8">
            <div class="flex items-center gap-2">
                <ArrowLeftRight class="size-5 text-[#8C8DD8]" />
                <h2 class="text-base font-bold text-slate-800 dark:text-white">آخر المعاملات</h2>
            </div>
            {#if recentTransactions.length > 0}
                <div class="flex flex-col gap-2">
                    {#each recentTransactions as t (t.id)}
                        <div class="flex items-center justify-between rounded-xl border border-white/20 bg-white/30 p-3 dark:border-slate-700/30 dark:bg-slate-800/30">
                            <div class="flex items-center gap-3">
                                {#if t.category.color}
                                    <span class="inline-block size-3 rounded-full" style="background-color: {t.category.color}"></span>
                                {/if}
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{t.category.name_ar}</span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">{formatDate(t.date)}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-xs text-slate-500 dark:text-slate-400">
                                    {#if t.payment_method === 'cash'}
                                        <Banknote class="inline size-3 ml-1" />
                                        نقدي
                                    {:else}
                                        <CreditCard class="inline size-3 ml-1" />
                                        بطاقة
                                    {/if}
                                </span>
                                <span class="text-sm font-bold {t.type === 'income' ? 'text-emerald-300' : 'text-rose-400'}">
                                    {t.type === 'income' ? '+' : '-'}{formatAmount(t.amount)} ر.س
                                </span>
                            </div>
                        </div>
                    {/each}
                </div>
            {:else}
                <div class="flex h-[200px] items-center justify-center rounded-xl border border-dashed border-slate-400/50 bg-slate-100/30 dark:bg-slate-800/30">
                    <p class="text-sm text-slate-500">لا توجد معاملات بعد</p>
                </div>
            {/if}
        </div>

        <div class="flex flex-col gap-4 rounded-2xl border border-white/10 bg-gradient-to-r from-[#53589A]/80 to-[#2B3070]/40 p-6 shadow-2xl backdrop-blur-xl sm:p-8">
            <div class="flex items-center gap-2">
                <Lightbulb class="size-5 text-[#8C8DD8]" />
                <h2 class="text-base font-bold text-white drop-shadow-sm">رؤى الذكاء الاصطناعي</h2>
            </div>
            <div class="flex h-[120px] items-center justify-center rounded-xl border border-dashed border-white/20 bg-white/5">
                <p class="text-sm text-slate-300">ستظهر الرؤى هنا بناءً على أنماط صرفك</p>
            </div>
        </div>

    </div>
</div>

<TransactionFormModal bind:open={modalOpen} transaction={null} {categories} />
