<?php

namespace App\Http\Controllers;

use App\Models\AiMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AiChatController extends Controller
{
    public function show()
    {
        return view('ai.chat');
    }

    public function send(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        $userId = auth()->check() ? auth()->id() : null;
        $msg = trim($request->input('message'));

        $userMessage = AiMessage::create([
            'user_id' => $userId,
            'role' => 'user',
            'content' => $msg,
        ]);

        $llmKey = config('app.llm_api_key', env('LLM_API_KEY', ''));
        $llmModel = config('app.llm_model', env('LLM_MODEL', 'local-model'));

        $headers = ['Accept' => 'application/json'];
        if ($llmKey !== '') {
            $headers['Authorization'] = 'Bearer ' . $llmKey;
        }

        $lastError = null;
        $replyText = null;

        foreach ($this->candidateLlmUrls() as $llmUrl) {
            try {
                $payload = $this->buildPayload($llmUrl, $msg, $llmModel);
                $response = Http::withHeaders($headers)
                    ->connectTimeout(5)
                    ->timeout(12)
                    ->post($llmUrl, $payload);

                $raw = $response->body();
                $body = null;

                // Try decode JSON safely and handle malformed UTF-8
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $body = $decoded;
                    $replyText = $this->extractReplyText($body);
                } else {
                    // Try to fix encoding and decode again
                    $fixed = mb_convert_encoding($raw, 'UTF-8', 'UTF-8');
                    $decoded2 = json_decode($fixed, true);
                    if (is_array($decoded2)) {
                        $body = $decoded2;
                        $replyText = $this->extractReplyText($body);
                    }
                }

                if ($replyText === null) {
                    if ($response->successful()) {
                        // Return sanitized raw text as a fallback instead of json_encode which may fail on invalid UTF-8
                        $sanitized = mb_convert_encoding($raw, 'UTF-8', 'UTF-8');
                        $replyText = $sanitized !== '' ? $sanitized : 'LLM server returned an empty response.';
                    } else {
                        $replyText = 'LLM server returned an unexpected response.';
                    }
                }

                break;
            } catch (\Throwable $e) {
                $lastError = $e;
                continue;
            }
        }

        if ($replyText === null) {
            $detail = $lastError ? $lastError->getMessage() : 'Unknown error';
            \Log::warning('LLM request failed', [
                'error' => $detail,
                'url_candidates' => $this->candidateLlmUrls(),
            ]);

            // Last-resort: try native PHP HTTP (file_get_contents) against the first candidate URL.
            // This can succeed in environments where Guzzle's handler is not able to connect.
            $urls = $this->candidateLlmUrls();
            if (!empty($urls)) {
                $firstUrl = $urls[0];
                try {
                    $fallbackPayload = [
                        'model' => $llmModel,
                        'messages' => [[ 'role' => 'user', 'content' => $msg ]],
                        'stream' => false,
                    ];

                    $opts = [
                        'http' => [
                            'method'  => 'POST',
                            'header'  => "Content-Type: application/json\r\nAccept: application/json\r\n",
                            'content' => json_encode($fallbackPayload),
                            'timeout' => 10,
                        ],
                    ];
                    $context = stream_context_create($opts);
                    $raw = @file_get_contents($firstUrl, false, $context);

                    if ($raw !== false) {
                        $bodyFallback = json_decode($raw, true);
                        $parsed = $this->extractReplyText($bodyFallback);
                        if ($parsed !== null) {
                            $replyText = $parsed;
                        } else {
                            \Log::warning('LLM fallback returned unparseable body', ['url' => $firstUrl, 'raw' => substr($raw, 0, 2000)]);
                        }
                    } else {
                        \Log::warning('LLM fallback file_get_contents failed', ['url' => $firstUrl]);
                    }
                } catch (\Throwable $e) {
                    \Log::warning('LLM fallback exception', ['error' => $e->getMessage()]);
                }
            }

            if ($replyText === null) {
                // Include a short reason to help local debugging without dumping stack traces
                $short = is_string($detail) ? substr($detail, 0, 200) : 'Unknown error';
                $replyText = 'LLM server is not available right now. Reason: ' . $short . '. Please start the local model server or set the correct LLM_API_URL in .env.';
            }
        }

        $assistant = AiMessage::create([
            'user_id' => $userId,
            'role' => 'assistant',
            'content' => $replyText,
        ]);

        return response()->json([
            'status' => 'ok',
            'reply' => $replyText,
            'user_message_id' => $userMessage->id,
            'assistant_message_id' => $assistant->id,
        ]);
    }

    protected function candidateLlmUrls(): array
    {
        $configured = trim((string) config('app.llm_api_url', env('LLM_API_URL', '')));
        $urls = [];

        if ($configured !== '') {
            $urls[] = rtrim($configured, '/');
        }

        // In production we prefer an explicit configured URL only. Local loopback
        // fallbacks are useful for developer machines but cause immediate connection
        // failures on hosted platforms (Render, Railway). Avoid trying localhost in
        // production to prevent noisy errors and slow fallbacks.
        if (env('APP_ENV') === 'production') {
            return array_values(array_unique($urls));
        }

        $urls = array_values(array_unique(array_merge($urls, [
            'http://127.0.0.1:11434/api/chat',
            'http://127.0.0.1:11434/v1/chat/completions',
            'http://127.0.0.1:5000/generate',
            'http://127.0.0.1:5000',
        ])));

        return $urls;
    }

    protected function buildPayload(string $url, string $message, string $model): array
    {
        $isOpenAiCompatible = str_contains($url, '/chat/completions') || str_contains($url, '/v1/chat/completions');
        $isOllamaChat = str_contains($url, '/api/chat');

        if ($isOpenAiCompatible) {
            return [
                'model' => $model,
                'messages' => [
                    ['role' => 'user', 'content' => $message],
                ],
                'temperature' => 0.2,
                'max_tokens' => 512,
            ];
        }

        if ($isOllamaChat) {
            return [
                'model' => $model,
                'messages' => [
                    ['role' => 'user', 'content' => $message],
                ],
                'stream' => false,
            ];
        }

        return [
            'prompt' => $message,
            'max_tokens' => 512,
            'temperature' => 0.2,
            'model' => $model,
        ];
    }

    protected function extractReplyText(mixed $body): ?string
    {
        if (is_string($body) && trim($body) !== '') {
            return $body;
        }

        if (! is_array($body)) {
            return null;
        }

        $candidates = [
            'text',
            'generated_text',
            'answer',
            'response',
            'result',
            'output',
        ];

        foreach ($candidates as $key) {
            if (isset($body[$key]) && is_string($body[$key])) {
                return $body[$key];
            }
        }

        if (isset($body['message']['content']) && is_string($body['message']['content'])) {
            return $body['message']['content'];
        }

        if (isset($body['choices']) && is_array($body['choices'])) {
            foreach ($body['choices'] as $choice) {
                if (isset($choice['text']) && is_string($choice['text'])) {
                    return $choice['text'];
                }

                if (isset($choice['message']['content']) && is_string($choice['message']['content'])) {
                    return $choice['message']['content'];
                }
            }
        }

        if (isset($body['data']) && is_array($body['data'])) {
            foreach ($body['data'] as $item) {
                if (isset($item['generated_text']) && is_string($item['generated_text'])) {
                    return $item['generated_text'];
                }
                if (isset($item['text']) && is_string($item['text'])) {
                    return $item['text'];
                }
            }
        }

        return null;
    }
}
