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
    import type { FinancialGoal } from '@/types';

    let {
        open = $bindable(false),
        goal,
        onClose,
    }: {
        open: boolean;
        goal: FinancialGoal;
        onClose: () => void;
    } = $props();

    const form = useForm({
        amount: '',
    });

    $effect(() => {
        if (open) {
            form.amount = '';
        }
    });

    function handleSubmit(e: Event) {
        e.preventDefault();
        form.post(`/financial-goals/${goal.id}/contribute`, {
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
    <DialogContent class="sm:max-w-sm">
        <DialogHeader>
            <DialogTitle>المساهمة في الهدف</DialogTitle>
        </DialogHeader>
        <div class="space-y-2">
            <p class="text-sm text-slate-600 dark:text-slate-400">
                الهدف: <span class="font-bold">{goal.title_ar}</span>
            </p>
            <p class="text-sm text-slate-600 dark:text-slate-400">
                المتبقي: <span class="font-bold text-emerald-300">{(goal.target_amount - goal.current_amount).toLocaleString()} ر.س</span>
            </p>
        </div>
        <form onsubmit={handleSubmit} class="flex flex-col gap-4">
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
            <DialogFooter>
                <Button type="button" variant="outline" onclick={onClose}>إلغاء</Button>
                <Button type="submit" disabled={form.processing} class="bg-gradient-to-r from-[#3c3f98] via-[#6b6ec6] to-[#6b6ec6] text-white">
                    {form.processing ? 'جاري...' : 'مساهمة'}
                </Button>
            </DialogFooter>
        </form>
    </DialogContent>
</Dialog>
