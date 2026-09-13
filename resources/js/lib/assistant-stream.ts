import AssistantStreamController from '@/actions/App/Http/Controllers/AssistantStreamController';

export interface AssistantToolCall {
    id: string;
    name: string;
    arguments?: Record<string, unknown>;
}

export interface AssistantToolResult {
    id: string;
    name: string;
    ok: boolean;
    summary: string;
    data?: unknown;
}

export type AssistantFrame =
    | { type: 'text'; delta: string }
    | ({ type: 'tool_call' } & AssistantToolCall)
    | ({ type: 'tool_result' } & AssistantToolResult)
    | { type: 'error'; message: string }
    | { type: 'done' };

export interface AssistantHistoryMessage {
    role: 'user' | 'assistant';
    content: string;
}

function xsrfToken(): string {
    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);

    return match ? decodeURIComponent(match[1]) : '';
}

export async function streamAssistant(
    message: string,
    history: AssistantHistoryMessage[],
    onFrame: (frame: AssistantFrame) => void,
    signal: AbortSignal,
): Promise<void> {
    const response = await fetch(AssistantStreamController.url(), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-XSRF-TOKEN': xsrfToken(),
        },
        body: JSON.stringify({ message, history }),
        signal,
    });

    if (!response.ok) {
        if (response.status === 422) {
            const errors = await response.json().catch(() => null);
            const first = errors?.errors
                ? Object.values(errors.errors)[0]?.[0]
                : null;

            throw new Error(first ?? 'المدخل غير صالح.');
        }

        throw new Error(`تعذر الاتصال بالمساعد (${response.status}).`);
    }

    if (!response.body) {
        throw new Error('تعذر استلام البث من المساعد.');
    }

    const reader = response.body.getReader();
    const decoder = new TextDecoder();
    let buffer = '';

    for (;;) {
        const { done, value } = await reader.read();

        if (done) {
            break;
        }

        buffer += decoder.decode(value, { stream: true });

        const chunks = buffer.split('\n\n');
        buffer = chunks.pop() ?? '';

        for (const chunk of chunks) {
            for (const line of chunk.split('\n')) {
                if (!line.startsWith('data: ')) {
                    continue;
                }

                try {
                    onFrame(JSON.parse(line.slice(6)) as AssistantFrame);
                } catch {
                    // Ignore malformed frames.
                }
            }
        }
    }
}
