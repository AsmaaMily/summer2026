<script module lang="ts">
    import BackgroundLayout from '@/layouts/BackgroundLayout.svelte';

    export const layout = BackgroundLayout;
</script>

<script lang="ts">
    import ArrowUp from 'lucide-svelte/icons/arrow-up';
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
        { text: 'أضف مصروف ٥٠ ريال قهوة أمس', icon: PlusCircle },
        { text: 'اعرض أكبر ٥ مصروفات', icon: ListChecks },
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
                    content:
                        'تعذر الاتصال بالمساعد. تحقق من اتصالك وحاول مرة أخرى.',
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

<AppHead title="المساعد المالي" />

<div class="flex h-[calc(100dvh-7rem)] flex-1 flex-col">
    <!-- Header -->
    <header
        class="flex items-center justify-between gap-3 border-b border-border/60 bg-background/80 px-4 py-3 backdrop-blur-xl md:px-6"
    >
        <div class="flex min-w-0 items-center gap-3">
            <AssistantAvatar size="md" />
            <div class="min-w-0">
                <h1 class="truncate text-sm font-semibold">المساعد المالي</h1>
                <p
                    class="flex items-center gap-1.5 text-xs text-muted-foreground"
                >
                    <span
                        class="size-1.5 rounded-full bg-income {streaming
                            ? 'animate-pulse'
                            : ''}"
                    ></span>
                    {streaming ? 'يكتب الآن…' : 'متصل'}
                </p>
            </div>
        </div>
        <Button
            variant="ghost"
            size="sm"
            class="gap-1.5 text-muted-foreground"
            onclick={resetConversation}
            disabled={messages.length === 0}
        >
            <RotateCcw class="size-3.5" />
            <span class="hidden sm:inline">محادثة جديدة</span>
        </Button>
    </header>

    <!-- Messages -->
    <div
        bind:this={scrollContainer}
        class="flex-1 overflow-y-auto scroll-smooth px-4 py-6 md:px-6"
        aria-live="polite"
        aria-label="منطقة الرسائل"
    >
        <div class="mx-auto flex min-h-full w-full max-w-3xl flex-col gap-5">
            {#if messages.length === 0}
                <!-- Welcome state -->
                <div
                    class="flex flex-1 flex-col items-center justify-center gap-8 py-10 text-center"
                >
                    <div class="flex flex-col items-center gap-5">
                        <div class="relative">
                            <div
                                class="absolute inset-0 -z-10 rounded-full bg-primary/25 blur-2xl"
                            ></div>
                            <AssistantAvatar
                                size="lg"
                                class="animate-fade-in-up"
                            />
                        </div>
                        <div class="space-y-2">
                            <h2 class="text-xl font-semibold tracking-tight">
                                كيف أقدر أساعدك؟
                            </h2>
                            <p
                                class="mx-auto max-w-sm text-sm text-muted-foreground"
                            >
                                اسأل عن مصروفاتك، أضِف أو عدّل أو احذف عمليات —
                                كل شيء بالمحادثة.
                            </p>
                        </div>
                    </div>

                    <div class="grid w-full gap-2.5 sm:grid-cols-2">
                        {#each EXAMPLES as example (example.text)}
                            <button
                                type="button"
                                class="group flex items-center gap-3 rounded-xl border border-border/70 bg-card px-4 py-3.5 text-start text-sm shadow-soft transition-all hover:border-primary/40 hover:bg-accent/50 active:scale-[0.98]"
                                onclick={() => void send(example.text)}
                            >
                                <span
                                    class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary transition-colors group-hover:bg-primary/15"
                                >
                                    <example.icon class="size-4.5" />
                                </span>
                                <span class="min-w-0 flex-1"
                                    >{example.text}</span
                                >
                            </button>
                        {/each}
                    </div>

                    <p
                        class="flex items-center gap-1.5 text-xs text-muted-foreground"
                    >
                        <Sparkles class="size-3.5" />
                        مدعوم بالذكاء الاصطناعي — راجِع الملخص بعد كل عملية
                    </p>
                </div>
            {:else}
                {#each messages as message (message.id)}
                    {#if message.role === 'user'}
                        <div class="flex animate-fade-in-up justify-end">
                            <div
                                class="max-w-[85%] whitespace-pre-wrap rounded-2xl rounded-ee-md bg-primary px-4 py-2.5 text-sm leading-relaxed text-primary-foreground shadow-soft tabular-nums"
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
                        <div
                            class="flex animate-fade-in-up items-start gap-2.5"
                        >
                            <AssistantAvatar size="sm" class="mt-0.5" />
                            <div
                                class="flex min-w-0 max-w-[85%] flex-col gap-1.5"
                            >
                                <div
                                    class="rounded-2xl rounded-ss-md px-4 py-3 text-sm leading-relaxed
                                        {message.error
                                        ? 'whitespace-pre-wrap border border-destructive/40 bg-destructive/10 text-destructive'
                                        : 'border border-border/70 bg-card shadow-soft'}"
                                    dir="auto"
                                >
                                    {#if message.error}
                                        {message.content}
                                    {:else}
                                        <MarkdownContent
                                            source={message.content}
                                        />
                                    {/if}
                                </div>
                                {#if message.retry}
                                    <button
                                        type="button"
                                        class="flex w-fit items-center gap-1.5 rounded-md px-1 py-0.5 text-xs text-muted-foreground transition-colors hover:text-foreground disabled:opacity-50"
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
                        <AssistantAvatar size="sm" class="mt-0.5" />
                        <div
                            class="flex items-center rounded-2xl rounded-ss-md border border-border/70 bg-card px-4 py-3.5 shadow-soft"
                        >
                            <TypingIndicator />
                        </div>
                    </div>
                {/if}
            {/if}
        </div>
    </div>

    <!-- Composer -->
    <div
        class="border-t border-border/60 bg-background/80 px-4 pb-3 pt-3 backdrop-blur-xl md:pt-4"
    >
        <div class="mx-auto w-full max-w-3xl">
            <div
                class="flex items-end gap-2 rounded-2xl border border-border/70 bg-card p-1.5 shadow-soft transition-colors focus-within:border-primary/50 focus-within:ring-2 focus-within:ring-primary/15"
            >
                <Textarea
                    bind:ref={textareaEl}
                    bind:value={input}
                    onkeydown={handleKeydown}
                    rows={1}
                    placeholder="اكتب رسالتك…"
                    disabled={streaming}
                    class="max-h-40 min-h-11 resize-none border-0 bg-transparent px-3 py-2.5 shadow-none focus-visible:ring-0 dark:bg-transparent"
                    dir="auto"
                />
                {#if streaming}
                    <Button
                        type="button"
                        variant="destructive"
                        size="icon"
                        class="size-11 shrink-0 rounded-xl"
                        onclick={stop}
                        aria-label="إيقاف التوليد"
                    >
                        <Square class="size-4 fill-current" />
                    </Button>
                {:else}
                    <Button
                        type="button"
                        size="icon"
                        class="size-11 shrink-0 rounded-xl transition-transform active:scale-90"
                        onclick={() => void send(input)}
                        disabled={input.trim() === ''}
                        aria-label="إرسال"
                    >
                        <ArrowUp class="size-5" />
                    </Button>
                {/if}
            </div>
            <p class="mt-2 text-center text-[0.7rem] text-muted-foreground">
                Enter للإرسال · Shift+Enter لسطر جديد
            </p>
        </div>
    </div>
</div>
