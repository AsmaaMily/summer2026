<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: 'سجل المعاملات',
                href: '/transactions',
            },
        ],
    };
</script>

<script lang="ts">
    import {
        Plus,
        Search,
        Trash2,
        Sparkles,
        ReceiptText,
    } from 'lucide-svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import {
        Card,
        CardContent,
        CardHeader,
        CardTitle,
    } from '@/components/ui/card';
    import {
        Select,
        SelectContent,
        SelectItem,
        SelectTrigger,
    } from '@/components/ui/select';

    let searchQuery = $state('');
    let filterCategory = $state('all');
    let filterType = $state('all');
    let filterPayment = $state('all');
</script>

<AppHead title="سجل المعاملات" />

<div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold md:text-2xl">سجل المعاملات</h1>
        <div class="flex items-center gap-2">
            <Button variant="outline" size="sm">
                <Sparkles class="ml-2 size-4" />
                <span class="hidden md:inline">تصنيف تلقائي</span>
            </Button>
            <Button size="sm">
                <Plus class="ml-2 size-4" />
                <span class="hidden md:inline">إضافة معاملة</span>
            </Button>
        </div>
    </div>

    <!-- البحث والتصفية -->
    <Card>
        <CardContent class="pt-6">
            <div class="flex flex-col gap-3 md:flex-row md:items-center">
                <div class="relative flex-1">
                    <Search class="absolute right-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        bind:value={searchQuery}
                        placeholder="بحث في المعاملات..."
                        class="pr-10"
                    />
                </div>
                <div class="flex flex-wrap gap-2">
                    <Select bind:value={filterType} type="single">
                        <SelectTrigger class="w-[130px]">
                            <span>
                                {filterType === 'all' ? 'النوع' : filterType === 'income' ? 'دخل' : 'مصروف'}
                            </span>
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">الكل</SelectItem>
                            <SelectItem value="income">دخل</SelectItem>
                            <SelectItem value="expense">مصروف</SelectItem>
                        </SelectContent>
                    </Select>
                    <Select bind:value={filterCategory} type="single">
                        <SelectTrigger class="w-[130px]">
                            <span>الفئة</span>
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">الكل</SelectItem>
                            <SelectItem value="food">مطاعم</SelectItem>
                            <SelectItem value="transport">مواصلات</SelectItem>
                            <SelectItem value="housing">سكن</SelectItem>
                            <SelectItem value="entertainment">ترفيه</SelectItem>
                            <SelectItem value="health">صحة</SelectItem>
                            <SelectItem value="education">تعليم</SelectItem>
                            <SelectItem value="shopping">تسوق</SelectItem>
                            <SelectItem value="utilities">خدمات</SelectItem>
                            <SelectItem value="salary">راتب</SelectItem>
                            <SelectItem value="other">أخرى</SelectItem>
                        </SelectContent>
                    </Select>
                    <Select bind:value={filterPayment} type="single">
                        <SelectTrigger class="w-[130px]">
                            <span>
                                {filterPayment === 'all' ? 'طريقة الدفع' : filterPayment === 'cash' ? 'نقدي' : 'بطاقة'}
                            </span>
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">الكل</SelectItem>
                            <SelectItem value="cash">نقدي</SelectItem>
                            <SelectItem value="card">بطاقة</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>
        </CardContent>
    </Card>

    <!-- جدول المعاملات -->
    <Card>
        <CardHeader class="flex flex-row items-center justify-between">
            <CardTitle class="text-sm font-medium text-muted-foreground">
                0 معاملة
            </CardTitle>
            <Button variant="ghost" size="sm" disabled>
                <Trash2 class="ml-2 size-4" />
                حذف المحدد
            </Button>
        </CardHeader>
        <CardContent>
            <div class="flex min-h-[300px] flex-col items-center justify-center gap-3 rounded-lg border border-dashed">
                <ReceiptText class="size-12 text-muted-foreground/50" />
                <div class="text-center">
                    <p class="text-sm font-medium">لا توجد معاملات</p>
                    <p class="text-xs text-muted-foreground">
                        ابدأ بإضافة أول معاملة مالية لك
                    </p>
                </div>
                <Button size="sm" class="mt-2">
                    <Plus class="ml-2 size-4" />
                    إضافة معاملة
                </Button>
            </div>
        </CardContent>
    </Card>
</div>
