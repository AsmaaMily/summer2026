<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: 'الميزانية والأهداف',
                href: '/budget',
            },
        ],
    };
</script>

<script lang="ts">
    import {
        Plus,
        Target,
        PieChart,
        AlertCircle,
    } from 'lucide-svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Button } from '@/components/ui/button';
    import {
        Card,
        CardContent,
        CardDescription,
        CardHeader,
        CardTitle,
    } from '@/components/ui/card';
    import {
        Tabs,
        TabsContent,
        TabsList,
        TabsTrigger,
    } from '@/components/ui/tabs';
</script>

<AppHead title="الميزانية والأهداف" />

<div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold md:text-2xl">الميزانية والأهداف</h1>
    </div>

    <Tabs value="budget" class="w-full">
        <TabsList class="grid w-full grid-cols-2 md:w-auto">
            <TabsTrigger value="budget">الميزانية</TabsTrigger>
            <TabsTrigger value="goals">الأهداف</TabsTrigger>
        </TabsList>

        <!-- الميزانية -->
        <TabsContent value="budget" class="mt-6 space-y-6">
            <!-- ملخص الميزانية -->
            <Card>
                <CardHeader class="flex flex-row items-center gap-2">
                    <PieChart class="size-5 text-muted-foreground" />
                    <CardTitle class="text-base">تقسيم الميزانية</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-muted-foreground">إجمالي الميزانية</span>
                            <span class="font-semibold">0 ر.س</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-muted-foreground">المصروف</span>
                            <span class="font-semibold text-red-600">0 ر.س</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-muted-foreground">المتبقي</span>
                            <span class="font-semibold text-green-600">0 ر.س</span>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- ميزانية الفئات -->
            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <div>
                        <CardTitle class="text-base">ميزانية الفئات</CardTitle>
                        <CardDescription>تابع صرفك حسب كل فئة</CardDescription>
                    </div>
                    <Button size="sm">
                        <Plus class="ml-2 size-4" />
                        تعيين ميزانية
                    </Button>
                </CardHeader>
                <CardContent>
                    <div class="flex min-h-[200px] flex-col items-center justify-center gap-3 rounded-lg border border-dashed">
                        <PieChart class="size-10 text-muted-foreground/50" />
                        <div class="text-center">
                            <p class="text-sm font-medium">لم يتم تعيين ميزانية</p>
                            <p class="text-xs text-muted-foreground">
                                حدد ميزانية لكل فئة لمتابعة صرفك
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- تنبيهات الميزانية -->
            <Card class="border-amber-200 dark:border-amber-900/50">
                <CardHeader class="flex flex-row items-center gap-2 pb-3">
                    <AlertCircle class="size-5 text-amber-500" />
                    <CardTitle class="text-base text-amber-700 dark:text-amber-400">
                        تنبيهات الميزانية
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="text-sm text-muted-foreground">
                        ستظهر تنبيهات عند الاقتراب من حدود الميزانية المحددة.
                    </p>
                </CardContent>
            </Card>
        </TabsContent>

        <!-- الأهداف -->
        <TabsContent value="goals" class="mt-6 space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-semibold">أهدافي المالية</h2>
                    <p class="text-sm text-muted-foreground">أنشئ أهدافاً مالية وتابع تقدمك</p>
                </div>
                <Button size="sm">
                    <Plus class="ml-2 size-4" />
                    هدف جديد
                </Button>
            </div>

            <div class="flex min-h-[300px] flex-col items-center justify-center gap-3 rounded-lg border border-dashed p-6">
                <Target class="size-12 text-muted-foreground/50" />
                <div class="text-center">
                    <p class="text-sm font-medium">لا توجد أهداف مالية</p>
                    <p class="text-xs text-muted-foreground">
                        أنشئ هدفك المالي الأول وابدأ في تتبع تقدمك
                    </p>
                </div>
                <Button size="sm" class="mt-2">
                    <Plus class="ml-2 size-4" />
                    إنشاء هدف
                </Button>
            </div>
        </TabsContent>
    </Tabs>
</div>
