<?php

namespace App\Services\Llm\Providers;

use App\Services\Llm\Contracts\LlmProviderInterface;
use App\Services\Llm\DTO\LlmResponse;
use App\Services\Llm\Exceptions\LlmRequestException;
use Illuminate\Support\Facades\Http;

class OpenAiProvider implements LlmProviderInterface
{
    public function __construct(
        private readonly string $model,
        private readonly ?string $endpoint,
        private readonly array $extraParams,
    ) {
    }

    public function complete(string $prompt, array $options = []): LlmResponse
    {
        $baseUrl = $this->endpoint ?? config('services.openai.base_url');
        $apiKey = config('services.openai.key');

        $response = Http::withToken((string) $apiKey)
            ->retry(3, 1000, throw: false)
            ->timeout(120)
            ->post(rtrim((string) $baseUrl, '/').'/chat/completions', array_merge([
                'model' => $this->model,
                'temperature' => $this->extraParams['temperature'] ?? 0.3,
                'max_tokens' => $this->extraParams['max_tokens'] ?? 1024,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ], $options));

        if ($response->failed()) {
            throw new LlmRequestException(
                "OpenAI API request failed with status {$response->status()}: {$response->body()}"
            );
        }

        $data = $response->json();

        return new LlmResponse(
            content: (string) ($data['choices'][0]['message']['content'] ?? ''),
            promptTokens: (int) ($data['usage']['prompt_tokens'] ?? 0),
            completionTokens: (int) ($data['usage']['completion_tokens'] ?? 0),
            raw: $data,
        );
    }
}
