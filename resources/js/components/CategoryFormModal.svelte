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
    import type { Category } from '@/types';

    let {
        open = $bindable(false),
        category = null,
        type,
        onClose,
    }: {
        open: boolean;
        category: Category | null;
        type: 'income' | 'expense';
        onClose: () => void;
    } = $props();

    const form = useForm({
        name: category?.name ?? '',
        name_ar: category?.name_ar ?? '',
        color: category?.color ?? '#6b6ec6',
        icon: category?.icon ?? '',
        type: type,
    });

    let lastOpen = false;
    $effect(() => {
        if (open && !lastOpen) {
            if (category) {
                form.name = category.name;
                form.name_ar = category.name_ar;
                form.color = category.color ?? '#6b6ec6';
                form.icon = category.icon ?? '';
                form.type = type;
            } else {
                form.name = '';
                form.name_ar = '';
                form.color = '#6b6ec6';
                form.icon = '';
                form.type = type;
            }
        }

        lastOpen = open;
    });

    function handleSubmit(e: Event) {
        e.preventDefault();
        const url = category ? `/categories/${category.id}` : '/categories';
        const method = category ? 'put' : 'post';

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

    const presetColors = [
        '#ef4444', '#f97316', '#eab308', '#22c55e', '#06b6d4',
        '#3b82f6', '#6366f1', '#8b5cf6', '#ec4899', '#6b6ec6',
    ];
</script>

<Dialog bind:open onOpenChange={handleOpenChange}>
    <DialogContent class="sm:max-w-md">
        <DialogHeader>
            <DialogTitle>{category ? 'تعديل الفئة' : 'إضافة فئة جديدة'}</DialogTitle>
        </DialogHeader>
        <form onsubmit={handleSubmit} class="flex flex-col gap-4">
            <div class="space-y-2">
                <Label for="name_ar">اسم الفئة (عربي)</Label>
                <Input
                    id="name_ar"
                    bind:value={form.name_ar}
                    placeholder="مثال: مطاعم"
                    class="bg-white/50 dark:bg-slate-800/50"
                />
                {#if form.errors.name_ar}
                    <p class="text-xs text-rose-400">{form.errors.name_ar}</p>
                {/if}
            </div>
            <div class="space-y-2">
                <Label for="name">Name (English)</Label>
                <Input
                    id="name"
                    bind:value={form.name}
                    placeholder="e.g. Restaurants"
                    class="bg-white/50 dark:bg-slate-800/50"
                />
                {#if form.errors.name}
                    <p class="text-xs text-rose-400">{form.errors.name}</p>
                {/if}
            </div>
            <div class="space-y-2">
                <Label>اللون</Label>
                <div class="flex flex-wrap gap-2">
                    {#each presetColors as color}
                        <button
                            type="button"
                            class="size-8 rounded-full border-2 transition-transform hover:scale-110 {form.color === color ? 'border-slate-800 dark:border-white scale-110' : 'border-transparent'}"
                            style="background-color: {color}"
                            aria-label="اختر اللون {color}"
                            onclick={() => (form.color = color)}
                        ></button>
                    {/each}
                </div>
                <Input
                    type="color"
                    bind:value={form.color}
                    class="h-10 w-full bg-white/50 dark:bg-slate-800/50"
                />
                {#if form.errors.color}
                    <p class="text-xs text-rose-400">{form.errors.color}</p>
                {/if}
            </div>
            <div class="space-y-2">
                <Label for="icon">الأيقونة (اختياري)</Label>
                <Input
                    id="icon"
                    bind:value={form.icon}
                    placeholder="مثال: utencil"
                    class="bg-white/50 dark:bg-slate-800/50"
                />
                {#if form.errors.icon}
                    <p class="text-xs text-rose-400">{form.errors.icon}</p>
                {/if}
            </div>
            <DialogFooter>
                <Button type="button" variant="outline" onclick={onClose}>إلغاء</Button>
                <Button type="submit" disabled={form.processing} class="bg-gradient-to-r from-[#3c3f98] via-[#6b6ec6] to-[#6b6ec6] text-white">
                    {form.processing ? 'جاري الحفظ...' : (category ? 'تحديث' : 'إضافة')}
                </Button>
            </DialogFooter>
        </form>
    </DialogContent>
</Dialog>
