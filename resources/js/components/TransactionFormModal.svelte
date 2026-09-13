<script lang="ts">
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
    import InputError from '@/components/InputError.svelte';
    import { useForm } from '@inertiajs/svelte';
    import type { Category, Transaction } from '@/types';

    let {
        open = $bindable(false),
        transaction = null,
        categories = [],
    }: {
        open: boolean;
        transaction: Transaction | null;
        categories: Category[];
    } = $props();

    const isEdit = $derived(transaction !== null);

    const form = useForm({
        amount: '',
        type: 'expense' as 'income' | 'expense',
        category_id: '',
        date: new Date().toISOString().split('T')[0],
        notes: '',
    });

    let lastOpen = false;
    $effect(() => {
        if (open && !lastOpen) {
            if (transaction) {
                form.amount = transaction.amount.toString();
                form.type = transaction.type;
                form.category_id = transaction.category.id.toString();
                form.date = transaction.date;
                form.notes = transaction.notes ?? '';
            } else {
                form.amount = '';
                form.type = 'expense';
                form.category_id = '';
                form.date = new Date().toISOString().split('T')[0];
                form.notes = '';
            }
            form.clearErrors();
        }
        lastOpen = open;
    });

    const filteredCategories = $derived(
        categories.filter((c) => c.type === form.type),
    );

    function handleSubmit() {
        if (isEdit && transaction) {
            form.put(`/transactions/${transaction.id}`, {
                onSuccess: () => {
                    open = false;
                },
            });
        } else {
            form.post('/transactions', {
                onSuccess: () => {
                    open = false;
                },
            });
        }
    }

    function handleTypeChange(value: string) {
        form.type = value as 'income' | 'expense';
        form.category_id = '';
    }
</script>

<Dialog bind:open>
    <DialogContent class="sm:max-w-lg border-white/30 bg-white/80 backdrop-blur-xl dark:border-slate-700/50 dark:bg-slate-900/80" dir="rtl">
        <DialogHeader>
            <DialogTitle class="text-slate-800 dark:text-white">
                {isEdit ? 'تعديل معاملة' : 'إضافة معاملة جديدة'}
            </DialogTitle>
        </DialogHeader>

        <form onsubmit={(e) => { e.preventDefault(); handleSubmit(); }} class="flex flex-col gap-4">
            <div class="grid grid-cols-2 gap-4">
                <div class="flex flex-col gap-2">
                    <Label for="amount" class="text-slate-700 dark:text-slate-300">المبلغ</Label>
                    <Input
                        id="amount"
                        type="number"
                        step="0.01"
                        min="0"
                        bind:value={form.amount}
                        placeholder="0.00"
                        class="h-10 border-white/40 bg-white/50 dark:border-slate-600/50 dark:bg-slate-800/50 dark:text-white"
                    />
                    <InputError message={form.errors.amount} />
                </div>

                <div class="flex flex-col gap-2">
                    <Label for="date" class="text-slate-700 dark:text-slate-300">التاريخ</Label>
                    <Input
                        id="date"
                        type="date"
                        bind:value={form.date}
                        class="h-10 border-white/40 bg-white/50 dark:border-slate-600/50 dark:bg-slate-800/50 dark:text-white"
                    />
                    <InputError message={form.errors.date} />
                </div>
            </div>

            <div class="flex flex-col gap-2">
                <Label class="text-slate-700 dark:text-slate-300">النوع</Label>
                <Select type="single" bind:value={form.type} onValueChange={handleTypeChange}>
                    <SelectTrigger class="h-10 border-white/40 bg-white/50 backdrop-blur-md focus:ring-[#8C8DD8] dark:border-slate-600/50 dark:bg-slate-800/50">
                        <span>{form.type === 'income' ? 'دخل' : 'مصروف'}</span>
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="expense">مصروف</SelectItem>
                        <SelectItem value="income">دخل</SelectItem>
                    </SelectContent>
                </Select>
                <InputError message={form.errors.type} />
            </div>

            <div class="flex flex-col gap-2">
                <Label class="text-slate-700 dark:text-slate-300">الفئة</Label>
                <Select type="single" bind:value={form.category_id} onValueChange={(v) => (form.category_id = v)}>
                    <SelectTrigger class="h-10 border-white/40 bg-white/50 backdrop-blur-md focus:ring-[#8C8DD8] dark:border-slate-600/50 dark:bg-slate-800/50">
                        <span>
                            {filteredCategories.find((c) => c.id.toString() === form.category_id?.toString())?.name_ar ?? 'اختر الفئة'}
                        </span>
                    </SelectTrigger>
                    <SelectContent>
                        {#each filteredCategories as cat (cat.id)}
                            <SelectItem value={cat.id.toString()}>
                                <span class="flex items-center gap-2">
                                    {#if cat.color}
                                        <span class="inline-block size-3 rounded-full" style="background-color: {cat.color}"></span>
                                    {/if}
                                    {cat.name_ar}
                                </span>
                            </SelectItem>
                        {/each}
                    </SelectContent>
                </Select>
                <InputError message={form.errors.category_id} />
            </div>

            <div class="flex flex-col gap-2">
                <Label for="notes" class="text-slate-700 dark:text-slate-300">ملاحظات</Label>
                <Textarea
                    id="notes"
                    bind:value={form.notes}
                    placeholder="أضف ملاحظات (اختياري)"
                    class="min-h-[80px] border-white/40 bg-white/50 dark:border-slate-600/50 dark:bg-slate-800/50 dark:text-white"
                />
                <InputError message={form.errors.notes} />
            </div>

            <DialogFooter class="gap-2 sm:gap-0 pt-2">
                <Button type="button" variant="outline" onclick={() => open = false} class="border-white/40 bg-white/50 dark:border-slate-600/50 dark:bg-slate-800/50 dark:text-white">
                    إلغاء
                </Button>
                <Button type="submit" disabled={form.processing} class="border-none bg-gradient-to-r from-[#3c3f98] via-[#6b6ec6] to-[#6b6ec6] text-white shadow-lg transition-opacity hover:opacity-90">
                    {form.processing ? 'جاري الحفظ...' : isEdit ? 'تحديث' : 'إضافة'}
                </Button>
            </DialogFooter>
        </form>
    </DialogContent>
</Dialog>
