<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: 'المساعد الذكي',
                href: '/ai-assistant',
            },
        ],
    };
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
        Card,
        CardContent,
        CardHeader,
        CardTitle,
    } from '@/components/ui/card';
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

<div class="flex h-full flex-1 flex-col">
    <Tabs value="chat" class="flex h-full flex-col">
        <div class="flex items-center justify-between border-b px-4 pt-4 md:px-6">
            <h1 class="text-xl font-bold md:text-2xl">المساعد الذكي</h1>
            <TabsList>
                <TabsTrigger value="chat">المحادثة</TabsTrigger>
                <TabsTrigger value="insights">الرؤى</TabsTrigger>
            </TabsList>
        </div>

        <TabsContent value="chat" class="flex flex-1 flex-col overflow-hidden">
            <!-- منطقة الرسائل -->
            <div class="flex flex-1 flex-col gap-4 overflow-y-auto p-4 md:p-6">
                <!-- رسالة ترحيبية من AI -->
                <div class="flex items-start gap-3">
                    <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-primary/10">
                        <Bot class="size-4 text-primary" />
                    </div>
                    <div class="flex-1 rounded-2xl rounded-tr-sm bg-muted px-4 py-3">
                        <p class="text-sm">
                            مرحباً! أنا مساعدك المالي الذكي. يمكنني مساعدتك في:
                        </p>
                        <ul class="mt-2 space-y-1 text-xs text-muted-foreground">
                            <li>• إضافة وتعديل المعاملات المالية</li>
                            <li>• الإجابة عن أسئلتك المالية</li>
                            <li>• تقديم نصائح توفير مخصصة</li>
                            <li>• تحليل أنماط صرفك</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- إجراءات سريعة -->
            <div class="flex flex-wrap gap-2 border-t px-4 py-3 md:px-6">
                {#each quickActions as action (action.label)}
                    <Button variant="outline" size="sm" class="gap-1.5">
                        <action.icon class="size-3.5" />
                        {action.label}
                    </Button>
                {/each}
            </div>

            <!-- حقل الإدخال -->
            <div class="border-t p-4 md:p-6">
                <form class="flex items-center gap-2">
                    <Input
                        bind:value={messageInput}
                        placeholder="اكتب رسالتك هنا..."
                        class="flex-1"
                    />
                    <Button type="submit" size="icon" disabled={!messageInput.trim()}>
                        <Send class="size-4" />
                    </Button>
                </form>
            </div>
        </TabsContent>

        <TabsContent value="insights" class="flex-1 overflow-y-auto p-4 md:p-6">
            <div class="space-y-4">
                <div class="flex items-center gap-2">
                    <Sparkles class="size-5 text-yellow-500" />
                    <h2 class="text-lg font-semibold">الرؤى والتحليلات</h2>
                </div>
                <p class="text-sm text-muted-foreground">
                    سيحلل المساعد الذكي أنماط صرفك ويقدم لك رؤى مخصصة هنا.
                </p>

                <div class="grid gap-4 md:grid-cols-2">
                    <Card>
                        <CardHeader class="pb-3">
                            <CardTitle class="flex items-center gap-2 text-sm">
                                <TrendingDown class="size-4 text-red-500" />
                                أنماط الصرف
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="flex h-[120px] items-center justify-center rounded-lg border border-dashed">
                                <p class="text-xs text-muted-foreground">ستظهر التحليلات هنا</p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader class="pb-3">
                            <CardTitle class="flex items-center gap-2 text-sm">
                                <Lightbulb class="size-4 text-yellow-500" />
                                اقتراحات التوفير
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="flex h-[120px] items-center justify-center rounded-lg border border-dashed">
                                <p class="text-xs text-muted-foreground">ستظهر الاقتراحات هنا</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </TabsContent>
    </Tabs>
</div>
