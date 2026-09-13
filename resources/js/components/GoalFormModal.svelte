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
    import { Textarea } from '@/components/ui/textarea';
    import {
        Select,
        SelectContent,
        SelectItem,
        SelectTrigger,
    } from '@/components/ui/select';
    import type { FinancialGoal, Category } from '@/types';

    let {
        open = $bindable(false),
        goal = null,
        categories,
        onClose,
    }: {
        open: boolean;
        goal: FinancialGoal | null;
        categories: Category[];
        onClose: () => void;
    } = $props();

    const form = useForm({
        title_ar: goal?.title_ar ?? '',
        target_amount: goal?.target_amount?.toString() ?? '',
        deadline: goal?.deadline ?? '',
        description: goal?.description ?? '',
        category_id: goal?.category_id?.toString() ?? '',
    });

    let lastOpen = false;
    $effect(() => {
        if (open && !lastOpen) {
            if (goal) {
                form.title_ar = goal.title_ar;
                form.target_amount = goal.target_amount.toString();
                form.deadline = goal.deadline ?? '';
                form.description = goal.description ?? '';
                form.category_id = goal.category_id?.toString() ?? '';
            } else {
                form.title_ar = '';
                form.target_amount = '';
                form.deadline = '';
                form.description = '';
                form.category_id = '';
            }
        }
        lastOpen = open;
    });

    function handleSubmit(e: Event) {
        e.preventDefault();
        const url = goal ? `/financial-goals/${goal.id}` : '/financial-goals';
        const method = goal ? 'put' : 'post';

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
            <DialogTitle>{goal ? 'تعديل الهدف' : 'إضافة هدف جديد'}</DialogTitle>
        </DialogHeader>
        <form onsubmit={handleSubmit} class="flex flex-col gap-4">
            <div class="space-y-2">
                <Label for="title_ar">عنوان الهدف (عربي)</Label>
                <Input
                    id="title_ar"
                    bind:value={form.title_ar}
                    placeholder="مثال: شراء سيارة"
                    class="bg-white/50 dark:bg-slate-800/50"
                />
                {#if form.errors.title_ar}
                    <p class="text-xs text-rose-500">{form.errors.title_ar}</p>
                {/if}
            </div>

            <div class="space-y-2">
                <Label for="target_amount">المبلغ المستهدف (ر.س)</Label>
                <Input
                    id="target_amount"
                    type="number"
                    bind:value={form.target_amount}
                    placeholder="0"
                    class="bg-white/50 dark:bg-slate-800/50"
                />
                {#if form.errors.target_amount}
                    <p class="text-xs text-rose-500">{form.errors.target_amount}</p>
                {/if}
            </div>
            <div class="space-y-2">
                <Label for="deadline">الموعد النهائي</Label>
                <Input
                    id="deadline"
                    type="date"
                    bind:value={form.deadline}
                    class="bg-white/50 dark:bg-slate-800/50"
                />
                {#if form.errors.deadline}
                    <p class="text-xs text-rose-500">{form.errors.deadline}</p>
                {/if}
            </div>
            <div class="space-y-2">
                <Label for="description">الوصف</Label>
                <Textarea
                    id="description"
                    bind:value={form.description}
                    placeholder="وصف اختياري..."
                    class="bg-white/50 dark:bg-slate-800/50"
                />
                {#if form.errors.description}
                    <p class="text-xs text-rose-500">{form.errors.description}</p>
                {/if}
            </div>
            <div class="space-y-2">
                <Label for="category">الفئة (اختياري)</Label>
                <Select type="single" bind:value={form.category_id} onValueChange={(v) => (form.category_id = v)}>
                    <SelectTrigger id="category" class="w-full bg-white/50 dark:bg-slate-800/50">
                        <span>{categories.find((c) => c.id.toString() === form.category_id)?.name_ar ?? 'بدون فئة'}</span>
                    </SelectTrigger>
                    <SelectContent>
                        {#each categories as cat (cat.id)}
                            <SelectItem value={cat.id.toString()}>{cat.name_ar}</SelectItem>
                        {/each}
                    </SelectContent>
                </Select>
                {#if form.errors.category_id}
                    <p class="text-xs text-rose-500">{form.errors.category_id}</p>
                {/if}
            </div>
            <DialogFooter>
                <Button type="button" variant="outline" onclick={onClose}>إلغاء</Button>
                <Button type="submit" disabled={form.processing} class="bg-gradient-to-r from-[#3c3f98] via-[#6b6ec6] to-[#6b6ec6] text-white">
                    {form.processing ? 'جاري الحفظ...' : (goal ? 'تحديث' : 'إضافة')}
                </Button>
            </DialogFooter>
        </form>
    </DialogContent>
</Dialog>
