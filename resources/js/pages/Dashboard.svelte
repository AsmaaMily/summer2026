<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: 'الرئيسية',
                href: '/dashboard',
            },
        ],
    };
</script>

<script lang="ts">
    import {
        Plus,
        TrendingUp,
        TrendingDown,
        Wallet,
        Lightbulb,
        AlertTriangle,
        Clock,
        Target,
    } from 'lucide-svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Button } from '@/components/ui/button';
    import {
        Card,
        CardContent,
        CardHeader,
        CardTitle,
        CardDescription,
    } from '@/components/ui/card';
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

<div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold md:text-2xl">لوحة التحكم</h1>
        <div class="flex items-center gap-3">
            <Select bind:value={selectedMonth} type="single">
                <SelectTrigger class="w-[150px] md:w-[180px]">
                    <span>
                        {months.find((m) => m.value === selectedMonth)
                            ?.label ?? 'اختر الشهر'}
                    </span>
                </SelectTrigger>
                <SelectContent>
                    {#each months as month (month.value)}
                        <SelectItem value={month.value}
                            >{month.label}</SelectItem
                        >
                    {/each}
                </SelectContent>
            </Select>
            <Button size="sm" class="md:size-default">
                <Plus class="ml-2 size-4" />
                <span class="hidden md:inline">إضافة معاملة</span>
            </Button>
        </div>
    </div>

    <!-- تنبيهات التنبؤ -->
    <Card class="border-amber-200 bg-amber-50 dark:border-amber-900/50 dark:bg-amber-950/30">
        <CardContent class="flex items-center gap-3 py-4">
            <AlertTriangle class="size-5 shrink-0 text-amber-600 dark:text-amber-400" />
            <div class="flex-1">
                <p class="text-sm font-medium text-amber-800 dark:text-amber-200">
                    تنبؤ: احتمالية تجاوز ميزانية فئة "المطاعم" بنسبة 15%
                </p>
                <p class="text-xs text-amber-600 dark:text-amber-400">
                    بناءً على نمط صرفك الحالي
                </p>
            </div>
        </CardContent>
    </Card>

    <!-- ملخص الدخل والمصروفات -->
    <div class="grid grid-cols-3 gap-2 md:gap-4">
        <Card>
            <CardHeader
                class="flex flex-row items-center justify-between space-y-0 pb-2"
            >
                <CardTitle class="text-sm font-medium">إجمالي الدخل</CardTitle>
                <TrendingUp class="size-4 text-white" />
            </CardHeader>
            <CardContent>
                <div class="text-2xl font-bold text-white">0 ر.س</div>
                <p class="text-xs text-muted-foreground">هذا الشهر</p>
            </CardContent>
        </Card>

        <Card>
            <CardHeader
                class="flex flex-row items-center justify-between space-y-0 pb-2"
            >
                <CardTitle class="text-sm font-medium"
                    >إجمالي المصروفات</CardTitle
                >
                <TrendingDown class="size-4 text-white" />
            </CardHeader>
            <CardContent>
                <div class="text-2xl font-bold text-white">0 ر.س</div>
                <p class="text-xs text-muted-foreground">هذا الشهر</p>
            </CardContent>
        </Card>

        <Card>
            <CardHeader
                class="flex flex-row items-center justify-between space-y-0 pb-2"
            >
                <CardTitle class="text-sm font-medium">صافي الرصيد</CardTitle>
                <Wallet class="size-5 text-white" />
            </CardHeader>
            <CardContent>
                <div class="text-2xl font-bold">0 ر.س</div>
                <p class="text-xs text-muted-foreground">هذا الشهر</p>
            </CardContent>
        </Card>
    </div>

    <!-- الرسوم البيانية -->
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-7">
        <Card class="lg:col-span-4">
            <CardHeader>
                <CardTitle>التدفق النقدي</CardTitle>
                <CardDescription>الدخل مقابل المصروفات</CardDescription>
            </CardHeader>
            <CardContent class="flex h-[250px] items-center justify-center rounded-lg border border-dashed">
                <p class="text-sm text-muted-foreground">مساحة الرسم البياني</p>
            </CardContent>
        </Card>

        <Card class="lg:col-span-3">
            <CardHeader>
                <CardTitle>المصروفات حسب الفئة</CardTitle>
            </CardHeader>
            <CardContent class="flex h-[250px] items-center justify-center rounded-lg border border-dashed">
                <p class="text-sm text-muted-foreground">مساحة الرسم البياني</p>
            </CardContent>
        </Card>
    </div>

    <!-- Widgets -->
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        <!-- آخر المعاملات -->
        <Card class="lg:col-span-1">
            <CardHeader class="flex flex-row items-center gap-2">
                <Clock class="size-4 text-muted-foreground" />
                <CardTitle class="text-sm">آخر المعاملات</CardTitle>
            </CardHeader>
            <CardContent>
                <div class="flex h-[150px] items-center justify-center rounded-lg border border-dashed">
                    <p class="text-xs text-muted-foreground">لا توجد معاملات بعد</p>
                </div>
            </CardContent>
        </Card>

        <!-- الالتزامات والفواتير الدورية -->
        <Card class="lg:col-span-1">
            <CardHeader class="flex flex-row items-center gap-2">
                <Clock class="size-4 text-muted-foreground" />
                <CardTitle class="text-sm">الفواتير الدورية</CardTitle>
            </CardHeader>
            <CardContent>
                <div class="flex h-[150px] items-center justify-center rounded-lg border border-dashed">
                    <p class="text-xs text-muted-foreground">لا توجد فواتير قادمة</p>
                </div>
            </CardContent>
        </Card>

        <!-- الأهداف المالية -->
        <Card class="lg:col-span-1">
            <CardHeader class="flex flex-row items-center gap-2">
                <Target class="size-4 text-muted-foreground" />
                <CardTitle class="text-sm">الأهداف المالية</CardTitle>
            </CardHeader>
            <CardContent>
                <div class="flex h-[150px] items-center justify-center rounded-lg border border-dashed">
                    <p class="text-xs text-muted-foreground">لا توجد أهداف بعد</p>
                </div>
            </CardContent>
        </Card>
    </div>

    <!-- رؤى الذكاء الاصطناعي -->
    <Card>
        <CardHeader class="flex flex-row items-center gap-2">
            <Lightbulb class="size-5 text-yellow-500" />
            <CardTitle>رؤى الذكاء الاصطناعي</CardTitle>
        </CardHeader>
        <CardContent>
            <div class="flex h-[120px] items-center justify-center rounded-lg border border-dashed">
                <p class="text-sm text-muted-foreground">ستظهر الرؤى هنا بناءً على أنماط صرفك</p>
            </div>
        </CardContent>
    </Card>
</div>

