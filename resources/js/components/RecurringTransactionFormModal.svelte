<script lang="ts">
    import { useForm } from '@inertiajs/svelte';
    import { Button } from '@/components/ui/button';
    import {
        Dialog,
        DialogContent,
        DialogHeader,
        DialogTitle,
        DialogFooter,
    } from '@/components/ui/dialog';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import {
        Select,
        SelectContent,
        SelectItem,
        SelectTrigger,
    } from '@/components/ui/select';
    import { Textarea } from '@/components/ui/textarea';
    import type {
        Category,
        RecurringFrequency,
        RecurringTransaction,
    } from '@/types';

    let {
        open = $bindable(false),
        recurringTransaction = null,
        categories = [],
        onClose,
    }: {
        open: boolean;
        recurringTransaction: RecurringTransaction | null;
        categories: Category[];
        onClose: () => void;
    } = $props();

    const frequencyLabels: Record<RecurringFrequency, string> = {
        daily: 'يومي',
        weekly: 'أسبوعي',
        monthly: 'شهري',
        yearly: 'سنوي',
    };

    const weekDays = [
        { value: '0', label: 'الأحد' },
        { value: '1', label: 'الاثنين' },
        { value: '2', label: 'الثلاثاء' },
        { value: '3', label: 'الأربعاء' },
        { value: '4', label: 'الخميس' },
        { value: '5', label: 'الجمعة' },
        { value: '6', label: 'السبت' },
    ];

    const today = new Date().toISOString().split('T')[0];

    const form = useForm({
        category_id: '',
        amount: '',
        frequency: 'monthly' as RecurringFrequency,
        day_of_month: '1',
        day_of_week: '0',
        start_date: today,
        end_date: '',
        notes: '',
        is_active: true,
    });

    const expenseCategories = $derived(
        categories.filter((c) => c.type === 'expense'),
    );

    let lastOpen = false;
    $effect(() => {
        if (open && !lastOpen) {
            if (recurringTransaction) {
                form.category_id = recurringTransaction.category_id.toString();
                form.amount = recurringTransaction.amount.toString();
                form.frequency = recurringTransaction.frequency;
                form.day_of_month = recurringTransaction.day_of_month?.toString() ?? '1';
                form.day_of_week = recurringTransaction.day_of_week?.toString() ?? '0';
                form.start_date = recurringTransaction.start_date;
                form.end_date = recurringTransaction.end_date ?? '';
                form.notes = recurringTransaction.notes ?? '';
                form.is_active = recurringTransaction.is_active;
            } else {
                form.reset();
                form.category_id = '';
                form.amount = '';
                form.frequency = 'monthly';
                form.day_of_month = '1';
                form.day_of_week = '0';
                form.start_date = today;
                form.end_date = '';
                form.notes = '';
                form.is_active = true;
            }

            form.clearErrors();
        }

        lastOpen = open;
    });

    function handleSubmit(e: Event) {
        e.preventDefault();

        const payload = {
            category_id: form.category_id,
            amount: form.amount,
            frequency: form.frequency,
            day_of_month: form.frequency === 'monthly' ? form.day_of_month : '',
            day_of_week: form.frequency === 'weekly' ? form.day_of_week : '',
            start_date: form.start_date,
            end_date: form.end_date,
            notes: form.notes,
            is_active: form.is_active,
        };

        const url = recurringTransaction
            ? `/recurring-transactions/${recurringTransaction.id}`
            : '/recurring-transactions';
        const method = recurringTransaction ? 'put' : 'post';

        form.transform(() => payload).submit(method as 'post' | 'put', url, {
            preserveScroll: true,
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
    <DialogContent class="sm:max-w-lg" dir="rtl">
        <DialogHeader>
            <DialogTitle>
                {recurringTransaction ? 'تعديل فاتورة دورية' : 'إضافة فاتورة دورية'}
            </DialogTitle>
        </DialogHeader>
        <form onsubmit={handleSubmit} class="flex flex-col gap-4">
            <div class="space-y-2">
                <Label for="amount">المبلغ (ر.س)</Label>
                <Input
                    id="amount"
                    type="number"
                    min="1"
                    bind:value={form.amount}
                    placeholder="0"
                    class="bg-white/50 dark:bg-slate-800/50"
                />
                {#if form.errors.amount}
                    <p class="text-xs text-rose-400">{form.errors.amount}</p>
                {/if}
            </div>

            <div class="space-y-2">
                <Label>الفئة</Label>
                <Select
                    type="single"
                    value={form.category_id}
                    onValueChange={(v) => (form.category_id = v)}
                >
                    <SelectTrigger class="w-full bg-white/50 dark:bg-slate-800/50">
                        <span>
                            {expenseCategories.find((c) => c.id.toString() === form.category_id)?.name_ar ?? 'اختر الفئة'}
                        </span>
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
                <Label>التكرار</Label>
                <Select type="single" bind:value={form.frequency}>
                    <SelectTrigger class="w-full bg-white/50 dark:bg-slate-800/50">
                        <span>{frequencyLabels[form.frequency]}</span>
                    </SelectTrigger>
                    <SelectContent>
                        {#each Object.entries(frequencyLabels) as [value, label] (value)}
                            <SelectItem {value}>{label}</SelectItem>
                        {/each}
                    </SelectContent>
                </Select>
                {#if form.errors.frequency}
                    <p class="text-xs text-rose-400">{form.errors.frequency}</p>
                {/if}
            </div>

            {#if form.frequency === 'weekly'}
                <div class="space-y-2">
                    <Label>يوم الأسبوع</Label>
                    <Select type="single" bind:value={form.day_of_week}>
                        <SelectTrigger class="w-full bg-white/50 dark:bg-slate-800/50">
                            <span>
                                {weekDays.find((d) => d.value === form.day_of_week)?.label ?? 'اختر اليوم'}
                            </span>
                        </SelectTrigger>
                        <SelectContent>
                            {#each weekDays as day (day.value)}
                                <SelectItem value={day.value}>{day.label}</SelectItem>
                            {/each}
                        </SelectContent>
                    </Select>
                    {#if form.errors.day_of_week}
                        <p class="text-xs text-rose-400">{form.errors.day_of_week}</p>
                    {/if}
                </div>
            {/if}

            {#if form.frequency === 'monthly'}
                <div class="space-y-2">
                    <Label for="day_of_month">يوم الشهر</Label>
                    <Input
                        id="day_of_month"
                        type="number"
                        min="1"
                        max="31"
                        bind:value={form.day_of_month}
                        class="bg-white/50 dark:bg-slate-800/50"
                    />
                    {#if form.errors.day_of_month}
                        <p class="text-xs text-rose-400">{form.errors.day_of_month}</p>
                    {/if}
                </div>
            {/if}

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                    <Label for="start_date">تاريخ البداية</Label>
                    <Input
                        id="start_date"
                        type="date"
                        bind:value={form.start_date}
                        class="bg-white/50 dark:bg-slate-800/50"
                    />
                    {#if form.errors.start_date}
                        <p class="text-xs text-rose-400">{form.errors.start_date}</p>
                    {/if}
                </div>

                <div class="space-y-2">
                    <Label for="end_date">تاريخ النهاية (اختياري)</Label>
                    <Input
                        id="end_date"
                        type="date"
                        bind:value={form.end_date}
                        class="bg-white/50 dark:bg-slate-800/50"
                    />
                    {#if form.errors.end_date}
                        <p class="text-xs text-rose-400">{form.errors.end_date}</p>
                    {/if}
                </div>
            </div>

            <div class="space-y-2">
                <Label>الحالة</Label>
                <Select
                    type="single"
                    value={form.is_active ? 'active' : 'inactive'}
                    onValueChange={(v) => (form.is_active = v === 'active')}
                >
                    <SelectTrigger class="w-full bg-white/50 dark:bg-slate-800/50">
                        <span>{form.is_active ? 'نشطة' : 'متوقفة'}</span>
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="active">نشطة</SelectItem>
                        <SelectItem value="inactive">متوقفة</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <div class="space-y-2">
                <Label for="notes">ملاحظات (اختياري)</Label>
                <Textarea
                    id="notes"
                    bind:value={form.notes}
                    placeholder="أضف ملاحظات"
                    class="bg-white/50 dark:bg-slate-800/50"
                />
                {#if form.errors.notes}
                    <p class="text-xs text-rose-400">{form.errors.notes}</p>
                {/if}
            </div>

            <DialogFooter>
                <Button type="button" variant="outline" onclick={onClose}>إلغاء</Button>
                <Button
                    type="submit"
                    disabled={form.processing}
                    class="bg-gradient-to-r from-[#3c3f98] via-[#6b6ec6] to-[#6b6ec6] text-white"
                >
                    {form.processing ? 'جاري الحفظ...' : recurringTransaction ? 'تحديث' : 'إضافة'}
                </Button>
            </DialogFooter>
        </form>
    </DialogContent>
</Dialog>
