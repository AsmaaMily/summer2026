<script module lang="ts">
    import BackgroundLayout from '@/layouts/BackgroundLayout.svelte';
    export const layout = BackgroundLayout;
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

<!-- تدرج مخفي يمكن استخدامه لعدة أيقونات -->
<svg width="0" height="0" class="absolute hidden">
    <defs>
        <linearGradient id="icon-grad" x1="0%" y1="0%" x2="100%" y2="0%">
            <!-- لون البداية -->
            <stop offset="0%" stop-color="#3c3f98" />
            <!-- لون النهاية -->
            <stop offset="100%" stop-color="#8C8DD8" />
        </linearGradient>
    </defs>
</svg>

<AppHead title="سجل المعاملات" />

<!-- إضافة mb-auto هنا هي السر لإجبار الصفحة على البدء من الأعلى دائماً في الجوال -->
<div class="mx-auto mb-auto w-full max-w-5xl p-4 pt-6 sm:p-8">
    <div class="flex flex-col gap-6">
        
        <!-- الترويسة وأزرار الإجراءات -->
<div class="flex items-center justify-between mb-2">
    <h1 class="text-xl font-bold md:text-2xl text-slate-800 dark:text-white drop-shadow-sm">سجل المعاملات</h1>
    
    <div class="flex items-center gap-3">
        
        <!-- زر التصنيف التلقائي (رجعناه للانحناء الافتراضي) -->
        <Button variant="outline" size="sm" class="md:size-default border-white/40 bg-white/40 text-slate-700 shadow-sm backdrop-blur-md transition-colors hover:bg-white/60 hover:text-[#6b6ec6] dark:border-slate-600/50 dark:bg-slate-800/50 dark:text-slate-200 dark:hover:bg-slate-700">
            <Sparkles class="ml-2 size-4 text-white" />
            <span class="hidden md:inline">تصنيف تلقائي</span>
        </Button>
        
        <!-- زر إضافة معاملة (نسخة طبق الأصل من الداشبورد) -->
        <Button size="sm" class="md:size-default border-none bg-gradient-to-r from-[#3c3f98] via-[#6b6ec6] to-[#6b6ec6] text-white transition-opacity hover:opacity-90 shadow-lg">
            <Plus class="ml-2 size-4" />
            <span class="hidden md:inline">إضافة معاملة</span>
        </Button>
        
    </div>
</div>

        <!-- 1. صندوق البحث والتصفية (تم التعديل إلى rounded-2xl) -->
        <div class="flex flex-col gap-4 rounded-2xl border border-white/30 bg-white/50 p-6 shadow-2xl backdrop-blur-xl dark:border-slate-700/50 dark:bg-slate-900/60 sm:p-8">
            <div class="flex flex-col gap-4 md:flex-row md:items-center">
                
                <!-- حقل البحث -->
                <div class="relative flex-1">
                    <Search class="absolute right-4 top-1/2 size-4 -translate-y-1/2 text-slate-500 dark:text-slate-400" />
                    <Input
                        bind:value={searchQuery}
                        placeholder="بحث في المعاملات..."
                        class="h-12 rounded-xl border-white/40 bg-white/50 pr-11 shadow-inner backdrop-blur-sm focus-visible:border-[#8C8DD8] focus-visible:ring-[#8C8DD8] dark:border-slate-600/50 dark:bg-slate-800/50 dark:text-white"
                    />
                </div>

                <!-- فلاتر التصفية -->
                <div class="flex flex-wrap gap-2">
                    <Select bind:value={filterType} type="single">
                        <SelectTrigger class="h-12 w-[110px] sm:w-[130px] rounded-xl border-white/40 bg-white/50 backdrop-blur-md focus:ring-[#8C8DD8] dark:border-slate-600/50 dark:bg-slate-800/50">
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
                        <SelectTrigger class="h-12 w-[110px] sm:w-[130px] rounded-xl border-white/40 bg-white/50 backdrop-blur-md focus:ring-[#8C8DD8] dark:border-slate-600/50 dark:bg-slate-800/50">
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
                        <SelectTrigger class="h-12 w-[110px] sm:w-[130px] rounded-xl border-white/40 bg-white/50 backdrop-blur-md focus:ring-[#8C8DD8] dark:border-slate-600/50 dark:bg-slate-800/50">
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
        </div>

        <!-- 2. صندوق جدول المعاملات (تم التعديل إلى rounded-2xl) -->
        <div class="flex flex-col gap-4 rounded-2xl border border-white/30 bg-white/50 p-6 shadow-2xl backdrop-blur-xl dark:border-slate-700/50 dark:bg-slate-900/60 sm:p-8">
            
            <div class="flex flex-row items-center justify-between border-b border-slate-300/30 pb-4 dark:border-slate-700/50">
                <h2 class="text-sm font-bold text-slate-600 dark:text-slate-300">
                    0 معاملة
                </h2>
                <!-- زر الحذف -->
                <Button variant="ghost" size="sm" disabled class="rounded-lg text-rose-400 hover:bg-rose-500/10 hover:text-rose-600 disabled:opacity-50">
                    <Trash2 class="ml-2 size-4" />
                    حذف المحدد
                </Button>
            </div>
            
            <!-- الحالة الفارغة (Empty State) -->
            <div class="flex min-h-[300px] flex-col items-center justify-center gap-3 rounded-xl border border-dashed border-slate-400/50 bg-slate-100/30 p-6 dark:border-slate-600/50 dark:bg-slate-900/30">
                <ReceiptText class="size-12 text-[#8C8DD8]/60" />
                <div class="text-center">
                    <p class="text-sm font-bold text-slate-700 dark:text-slate-300">لا توجد معاملات</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                        ابدأ بإضافة أول معاملة مالية لك
                    </p>
                </div>
                <!-- زر الإضافة الداخلي -->
                <Button size="sm" class="mt-3 rounded-l border-none bg-gradient-to-r from-[#3c3f98] via-[#6b6ec6] to-[#6b6ec6] text-white shadow-lg transition-transform hover:scale-105 hover:opacity-90">
                    <Plus class="ml-2 size-4" />
                    إضافة معاملة
                </Button>
            </div>
            
        </div>
    </div>
</div>