<?php

namespace App\Services\Llm\Providers;

use App\Services\Llm\Contracts\LlmProviderInterface;
use App\Services\Llm\DTO\LlmResponse;
use App\Services\Llm\Exceptions\LlmRequestException;
use Illuminate\Support\Facades\Http;

class AnthropicProvider implements LlmProviderInterface
{
    public function __construct(
        private readonly string $model,
        private readonly ?string $endpoint,
        private readonly array $extraParams,
    ) {
    }

    public function complete(string $prompt, array $options = []): LlmResponse
    {
        $baseUrl = $this->endpoint ?? config('services.anthropic.base_url');
        $apiKey = config('services.anthropic.key');

        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
            'anthropic-version' => '2023-06-01',
        ])
            ->retry(3, 1000, throw: false)
            ->timeout(120)
            ->post(rtrim((string) $baseUrl, '/').'/v1/messages', array_merge([
                'model' => $this->model,
                'max_tokens' => $this->extraParams['max_tokens'] ?? 1024,
                'temperature' => $this->extraParams['temperature'] ?? 0.3,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ], $options));

        if ($response->failed()) {
            throw new LlmRequestException(
                "Anthropic API request failed with status {$response->status()}: {$response->body()}"
            );
        }

        $data = $response->json();

        $content = collect($data['content'] ?? [])
            ->where('type', 'text')
            ->pluck('text')
            ->implode('');

        return new LlmResponse(
            content: $content,
            promptTokens: (int) ($data['usage']['input_tokens'] ?? 0),
            completionTokens: (int) ($data['usage']['output_tokens'] ?? 0),
            raw: $data,
        );
    }
}
