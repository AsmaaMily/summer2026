<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: 'الصفحة الشخصية',
                href: '/profile',
            },
        ],
    };
</script>

<script lang="ts">
    import {
        Plus,
        Edit,
        Trash2,
        Download,
        FileText,
        Wallet,
    } from 'lucide-svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
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
    import { Badge } from '@/components/ui/badge';
</script>

<AppHead title="الصفحة الشخصية" />

<div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
    <h1 class="text-xl font-bold md:text-2xl">الصفحة الشخصية</h1>

    <Tabs value="categories" class="w-full">
        <TabsList class="grid w-full grid-cols-3 md:w-auto">
            <TabsTrigger value="categories">الفئات</TabsTrigger>
            <TabsTrigger value="salary">الراتب</TabsTrigger>
            <TabsTrigger value="export">التقارير</TabsTrigger>
        </TabsList>

        <!-- إدارة الفئات -->
        <TabsContent value="categories" class="mt-6">
            <Tabs value="expense" class="w-full">
                <TabsList class="grid w-full grid-cols-2 md:w-auto">
                    <TabsTrigger value="expense">فئات المصروفات</TabsTrigger>
                    <TabsTrigger value="income">فئات الدخل</TabsTrigger>
                </TabsList>

                <TabsContent value="expense" class="mt-4">
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between">
                            <div>
                                <CardTitle class="text-base">فئات المصروفات</CardTitle>
                                <CardDescription>أدر الفئات المخصصة للمصروفات</CardDescription>
                            </div>
                            <Button size="sm">
                                <Plus class="ml-2 size-4" />
                                إضافة فئة
                            </Button>
                        </CardHeader>
                        <CardContent>
                            <div class="flex flex-wrap gap-2">
                                {#each ['مطاعم', 'مواصلات', 'سكن', 'ترفيه', 'صحة', 'تعليم', 'تسوق', 'خدمات'] as category}
                                    <Badge variant="secondary" class="flex items-center gap-1 px-3 py-1.5">
                                        {category}
                                        <button class="mr-1 rounded-full p-0.5 hover:bg-muted">
                                            <Edit class="size-3" />
                                        </button>
                                        <button class="rounded-full p-0.5 hover:bg-destructive/20 hover:text-destructive">
                                            <Trash2 class="size-3" />
                                        </button>
                                    </Badge>
                                {/each}
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>

                <TabsContent value="income" class="mt-4">
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between">
                            <div>
                                <CardTitle class="text-base">فئات الدخل</CardTitle>
                                <CardDescription>أدر الفئات المخصصة للدخل</CardDescription>
                            </div>
                            <Button size="sm">
                                <Plus class="ml-2 size-4" />
                                إضافة فئة
                            </Button>
                        </CardHeader>
                        <CardContent>
                            <div class="flex flex-wrap gap-2">
                                <Badge variant="secondary" class="flex items-center gap-1 px-3 py-1.5">
                                    راتب
                                    <button class="mr-1 rounded-full p-0.5 hover:bg-muted">
                                        <Edit class="size-3" />
                                    </button>
                                </Badge>
                                <Badge variant="secondary" class="flex items-center gap-1 px-3 py-1.5">
                                    أخرى
                                    <button class="mr-1 rounded-full p-0.5 hover:bg-muted">
                                        <Edit class="size-3" />
                                    </button>
                                </Badge>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>
            </Tabs>
        </TabsContent>

        <!-- تغيير الراتب -->
        <TabsContent value="salary" class="mt-6">
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2 text-base">
                        <Wallet class="size-5" />
                        الراتب الشهري
                    </CardTitle>
                    <CardDescription>حدد راتبك الشهري لحساب الميزانية والتوفير</CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="space-y-2">
                        <Label for="salary">الراتب الشهري (ر.س)</Label>
                        <Input
                            id="salary"
                            type="number"
                            placeholder="0"
                            value="0"
                        />
                    </div>
                    <Button>حفظ التغييرات</Button>
                </CardContent>
            </Card>
        </TabsContent>

        <!-- تصدير التقارير -->
        <TabsContent value="export" class="mt-6">
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2 text-base">
                        <FileText class="size-5" />
                        إعداد التقارير
                    </CardTitle>
                    <CardDescription>صدّر معاملاتك وتقاريرك المالية</CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <Button variant="outline" class="flex-1">
                            <Download class="ml-2 size-4" />
                            تصدير CSV
                        </Button>
                        <Button variant="outline" class="flex-1">
                            <Download class="ml-2 size-4" />
                            تصدير PDF
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </TabsContent>
    </Tabs>
</div>
