<script module lang="ts">
    import BackgroundLayout from '@/layouts/BackgroundLayout.svelte';
    export const layout = BackgroundLayout;
</script>

<script lang="ts">
    import {
        Send,
        Bot,
        Lightbulb,
        Plus,
        TrendingDown,
        Sparkles,
    } from 'lucide-svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import {
        Tabs,
        TabsContent,
        TabsList,
        TabsTrigger,
    } from '@/components/ui/tabs';

    let messageInput = $state('');

    const quickActions = [
        { label: 'إضافة معاملة', icon: Plus },
        { label: 'كم صرفت هذا الشهر؟', icon: TrendingDown },
        { label: 'نصائح توفير', icon: Lightbulb },
    ];
</script>

<AppHead title="المساعد الذكي" />

<div class="mx-auto w-full max-w-5xl p-4 sm:p-8">
    <div class="flex h-full flex-1 flex-col gap-6">
        
        <!-- الترويسة الخارجية (مطابقة لباقي الصفحات) -->
        <div class="flex items-center justify-between mb-2">
            <h1 class="text-xl font-bold md:text-2xl text-slate-800 dark:text-white drop-shadow-sm">المساعد الذكي</h1>
        </div>

        <!-- حاوية زجاجية كبيرة تضم المحادثة والرؤى -->
        <!-- تم تعديل الارتفاع قليلاً ليتناسب مع خروج العنوان للأعلى -->
        <div class="flex h-[calc(100vh-12rem)] min-h-[500px] flex-col overflow-hidden rounded-3xl border border-white/30 bg-white/50 shadow-2xl backdrop-blur-xl dark:border-slate-700/50 dark:bg-slate-900/60">
            
            <Tabs value="chat" class="flex h-full flex-col">
                
                <!-- أزرار التبديل (Tabs) متوسطة في الأعلى -->
                <div class="flex justify-center border-b border-white/20 p-4">
                    <TabsList class="h-11 rounded-full border border-white/20 bg-white/30 p-1 shadow-inner dark:bg-slate-800/50">
                        <TabsTrigger value="chat" class="rounded-full px-6 data-[state=active]:bg-white data-[state=active]:text-[#6b6ec6] data-[state=active]:shadow-sm dark:data-[state=active]:bg-slate-700 dark:data-[state=active]:text-white">المحادثة</TabsTrigger>
                        <TabsTrigger value="insights" class="rounded-full px-6 data-[state=active]:bg-white data-[state=active]:text-[#6b6ec6] data-[state=active]:shadow-sm dark:data-[state=active]:bg-slate-700 dark:data-[state=active]:text-white">الرؤى</TabsTrigger>
                    </TabsList>
                </div>

                <!-- تبويب: المحادثة -->
                <TabsContent value="chat" class="mt-0 flex flex-1 flex-col overflow-hidden focus-visible:outline-none">
                    <!-- منطقة الرسائل -->
                    <div class="flex flex-1 flex-col gap-4 overflow-y-auto p-4 md:p-6">
                        <!-- رسالة ترحيبية من المساعد -->
                        <div class="flex items-start gap-3">
                            <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-r from-[#6b6ec6] to-[#8C8DD8] shadow-md">
                                <Bot class="size-5 text-white" />
                            </div>
                            <div class="flex-1 rounded-2xl rounded-tr-sm border border-white/40 bg-white/70 p-4 shadow-sm backdrop-blur-md dark:border-slate-600/50 dark:bg-slate-800/70">
                                <p class="text-sm font-medium text-slate-800 dark:text-white">
                                    مرحباً! أنا مساعدك المالي الذكي. يمكنني مساعدتك في:
                                </p>
                                <ul class="mt-3 space-y-1.5 text-xs text-slate-600 dark:text-slate-300">
                                    <li class="flex items-center gap-1.5"><div class="size-1.5 rounded-full bg-[#8C8DD8]"></div> إضافة وتعديل المعاملات المالية</li>
                                    <li class="flex items-center gap-1.5"><div class="size-1.5 rounded-full bg-[#8C8DD8]"></div> الإجابة عن أسئلتك المالية</li>
                                    <li class="flex items-center gap-1.5"><div class="size-1.5 rounded-full bg-[#8C8DD8]"></div> تقديم نصائح توفير مخصصة</li>
                                    <li class="flex items-center gap-1.5"><div class="size-1.5 rounded-full bg-[#8C8DD8]"></div> تحليل أنماط صرفك</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- إجراءات سريعة (أزرار شفافة) -->
                    <div class="flex flex-wrap gap-2 border-t border-white/20 p-4 md:px-6">
                        {#each quickActions as action (action.label)}
                            <Button variant="outline" size="sm" class="gap-1.5 rounded-full border-white/40 bg-white/40 text-slate-700 transition-colors hover:bg-white/60 hover:text-[#6b6ec6] dark:border-slate-600/50 dark:bg-slate-800/50 dark:text-slate-200 dark:hover:bg-slate-700">
                                <action.icon class="size-4 text-[#8C8DD8]" />
                                {action.label}
                            </Button>
                        {/each}
                    </div>

                    <!-- حقل الإدخال -->
                    <div class="border-t border-white/20 bg-white/10 p-4 dark:bg-slate-900/10 md:px-6">
                        <form class="flex items-center gap-3">
                            <Input
                                bind:value={messageInput}
                                placeholder="اكتب رسالتك هنا..."
                                class="h-12 flex-1 rounded-full border-white/40 bg-white/50 px-5 shadow-inner backdrop-blur-sm focus-visible:border-[#8C8DD8] focus-visible:ring-[#8C8DD8] dark:border-slate-600/50 dark:bg-slate-800/50 dark:text-white"
                            />
                            <Button type="submit" size="icon" disabled={!messageInput.trim()} class="size-12 shrink-0 rounded-full border-none bg-gradient-to-r from-[#3c3f98] via-[#6b6ec6] to-[#6b6ec6] text-white shadow-lg transition-transform hover:scale-105 hover:opacity-90 disabled:opacity-50">
                                <Send class="mr-1 size-5" dir="rtl" />
                            </Button>
                        </form>
                    </div>
                </TabsContent>

                <!-- تبويب: الرؤى والتحليلات -->
                <TabsContent value="insights" class="mt-0 flex-1 overflow-y-auto p-4 focus-visible:outline-none md:p-6">
                    <div class="space-y-6">
                        <div>
                            <div class="mb-2 flex items-center gap-2">
                                <Sparkles class="size-6 text-[#8C8DD8] drop-shadow-md" />
                                <h2 class="text-lg font-bold text-slate-800 dark:text-white">الرؤى والتحليلات</h2>
                            </div>
                            <p class="text-sm text-slate-600 dark:text-slate-300">
                                سيحلل المساعد الذكي أنماط صرفك ويقدم لك رؤى مخصصة هنا.
                            </p>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <!-- مربع أنماط الصرف -->
                            <div class="flex flex-col gap-4 rounded-2xl border border-white/30 bg-white/40 p-5 shadow-xl backdrop-blur-md dark:border-slate-700/50 dark:bg-slate-800/40">
                                <div class="flex items-center gap-2">
                                    <TrendingDown class="size-5 text-rose-400" />
                                    <h3 class="text-base font-bold text-slate-800 dark:text-white">أنماط الصرف</h3>
                                </div>
                                <div class="flex h-[120px] items-center justify-center rounded-xl border border-dashed border-slate-400/50 bg-slate-100/30 dark:border-slate-600/50 dark:bg-slate-900/30">
                                    <p class="text-sm text-slate-500 dark:text-slate-400">ستظهر التحليلات هنا</p>
                                </div>
                            </div>

                            <!-- مربع اقتراحات التوفير -->
                            <div class="flex flex-col gap-4 rounded-2xl border border-white/30 bg-white/40 p-5 shadow-xl backdrop-blur-md dark:border-slate-700/50 dark:bg-slate-800/40">
                                <div class="flex items-center gap-2">
                                    <Lightbulb class="size-5 text-[#8C8DD8]" />
                                    <h3 class="text-base font-bold text-slate-800 dark:text-white">اقتراحات التوفير</h3>
                                </div>
                                <div class="flex h-[120px] items-center justify-center rounded-xl border border-dashed border-slate-400/50 bg-slate-100/30 dark:border-slate-600/50 dark:bg-slate-900/30">
                                    <p class="text-sm text-slate-500 dark:text-slate-400">ستظهر الاقتراحات هنا</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </TabsContent>
                
            </Tabs>
        </div>
    </div>
</div>