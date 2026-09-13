<script module lang="ts">
    import BackgroundLayout from '@/layouts/BackgroundLayout.svelte';

    export const layout = BackgroundLayout;
</script>

<script lang="ts">
    import Bot from 'lucide-svelte/icons/bot';
    import Send from 'lucide-svelte/icons/send';
    import ListChecks from 'lucide-svelte/icons/list-checks';
    import PlusCircle from 'lucide-svelte/icons/plus-circle';
    import RotateCcw from 'lucide-svelte/icons/rotate-ccw';
    import Sparkles from 'lucide-svelte/icons/sparkles';
    import Square from 'lucide-svelte/icons/square';
    import Trash2 from 'lucide-svelte/icons/trash-2';
    import TrendingDown from 'lucide-svelte/icons/trending-down';
    import type { Component, SvelteComponent } from 'svelte';
    import { untrack } from 'svelte';
    import AppHead from '@/components/AppHead.svelte';
    import AssistantAvatar from '@/components/assistant/AssistantAvatar.svelte';
    import MarkdownContent from '@/components/assistant/MarkdownContent.svelte';
    import ToolCallCard from '@/components/assistant/ToolCallCard.svelte';
    import type { ToolCallView } from '@/components/assistant/ToolCallCard.svelte';
    import TypingIndicator from '@/components/assistant/TypingIndicator.svelte';
    import { Button } from '@/components/ui/button';
    import { Textarea } from '@/components/ui/textarea';
    import { streamAssistant } from '@/lib/assistant-stream';
    import type {
        AssistantFrame,
        AssistantHistoryMessage,
    } from '@/lib/assistant-stream';

    type Message =
        | { id: string; role: 'user'; content: string }
        | {
              id: string;
              role: 'assistant';
              content: string;
              error?: boolean;
              retry?: () => void;
          }
        | { id: string; role: 'tool'; call: ToolCallView };

    let messages = $state<Message[]>([]);
    let input = $state('');
    let streaming = $state(false);

    let scrollContainer: HTMLElement | null = $state(null);
    let textareaEl: HTMLTextAreaElement | null = $state(null);

    let history = $derived(
        messages
            .filter(
                (
                    message,
                ): message is Extract<
                    Message,
                    { role: 'user' | 'assistant' }
                > => message.role !== 'tool',
            )
            .filter((message) => message.content.trim() !== '')
            .slice(-20)
            .map((message) => ({
                role: message.role,
                content: message.content,
            })),
    );

    type ExampleIcon =
        | Component<{ class?: string }>
        | (new (...args: any[]) => SvelteComponent<{ class?: string }>);

    const EXAMPLES: { text: string; icon: ExampleIcon }[] = [
        { text: 'كم صرفت هذا الشهر؟', icon: TrendingDown },
        { text: 'أضف مصروف 50 ريال قهوة أمس', icon: PlusCircle },
        { text: 'اعرض أكبر 5 مصروفات', icon: ListChecks },
        { text: 'احذف آخر عملية', icon: Trash2 },
    ];

    let showTypingIndicator = $derived.by(() => {
        if (!streaming) {
            return false;
        }

        const last = messages.length > 0 ? messages[messages.length - 1] : null;

        if (last === null) {
            return true;
        }

        if (last.role === 'tool') {
            return false;
        }

        if (last.role === 'assistant' && !last.error && last.content !== '') {
            return false;
        }

        return true;
    });

    let uid = 0;

    function nextId(): string {
        uid += 1;

        return `m${uid}`;
    }

    /**
     * Scroll to the bottom whenever the transcript grows — either a new
     * message arrives or streaming text extends the last assistant bubble.
     */
    function scheduleAutoscroll(
        messageCount: number,
        lastContentLength: number,
    ): void {
        untrack(() => {
            queueMicrotask(() => {
                void messageCount;
                void lastContentLength;

                scrollContainer?.scrollTo({
                    top: scrollContainer.scrollHeight,
                    behavior: 'smooth',
                });
            });
        });
    }

    $effect(() => {
        const last = messages.length > 0 ? messages[messages.length - 1] : null;

        scheduleAutoscroll(
            messages.length,
            last !== null && last.role !== 'tool' ? last.content.length : 0,
        );
    });

    function appendToolCallFrame(
        frame: Extract<AssistantFrame, { type: 'tool_call' }>,
    ): void {
        messages.push({
            id: nextId(),
            role: 'tool',
            call: {
                id: frame.id,
                name: frame.name,
                arguments: frame.arguments,
                status: 'running',
            },
        });
    }

    function applyToolResultFrame(
        frame: Extract<AssistantFrame, { type: 'tool_result' }>,
    ): void {
        for (let index = messages.length - 1; index >= 0; index -= 1) {
            const message = messages[index];

            if (message.role === 'tool' && message.call.id === frame.id) {
                message.call.status = frame.ok ? 'success' : 'failed';
                message.call.summary = frame.summary;
                message.call.data = frame.data;

                return;
            }
        }
    }

    function appendDelta(delta: string): void {
        const last = messages[messages.length - 1];

        if (last && last.role === 'assistant' && !last.error) {
            last.content += delta;

            return;
        }

        messages.push({ id: nextId(), role: 'assistant', content: delta });
    }

    let controller: AbortController | null = null;

    async function send(messageText: string): Promise<void> {
        const trimmed = messageText.trim();

        if (trimmed === '' || streaming) {
            return;
        }

        messages.push({ id: nextId(), role: 'user', content: trimmed });

        input = '';

        await run(trimmed);
    }

    async function run(messageText: string): Promise<void> {
        streaming = true;

        controller = new AbortController();

        const historySnapshot: AssistantHistoryMessage[] = [...history];

        try {
            await streamAssistant(
                messageText,
                historySnapshot,
                (frame) => {
                    switch (frame.type) {
                        case 'text':
                            appendDelta(frame.delta);
                            break;
                        case 'tool_call':
                            appendToolCallFrame(frame);
                            break;
                        case 'tool_result':
                            applyToolResultFrame(frame);
                            break;
                        case 'error':
                            messages.push({
                                id: nextId(),
                                role: 'assistant',
                                content: frame.message,
                                error: true,
                                retry: () => retry(messageText),
                            });
                            break;
                        case 'done':
                            break;
                    }
                },
                controller.signal,
            );
        } catch (error) {
            if ((error as Error).name !== 'AbortError') {
                messages.push({
                    id: nextId(),
                    role: 'assistant',
                    content: 'تعذر الاتصال بالمساعد. تحقق من اتصالك وحاول مرة أخرى.',
                    error: true,
                    retry: () => retry(messageText),
                });
            }
        } finally {
            controller = null;
            streaming = false;

            queueMicrotask(() => textareaEl?.focus());
        }
    }

    function retry(messageText: string): void {
        if (streaming) {
            return;
        }

        // Drop trailing error bubbles.
        while (messages.length > 0) {
            const last = messages[messages.length - 1];

            if (last.role === 'assistant' && last.error) {
                messages.pop();
                continue;
            }

            break;
        }

        void run(messageText);
    }

    function stop(): void {
        controller?.abort();
        controller = null;
        streaming = false;
    }

    function resetConversation(): void {
        stop();
        messages = [];
    }

    function handleKeydown(event: KeyboardEvent): void {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();

            void send(input);
        }
    }
</script>

<svg width="0" height="0" class="absolute h-0 w-0 pointer-events-none">
    <defs>
        <linearGradient id="icon-grad" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" stop-color="#3c3f98" />
            <stop offset="100%" stop-color="#8C8DD8" />
        </linearGradient>
    </defs>
</svg>

<AppHead title="المساعد المالي" />

<!-- الحاوية الأساسية مطابقة للداشبورد -->
<div class="mx-auto w-full max-w-5xl p-4 sm:p-8">
    <div class="flex h-[calc(100vh-8rem)] min-h-[600px] flex-col gap-4 md:gap-6">
        
        <!-- Header: مطابق لتصميم العناوين في الداشبورد -->
        <div class="mb-2 flex items-center justify-between">
            <div class="flex flex-col">
                <h1 class="text-xl font-bold text-slate-800 drop-shadow-sm dark:text-white md:text-2xl">
                    المساعد الذكي
                </h1>
                <p class="mt-1 flex items-center gap-1.5 text-xs font-medium text-slate-500 dark:text-slate-400">
                    <span
                        class="size-1.5 rounded-full bg-emerald-500 {streaming ? 'animate-pulse' : ''}"
                    ></span>
                    {streaming ? 'يكتب الآن…' : 'متصل'}
                </p>
            </div>
            
            <div class="flex items-center gap-3">
                <Button
                    variant="outline"
                    size="sm"
                    class="border-white/40 bg-white/50 text-slate-700 shadow-sm backdrop-blur-md transition-colors hover:bg-white/60 hover:text-[#6b6ec6] dark:border-slate-600/50 dark:bg-slate-800/50 dark:text-slate-200 dark:hover:bg-slate-700 md:size-default"
                    onclick={resetConversation}
                    disabled={messages.length === 0}
                >
                    <RotateCcw class="size-4 md:ml-1.5" />
                    <span class="hidden md:inline">محادثة جديدة</span>
                </Button>
            </div>
        </div>

        <!-- حاوية المحادثة الرئيسية (نفس ستايل كروت الداشبورد) -->
        <div class="flex flex-1 flex-col overflow-hidden rounded-2xl border border-white/30 bg-white/50 shadow-2xl backdrop-blur-xl dark:border-slate-700/50 dark:bg-slate-900/60">
            
            <!-- Messages Area -->
            <div
                bind:this={scrollContainer}
                class="flex-1 overflow-y-auto scroll-smooth px-4 py-6 focus-visible:outline-none md:px-6 md:py-8"
                aria-live="polite"
                aria-label="منطقة الرسائل"
            >
                <div class="mx-auto flex min-h-full w-full max-w-3xl flex-col gap-5">
                    {#if messages.length === 0}
                        <!-- Welcome state -->
                        <div class="flex flex-1 flex-col items-center justify-center gap-8 py-10 text-center">
                            <div class="flex flex-col items-center gap-6">
                                <!-- أيقونة الروبوت بتدرج الأزرار -->
                                <div class="relative flex items-center justify-center">
                                    <div class="absolute inset-0 -z-10 rounded-full bg-gradient-to-r from-[#6b6ec6]/30 to-[#8C8DD8]/30 blur-2xl"></div>
                                    <div class="flex size-20 items-center justify-center rounded-2xl bg-gradient-to-r from-[#3c3f98] via-[#6b6ec6] to-[#6b6ec6] shadow-xl animate-fade-in-up">
                                        <Bot class="size-10 text-white" />
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <h2 class="text-xl font-bold text-slate-800 drop-shadow-sm dark:text-white">
                                        كيف أقدر أساعدك؟
                                    </h2>
                                    <p class="mx-auto max-w-sm text-sm text-slate-600 dark:text-slate-300">
                                        اسأل عن مصروفاتك، أضِف أو عدّل أو احذف عمليات — كل شيء بالمحادثة.
                                    </p>
                                </div>
                            </div>

                            <!-- أزرار الأمثلة بتصميم زجاجي -->
                            <div class="grid w-full gap-3 sm:grid-cols-2">
                                {#each EXAMPLES as example (example.text)}
                                    <button
                                        type="button"
                                        class="group flex items-center gap-3 rounded-2xl border border-white/40 bg-white/40 px-4 py-3.5 text-start text-sm shadow-sm backdrop-blur-md transition-all hover:bg-white/60 hover:text-[#6b6ec6] active:scale-[0.98] dark:border-slate-600/50 dark:bg-slate-800/40 dark:text-slate-200 dark:hover:bg-slate-700/60"
                                        onclick={() => void send(example.text)}
                                    >
                                        <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-white/50 text-[#8C8DD8] shadow-inner transition-colors group-hover:bg-white/80 dark:bg-slate-700/50">
                                            <example.icon class="size-4.5" />
                                        </span>
                                        <span class="min-w-0 flex-1 font-medium">{example.text}</span>
                                    </button>
                                {/each}
                            </div>

                            <p class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                                <Sparkles class="size-3.5 text-amber-200" />
                                مدعوم بالذكاء الاصطناعي — راجِع الملخص بعد كل عملية
                            </p>
                        </div>
                    {:else}
                        {#each messages as message (message.id)}
                            {#if message.role === 'user'}
                                <!-- رسالة المستخدم -->
                                <div class="flex animate-fade-in-up justify-end">
                                    <div
                                        class="max-w-[85%] whitespace-pre-wrap rounded-2xl rounded-ee-sm bg-gradient-to-r from-[#3c3f98] to-[#6b6ec6] px-4 py-2.5 text-sm leading-relaxed text-white shadow-md tabular-nums"
                                        dir="auto"
                                    >
                                        {message.content}
                                    </div>
                                </div>
                            {:else if message.role === 'tool'}
                                <div class="animate-fade-in-up ps-10">
                                    <ToolCallCard call={message.call} />
                                </div>
                            {:else}
                                <!-- رسالة المساعد -->
                                <div class="flex animate-fade-in-up items-start gap-2.5">
                                    <AssistantAvatar size="sm" class="mt-0.5 shadow-sm" />
                                    <div class="flex min-w-0 max-w-[85%] flex-col gap-1.5">
                                        <div
                                            class="rounded-2xl rounded-ss-sm px-4 py-3 text-sm leading-relaxed shadow-sm backdrop-blur-md
                                                {message.error
                                                ? 'whitespace-pre-wrap border border-rose-400/50 bg-rose-50/70 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400'
                                                : 'border border-white/40 bg-white/70 text-slate-800 dark:border-slate-600/50 dark:bg-slate-800/70 dark:text-slate-100'}"
                                            dir="auto"
                                        >
                                            {#if message.error}
                                                {message.content}
                                            {:else}
                                                <MarkdownContent source={message.content} />
                                            {/if}
                                        </div>
                                        {#if message.retry}
                                            <button
                                                type="button"
                                                class="flex w-fit items-center gap-1.5 rounded-md px-1 py-0.5 text-xs font-medium text-slate-500 transition-colors hover:text-[#6b6ec6] disabled:opacity-50 dark:text-slate-400 dark:hover:text-white"
                                                onclick={message.retry}
                                                disabled={streaming}
                                            >
                                                <RotateCcw class="size-3" />
                                                إعادة المحاولة
                                            </button>
                                        {/if}
                                    </div>
                                </div>
                            {/if}
                        {/each}

                        {#if showTypingIndicator}
                            <div class="flex animate-fade-in items-start gap-2.5">
                                <AssistantAvatar size="sm" class="mt-0.5 shadow-sm" />
                                <div class="flex items-center rounded-2xl rounded-ss-sm border border-white/40 bg-white/70 px-4 py-3.5 shadow-sm backdrop-blur-md dark:border-slate-600/50 dark:bg-slate-800/70">
                                    <TypingIndicator />
                                </div>
                            </div>
                        {/if}
                    {/if}
                </div>
            </div>

            <!-- Composer: صندوق الإدخال الزجاجي -->
            <div class="border-t border-white/20 bg-white/30 p-4 dark:bg-slate-900/40 md:px-6 md:py-5">
                <div class="mx-auto w-full max-w-3xl">
                    <div class="flex items-end gap-2 rounded-3xl border border-white/40 bg-white/60 p-1.5 shadow-inner backdrop-blur-md transition-colors focus-within:border-[#8C8DD8] focus-within:ring-2 focus-within:ring-[#8C8DD8]/30 dark:border-slate-600/50 dark:bg-slate-800/60">
                        <Textarea
                            bind:ref={textareaEl}
                            bind:value={input}
                            onkeydown={handleKeydown}
                            rows={1}
                            placeholder="اكتب رسالتك هنا..."
                            disabled={streaming}
                            class="max-h-40 min-h-11 resize-none border-0 bg-transparent px-4 py-2.5 text-slate-800 shadow-none placeholder:text-slate-500 focus-visible:ring-0 dark:text-white"
                            dir="auto"
                        />
                        {#if streaming}
                            <Button
                                type="button"
                                variant="destructive"
                                size="icon"
                                class="size-11 shrink-0 rounded-full shadow-lg transition-transform hover:scale-105 active:scale-90"
                                onclick={stop}
                                aria-label="إيقاف التوليد"
                            >
                                <Square class="size-4 fill-current" />
                            </Button>
                        {:else}
                            <Button
                                type="button"
                                size="icon"
                                class="size-11 shrink-0 rounded-full border-none bg-gradient-to-r from-[#3c3f98] via-[#6b6ec6] to-[#6b6ec6] text-white shadow-lg transition-transform hover:scale-105 active:scale-90 disabled:opacity-50"
                                onclick={() => void send(input)}
                                disabled={input.trim() === ''}
                                aria-label="إرسال"
                            >
                                <!-- تعديل المسافة -mr-1 لجعل الطائرة الورقية تبدو في المنتصف بصرياً -->
                                <Send class="size-5 -mr-1" />
                            </Button>
                        {/if}
                    </div>
                    <p class="mt-2 text-center text-[0.7rem] font-medium text-slate-500 dark:text-slate-400">
                        Enter للإرسال · Shift+Enter لسطر جديد
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>