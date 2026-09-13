<script module lang="ts">
    import BackgroundLayout from '@/layouts/BackgroundLayout.svelte';
    export const layout = BackgroundLayout;
</script>

<script lang="ts">
    import {
        Plus,
        PieChart,
        AlertCircle,
    } from 'lucide-svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Button } from '@/components/ui/button';
</script>

<AppHead title="الميزانية" />

<div class="mx-auto w-full max-w-5xl p-4 sm:p-8">
    <div class="flex h-full flex-1 flex-col gap-6">
        
        <!-- الترويسة -->
        <div class="flex items-center justify-between mb-2">
            <h1 class="text-xl font-bold md:text-2xl text-slate-800 dark:text-white drop-shadow-sm">الميزانية</h1>
        </div>

        <!-- 1. ملخص الميزانية (مربع زجاجي) -->
        <div class="flex flex-col gap-4 rounded-2xl border border-white/30 bg-white/50 p-6 shadow-2xl backdrop-blur-xl dark:border-slate-700/50 dark:bg-slate-900/60 sm:p-8">
            <div class="flex items-center gap-2 mb-2">
                <PieChart class="size-5 text-[#8C8DD8]" />
                <h2 class="text-base font-bold text-slate-800 dark:text-white">تقسيم الميزانية</h2>
            </div>
            
            <div class="flex flex-col gap-4">
                <div class="flex items-center justify-between border-b border-slate-300/30 dark:border-slate-700/50 pb-3">
                    <span class="text-sm font-medium text-slate-600 dark:text-slate-300">إجمالي الميزانية</span>
                    <span class="text-lg font-bold text-slate-800 dark:text-white">0 ر.س</span>
                </div>
                <div class="flex items-center justify-between border-b border-slate-300/30 dark:border-slate-700/50 pb-3">
                    <span class="text-sm font-medium text-slate-600 dark:text-slate-300">المصروف</span>
                    <span class="text-lg font-bold text-rose-400">0 ر.س</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-slate-600 dark:text-slate-300">المتبقي</span>
                    <span class="text-lg font-bold text-emerald-300">0 ر.س</span>
                </div>
            </div>
        </div>

        <!-- 2. ميزانية الفئات (مربع زجاجي مع الزر الجديد) -->
        <div class="flex flex-col gap-4 rounded-2xl border border-white/30 bg-white/50 p-6 shadow-2xl backdrop-blur-xl dark:border-slate-700/50 dark:bg-slate-900/60 sm:p-8">
            <!-- الهيدر والزر -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <PieChart class="size-5 text-[#8C8DD8]" />
                        <h2 class="text-base font-bold text-slate-800 dark:text-white">ميزانية الفئات</h2>
                    </div>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">تابع صرفك حسب كل فئة</p>
                </div>
                
                <!-- زر تعيين الميزانية بالتنسيق المطلوب -->
                <Button size="sm" class="md:size-default border-none bg-gradient-to-r from-[#3c3f98] via-[#6b6ec6] to-[#6b6ec6] text-white shadow-lg transition-opacity hover:opacity-90">
                    <Plus class="ml-2 size-4" />
                    <span>تعيين ميزانية</span>
                </Button>
            </div>
            
            <!-- الصندوق المقطع الداخلي -->
            <div class="mt-2 flex min-h-[200px] flex-col items-center justify-center gap-3 rounded-xl border border-dashed border-slate-400/50 bg-slate-100/30 dark:bg-slate-800/30 p-4">
                <PieChart class="size-10 text-[#8C8DD8]/50" />
                <div class="text-center">
                    <p class="text-sm font-bold text-slate-700 dark:text-slate-300">لم يتم تعيين ميزانية</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                        حدد ميزانية لكل فئة لمتابعة صرفك
                    </p>
                </div>
            </div>
        </div>

        <!-- 3. تنبيهات الميزانية (مربع زجاجي بتمويه أصفر شفاف) -->
<div class="flex flex-col gap-3 rounded-2xl border border-amber-300/30 bg-amber-100/20 p-6 shadow-xl backdrop-blur-xl dark:border-amber-500/20 dark:bg-amber-900/15 sm:p-8">
    <div class="flex items-center gap-2">
        <AlertCircle class="size-5 text-amber-600 dark:text-amber-200/80" />
        <h2 class="text-base font-bold text-amber-800 dark:text-amber-200/80">تنبيهات الميزانية</h2>
    </div>
    <p class="text-sm text-amber-100 dark:text-amber-00/80">
        ستظهر تنبيهات عند الاقتراب من حدود الميزانية المحددة.
    </p>
</div>

    </div>
</div>