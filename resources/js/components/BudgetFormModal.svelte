<script lang="ts">
    import { useForm } from '@inertiajs/svelte';
    import {
        Dialog,
        DialogContent,
        DialogHeader,
        DialogTitle,
        DialogFooter,
    } from '@/components/ui/dialog';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import {
        Select,
        SelectContent,
        SelectItem,
        SelectTrigger,
    } from '@/components/ui/select';
    import type { Budget, Category } from '@/types';

    let {
        open = $bindable(false),
        budget = null,
        categories,
        currentMonth,
        onClose,
    }: {
        open: boolean;
        budget: Budget | null;
        categories: Category[];
        currentMonth: string;
        onClose: () => void;
    } = $props();

    const expenseCategories = $derived(categories.filter((c) => c.type === 'expense'));

    const form = useForm({
        category_id: budget?.category_id?.toString() ?? '',
        amount: budget?.amount?.toString() ?? '',
        month: budget?.month ?? currentMonth,
    });

    let lastOpen = false;
    $effect(() => {
        if (open && !lastOpen) {
            if (budget) {
                form.category_id = budget.category_id.toString();
                form.amount = budget.amount.toString();
                form.month = budget.month;
            } else {
                form.category_id = '';
                form.amount = '';
                form.month = currentMonth;
            }
        }
        lastOpen = open;
    });

    function handleSubmit(e: Event) {
        e.preventDefault();
        const url = budget ? `/budgets/${budget.id}` : '/budgets';
        const method = budget ? 'put' : 'post';

        form.submit(method as 'post' | 'put', url, {
            onSuccess: () => {
                onClose();
                form.reset();
            },
        });
    }

    function handleOpenChange(value: boolean) {
        if (!value) {
            onClose();
        }
    }
</script>

<Dialog bind:open onOpenChange={handleOpenChange}>
    <DialogContent class="sm:max-w-md">
        <DialogHeader>
            <DialogTitle>{budget ? 'تعديل الميزانية' : 'تعيين ميزانية جديدة'}</DialogTitle>
        </DialogHeader>
        <form onsubmit={handleSubmit} class="flex flex-col gap-4">
            <div class="space-y-2">
                <Label for="category">الفئة</Label>
                <Select type="single" bind:value={form.category_id} onValueChange={(v) => (form.category_id = v)}>
                    <SelectTrigger id="category" class="w-full bg-white/50 dark:bg-slate-800/50">
                        <span>{expenseCategories.find((c) => c.id.toString() === form.category_id)?.name_ar ?? 'اختر الفئة'}</span>
                    </SelectTrigger>
                    <SelectContent>
                        {#each expenseCategories as cat (cat.id)}
                            <SelectItem value={cat.id.toString()}>{cat.name_ar}</SelectItem>
                        {/each}
                    </SelectContent>
                </Select>
                {#if form.errors.category_id}
                    <p class="text-xs text-rose-400">{form.errors.category_id}</p>
                {/if}
            </div>
            <div class="space-y-2">
                <Label for="amount">المبلغ (ر.س)</Label>
                <Input
                    id="amount"
                    type="number"
                    bind:value={form.amount}
                    placeholder="0"
                    class="bg-white/50 dark:bg-slate-800/50"
                />
                {#if form.errors.amount}
                    <p class="text-xs text-rose-400">{form.errors.amount}</p>
                {/if}
            </div>
            <div class="space-y-2">
                <Label for="month">الشهر</Label>
                <Input
                    id="month"
                    type="month"
                    bind:value={form.month}
                    class="bg-white/50 dark:bg-slate-800/50"
                />
                {#if form.errors.month}
                    <p class="text-xs text-rose-400">{form.errors.month}</p>
                {/if}
            </div>
            <DialogFooter>
                <Button type="button" variant="outline" onclick={onClose}>إلغاء</Button>
                <Button type="submit" disabled={form.processing} class="bg-gradient-to-r from-[#3c3f98] via-[#6b6ec6] to-[#6b6ec6] text-white">
                    {form.processing ? 'جاري الحفظ...' : (budget ? 'تحديث' : 'إضافة')}
                </Button>
            </DialogFooter>
        </form>
    </DialogContent>
</Dialog>
