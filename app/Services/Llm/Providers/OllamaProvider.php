<?php

namespace App\Services\Llm\Providers;

use App\Services\Llm\Contracts\LlmProviderInterface;
use App\Services\Llm\DTO\LlmResponse;
use App\Services\Llm\Exceptions\LlmRequestException;
use Illuminate\Support\Facades\Http;

class OllamaProvider implements LlmProviderInterface
{
    public function __construct(
        private readonly string $model,
        private readonly ?string $endpoint,
        private readonly array $extraParams,
    ) {
    }

    public function complete(string $prompt, array $options = []): LlmResponse
    {
        $baseUrl = $this->endpoint ?? config('services.ollama.base_url');

        $response = Http::retry(3, 1000, throw: false)
            ->timeout(180)
            ->post(rtrim((string) $baseUrl, '/').'/api/chat', array_merge([
                'model' => $this->model,
                'stream' => false,
                'options' => [
                    'temperature' => $this->extraParams['temperature'] ?? 0.3,
                ],
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ], $options));

        if ($response->failed()) {
            throw new LlmRequestException(
                "Ollama API request failed with status {$response->status()}: {$response->body()}"
            );
        }

        $data = $response->json();

        return new LlmResponse(
            content: (string) ($data['message']['content'] ?? ''),
            promptTokens: (int) ($data['prompt_eval_count'] ?? 0),
            completionTokens: (int) ($data['eval_count'] ?? 0),
            raw: $data,
        );
    }
}
