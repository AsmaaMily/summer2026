<?php

namespace App\Http\Controllers;

use App\Ai\Agents\FinanceAssistant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Streaming\Events\Error;
use Laravel\Ai\Streaming\Events\StreamEvent;
use Laravel\Ai\Streaming\Events\TextDelta;
use Laravel\Ai\Streaming\Events\ToolCall;
use Laravel\Ai\Streaming\Events\ToolResult;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class AssistantStreamController extends Controller
{
    public function __invoke(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:4000'],
            'history' => ['nullable', 'array', 'max:20'],
            'history.*.role' => ['required', 'string', 'in:user,assistant'],
            'history.*.content' => ['required', 'string', 'max:4000'],
        ]);

        $history = $this->sanitizeHistory($validated['history'] ?? []);

        $agent = new FinanceAssistant($request->user(), $history);

        return response()->stream(
            fn () => $this->writeStream($agent, $validated['message']),
            200,
            [
                'Content-Type' => 'text/event-stream; charset=UTF-8',
                'Cache-Control' => 'no-cache',
                'X-Accel-Buffering' => 'no',
            ],
        );
    }

    /**
     * Only keep well-formed user/assistant messages, capped at the configured
     * history limit. Anything else sent by the client is discarded.
     *
     * @param  array<int, mixed>  $history
     * @return Message[]
     */
    protected function sanitizeHistory(array $history): array
    {
        $limit = max(1, (int) config('ai.assistant.max_history_messages', 20));

        return collect($history)
            ->filter(fn ($message) => is_array($message)
                && in_array($message['role'] ?? null, ['user', 'assistant'], true)
                && is_string($message['content'] ?? null)
                && trim($message['content']) !== '')
            ->slice(-$limit)
            ->map(fn (array $message) => new Message($message['role'], $message['content']))
            ->values()
            ->all();
    }

    protected function writeStream(FinanceAssistant $agent, string $message): void
    {
        try {
            foreach ($agent->stream($message) as $event) {
                if (connection_aborted() !== 0) {
                    break;
                }

                $frame = $this->toFrame($event);

                if ($frame !== null) {
                    $this->send($frame);
                }
            }
        } catch (Throwable $e) {
            report($e);

            $this->send(['type' => 'error', 'message' => 'حدث خطأ أثناء توليد الرد. حاول مرة أخرى.']);
        }

        $this->send(['type' => 'done']);
    }

    /**
     * Map a package stream event to the assistant frame protocol.
     *
     * @return array<string, mixed>|null
     */
    protected function toFrame(StreamEvent $event): ?array
    {
        if ($event instanceof TextDelta) {
            return ['type' => 'text', 'delta' => $event->delta];
        }

        if ($event instanceof ToolCall) {
            return [
                'type' => 'tool_call',
                'id' => $event->toolCall->id,
                'name' => $event->toolCall->name,
                'arguments' => $event->toolCall->arguments,
            ];
        }

        if ($event instanceof ToolResult) {
            $result = json_decode((string) $event->toolResult->result, true);

            $frame = [
                'type' => 'tool_result',
                'id' => $event->toolResult->id,
                'name' => $event->toolResult->name,
                'ok' => $event->successful && ! $event->denied && ($result['ok'] ?? true),
                'summary' => is_array($result) && array_key_exists('summary', $result)
                    ? (string) $result['summary']
                    : 'تم التنفيذ',
                'data' => is_array($result) ? ($result['data'] ?? null) : null,
            ];

            return $frame;
        }

        if ($event instanceof Error) {
            Log::warning('Assistant stream error event', ['message' => $event->message, 'type' => $event->type]);

            return ['type' => 'error', 'message' => 'حدث خطأ أثناء توليد الرد. حاول مرة أخرى.'];
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $frame
     */
    protected function send(array $frame): void
    {
        echo 'data: '.json_encode($frame, JSON_UNESCAPED_UNICODE)."\n\n";

        if (ob_get_level() > 0) {
            @ob_flush();
        }

        @flush();
    }
}
