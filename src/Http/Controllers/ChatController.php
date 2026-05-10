<?php

namespace Hakim\ClaudeChat\Http\Controllers;

use Hakim\ClaudeChat\ChatToolInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatController extends Controller
{
    public function __construct(private readonly ChatToolInterface $toolService) {}

    public function send(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $today    = now()->toDateString();
        $userName = auth()->user()?->name ?? 'User';
        $tools    = $this->toolService->getTools();
        $history  = $request->session()->get('claude_chat_history', []);

        $history[] = ['role' => 'user', 'content' => $validated['message']];

        return response()->stream(function () use ($history, $today, $userName, $tools, $request) {
            $flush = function (): void {
                if (ob_get_level() > 0) ob_flush();
                flush();
            };

            $systemPrompt  = "Today is {$today}. The logged-in user is {$userName}.\n\n" . config('claude-chat.system_prompt');
            $messages      = $history;
            $maxIterations = (int) config('claude-chat.max_iterations', 5);
            $iteration     = 0;
            $finalText     = '';

            while ($iteration < $maxIterations) {
                $iteration++;

                $response = Http::withHeaders([
                    'x-api-key'         => config('claude-chat.key'),
                    'anthropic-version' => '2023-06-01',
                    'content-type'      => 'application/json',
                ])->post(rtrim(config('claude-chat.base_url'), '/') . '/v1/messages', [
                    'model'      => config('claude-chat.model'),
                    'max_tokens' => (int) config('claude-chat.max_tokens'),
                    'system'     => $systemPrompt,
                    'messages'   => $messages,
                    'tools'      => $tools,
                ]);

                if ($response->failed()) {
                    echo 'data: ' . json_encode(['error' => 'Failed to get a response from the AI.']) . "\n\n";
                    echo "data: [DONE]\n\n";
                    $flush();
                    return;
                }

                $data       = $response->json();
                $stopReason = $data['stop_reason'] ?? 'end_turn';

                if ($stopReason === 'tool_use') {
                    $assistantContent = $data['content'] ?? [];
                    foreach ($assistantContent as &$block) {
                        if (($block['type'] ?? '') === 'tool_use' && empty($block['input'])) {
                            $block['input'] = new \stdClass();
                        }
                    }
                    unset($block);
                    $messages[]       = ['role' => 'assistant', 'content' => $assistantContent];

                    $toolResults = [];
                    foreach ($assistantContent as $block) {
                        if (($block['type'] ?? '') === 'tool_use') {
                            $input  = $block['input'] ?? [];
                            $input  = $input instanceof \stdClass ? [] : (array) $input;
                            $result = $this->toolService->handleToolCall($block['name'], $input);
                            $toolResults[] = [
                                'type'        => 'tool_result',
                                'tool_use_id' => $block['id'],
                                'content'     => json_encode($result),
                            ];
                        }
                    }

                    $messages[] = ['role' => 'user', 'content' => $toolResults];
                    continue;
                }

                foreach ($data['content'] ?? [] as $block) {
                    if (($block['type'] ?? '') === 'text') {
                        $finalText = $block['text'];
                        break;
                    }
                }
                break;
            }

            $words = explode(' ', $finalText);
            foreach ($words as $i => $word) {
                $token = ($i === 0 ? '' : ' ') . $word;
                echo 'data: ' . json_encode(['token' => $token]) . "\n\n";
                $flush();
            }

            echo "data: [DONE]\n\n";
            $flush();

            $history[] = ['role' => 'assistant', 'content' => $finalText];

            $maxHistory = (int) config('claude-chat.max_history', 20);
            while (count($history) > $maxHistory) {
                array_shift($history);
                array_shift($history);
            }

            $request->session()->put('claude_chat_history', $history);
            $request->session()->save();
        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    public function clear(Request $request): JsonResponse
    {
        $request->session()->forget('claude_chat_history');
        return response()->json(['status' => 'ok']);
    }
}
