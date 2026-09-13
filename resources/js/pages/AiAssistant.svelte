<script module lang="ts">
    import BackgroundLayout from '@/layouts/BackgroundLayout.svelte';
    export const layout = BackgroundLayout;
</script>

<script lang="ts">
    import {
        Bot,
        Send,
        Square,
        RotateCcw,
        Loader2,
        Check,
        X,
        Search,
        Plus,
        Pencil,
        Trash2,
        Wrench,
        ChevronDown,
        AlertCircle,
    } from 'lucide-svelte';
    import type { Component } from 'svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Button } from '@/components/ui/button';
    import { stream as assistantStream } from '@/routes/ai-assistant';
    import { isRtlText, renderMarkdown } from '@/lib/markdown';

    type ToolStatus = 'running' | 'done' | 'failed';

    interface ToolCallCard {
        id: string;
        name: string;
        args: Record<string, unknown>;
        status: ToolStatus;
        summary: string;
        open: boolean;
    }

    interface ChatMessage {
        id: string;
        role: 'user' | 'assistant';
        content: string;
        html: string;
        tools: ToolCallCard[];
        error: boolean;
        stopped: boolean;
    }

    const REQUEST_TIMEOUT_MS = 300_000;
    const RENDER_INTERVAL_MS = 60;

    const TOOL_META: Record<string, { label: string; icon: Component<{ class?: string }> }> = {
        ListTransactions: { label: 'عرض العمليات', icon: Search },
        CreateTransactions: { label: 'إضافة عمليات', icon: Plus },
        UpdateTransactions: { label: 'تعديل عمليات', icon: Pencil },
        DeleteTransactions: { label: 'حذف عمليات', icon: Trash2 },
    };

    const ARG_LABELS: Record<string, string> = {
        id: 'المعرف',
        ids: 'المعرفات',
        date: 'التاريخ',
        date_from: 'من تاريخ',
        date_to: 'إلى تاريخ',
        type: 'النوع',
        category: 'الفئة',
        category_id: 'معرف الفئة',
        amount: 'المبلغ',
        min_amount: 'أدنى مبلغ',
        max_amount: 'أقصى مبلغ',
        search: 'بحث',
        sort: 'الترتيب',
        limit: 'الحد',
        notes: 'الملاحظة',
        payment_method: 'طريقة الدفع',
        is_recurring: 'متكررة',
        transactions: 'العمليات',
        updates: 'التعديلات',
    };

    const EXAMPLES = [
        'كم صرفت هذا الشهر؟',
        'أضف مصروف ٥٠ ريال قهوة أمس',
        'اعرض أكبر ٥ مصروفات',
        'احذف آخر عملية',
    ];

    let messages = $state<ChatMessage[]>([]);
    let input = $state('');
    let streaming = $state(false);

    let scrollEl: HTMLElement | undefined = $state();
    let inputEl: HTMLTextAreaElement | undefined = $state();
    let autoScroll = true;

    let abortController: AbortController | null = null;
    let timedOut = false;

    let lastRenderAt = 0;
    let renderTimer: ReturnType<typeof setTimeout> | null = null;

    const isEmpty = $derived(messages.length === 0);

    function uid(): string {
        return crypto.randomUUID();
    }

    function makeMessage(role: 'user' | 'assistant'): ChatMessage {
        return {
            id: uid(),
            role,
            content: '',
            html: '',
            tools: [],
            error: false,
            stopped: false,
        };
    }

    function readXsrfToken(): string {
        const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);

        return match ? decodeURIComponent(match[1]) : '';
    }

    /**
     * Re-render the streamed markdown at most ~16 times per second. The final
     * render on `done` always runs immediately so nothing is left behind.
     */
    function scheduleRender(message: ChatMessage, immediate = false): void {
        const run = (): void => {
            message.html = renderMarkdown(message.content);
            lastRenderAt = performance.now();
            renderTimer = null;
        };

        if (immediate) {
            if (renderTimer) {
                clearTimeout(renderTimer);
            }

            run();

            return;
        }

        const elapsed = performance.now() - lastRenderAt;

        if (elapsed >= RENDER_INTERVAL_MS) {
            run();
        } else if (!renderTimer) {
            renderTimer = setTimeout(run, RENDER_INTERVAL_MS - elapsed);
        }
    }

    function conversationHistory(): Array<{ role: 'user' | 'assistant'; content: string }> {
        return messages
            .filter((message) => message.content.trim() !== '' && !message.error)
            .map((message) => ({ role: message.role, content: message.content }));
    }

    async function send(text: string): Promise<void> {
        const trimmed = text.trim();

        if (!trimmed || streaming) {
            return;
        }

        input = '';

        messages.push({ ...makeMessage('user'), content: trimmed });
        messages.push(makeMessage('assistant'));

        const assistant = messages[messages.length - 1];

        await streamResponse(trimmed, assistant);
    }

    async function retry(failed: ChatMessage): Promise<void> {
        if (streaming) {
            return;
        }

        const index = messages.indexOf(failed);

        if (index === -1) {
            return;
        }

        let userText = '';

        for (let i = index - 1; i >= 0; i -= 1) {
            if (messages[i].role === 'user') {
                userText = messages[i].content;
                break;
            }
        }

        if (!userText) {
            return;
        }

        messages.splice(index, 1);
        messages.push(makeMessage('assistant'));

        const assistant = messages[messages.length - 1];

        await streamResponse(userText, assistant);
    }

    async function streamResponse(text: string, assistant: ChatMessage): Promise<void> {
        streaming = true;
        timedOut = false;

        const controller = new AbortController();
        abortController = controller;

        const timeoutId = setTimeout(() => {
            timedOut = true;
            controller.abort();
        }, REQUEST_TIMEOUT_MS);

        try {
            const response = await fetch(assistantStream.url(), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'text/event-stream',
                    'X-XSRF-TOKEN': readXsrfToken(),
                },
                body: JSON.stringify({ message: text, history: conversationHistory() }),
                signal: controller.signal,
            });

            if (!response.ok || !response.body) {
                throw new Error(`HTTP ${response.status}`);
            }

            const reader = response.body.getReader();
            const decoder = new TextDecoder();
            let buffer = '';

            const handleFrame = (frame: Record<string, unknown>): void => {
                const type = frame.type as string;

                if (type === 'text') {
                    assistant.content += frame.delta as string;
                    scheduleRender(assistant);
                } else if (type === 'tool_call') {
                    assistant.tools.push({
                        id: frame.id as string,
                        name: frame.name as string,
                        args: (frame.arguments ?? {}) as Record<string, unknown>,
                        status: 'running',
                        summary: '',
                        open: false,
                    });
                } else if (type === 'tool_result') {
                    const card = assistant.tools.find((tool) => tool.id === frame.id);

                    if (card) {
                        card.status = frame.ok ? 'done' : 'failed';
                        card.summary = frame.summary as string;
                    }
                } else if (type === 'error') {
                    assistant.error = true;
                    assistant.content = assistant.content
                        ? assistant.content
                        : (frame.message as string);
                }
            };

            for (;;) {
                const { done, value } = await reader.read();

                if (done) {
                    break;
                }

                buffer += decoder.decode(value, { stream: true });

                const blocks = buffer.split('\n\n');
                buffer = blocks.pop() ?? '';

                for (const block of blocks) {
                    for (const line of block.split('\n')) {
                        if (line.startsWith('data: ')) {
                            const payload = line.slice(6).trim();

                            if (payload) {
                                try {
                                    handleFrame(JSON.parse(payload) as Record<string, unknown>);
                                } catch {
                                    // ignore malformed frames
                                }
                            }
                        }
                    }
                }
            }
        } catch (error) {
            if (controller.signal.aborted && !timedOut) {
                assistant.stopped = true;
            } else {
                assistant.error = true;
                assistant.content = timedOut
                    ? 'انتهت مهلة الاتصال بالمساعد (٥ دقائق). حاول مرة أخرى.'
                    : assistant.content || 'تعذر الاتصال بالمساعد. تحقق من اتصالك وحاول مرة أخرى.';
            }
        } finally {
            clearTimeout(timeoutId);
            abortController = null;
            streaming = false;
            scheduleRender(assistant, true);
        }
    }

    function stop(): void {
        abortController?.abort();
    }

    function newConversation(): void {
        stop();
        messages = [];
        input = '';
        inputEl?.focus();
    }

    function onScroll(): void {
        if (!scrollEl) {
            return;
        }

        const nearBottom =
            scrollEl.scrollHeight - scrollEl.scrollTop - scrollEl.clientHeight < 80;

        autoScroll = nearBottom;
    }

    $effect(() => {
        void messages.map((message) => message.content + message.html + message.tools.length);

        if (scrollEl && autoScroll) {
            scrollEl.scrollTop = scrollEl.scrollHeight;
        }
    });

    function toolLabel(name: string): string {
        return TOOL_META[name]?.label ?? name;
    }

    function formatValue(value: unknown): string {
        if (value === null || value === undefined) {
            return '—';
        }

        if (typeof value === 'boolean') {
            return value ? 'نعم' : 'لا';
        }

        return String(value);
    }
</script>

<AppHead title="المساعد الذكي" />

<div class="mx-auto w-full max-w-5xl p-4 sm:p-8">
    <div class="flex h-full flex-1 flex-col gap-6">
        <div class="mb-2 flex items-center justify-between">
            <h1 class="text-xl font-bold text-slate-800 drop-shadow-sm md:text-2xl dark:text-white">
                المساعد الذكي
            </h1>
            <Button
                type="button"
                variant="outline"
                size="sm"
                onclick={newConversation}
                class="gap-1.5 rounded-full border-white/40 bg-white/40 text-slate-700 transition-colors hover:bg-white/60 hover:text-[#6b6ec6] dark:border-slate-600/50 dark:bg-slate-800/50 dark:text-slate-200 dark:hover:bg-slate-700"
            >
                <RotateCcw class="size-4 text-[#8C8DD8]" />
                محادثة جديدة
            </Button>
        </div>

        <div class="flex h-[calc(100vh-12rem)] min-h-[500px] flex-col overflow-hidden rounded-3xl border border-white/30 bg-white/50 shadow-2xl backdrop-blur-xl dark:border-slate-700/50 dark:bg-slate-900/60">
            <!-- منطقة الرسائل -->
            <div
                bind:this={scrollEl}
                onscroll={onScroll}
                role="log"
                aria-live="polite"
                aria-label="محادثة المساعد الذكي"
                class="flex flex-1 flex-col gap-4 overflow-y-auto p-4 md:p-6"
            >
                {#if isEmpty}
                    <!-- الحالة الفارغة مع الأمثلة -->
                    <div class="flex flex-1 flex-col items-center justify-center gap-6">
                        <div class="flex size-16 items-center justify-center rounded-full bg-gradient-to-r from-[#6b6ec6] to-[#8C8DD8] shadow-lg">
                            <Bot class="size-8 text-white" />
                        </div>
                        <div class="space-y-2 text-center">
                            <h2 class="text-lg font-bold text-slate-800 dark:text-white">
                                مرحباً! أنا مساعدك المالي الذكي
                            </h2>
                            <p class="max-w-md text-sm text-slate-600 dark:text-slate-300">
                                اسألني عن مصروفاتك، أو اطلب مني إضافة أو تعديل أو حذف عمليات — جرّب أحد الأمثلة:
                            </p>
                        </div>
                        <div class="flex max-w-xl flex-wrap justify-center gap-2">
                            {#each EXAMPLES as example (example)}
                                <button
                                    type="button"
                                    class="rounded-full border border-white/40 bg-white/40 px-4 py-2 text-sm font-medium text-slate-700 shadow-sm backdrop-blur-sm transition-colors hover:bg-white/60 hover:text-[#6b6ec6] dark:border-slate-600/50 dark:bg-slate-800/50 dark:text-slate-200 dark:hover:bg-slate-700 dark:hover:text-white"
                                    onclick={() => send(example)}
                                >
                                    {example}
                                </button>
                            {/each}
                        </div>
                    </div>
                {:else}
                    {#each messages as message (message.id)}
                        {#if message.role === 'user'}
                            <div class="flex items-start justify-end gap-3" dir={isRtlText(message.content) ? 'rtl' : 'ltr'}>
                                <div class="max-w-[85%] rounded-2xl rounded-tl-sm bg-gradient-to-r from-[#3c3f98] to-[#6b6ec6] p-4 text-white shadow-md">
                                    <p class="whitespace-pre-wrap break-words text-sm font-medium">{message.content}</p>
                                </div>
                            </div>
                        {:else}
                            <div class="flex items-start gap-3">
                                <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-r from-[#6b6ec6] to-[#8C8DD8] shadow-md">
                                    <Bot class="size-5 text-white" />
                                </div>
                                <div class="flex min-w-0 max-w-[85%] flex-1 flex-col gap-3">
                                    {#each message.tools as tool (tool.id)}
                                        {@const meta = TOOL_META[tool.name]}
                                        <div
                                            class="overflow-hidden rounded-2xl border border-white/40 bg-white/60 shadow-sm backdrop-blur-md dark:border-slate-600/50 dark:bg-slate-800/60"
                                            dir="rtl"
                                        >
                                            <div class="flex items-center gap-2 p-3">
                                                <div
                                                    class="flex size-8 shrink-0 items-center justify-center rounded-full {tool.status === 'done'
                                                        ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-300'
                                                        : tool.status === 'failed'
                                                            ? 'bg-rose-100 text-rose-600 dark:bg-rose-900/40 dark:text-rose-300'
                                                            : 'bg-[#8C8DD8]/15 text-[#8C8DD8]'}"
                                                >
                                                    {#if meta}
                                                        <meta.icon class="size-4" />
                                                    {:else}
                                                        <Wrench class="size-4" />
                                                    {/if}
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <p class="truncate text-sm font-semibold text-slate-800 dark:text-white">
                                                        {toolLabel(tool.name)}
                                                    </p>
                                                    {#if tool.summary}
                                                        <p class="truncate text-xs text-slate-500 dark:text-slate-400">{tool.summary}</p>
                                                    {/if}
                                                </div>
                                                {#if tool.status === 'running'}
                                                    <Loader2 class="size-4 shrink-0 animate-spin text-[#8C8DD8]" />
                                                {:else if tool.status === 'done'}
                                                    <Check class="size-4 shrink-0 text-emerald-500" />
                                                {:else}
                                                    <X class="size-4 shrink-0 text-rose-500" />
                                                {/if}
                                                <button
                                                    type="button"
                                                    aria-label={tool.open ? 'إخفاء التفاصيل' : 'عرض التفاصيل'}
                                                    class="text-slate-400 transition-colors hover:text-slate-600 dark:hover:text-slate-200"
                                                    onclick={() => (tool.open = !tool.open)}
                                                >
                                                    <ChevronDown class="size-4 transition-transform {tool.open ? 'rotate-180' : ''}" />
                                                </button>
                                            </div>
                                            {#if tool.open}
                                                <div class="border-t border-white/40 px-4 py-3 text-xs dark:border-slate-600/50">
                                                    <p class="mb-2 font-semibold text-slate-500 dark:text-slate-400">المعطيات</p>
                                                    <ul class="space-y-1 text-slate-700 dark:text-slate-200">
                                                        {#each Object.entries(tool.args) as [key, value] (key)}
                                                            <li>
                                                                <span class="font-medium">{ARG_LABELS[key] ?? key}:</span>
                                                                {formatValue(value)}
                                                            </li>
                                                        {:else}
                                                            <li class="text-slate-400">—</li>
                                                        {/each}
                                                    </ul>
                                                </div>
                                            {/if}
                                        </div>
                                    {/each}

                                    {#if message.content}
                                        <div
                                            class="rounded-2xl rounded-tr-sm border border-white/40 bg-white/70 p-4 shadow-sm backdrop-blur-md dark:border-slate-600/50 dark:bg-slate-800/70"
                                            dir={isRtlText(message.content) ? 'rtl' : 'ltr'}
                                        >
                                            <div class="md-body text-sm text-slate-800 dark:text-slate-100">
                                                <!-- eslint-disable-next-line svelte/no-at-html-tags -- محتوى مُعقّم عبر DOMPurify -->
                                                {@html message.html}
                                            </div>
                                        </div>
                                    {:else if streaming && !message.tools.length}
                                        <div class="flex items-center gap-1.5 rounded-2xl rounded-tr-sm border border-white/40 bg-white/70 px-4 py-3.5 shadow-sm backdrop-blur-md dark:border-slate-600/50 dark:bg-slate-800/70">
                                            <span class="typing-dot"></span>
                                            <span class="typing-dot [animation-delay:150ms]"></span>
                                            <span class="typing-dot [animation-delay:300ms]"></span>
                                        </div>
                                    {/if}

                                    {#if message.error}
                                        <div class="flex items-center gap-3 rounded-2xl border border-rose-200/60 bg-rose-50/70 p-3 text-sm text-rose-700 backdrop-blur-md dark:border-rose-500/30 dark:bg-rose-950/40 dark:text-rose-300" dir="rtl">
                                            <AlertCircle class="size-5 shrink-0" />
                                            <p class="flex-1">{message.content}</p>
                                            <Button
                                                type="button"
                                                size="sm"
                                                variant="outline"
                                                onclick={() => retry(message)}
                                                class="h-8 shrink-0 gap-1.5 rounded-full border-rose-300 bg-white/60 text-rose-700 hover:bg-rose-100 hover:text-rose-800 dark:border-rose-500/40 dark:bg-rose-900/30 dark:text-rose-300"
                                            >
                                                <RotateCcw class="size-3.5" />
                                                إعادة المحاولة
                                            </Button>
                                        </div>
                                    {:else if message.stopped}
                                        <p class="text-xs text-slate-400" dir="rtl">تم إيقاف الرد.</p>
                                    {/if}
                                </div>
                            </div>
                        {/if}
                    {/each}
                {/if}
            </div>

            <!-- حقل الإدخال -->
            <div class="border-t border-white/20 bg-white/10 p-4 md:px-6 dark:bg-slate-900/10">
                <form
                    class="flex items-end gap-3"
                    onsubmit={(event) => {
                        event.preventDefault();
                        void send(input);
                    }}
                >
                    <textarea
                        bind:this={inputEl}
                        bind:value={input}
                        rows="1"
                        placeholder="اكتب رسالتك هنا..."
                        aria-label="اكتب رسالتك للمساعد"
                        disabled={streaming}
                        class="max-h-36 min-h-12 flex-1 resize-none rounded-2xl border border-white/40 bg-white/50 px-5 py-3 text-sm shadow-inner backdrop-blur-sm placeholder:text-slate-400 focus-visible:border-[#8C8DD8] focus-visible:ring-[#8C8DD8] disabled:opacity-60 dark:border-slate-600/50 dark:bg-slate-800/50 dark:text-white"
                        onkeydown={(event) => {
                            if (event.key === 'Enter' && !event.shiftKey) {
                                event.preventDefault();
                                void send(input);
                            }
                        }}
                    ></textarea>
                    {#if streaming}
                        <Button
                            type="button"
                            aria-label="إيقاف الرد"
                            onclick={stop}
                            class="size-12 shrink-0 rounded-2xl border-none bg-rose-500/90 text-white shadow-lg transition-transform hover:scale-105 hover:bg-rose-500"
                        >
                            <Square class="mx-auto size-5" />
                        </Button>
                    {:else}
                        <Button
                            type="submit"
                            aria-label="إرسال"
                            disabled={!input.trim()}
                            class="size-12 shrink-0 rounded-2xl border-none bg-gradient-to-r from-[#3c3f98] via-[#6b6ec6] to-[#6b6ec6] text-white shadow-lg transition-transform hover:scale-105 hover:opacity-90 disabled:opacity-50"
                        >
                            <Send class="mx-auto size-5" />
                        </Button>
                    {/if}
                </form>
                <p class="mt-2 text-center text-[11px] text-slate-400" dir="rtl">
                    Enter للإرسال · Shift+Enter لسطر جديد — المساعد قد يعدّل بياناتك بناءً على طلبك
                </p>
            </div>
        </div>
    </div>
</div>

<style>
    .md-body {
        line-height: 1.75;
    }

    .md-body :global(p) {
        margin: 0.25rem 0;
    }

    .md-body :global(h3) {
        margin: 0.75rem 0 0.25rem;
        font-size: 1rem;
        font-weight: 700;
    }

    .md-body :global(h4) {
        margin: 0.75rem 0 0.25rem;
        font-size: 0.9rem;
        font-weight: 700;
    }

    .md-body :global(ul) {
        margin: 0.25rem 0;
        padding-inline-start: 1.25rem;
        list-style: disc;
    }

    .md-body :global(ol) {
        margin: 0.25rem 0;
        padding-inline-start: 1.25rem;
        list-style: decimal;
    }

    .md-body :global(li) {
        margin: 0.125rem 0;
    }

    .md-body :global(table) {
        display: block;
        width: 100%;
        margin: 0.5rem 0;
        overflow-x: auto;
        border-collapse: collapse;
    }

    .md-body :global(th),
    .md-body :global(td) {
        padding: 0.3rem 0.6rem;
        border: 1px solid rgba(148, 163, 184, 0.3);
        text-align: start;
        white-space: nowrap;
    }

    .md-body :global(th) {
        background: rgba(107, 110, 198, 0.1);
        font-weight: 700;
    }

    .md-body :global(code) {
        direction: ltr;
        display: inline-block;
        border-radius: 0.25rem;
        background: rgba(148, 163, 184, 0.15);
        padding: 0.1rem 0.35rem;
        font-size: 0.85em;
    }

    .md-body :global(pre) {
        direction: ltr;
        overflow-x: auto;
        border-radius: 0.5rem;
        background: rgba(15, 23, 42, 0.85);
        color: #e2e8f0;
        padding: 0.75rem 1rem;
        margin: 0.5rem 0;
        text-align: left;
    }

    .md-body :global(pre code) {
        display: block;
        background: none;
        padding: 0;
        color: inherit;
    }

    .md-body :global(blockquote) {
        margin: 0.5rem 0;
        padding-inline-start: 0.75rem;
        border-inline-start: 3px solid #8c8dd8;
        color: rgba(100, 116, 139, 0.95);
    }

    .md-body :global(a) {
        color: #6b6ec6;
        text-decoration: underline;
        text-underline-offset: 2px;
    }

    .md-body :global(strong) {
        font-weight: 700;
    }

    .typing-dot {
        width: 0.5rem;
        height: 0.5rem;
        border-radius: 9999px;
        background: #8c8dd8;
        animation: typing-bounce 1.2s ease-in-out infinite;
    }

    @keyframes typing-bounce {
        0%,
        60%,
        100% {
            transform: translateY(0);
            opacity: 0.4;
        }

        30% {
            transform: translateY(-0.35rem);
            opacity: 1;
        }
    }
</style>
