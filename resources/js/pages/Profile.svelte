<script module lang="ts">
    import BackgroundLayout from '@/layouts/BackgroundLayout.svelte';
    export const layout = BackgroundLayout;
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
        Tabs,
        TabsContent,
        TabsList,
        TabsTrigger,
    } from '@/components/ui/tabs';
</script>

<AppHead title="الصفحة الشخصية" />

<div class="mx-auto mb-auto w-full max-w-5xl p-4 pt-6 sm:p-8">
    <div class="flex flex-col gap-6">
        
        <!-- الترويسة -->
        <div class="flex items-center justify-between mb-2">
            <h1 class="text-xl font-bold md:text-2xl text-slate-800 dark:text-white drop-shadow-sm">الصفحة الشخصية</h1>
        </div>

        <!-- التبويبات الأساسية -->
        <Tabs value="categories" class="w-full">
            
            <!-- أزرار التبديل الرئيسية (كبسولة زجاجية) -->
            <TabsList class="flex h-14 w-full rounded-full border border-white/20 bg-white/30 p-1.5 shadow-inner dark:bg-slate-800/50 sm:w-fit sm:min-w-[400px]">
                <TabsTrigger value="categories" class="flex-1 rounded-full text-sm data-[state=active]:bg-white data-[state=active]:text-[#6b6ec6] data-[state=active]:shadow-sm dark:data-[state=active]:bg-slate-700 dark:data-[state=active]:text-white">الفئات</TabsTrigger>
                <TabsTrigger value="salary" class="flex-1 rounded-full text-sm data-[state=active]:bg-white data-[state=active]:text-[#6b6ec6] data-[state=active]:shadow-sm dark:data-[state=active]:bg-slate-700 dark:data-[state=active]:text-white">الراتب</TabsTrigger>
                <TabsTrigger value="export" class="flex-1 rounded-full text-sm data-[state=active]:bg-white data-[state=active]:text-[#6b6ec6] data-[state=active]:shadow-sm dark:data-[state=active]:bg-slate-700 dark:data-[state=active]:text-white">التقارير</TabsTrigger>
            </TabsList>

            <!-- 1. إدارة الفئات -->
            <TabsContent value="categories" class="mt-6 focus-visible:outline-none">
                <Tabs value="expense" class="w-full">
                    
                    <!-- أزرار التبديل الفرعية (الدخل / المصروفات) -->
                    <TabsList class="flex h-12 w-full rounded-full border border-white/20 bg-white/20 p-1 shadow-inner dark:bg-slate-800/40 sm:w-[300px]">
                        <TabsTrigger value="expense" class="flex-1 rounded-full text-xs sm:text-sm data-[state=active]:bg-white data-[state=active]:text-[#6b6ec6] dark:data-[state=active]:bg-slate-700 dark:data-[state=active]:text-white">المصروفات</TabsTrigger>
                        <TabsTrigger value="income" class="flex-1 rounded-full text-xs sm:text-sm data-[state=active]:bg-white data-[state=active]:text-[#6b6ec6] dark:data-[state=active]:bg-slate-700 dark:data-[state=active]:text-white">الدخل</TabsTrigger>
                    </TabsList>

                    <!-- فئات المصروفات -->
                    <TabsContent value="expense" class="mt-4 focus-visible:outline-none">
                        <div class="flex flex-col gap-4 rounded-2xl border border-white/30 bg-white/50 p-6 shadow-2xl backdrop-blur-xl dark:border-slate-700/50 dark:bg-slate-900/60 sm:p-8">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h2 class="text-base font-bold text-slate-800 dark:text-white">فئات المصروفات</h2>
                                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">أدر الفئات المخصصة للمصروفات</p>
                                </div>
                                <Button class="rounded-xl border-none bg-gradient-to-r from-[#3c3f98] via-[#6b6ec6] to-[#6b6ec6] text-white shadow-lg transition-transform hover:scale-105 hover:opacity-90">
                                    <Plus class="ml-2 size-4" />
                                    إضافة فئة
                                </Button>
                            </div>
                            
                            <div class="mt-2 flex flex-wrap gap-3">
                                {#each ['مطاعم', 'مواصلات', 'سكن', 'ترفيه', 'صحة', 'تعليم', 'تسوق', 'خدمات'] as category}
                                    <!-- كبسولة الفئة الزجاجية -->
                                    <div class="flex items-center gap-2 rounded-full border border-white/40 bg-white/60 py-1.5 pl-2 pr-4 text-sm font-medium text-slate-700 shadow-sm backdrop-blur-md transition-colors hover:bg-white/80 dark:border-slate-600/50 dark:bg-slate-800/60 dark:text-slate-200 dark:hover:bg-slate-700/80">
                                        <span>{category}</span>
                                        <div class="ml-1 flex items-center gap-1 border-r border-slate-300/50 pr-2 dark:border-slate-600/50">
                                            <button class="rounded-full p-1.5 text-slate-400 transition-colors hover:bg-white hover:text-[#6b6ec6] dark:hover:bg-slate-600 dark:hover:text-white" title="تعديل">
                                                <Edit class="size-3.5" />
                                            </button>
                                            <button class="rounded-full p-1.5 text-slate-400 transition-colors hover:bg-rose-100 hover:text-rose-600 dark:hover:bg-rose-900/30 dark:hover:text-rose-400" title="حذف">
                                                <Trash2 class="size-3.5" />
                                            </button>
                                        </div>
                                    </div>
                                {/each}
                            </div>
                        </div>
                    </TabsContent>

                    <!-- فئات الدخل -->
                    <TabsContent value="income" class="mt-4 focus-visible:outline-none">
                        <div class="flex flex-col gap-4 rounded-2xl border border-white/30 bg-white/50 p-6 shadow-2xl backdrop-blur-xl dark:border-slate-700/50 dark:bg-slate-900/60 sm:p-8">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h2 class="text-base font-bold text-slate-800 dark:text-white">فئات الدخل</h2>
                                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">أدر الفئات المخصصة للدخل</p>
                                </div>
                                <Button class="rounded-xl border-none bg-gradient-to-r from-[#3c3f98] via-[#6b6ec6] to-[#6b6ec6] text-white shadow-lg transition-transform hover:scale-105 hover:opacity-90">
                                    <Plus class="ml-2 size-4" />
                                    إضافة فئة
                                </Button>
                            </div>
                            
                            <div class="mt-2 flex flex-wrap gap-3">
                                {#each ['راتب', 'أخرى'] as category}
                                    <div class="flex items-center gap-2 rounded-full border border-white/40 bg-white/60 py-1.5 pl-2 pr-4 text-sm font-medium text-slate-700 shadow-sm backdrop-blur-md transition-colors hover:bg-white/80 dark:border-slate-600/50 dark:bg-slate-800/60 dark:text-slate-200 dark:hover:bg-slate-700/80">
                                        <span>{category}</span>
                                        <div class="ml-1 flex items-center gap-1 border-r border-slate-300/50 pr-2 dark:border-slate-600/50">
                                            <button class="rounded-full p-1.5 text-slate-400 transition-colors hover:bg-white hover:text-[#6b6ec6] dark:hover:bg-slate-600 dark:hover:text-white" title="تعديل">
                                                <Edit class="size-3.5" />
                                            </button>
                                            <button class="rounded-full p-1.5 text-slate-400 transition-colors hover:bg-rose-100 hover:text-rose-600 dark:hover:bg-rose-900/30 dark:hover:text-rose-400" title="حذف">
                                                <Trash2 class="size-3.5" />
                                            </button>
                                        </div>
                                    </div>
                                {/each}
                            </div>
                        </div>
                    </TabsContent>
                </Tabs>
            </TabsContent>

            <!-- 2. تغيير الراتب -->
            <TabsContent value="salary" class="mt-6 focus-visible:outline-none">
                <div class="flex flex-col gap-6 rounded-2xl border border-white/30 bg-white/50 p-6 shadow-2xl backdrop-blur-xl dark:border-slate-700/50 dark:bg-slate-900/60 sm:p-8">
                    <div>
                        <div class="flex items-center gap-2">
                            <Wallet class="size-5 text-[#8C8DD8]" />
                            <h2 class="text-base font-bold text-slate-800 dark:text-white">الراتب الشهري</h2>
                        </div>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">حدد راتبك الشهري لحساب الميزانية والتوفير بدقة</p>
                    </div>
                    
                    <div class="flex flex-col gap-4 sm:max-w-sm">
                        <div class="space-y-2">
                            <Label for="salary" class="text-sm font-medium text-slate-700 dark:text-slate-300">الراتب الشهري (ر.س)</Label>
                            <Input
                                id="salary"
                                type="number"
                                placeholder="0"
                                value="0"
                                class="h-12 rounded-xl border-white/40 bg-white/50 px-4 shadow-inner backdrop-blur-sm focus-visible:border-[#8C8DD8] focus-visible:ring-[#8C8DD8] dark:border-slate-600/50 dark:bg-slate-800/50 dark:text-white"
                            />
                        </div>
                        <Button class="h-12 w-full rounded-xl border-none bg-gradient-to-r from-[#3c3f98] via-[#6b6ec6] to-[#6b6ec6] text-white shadow-lg transition-transform hover:scale-105 hover:opacity-90">
                            حفظ التغييرات
                        </Button>
                    </div>
                </div>
            </TabsContent>

            <!-- 3. تصدير التقارير -->
            <TabsContent value="export" class="mt-6 focus-visible:outline-none">
                <div class="flex flex-col gap-6 rounded-2xl border border-white/30 bg-white/50 p-6 shadow-2xl backdrop-blur-xl dark:border-slate-700/50 dark:bg-slate-900/60 sm:p-8">
                    <div>
                        <div class="flex items-center gap-2">
                            <FileText class="size-5 text-[#8C8DD8]" />
                            <h2 class="text-base font-bold text-slate-800 dark:text-white">إعداد التقارير</h2>
                        </div>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">صدّر معاملاتك وتقاريرك المالية للاحتفاظ بها أو طباعتها</p>
                    </div>
                    
                    <div class="flex flex-row gap-3 sm:flex-row sm:max-w-md justify-center">
                        <div class= "h-18">
                            <Button variant="outline" class="!h-16 flex-1 rounded-xl border-white/40 bg-white/40 text-slate-700 shadow-sm backdrop-blur-md transition-colors hover:bg-white/60 hover:text-[#6b6ec6] dark:border-slate-600/50 dark:bg-slate-800/50 dark:text-slate-200 dark:hover:bg-slate-700">
                            <Download class="ml-2 size-4" />
                            تصدير كـ CSV
                            </Button>
                        </div>
                        
                        <div class="h-18">
                            <Button variant="outline" class="!h-16 flex-1 rounded-xl border-white/40 bg-white/40 text-slate-700 shadow-sm backdrop-blur-md transition-colors hover:bg-white/60 hover:text-rose-400 dark:border-slate-600/50 dark:bg-slate-800/50 dark:text-slate-200 dark:hover:bg-slate-700 dark:hover:text-rose-400">
                            <Download class="ml-2 size-4" />
                            تصدير كـ PDF
                            </Button>
                        </div>
                        
                    </div>
                </div>
            </TabsContent>
            
        </Tabs>
    </div>
</div>