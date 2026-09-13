<?php

namespace App\Http\Controllers;

use App\Ai\Agents\FinanceAssistant;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Streaming\Events\Error as StreamErrorEvent;
use Laravel\Ai\Streaming\Events\TextDelta;
use Laravel\Ai\Streaming\Events\ToolCall;
use Laravel\Ai\Streaming\Events\ToolResult;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class AssistantStreamController extends Controller
{
    /**
     * The maximum time, in seconds, the stream may run across all layers.
     */
    protected const STREAM_TIMEOUT = 300;

    /**
     * Seconds of silence after which a keep-alive ping is emitted.
     */
    protected const HEARTBEAT_SECONDS = 15;

    /**
     * Stream the finance assistant's response to the client over SSE.
     */
    public function __invoke(Request $request): StreamedResponse
    {
        set_time_limit(self::STREAM_TIMEOUT);
        ini_set('max_execution_time', (string) self::STREAM_TIMEOUT);
        ignore_user_abort(false);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:4000'],
            'history' => ['nullable', 'array', 'max:100'],
            'history.*.role' => ['required', 'string', 'in:user,assistant'],
            'history.*.content' => ['required', 'string', 'max:4000'],
        ]);

        /** @var User $user */
        $user = $request->user();

        $history = collect($validated['history'] ?? [])
            ->slice(-20)
            ->map(fn (array $message): Message => new Message($message['role'], $message['content']))
            ->values()
            ->all();

        $agent = new FinanceAssistant(user: $user, history: $history);

        return response()->eventStream(
            function () use ($agent, $validated): \Generator {
                $lastEmit = microtime(true);

                try {
                    $stream = $agent->stream(
                        $validated['message'],
                        model: (string) config('ai.providers.opencode.models.text.default'),
                        timeout: self::STREAM_TIMEOUT,
                    );

                    foreach ($stream as $event) {
                        $frame = match (true) {
                            $event instanceof TextDelta => ['type' => 'text', 'delta' => $event->delta],
                            $event instanceof ToolCall => [
                                'type' => 'tool_call',
                                'id' => $event->toolCall->id,
                                'name' => $event->toolCall->name,
                                'arguments' => $event->toolCall->arguments,
                            ],
                            $event instanceof ToolResult => $this->toolResultFrame($event),
                            $event instanceof StreamErrorEvent => $this->errorFrame($event),
                            default => null,
                        };

                        if ($frame === null) {
                            continue;
                        }

                        if (microtime(true) - $lastEmit >= self::HEARTBEAT_SECONDS) {
                            yield ['type' => 'ping'];
                        }

                        $lastEmit = microtime(true);
                        yield $frame;
                    }

                    yield ['type' => 'done'];
                } catch (Throwable $exception) {
                    report($exception);

                    yield ['type' => 'error', 'message' => 'حدث خطأ غير متوقع أثناء توليد الرد. حاول مرة أخرى.'];
                    yield ['type' => 'done'];
                }
            },
            [],
            null,
        );
    }

    /**
     * Map a tool result event to its SSE frame, extracting the unified summary.
     *
     * @return array<string, mixed>
     */
    protected function toolResultFrame(ToolResult $event): array
    {
        $result = $event->toolResult->result;
        $summary = null;
        $ok = $event->successful;

        if (is_string($result)) {
            $decoded = json_decode($result, true);

            if (is_array($decoded) && isset($decoded['summary']) && is_string($decoded['summary'])) {
                $summary = $decoded['summary'];
                $ok = $ok && (bool) ($decoded['ok'] ?? true);
            }
        }

        return [
            'type' => 'tool_result',
            'id' => $event->toolResult->id,
            'name' => $event->toolResult->name,
            'summary' => $summary ?? ($event->error ?? ($ok ? 'تم تنفيذ الأداة' : 'فشل تنفيذ الأداة')),
            'ok' => $ok,
        ];
    }

    /**
     * Map a stream error event to a generic SSE frame without leaking details.
     *
     * @return array<string, mixed>
     */
    protected function errorFrame(StreamErrorEvent $event): array
    {
        report(new \RuntimeException('AI stream error: '.$event->message));

        return ['type' => 'error', 'message' => 'حدث خطأ أثناء الاتصال بالمساعد. حاول مرة أخرى.'];
    }
}
