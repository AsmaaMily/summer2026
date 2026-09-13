<script module lang="ts">
    import BackgroundLayout from '@/layouts/BackgroundLayout.svelte';
    export const layout = BackgroundLayout;
</script>

<script lang="ts">
    import {
        Plus,
        Search,
        Trash2,
        Sparkles,
        ReceiptText,
        Pencil,
        CreditCard,
        Banknote,
    } from 'lucide-svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import {
        Select,
        SelectContent,
        SelectItem,
        SelectTrigger,
    } from '@/components/ui/select';
    import { Checkbox } from '@/components/ui/checkbox';
    import TransactionFormModal from '@/components/TransactionFormModal.svelte';
    import ConfirmDialog from '@/components/ConfirmDialog.svelte';
    import Pagination from '@/components/Pagination.svelte';
    import { router } from '@inertiajs/svelte';
    import { formatAmount, formatDate } from '@/lib/utils';
    import type { TransactionsProps, Transaction } from '@/types';

    let {
        transactions,
        categories,
        filters,
    }: TransactionsProps = $props();

    let modalOpen = $state(false);
    let editingTransaction = $state<Transaction | null>(null);
    let deleteConfirmOpen = $state(false);
    let deletingId = $state<number | null>(null);
    let bulkDeleteConfirmOpen = $state(false);
    let selectedIds = $state<Set<number>>(new Set());

    let searchInput = $state(filters.search);
    let debounceTimer: ReturnType<typeof setTimeout>;

    function handleSearch(value: string) {
        searchInput = value;
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            applyFilters({ search: value });
        }, 300);
    }

    function applyFilters(overrides: Record<string, string> = {}) {
        const params: Record<string, string> = {};
        const search = overrides.search ?? filters.search;
        const type = overrides.type ?? filters.type;
        const categoryId = overrides.category_id ?? filters.category_id;
        const payment = overrides.payment_method ?? filters.payment_method;
        const dateFrom = overrides.date_from ?? filters.date_from;
        const dateTo = overrides.date_to ?? filters.date_to;
        const page = overrides.page ?? '1';

        if (search) params.search = search;
        if (type && type !== 'all') params.type = type;
        if (categoryId && categoryId !== 'all') params.category_id = categoryId;
        if (payment && payment !== 'all') params.payment_method = payment;
        if (dateFrom) params.date_from = dateFrom;
        if (dateTo) params.date_to = dateTo;
        if (page !== '1') params.page = page;

        router.visit('/transactions', {
            data: params,
            preserveState: true,
            replace: true,
        });
    }

    function handlePageChange(page: number) {
        applyFilters({ page: page.toString() });
    }

    function openAddModal() {
        editingTransaction = null;
        modalOpen = true;
    }

    function openEditModal(transaction: Transaction) {
        editingTransaction = transaction;
        modalOpen = true;
    }

    function confirmDelete(id: number) {
        deletingId = id;
        deleteConfirmOpen = true;
    }

    function handleDelete() {
        if (deletingId !== null) {
            router.delete(`/transactions/${deletingId}`, {
                onSuccess: () => {
                    deletingId = null;
                },
            });
        }
    }

    function handleBulkDelete() {
        router.post('/transactions/bulk-destroy', {
            ids: Array.from(selectedIds),
        }, {
            onSuccess: () => {
                selectedIds = new Set();
            },
        });
    }

    function toggleSelectAll(checked: boolean) {
        if (checked) {
            selectedIds = new Set(transactions.data.map((t) => t.id));
        } else {
            selectedIds = new Set();
        }
    }

    function toggleSelect(id: number, checked: boolean) {
        const next = new Set(selectedIds);
        if (checked) {
            next.add(id);
        } else {
            next.delete(id);
        }
        selectedIds = next;
    }

    const isAllSelected = $derived(
        transactions.data.length > 0 && selectedIds.size === transactions.data.length,
    );

    const expenseCategories = $derived(categories.filter((c) => c.type === 'expense'));
    const incomeCategories = $derived(categories.filter((c) => c.type === 'income'));
    const allCategoriesForFilter = $derived(categories);
</script>

<svg width="0" height="0" class="absolute hidden">
    <defs>
        <linearGradient id="icon-grad" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" stop-color="#3c3f98" />
            <stop offset="100%" stop-color="#8C8DD8" />
        </linearGradient>
    </defs>
</svg>

<AppHead title="سجل المعاملات" />

<div class="mx-auto mb-auto w-full max-w-5xl p-4 pt-6 sm:p-8">
    <div class="flex flex-col gap-6">

        <div class="flex items-center justify-between mb-2">
            <h1 class="text-xl font-bold md:text-2xl text-slate-800 dark:text-white drop-shadow-sm">سجل المعاملات</h1>
            <div class="flex items-center gap-3">
                <Button variant="outline" size="sm" class="md:size-default rounded-xl border-white/40 bg-white/40 text-slate-700 shadow-sm backdrop-blur-md transition-colors hover:bg-white/60 hover:text-[#6b6ec6] dark:border-slate-600/50 dark:bg-slate-800/50 dark:text-slate-200 dark:hover:bg-slate-700">
                    <Sparkles class="ml-2 size-4 text-amber-300" />
                    <span class="hidden md:inline">تصنيف تلقائي</span>
                </Button>
                <Button size="sm" onclick={openAddModal} class="md:size-default rounded-xl border-none bg-gradient-to-r from-[#3c3f98] via-[#6b6ec6] to-[#6b6ec6] text-white transition-transform hover:scale-105 hover:opacity-90 shadow-lg">
                    <Plus class="ml-2 size-4" />
                    <span class="hidden md:inline">إضافة معاملة</span>
                </Button>
            </div>
        </div>

        <div class="flex flex-col gap-4 rounded-2xl border border-white/30 bg-white/50 p-6 shadow-2xl backdrop-blur-xl dark:border-slate-700/50 dark:bg-slate-900/60 sm:p-8">
            <div class="flex flex-col gap-4 md:flex-row md:items-center">
                <div class="relative flex-1">
                    <Search class="absolute right-4 top-1/2 size-4 -translate-y-1/2 text-slate-500 dark:text-slate-400" />
                    <Input
                        value={searchInput}
                        oninput={(e) => handleSearch((e.target as HTMLInputElement).value)}
                        placeholder="بحث في المعاملات..."
                        class="h-12 rounded-xl border-white/40 bg-white/50 pr-11 shadow-inner backdrop-blur-sm focus-visible:border-[#8C8DD8] focus-visible:ring-[#8C8DD8] dark:border-slate-600/50 dark:bg-slate-800/50 dark:text-white"
                    />
                </div>

                <div class="flex flex-wrap gap-2">
                    <Select value={filters.type || 'all'} onValueChange={(v) => applyFilters({ type: v })} type="single">
                        <SelectTrigger class="h-12 w-[110px] sm:w-[130px] rounded-xl border-white/40 bg-white/50 backdrop-blur-md focus:ring-[#8C8DD8] dark:border-slate-600/50 dark:bg-slate-800/50">
                            <span>
                                {filters.type === 'all' || !filters.type ? 'النوع' : filters.type === 'income' ? 'دخل' : 'مصروف'}
                            </span>
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">الكل</SelectItem>
                            <SelectItem value="income">دخل</SelectItem>
                            <SelectItem value="expense">مصروف</SelectItem>
                        </SelectContent>
                    </Select>

                    <Select value={filters.category_id || 'all'} onValueChange={(v) => applyFilters({ category_id: v })} type="single">
                        <SelectTrigger class="h-12 w-[110px] sm:w-[130px] rounded-xl border-white/40 bg-white/50 backdrop-blur-md focus:ring-[#8C8DD8] dark:border-slate-600/50 dark:bg-slate-800/50">
                            <span>
                                {filters.category_id && filters.category_id !== 'all'
                                    ? categories.find((c) => c.id.toString() === filters.category_id)?.name_ar ?? 'الفئة'
                                    : 'الفئة'}
                            </span>
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">الكل</SelectItem>
                            {#each allCategoriesForFilter as cat (cat.id)}
                                <SelectItem value={cat.id.toString()}>{cat.name_ar}</SelectItem>
                            {/each}
                        </SelectContent>
                    </Select>

                    <Select value={filters.payment_method || 'all'} onValueChange={(v) => applyFilters({ payment_method: v })} type="single">
                        <SelectTrigger class="h-12 w-[110px] sm:w-[130px] rounded-xl border-white/40 bg-white/50 backdrop-blur-md focus:ring-[#8C8DD8] dark:border-slate-600/50 dark:bg-slate-800/50">
                            <span>
                                {filters.payment_method === 'all' || !filters.payment_method ? 'طريقة الدفع' : filters.payment_method === 'cash' ? 'نقدي' : 'بطاقة'}
                            </span>
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">الكل</SelectItem>
                            <SelectItem value="cash">نقدي</SelectItem>
                            <SelectItem value="card">بطاقة</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-4 rounded-2xl border border-white/30 bg-white/50 p-6 shadow-2xl backdrop-blur-xl dark:border-slate-700/50 dark:bg-slate-900/60 sm:p-8">

            <div class="flex flex-row items-center justify-between border-b border-slate-300/30 pb-4 dark:border-slate-700/50">
                <h2 class="text-sm font-bold text-slate-600 dark:text-slate-300">
                    {transactions.total} معاملة
                </h2>
                <Button
                    variant="ghost"
                    size="sm"
                    disabled={selectedIds.size === 0}
                    onclick={() => bulkDeleteConfirmOpen = true}
                    class="rounded-lg text-rose-400 hover:bg-rose-500/10 hover:text-rose-400 disabled:opacity-50"
                >
                    <Trash2 class="ml-2 size-4" />
                    حذف المحدد ({selectedIds.size})
                </Button>
            </div>

            {#if transactions.data.length > 0}
                <div class="flex flex-col gap-2">
                    <div class="flex items-center gap-3 px-2 pb-2">
                        <Checkbox
                            checked={isAllSelected}
                            onCheckedChange={(v) => toggleSelectAll(v)}
                        />
                        <span class="text-xs text-slate-500 dark:text-slate-400">تحديد الكل</span>
                    </div>

                    {#each transactions.data as t (t.id)}
                        <div class="flex items-center gap-3 rounded-xl border border-white/20 bg-white/30 p-3 transition-colors hover:bg-white/40 dark:border-slate-700/30 dark:bg-slate-800/30 dark:hover:bg-slate-800/40">
                            <Checkbox
                                checked={selectedIds.has(t.id)}
                                onCheckedChange={(v) => toggleSelect(t.id, v)}
                            />

                            <div class="flex flex-1 items-center gap-3 min-w-0">
                                <div class="flex flex-col min-w-0">
                                    <div class="flex items-center gap-2">
                                        {#if t.category.color}
                                            <span class="inline-block size-2.5 rounded-full shrink-0" style="background-color: {t.category.color}"></span>
                                        {/if}
                                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300 truncate">{t.category.name_ar}</span>
                                    </div>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="text-xs text-slate-500 dark:text-slate-400">{formatDate(t.date)}</span>
                                        <span class="text-xs text-slate-400 dark:text-slate-500">
                                            {#if t.payment_method === 'cash'}
                                                <Banknote class="inline size-3" />
                                            {:else}
                                                <CreditCard class="inline size-3" />
                                            {/if}
                                        </span>
                                        {#if t.notes}
                                            <span class="text-xs text-slate-400 dark:text-slate-500 truncate max-w-[120px]">{t.notes}</span>
                                        {/if}
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 shrink-0">
                                <span class="text-sm font-bold {t.type === 'income' ? 'text-emerald-300' : 'text-rose-400'}">
                                    {t.type === 'income' ? '+' : '-'}{formatAmount(t.amount)} ر.س
                                </span>
                                <Button variant="ghost" size="icon-xs" onclick={() => openEditModal(t)} class="text-slate-500 hover:text-[#6b6ec6] dark:text-slate-400">
                                    <Pencil class="size-3.5" />
                                </Button>
                                <Button variant="ghost" size="icon-xs" onclick={() => confirmDelete(t.id)} class="text-slate-500 hover:text-rose-400 dark:text-slate-400">
                                    <Trash2 class="size-3.5" />
                                </Button>
                            </div>
                        </div>
                    {/each}
                </div>

                <Pagination
                    currentPage={transactions.current_page}
                    lastPage={transactions.last_page}
                    onPageChange={handlePageChange}
                />
            {:else}
                <div class="flex min-h-[300px] flex-col items-center justify-center gap-3 rounded-xl border border-dashed border-slate-400/50 bg-slate-100/30 p-6 dark:border-slate-600/50 dark:bg-slate-900/30">
                    <ReceiptText class="size-12 text-[#8C8DD8]/60" />
                    <div class="text-center">
                        <p class="text-sm font-bold text-slate-700 dark:text-slate-300">لا توجد معاملات</p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            ابدأ بإضافة أول معاملة مالية لك
                        </p>
                    </div>
                    <Button size="sm" onclick={openAddModal} class="mt-3 rounded-xl border-none bg-gradient-to-r from-[#3c3f98] via-[#6b6ec6] to-[#6b6ec6] text-white shadow-lg transition-transform hover:scale-105 hover:opacity-90">
                        <Plus class="ml-2 size-4" />
                        إضافة معاملة
                    </Button>
                </div>
            {/if}

        </div>
    </div>
</div>

<TransactionFormModal bind:open={modalOpen} transaction={editingTransaction} {categories} />

<ConfirmDialog
    bind:open={deleteConfirmOpen}
    title="حذف معاملة"
    message="هل أنت متأكد من حذف هذه المعاملة؟ لا يمكن التراجع عن هذا الإجراء."
    onConfirm={handleDelete}
    onCancel={() => deletingId = null}
/>

<ConfirmDialog
    bind:open={bulkDeleteConfirmOpen}
    title="حذف المعاملات المحددة"
    message="هل أنت متأكد من حذف {selectedIds.size} معاملة محددة؟ لا يمكن التراجع عن هذا الإجراء."
    onConfirm={handleBulkDelete}
    onCancel={() => {}}
/>