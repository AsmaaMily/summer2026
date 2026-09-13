<script module lang="ts">
    import BackgroundLayout from '@/layouts/BackgroundLayout.svelte';
    export const layout = BackgroundLayout;
</script>

<script lang="ts">
    import {
        Plus,
        Wallet,
        Lightbulb,
        ReceiptText,
        CalendarClock,
        ArrowLeftRight
    } from 'lucide-svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Button } from '@/components/ui/button';
    import {
        Select,
        SelectContent,
        SelectItem,
        SelectTrigger,
    } from '@/components/ui/select';

    let selectedMonth = $state<string>('2026-09');

    const months = [
        { value: '2026-01', label: 'يناير 2026' },
        { value: '2026-02', label: 'فبراير 2026' },
        { value: '2026-03', label: 'مارس 2026' },
        { value: '2026-04', label: 'أبريل 2026' },
        { value: '2026-05', label: 'مايو 2026' },
        { value: '2026-06', label: 'يونيو 2026' },
        { value: '2026-07', label: 'يوليو 2026' },
        { value: '2026-08', label: 'أغسطس 2026' },
        { value: '2026-09', label: 'سبتمبر 2026' },
        { value: '2026-10', label: 'أكتوبر 2026' },
        { value: '2026-11', label: 'نوفمبر 2026' },
        { value: '2026-12', label: 'ديسمبر 2026' },
    ];
</script>

<AppHead title="الرئيسية" />

<div class="mx-auto w-full max-w-5xl p-4 sm:p-8">
    <div class="flex h-full flex-1 flex-col gap-6">
        
        <!-- الترويسة -->
        <div class="flex items-center justify-between mb-2">
            <h1 class="text-xl font-bold md:text-2xl text-slate-800 dark:text-white drop-shadow-sm">لوحة التحكم</h1>
            <div class="flex items-center gap-3">
                <Select bind:value={selectedMonth} type="single">
                    <SelectTrigger class="w-[150px] md:w-[180px] bg-white/50 backdrop-blur-md border-white/30 focus:ring-[#8C8DD8] dark:bg-slate-800/50">
                        <span>
                            {months.find((m) => m.value === selectedMonth)?.label ?? 'اختر الشهر'}
                        </span>
                    </SelectTrigger>
                    <SelectContent>
                        {#each months as month (month.value)}
                            <SelectItem value={month.value}>{month.label}</SelectItem>
                        {/each}
                    </SelectContent>
                </Select>
                <Button size="sm" class="md:size-default border-none bg-gradient-to-r from-[#3c3f98] via-[#6b6ec6] to-[#6b6ec6] text-white transition-opacity hover:opacity-90 text-white hover:opacity-90 shadow-lg">
                    <Plus class="ml-2 size-4" />
                    <span class="hidden md:inline">إضافة معاملة</span>
                </Button>
            </div>
        </div>

        <!-- 1. مستطيل صافي الرصيد -->
        <div class="flex items-center justify-between rounded-2xl border border-white/30 bg-white/50 p-6 shadow-2xl backdrop-blur-xl dark:border-slate-700/50 dark:bg-slate-900/60 sm:p-8">
            <div class="flex items-center gap-3">
                <Wallet class="size-6 text-[#8C8DD8]" />
                <h2 class="text-lg font-bold text-white drop-shadow-md">صافي الرصيد</h2>
            </div>
            <div class="text-2xl font-extrabold text-slate-800 dark:text-white">
                0 ر.س
            </div>
        </div>

        <!-- 2. مربعين متجاورين (يمين: المصروفات، يسار: الفواتير) -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            
            <!-- المربع الأيمن: المصروفات حسب الفئة -->
            <div class="flex flex-col gap-4 rounded-2xl border border-white/30 bg-white/50 p-6 shadow-2xl backdrop-blur-xl dark:border-slate-700/50 dark:bg-slate-900/60 sm:p-8">
                <div class="flex items-center gap-2">
                    <ReceiptText class="size-5 text-[#8C8DD8]" />
                    <h2 class="text-base font-bold text-slate-800 dark:text-white">المصروفات حسب الفئة</h2>
                </div>
                <div class="flex h-[180px] items-center justify-center rounded-xl border border-dashed border-slate-400/50 bg-slate-100/30 dark:bg-slate-800/30">
                    <p class="text-sm text-slate-500">مساحة الرسم البياني الدائري</p>
                </div>
            </div>

            <!-- المربع الأيسر: الفواتير الدورية -->
            <div class="flex flex-col gap-4 rounded-2xl border border-white/30 bg-white/50 p-6 shadow-2xl backdrop-blur-xl dark:border-slate-700/50 dark:bg-slate-900/60 sm:p-8">
                <div class="flex items-center gap-2">
                    <CalendarClock class="size-5 text-[#8C8DD8]" />
                    <h2 class="text-base font-bold text-slate-800 dark:text-white">الفواتير الدورية</h2>
                </div>
                <div class="flex h-[180px] items-center justify-center rounded-xl border border-dashed border-slate-400/50 bg-slate-100/30 dark:bg-slate-800/30">
                    <p class="text-sm text-slate-500">لا توجد فواتير قادمة</p>
                </div>
            </div>

        </div>

        <!-- 3. مستطيل آخر المعاملات -->
        <div class="flex flex-col gap-4 rounded-2xl border border-white/30 bg-white/50 p-6 shadow-2xl backdrop-blur-xl dark:border-slate-700/50 dark:bg-slate-900/60 sm:p-8">
            <div class="flex items-center gap-2">
                <ArrowLeftRight class="size-5 text-[#8C8DD8]" />
                <h2 class="text-base font-bold text-slate-800 dark:text-white">آخر المعاملات</h2>
            </div>
            <div class="flex h-[200px] items-center justify-center rounded-xl border border-dashed border-slate-400/50 bg-slate-100/30 dark:bg-slate-800/30">
                <p class="text-sm text-slate-500">لا توجد معاملات بعد</p>
            </div>
        </div>

        <!-- 4. مستطيل رؤى الذكاء الاصطناعي (بتدرج داكن جداً مائل للأسود) -->
        <div class="flex flex-col gap-4 rounded-2xl border border-white/10 bg-gradient-to-r from-[#53589A]/80 to-[#2B3070]/40 p-6 shadow-2xl backdrop-blur-xl sm:p-8">
    <div class="flex items-center gap-2">
        <!-- الأيقونة بلون موف فاتح لتبرز وتضيء فوق الخلفية الغامقة -->
        <Lightbulb class="size-5 text-[#8C8DD8]" />
        <h2 class="text-base font-bold text-white drop-shadow-sm">رؤى الذكاء الاصطناعي</h2>
    </div>
    <!-- المربع الداخلي: خففنا حدة الإطار المقطع واستخدمنا خلفية شفافة داكنة لراحة العين -->
    <div class="flex h-[120px] items-center justify-center rounded-xl border border-dashed border-white/20 bg-white/5">
        <p class="text-sm text-slate-300">ستظهر الرؤى هنا بناءً على أنماط صرفك</p>
    </div>
</div>

    </div>
</div>