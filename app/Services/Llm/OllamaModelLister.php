<?php

namespace App\Services\Llm;

use App\Services\Llm\Exceptions\LlmRequestException;
use Illuminate\Support\Facades\Http;

class OllamaModelLister
{
    /**
     * @return array<int, string>
     */
    public function list(?string $baseUrl = null): array
    {
        $baseUrl = $baseUrl ?: config('services.ollama.base_url');

        $response = Http::timeout(5)->get(rtrim((string) $baseUrl, '/').'/api/tags');

        if ($response->failed()) {
            throw new LlmRequestException(
                "Ollama API request failed with status {$response->status()}: {$response->body()}"
            );
        }

        return collect($response->json('models', []))
            ->pluck('name')
            ->filter()
            ->sort()
            ->values()
            ->all();
    }
}
