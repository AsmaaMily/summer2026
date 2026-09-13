<script module lang="ts">
    import BackgroundLayout from '@/layouts/BackgroundLayout.svelte';
    export const layout = BackgroundLayout;
</script>

<script lang="ts">
    import {
        Plus,
        PieChart,
        AlertCircle,
        Edit,
        Trash2,
        Target,
        Trophy,
        TrendingUp,
    } from 'lucide-svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Button } from '@/components/ui/button';
    import { Badge } from '@/components/ui/badge';
    import BudgetFormModal from '@/components/BudgetFormModal.svelte';
    import GoalFormModal from '@/components/GoalFormModal.svelte';
    import ContributeModal from '@/components/ContributeModal.svelte';
    import type { Budget, FinancialGoal, Category } from '@/types';

    let {
        budgets = [],
        goals = [],
        categories = [],
        totalBudget = 0,
        totalSpent = 0,
        currentMonth = '',
    }: {
        budgets: Budget[];
        goals: FinancialGoal[];
        categories: Category[];
        totalBudget: number;
        totalSpent: number;
        currentMonth: string;
    } = $props();

    let showBudgetModal = $state(false);
    let editingBudget = $state<Budget | null>(null);
    let showGoalModal = $state(false);
    let editingGoal = $state<FinancialGoal | null>(null);
    let contributingGoal = $state<FinancialGoal | null>(null);
    let showContributeModal = $state(false);

    const remaining = $derived(totalBudget - totalSpent);

    const budgetAlerts = $derived(
        budgets.filter((b) => b.spent > b.amount * 0.8)
    );

    function openBudgetModal(budget: Budget | null = null) {
        editingBudget = budget;
        showBudgetModal = true;
    }

    function openGoalModal(goal: FinancialGoal | null = null) {
        editingGoal = goal;
        showGoalModal = true;
    }

    function openContributeModal(goal: FinancialGoal) {
        contributingGoal = goal;
        showContributeModal = true;
    }

    function deleteBudget(id: number) {
        if (confirm('هل أنت متأكد من حذف هذه الميزانية؟')) {
            fetch(`/budgets/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            }).then(() => {
                window.location.reload();
            });
        }
    }

    function deleteGoal(id: number) {
        if (confirm('هل أنت متأكد من حذف هذا الهدف؟')) {
            fetch(`/financial-goals/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            }).then(() => {
                window.location.reload();
            });
        }
    }

    function getProgressColor(ratio: number): string {
        if (ratio > 0.8) return 'from-rose-500 to-red-500';
        if (ratio > 0.6) return 'from-amber-400 to-orange-500';
        return 'from-emerald-400 to-green-500';
    }

    function getStatusBadge(status: string): { text: string; class: string } {
        switch (status) {
            case 'completed':
                return { text: 'مكتمل', class: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' };
            case 'abandoned':
                return { text: 'متوقف', class: 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300' };
            default:
                return { text: 'نشط', class: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' };
        }
    }
</script>

<AppHead title="الميزانية" />

<div class="mx-auto w-full max-w-5xl p-4 sm:p-8">
    <div class="flex h-full flex-1 flex-col gap-6">
        
        <!-- الترويسة -->
        <div class="flex items-center justify-between mb-2">
            <h1 class="text-xl font-bold md:text-2xl text-slate-800 dark:text-white drop-shadow-sm">الميزانية والأهداف</h1>
        </div>

        <!-- 1. ملخص الميزانية -->
        <div class="flex flex-col gap-4 rounded-2xl border border-white/30 bg-white/50 p-6 shadow-2xl backdrop-blur-xl dark:border-slate-700/50 dark:bg-slate-900/60 sm:p-8">
            <div class="flex items-center gap-2 mb-2">
                <PieChart class="size-5 text-[#8C8DD8]" />
                <h2 class="text-base font-bold text-slate-800 dark:text-white">تقسيم الميزانية</h2>
            </div>
            
            <div class="flex flex-col gap-4">
                <div class="flex items-center justify-between border-b border-slate-300/30 dark:border-slate-700/50 pb-3">
                    <span class="text-sm font-medium text-slate-600 dark:text-slate-300">إجمالي الميزانية</span>
                    <span class="text-lg font-bold text-slate-800 dark:text-white">{totalBudget.toLocaleString()} ر.س</span>
                </div>
                <div class="flex items-center justify-between border-b border-slate-300/30 dark:border-slate-700/50 pb-3">
                    <span class="text-sm font-medium text-slate-600 dark:text-slate-300">المصروف</span>
                    <span class="text-lg font-bold text-rose-400">{totalSpent.toLocaleString()} ر.س</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-slate-600 dark:text-slate-300">المتبقي</span>
                    <span class="text-lg font-bold {remaining >= 0 ? 'text-emerald-300' : 'text-rose-400'}">{remaining.toLocaleString()} ر.س</span>
                </div>
            </div>
        </div>

        <!-- 2. ميزانية الفئات -->
        <div class="flex flex-col gap-4 rounded-2xl border border-white/30 bg-white/50 p-6 shadow-2xl backdrop-blur-xl dark:border-slate-700/50 dark:bg-slate-900/60 sm:p-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <PieChart class="size-5 text-[#8C8DD8]" />
                        <h2 class="text-base font-bold text-slate-800 dark:text-white">ميزانية الفئات</h2>
                    </div>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">تابع صرفك حسب كل فئة</p>
                </div>
                
                <Button onclick={() => openBudgetModal(null)} size="sm" class="md:size-default border-none bg-gradient-to-r from-[#3c3f98] via-[#6b6ec6] to-[#6b6ec6] text-white shadow-lg transition-opacity hover:opacity-90">
                    <Plus class="ml-2 size-4" />
                    <span>تعيين ميزانية</span>
                </Button>
            </div>
            
            {#if budgets.length === 0}
                <div class="mt-2 flex min-h-[200px] flex-col items-center justify-center gap-3 rounded-xl border border-dashed border-slate-400/50 bg-slate-100/30 dark:bg-slate-800/30 p-4">
                    <PieChart class="size-10 text-[#8C8DD8]/50" />
                    <div class="text-center">
                        <p class="text-sm font-bold text-slate-700 dark:text-slate-300">لم يتم تعيين ميزانية</p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            حدد ميزانية لكل فئة لمتابعة صرفك
                        </p>
                    </div>
                </div>
            {:else}
                <div class="mt-2 flex flex-col gap-3">
                    {#each budgets as budget (budget.id)}
                        {@const ratio = budget.amount > 0 ? budget.spent / budget.amount : 0}
                        {@const percentage = Math.min(ratio * 100, 100)}
                        <div class="flex flex-col gap-2 rounded-xl border border-white/40 bg-white/60 p-4 dark:border-slate-600/50 dark:bg-slate-800/60">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="size-3 rounded-full" style="background-color: {budget.category.color ?? '#6b6ec6'}"></span>
                                    <span class="text-sm font-bold text-slate-800 dark:text-white">{budget.category.name_ar}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <Button variant="ghost" size="icon-sm" onclick={() => openBudgetModal(budget)}>
                                        <Edit class="size-4" />
                                    </Button>
                                    <Button variant="ghost" size="icon-sm" onclick={() => deleteBudget(budget.id)}>
                                        <Trash2 class="size-4 text-rose-400" />
                                    </Button>
                                </div>
                            </div>
                            <div class="flex items-center justify-between text-xs text-slate-600 dark:text-slate-400">
                                <span>المصروف: {budget.spent.toLocaleString()} ر.س</span>
                                <span>الميزانية: {budget.amount.toLocaleString()} ر.س</span>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                                <div class="h-full rounded-full bg-gradient-to-r {getProgressColor(ratio)} transition-all" style="width: {percentage}%"></div>
                            </div>
                            {#if ratio > 0.8}
                                <p class="text-xs font-medium text-rose-400 dark:text-rose-400">
                                    ⚠️ تجاوزت {Math.round(percentage)}% من الميزانية
                                </p>
                            {/if}
                        </div>
                    {/each}
                </div>
            {/if}
        </div>

        <!-- 3. الأهداف المالية -->
        <div class="flex flex-col gap-4 rounded-2xl border border-white/30 bg-white/50 p-6 shadow-2xl backdrop-blur-xl dark:border-slate-700/50 dark:bg-slate-900/60 sm:p-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <Target class="size-5 text-[#8C8DD8]" />
                        <h2 class="text-base font-bold text-slate-800 dark:text-white">الأهداف المالية</h2>
                    </div>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">تابع تقدمك نحو أهدافك المالية</p>
                </div>
                
                <Button onclick={() => openGoalModal(null)} size="sm" class="md:size-default border-none bg-gradient-to-r from-[#3c3f98] via-[#6b6ec6] to-[#6b6ec6] text-white shadow-lg transition-opacity hover:opacity-90">
                    <Plus class="ml-2 size-4" />
                    <span>إضافة هدف</span>
                </Button>
            </div>
            
            {#if goals.length === 0}
                <div class="mt-2 flex min-h-[200px] flex-col items-center justify-center gap-3 rounded-xl border border-dashed border-slate-400/50 bg-slate-100/30 dark:bg-slate-800/30 p-4">
                    <Trophy class="size-10 text-[#8C8DD8]/50" />
                    <div class="text-center">
                        <p class="text-sm font-bold text-slate-700 dark:text-slate-300">لا توجد أهداف مالية</p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            أنشئ أهدافاً مالية لمتابعة تقدمك
                        </p>
                    </div>
                </div>
            {:else}
                <div class="mt-2 flex flex-col gap-3">
                    {#each goals as goal (goal.id)}
                        {@const progress = goal.target_amount > 0 ? (goal.current_amount / goal.target_amount) * 100 : 0}
                        {@const statusBadge = getStatusBadge(goal.status)}
                        <div class="flex flex-col gap-3 rounded-xl border border-white/40 bg-white/60 p-4 dark:border-slate-600/50 dark:bg-slate-800/60">
                            <div class="flex items-start justify-between">
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-bold text-slate-800 dark:text-white">{goal.title_ar}</span>
                                        <span class="rounded-full px-2 py-0.5 text-xs font-medium {statusBadge.class}">{statusBadge.text}</span>
                                    </div>
                                    {#if goal.deadline}
                                        <span class="text-xs text-slate-500 dark:text-slate-400">الموعد: {new Date(goal.deadline).toLocaleDateString('ar-SA')}</span>
                                    {/if}
                                </div>
                                <div class="flex items-center gap-1">
                                    {#if goal.status === 'active'}
                                        <Button variant="ghost" size="icon-sm" onclick={() => openContributeModal(goal)}>
                                            <TrendingUp class="size-4 text-emerald-300" />
                                        </Button>
                                    {/if}
                                    <Button variant="ghost" size="icon-sm" onclick={() => openGoalModal(goal)}>
                                        <Edit class="size-4" />
                                    </Button>
                                    <Button variant="ghost" size="icon-sm" onclick={() => deleteGoal(goal.id)}>
                                        <Trash2 class="size-4 text-rose-400" />
                                    </Button>
                                </div>
                            </div>
                            <div class="flex items-center justify-between text-xs text-slate-600 dark:text-slate-400">
                                <span>المحقق: {goal.current_amount.toLocaleString()} ر.س</span>
                                <span>المستهدف: {goal.target_amount.toLocaleString()} ر.س</span>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                                <div class="h-full rounded-full bg-gradient-to-r from-[#6b6ec6] to-[#8C8DD8] transition-all" style="width: {Math.min(progress, 100)}%"></div>
                            </div>
                            <p class="text-xs font-medium text-[#6b6ec6]">{Math.round(progress)}% مكتمل</p>
                        </div>
                    {/each}
                </div>
            {/if}
        </div>

        <!-- 4. تنبيهات الميزانية -->
        {#if budgetAlerts.length > 0}
            <div class="flex flex-col gap-3 rounded-2xl border border-amber-300/30 bg-amber-100/20 p-6 shadow-xl backdrop-blur-xl dark:border-amber-500/20 dark:bg-amber-900/15 sm:p-8">
                <div class="flex items-center gap-2">
                    <AlertCircle class="size-5 text-amber-600 dark:text-amber-200/80" />
                    <h2 class="text-base font-bold text-amber-800 dark:text-amber-200/80">تنبيهات الميزانية</h2>
                </div>
                <div class="flex flex-col gap-2">
                    {#each budgetAlerts as alert (alert.id)}
                        {@const percentage = Math.round((alert.spent / alert.amount) * 100)}
                        <p class="text-sm text-amber-800 dark:text-amber-200/80">
                            ⚠️ فئة <span class="font-bold">{alert.category.name_ar}</span>: تم صرف {percentage}% من الميزانية
                        </p>
                    {/each}
                </div>
            </div>
        {/if}
    </div>
</div>

<BudgetFormModal
    bind:open={showBudgetModal}
    budget={editingBudget}
    {categories}
    {currentMonth}
    onClose={() => (showBudgetModal = false)}
/>

<GoalFormModal
    bind:open={showGoalModal}
    goal={editingGoal}
    {categories}
    onClose={() => (showGoalModal = false)}
/>

{#if contributingGoal}
    <ContributeModal
        bind:open={showContributeModal}
        goal={contributingGoal}
        onClose={() => {
            showContributeModal = false;
            contributingGoal = null;
        }}
    />
{/if}
