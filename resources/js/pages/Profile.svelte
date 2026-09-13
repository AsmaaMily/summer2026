<script module lang="ts">
    import BackgroundLayout from '@/layouts/BackgroundLayout.svelte';
    export const layout = BackgroundLayout;
</script>

<script lang="ts">
    import {
        Plus,
        Edit,
        Trash2,
        Download,
        FileText,
        Wallet,
        CalendarClock,
        Power,
    } from 'lucide-svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import {
        Tabs,
        TabsContent,
        TabsList,
        TabsTrigger,
    } from '@/components/ui/tabs';
    import CategoryFormModal from '@/components/CategoryFormModal.svelte';
    import RecurringTransactionFormModal from '@/components/RecurringTransactionFormModal.svelte';
    import { useForm, router } from '@inertiajs/svelte';
    import { formatAmount, formatDate } from '@/lib/utils';
    import type { Category, RecurringFrequency, RecurringTransaction } from '@/types';

    let {
        expenseCategories = [],
        incomeCategories = [],
        recurringTransactions = [],
        monthlySalary = null,
    }: {
        expenseCategories: Category[];
        incomeCategories: Category[];
        recurringTransactions: RecurringTransaction[];
        monthlySalary: number | null;
    } = $props();

    let showCategoryModal = $state(false);
    let editingCategory = $state<Category | null>(null);
    let currentCategoryType = $state<'income' | 'expense'>('expense');
    let deleteConfirmId = $state<number | null>(null);
    let showRecurringModal = $state(false);
    let editingRecurring = $state<RecurringTransaction | null>(null);

    const allCategories = $derived([...expenseCategories, ...incomeCategories]);

    const frequencyLabels: Record<RecurringFrequency, string> = {
        daily: 'يومي',
        weekly: 'أسبوعي',
        monthly: 'شهري',
        yearly: 'سنوي',
    };

    function openRecurringModal(recurring: RecurringTransaction | null = null) {
        editingRecurring = recurring;
        showRecurringModal = true;
    }

    function handleDeleteRecurring(id: number) {
        if (confirm('هل أنت متأكد من حذف هذه الفاتورة الدورية؟')) {
            router.delete(`/recurring-transactions/${id}`, {
                preserveScroll: true,
            });
        }
    }

    function toggleRecurringActive(recurring: RecurringTransaction) {
        router.put(`/recurring-transactions/${recurring.id}`, {
            category_id: recurring.category_id,
            amount: recurring.amount,
            frequency: recurring.frequency,
            day_of_month: recurring.frequency === 'monthly' ? recurring.day_of_month : null,
            day_of_week: recurring.frequency === 'weekly' ? recurring.day_of_week : null,
            start_date: recurring.start_date,
            end_date: recurring.end_date,
            notes: recurring.notes,
            is_active: !recurring.is_active,
        }, {
            preserveScroll: true,
        });
    }

    const salaryForm = useForm({
        monthly_salary: monthlySalary?.toString() ?? '',
    });

    function openCategoryModal(type: 'income' | 'expense', category: Category | null = null) {
        currentCategoryType = type;
        editingCategory = category;
        showCategoryModal = true;
    }

    function handleDeleteCategory(id: number) {
        if (confirm('هل أنت متأكد من حذف هذه الفئة؟')) {
            fetch(`/categories/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            }).then(() => {
                window.location.reload();
            });
        }
    }

    function handleSalarySubmit(e: Event) {
        e.preventDefault();
        salaryForm.put('/profile/salary', {
            onSuccess: () => {
                salaryForm.reset();
            },
        });
    }

    function handleExportCsv() {
        window.location.href = '/export/csv';
    }
</script>

<AppHead title="الصفحة الشخصية" />

<div class="mx-auto mb-auto w-full max-w-5xl p-4 pt-6 sm:p-8">
    <div class="flex flex-col gap-6">
        
        <!-- الترويسة -->
        <div class="flex items-center justify-between mb-2">
            <h1 class="text-xl font-bold md:text-2xl text-slate-800 dark:text-white drop-shadow-sm">الصفحة الشخصية</h1>
        </div>

        <!-- التبويبات الأساسية -->
        <Tabs value="categories" class="w-full">
            
            <!-- أزرار التبديل الرئيسية -->
            <TabsList class="flex h-14 w-full rounded-full border border-white/20 bg-white/30 p-1.5 shadow-inner dark:bg-slate-800/50 sm:w-fit sm:min-w-[520px]">
                <TabsTrigger value="categories" class="flex-1 rounded-full text-xs sm:text-sm data-[state=active]:bg-white data-[state=active]:text-[#6b6ec6] data-[state=active]:shadow-sm dark:data-[state=active]:bg-slate-700 dark:data-[state=active]:text-white">الفئات</TabsTrigger>
                <TabsTrigger value="recurring" class="flex-1 rounded-full text-xs sm:text-sm data-[state=active]:bg-white data-[state=active]:text-[#6b6ec6] data-[state=active]:shadow-sm dark:data-[state=active]:bg-slate-700 dark:data-[state=active]:text-white">الفواتير الدورية</TabsTrigger>
                <TabsTrigger value="salary" class="flex-1 rounded-full text-xs sm:text-sm data-[state=active]:bg-white data-[state=active]:text-[#6b6ec6] data-[state=active]:shadow-sm dark:data-[state=active]:bg-slate-700 dark:data-[state=active]:text-white">الراتب</TabsTrigger>
                <TabsTrigger value="export" class="flex-1 rounded-full text-xs sm:text-sm data-[state=active]:bg-white data-[state=active]:text-[#6b6ec6] data-[state=active]:shadow-sm dark:data-[state=active]:bg-slate-700 dark:data-[state=active]:text-white">التقارير</TabsTrigger>
            </TabsList>

            <!-- 1. إدارة الفئات -->
            <TabsContent value="categories" class="mt-6 focus-visible:outline-none">
                <Tabs value="expense" class="w-full">
                    
                    <!-- أزرار التبديل الفرعية -->
                    <TabsList class="flex h-12 w-full rounded-full border border-white/20 bg-white/20 p-1 shadow-inner dark:bg-slate-800/40 sm:w-[300px]">
                        <TabsTrigger value="expense" class="flex-1 rounded-full text-xs sm:text-sm data-[state=active]:bg-white data-[state=active]:text-[#6b6ec6] dark:data-[state=active]:bg-slate-700 dark:data-[state=active]:text-white">المصروفات</TabsTrigger>
                        <TabsTrigger value="income" class="flex-1 rounded-full text-xs sm:text-sm data-[state=active]:bg-white data-[state=active]:text-[#6b6ec6] dark:data-[state=active]:bg-slate-700 dark:data-[state=active]:text-white">الدخل</TabsTrigger>
                    </TabsList>

                    <!-- فئات المصروفات -->
                    <TabsContent value="expense" class="mt-4 focus-visible:outline-none">
                        <div class="flex flex-col gap-4 rounded-2xl border border-white/30 bg-white/50 p-6 shadow-2xl backdrop-blur-xl dark:border-slate-700/50 dark:bg-slate-900/60 sm:p-8">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h2 class="text-base font-bold text-slate-800 dark:text-white">فئات المصروفات</h2>
                                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">أدر الفئات المخصصة للمصروفات</p>
                                </div>
                                <Button onclick={() => openCategoryModal('expense')} class="rounded-xl border-none bg-gradient-to-r from-[#3c3f98] via-[#6b6ec6] to-[#6b6ec6] text-white shadow-lg transition-transform hover:scale-105 hover:opacity-90">
                                    <Plus class="ml-2 size-4" />
                                    إضافة فئة
                                </Button>
                            </div>
                            
                            <div class="mt-2 flex flex-wrap gap-3">
                                {#each expenseCategories as category (category.id)}
                                    <div class="flex items-center gap-2 rounded-full border border-white/40 bg-white/60 py-1.5 pl-2 pr-4 text-sm font-medium text-slate-700 shadow-sm backdrop-blur-md transition-colors hover:bg-white/80 dark:border-slate-600/50 dark:bg-slate-800/60 dark:text-slate-200 dark:hover:bg-slate-700/80">
                                        <span class="size-3 rounded-full" style="background-color: {category.color ?? '#6b6ec6'}"></span>
                                        <span>{category.name_ar}</span>
                                        <div class="ml-1 flex items-center gap-1 border-r border-slate-300/50 pr-2 dark:border-slate-600/50">
                                            <button onclick={() => openCategoryModal('expense', category)} class="rounded-full p-1.5 text-slate-400 transition-colors hover:bg-white hover:text-[#6b6ec6] dark:hover:bg-slate-600 dark:hover:text-white" title="تعديل">
                                                <Edit class="size-3.5" />
                                            </button>
                                            {#if !category.is_default}
                                                <button onclick={() => handleDeleteCategory(category.id)} class="rounded-full p-1.5 text-slate-400 transition-colors hover:bg-rose-100 hover:text-rose-400 dark:hover:bg-rose-500/30 dark:hover:text-rose-400" title="حذف">
                                                    <Trash2 class="size-3.5" />
                                                </button>
                                            {/if}
                                        </div>
                                    </div>
                                {/each}
                            </div>
                        </div>
                    </TabsContent>

                    <!-- فئات الدخل -->
                    <TabsContent value="income" class="mt-4 focus-visible:outline-none">
                        <div class="flex flex-col gap-4 rounded-2xl border border-white/30 bg-white/50 p-6 shadow-2xl backdrop-blur-xl dark:border-slate-700/50 dark:bg-slate-900/60 sm:p-8">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h2 class="text-base font-bold text-slate-800 dark:text-white">فئات الدخل</h2>
                                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">أدر الفئات المخصصة للدخل</p>
                                </div>
                                <Button onclick={() => openCategoryModal('income')} class="rounded-xl border-none bg-gradient-to-r from-[#3c3f98] via-[#6b6ec6] to-[#6b6ec6] text-white shadow-lg transition-transform hover:scale-105 hover:opacity-90">
                                    <Plus class="ml-2 size-4" />
                                    إضافة فئة
                                </Button>
                            </div>
                            
                            <div class="mt-2 flex flex-wrap gap-3">
                                {#each incomeCategories as category (category.id)}
                                    <div class="flex items-center gap-2 rounded-full border border-white/40 bg-white/60 py-1.5 pl-2 pr-4 text-sm font-medium text-slate-700 shadow-sm backdrop-blur-md transition-colors hover:bg-white/80 dark:border-slate-600/50 dark:bg-slate-800/60 dark:text-slate-200 dark:hover:bg-slate-700/80">
                                        <span class="size-3 rounded-full" style="background-color: {category.color ?? '#6b6ec6'}"></span>
                                        <span>{category.name_ar}</span>
                                        <div class="ml-1 flex items-center gap-1 border-r border-slate-300/50 pr-2 dark:border-slate-600/50">
                                            <button onclick={() => openCategoryModal('income', category)} class="rounded-full p-1.5 text-slate-400 transition-colors hover:bg-white hover:text-[#6b6ec6] dark:hover:bg-slate-600 dark:hover:text-white" title="تعديل">
                                                <Edit class="size-3.5" />
                                            </button>
                                            {#if !category.is_default}
                                                <button onclick={() => handleDeleteCategory(category.id)} class="rounded-full p-1.5 text-slate-400 transition-colors hover:bg-rose-100 hover:text-rose-400 dark:hover:bg-rose-900/30 dark:hover:text-rose-400" title="حذف">
                                                    <Trash2 class="size-3.5" />
                                                </button>
                                            {/if}
                                        </div>
                                    </div>
                                {/each}
                            </div>
                        </div>
                    </TabsContent>
                </Tabs>
            </TabsContent>

            <!-- 2. الفواتير الدورية -->
            <TabsContent value="recurring" class="mt-6 focus-visible:outline-none">
                <div class="flex flex-col gap-4 rounded-2xl border border-white/30 bg-white/50 p-6 shadow-2xl backdrop-blur-xl dark:border-slate-700/50 dark:bg-slate-900/60 sm:p-8">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <CalendarClock class="size-5 text-[#8C8DD8]" />
                                <h2 class="text-base font-bold text-slate-800 dark:text-white">الفواتير الدورية</h2>
                            </div>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">أضف الالتزامات المتكررة مثل الإيجار والرواتب والاشتراكات</p>
                        </div>
                        <Button onclick={() => openRecurringModal()} class="rounded-xl border-none bg-gradient-to-r from-[#3c3f98] via-[#6b6ec6] to-[#6b6ec6] text-white shadow-lg transition-transform hover:scale-105 hover:opacity-90">
                            <Plus class="ml-2 size-4" />
                            إضافة فاتورة دورية
                        </Button>
                    </div>

                    {#if recurringTransactions.length === 0}
                        <div class="mt-2 flex min-h-[200px] flex-col items-center justify-center gap-3 rounded-xl border border-dashed border-slate-400/50 bg-slate-100/30 dark:bg-slate-800/30">
                            <CalendarClock class="size-10 text-[#8C8DD8]/50" />
                            <div class="text-center">
                                <p class="text-sm font-bold text-slate-700 dark:text-slate-300">لا توجد فواتير دورية</p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">أضف أول فاتورة دورية لمتابعة التزاماتك</p>
                            </div>
                        </div>
                    {:else}
                        <div class="mt-2 flex flex-col gap-3">
                            {#each recurringTransactions as recurring (recurring.id)}
                                <div class="flex flex-col gap-3 rounded-xl border border-white/40 bg-white/60 p-4 dark:border-slate-600/50 dark:bg-slate-800/60 {recurring.is_active ? '' : 'opacity-60'}">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex flex-col gap-1 min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span class="size-3 shrink-0 rounded-full" style="background-color: {recurring.category.color ?? '#6b6ec6'}"></span>
                                                <span class="text-sm font-bold text-slate-800 dark:text-white">{recurring.category.name_ar}</span>
                                                {#if !recurring.is_active}
                                                    <span class="rounded-full bg-slate-200/70 px-2 py-0.5 text-xs text-slate-500 dark:bg-slate-700/70 dark:text-slate-300">متوقفة</span>
                                                {/if}
                                            </div>
                                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500 dark:text-slate-400">
                                                <span>{frequencyLabels[recurring.frequency]}</span>
                                                {#if recurring.next_due_date}
                                                    <span>الاستحقاق القادم: {formatDate(recurring.next_due_date)}</span>
                                                {/if}
                                            </div>
                                            {#if recurring.notes}
                                                <span class="mt-0.5 max-w-[260px] truncate text-xs text-slate-400 dark:text-slate-500">{recurring.notes}</span>
                                            {/if}
                                        </div>

                                        <div class="flex shrink-0 items-center gap-1">
                                            <span class="me-1 text-sm font-bold text-rose-400">
                                                -{formatAmount(recurring.amount)} ر.س
                                            </span>
                                            <Button variant="ghost" size="icon-sm" onclick={() => toggleRecurringActive(recurring)} title={recurring.is_active ? 'إيقاف' : 'تفعيل'}>
                                                <Power class="size-4 {recurring.is_active ? 'text-emerald-300' : 'text-slate-400'}" />
                                            </Button>
                                            <Button variant="ghost" size="icon-sm" onclick={() => openRecurringModal(recurring)}>
                                                <Edit class="size-4" />
                                            </Button>
                                            <Button variant="ghost" size="icon-sm" onclick={() => handleDeleteRecurring(recurring.id)}>
                                                <Trash2 class="size-4 text-rose-400" />
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            {/each}
                        </div>
                    {/if}
                </div>
            </TabsContent>

            <!-- 3. تغيير الراتب -->
            <TabsContent value="salary" class="mt-6 focus-visible:outline-none">
                <div class="flex flex-col gap-6 rounded-2xl border border-white/30 bg-white/50 p-6 shadow-2xl backdrop-blur-xl dark:border-slate-700/50 dark:bg-slate-900/60 sm:p-8">
                    <div>
                        <div class="flex items-center gap-2">
                            <Wallet class="size-5 text-[#8C8DD8]" />
                            <h2 class="text-base font-bold text-slate-800 dark:text-white">الراتب الشهري</h2>
                        </div>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">حدد راتبك الشهري لحساب الميزانية والتوفير بدقة</p>
                    </div>
                    
                    <form onsubmit={handleSalarySubmit} class="flex flex-col gap-4 sm:max-w-sm">
                        <div class="space-y-2">
                            <Label for="salary" class="text-sm font-medium text-slate-700 dark:text-slate-300">الراتب الشهري (ر.س)</Label>
                            <Input
                                id="salary"
                                type="number"
                                bind:value={salaryForm.monthly_salary}
                                placeholder="0"
                                class="h-12 rounded-xl border-white/40 bg-white/50 px-4 shadow-inner backdrop-blur-sm focus-visible:border-[#8C8DD8] focus-visible:ring-[#8C8DD8] dark:border-slate-600/50 dark:bg-slate-800/50 dark:text-white"
                            />
                            {#if salaryForm.errors.monthly_salary}
                                <p class="text-xs text-rose-400">{salaryForm.errors.monthly_salary}</p>
                            {/if}
                        </div>
                        <Button type="submit" disabled={salaryForm.processing} class="h-12 w-full rounded-xl border-none bg-gradient-to-r from-[#3c3f98] via-[#6b6ec6] to-[#6b6ec6] text-white shadow-lg transition-transform hover:scale-105 hover:opacity-90">
                            {salaryForm.processing ? 'جاري الحفظ...' : 'حفظ التغييرات'}
                        </Button>
                    </form>
                </div>
            </TabsContent>

            <!-- 4. تصدير التقارير -->
            <TabsContent value="export" class="mt-6 focus-visible:outline-none">
                <div class="flex flex-col gap-6 rounded-2xl border border-white/30 bg-white/50 p-6 shadow-2xl backdrop-blur-xl dark:border-slate-700/50 dark:bg-slate-900/60 sm:p-8">
                    <div>
                        <div class="flex items-center gap-2">
                            <FileText class="size-5 text-[#8C8DD8]" />
                            <h2 class="text-base font-bold text-slate-800 dark:text-white">إعداد التقارير</h2>
                        </div>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">صدّر معاملاتك وتقاريرك المالية للاحتفاظ بها أو طباعتها</p>
                    </div>
                    
                    <div class="flex flex-row gap-3 sm:flex-row sm:max-w-md justify-center">
                        <div class="h-18">
                            <Button onclick={handleExportCsv} variant="outline" class="!h-16 flex-1 rounded-xl border-white/40 bg-white/40 text-slate-700 shadow-sm backdrop-blur-md transition-colors hover:bg-white/60 hover:text-[#6b6ec6] dark:border-slate-600/50 dark:bg-slate-800/50 dark:text-slate-200 dark:hover:bg-slate-700">
                                <Download class="ml-2 size-4" />
                                تصدير كـ CSV
                            </Button>
                        </div>
                        
                        <div class="h-18">
                            <Button variant="outline" disabled class="!h-16 flex-1 rounded-xl border-white/40 bg-white/40 text-slate-700 shadow-sm backdrop-blur-md transition-colors hover:bg-white/60 hover:text-rose-400 dark:border-slate-600/50 dark:bg-slate-800/50 dark:text-slate-200 dark:hover:bg-slate-700 dark:hover:text-rose-400">
                                <Download class="ml-2 size-4" />
                                تصدير كـ PDF
                            </Button>
                        </div>
                    </div>
                </div>
            </TabsContent>
            
        </Tabs>
    </div>
</div>

<CategoryFormModal
    bind:open={showCategoryModal}
    category={editingCategory}
    type={currentCategoryType}
    onClose={() => (showCategoryModal = false)}
/>

<RecurringTransactionFormModal
    bind:open={showRecurringModal}
    recurringTransaction={editingRecurring}
    categories={allCategories}
    onClose={() => {
        showRecurringModal = false;
        editingRecurring = null;
    }}
/>
