<?php

namespace App\Services\Llm\Contracts;

use App\Services\Llm\DTO\LlmResponse;

interface LlmProviderInterface
{
    /**
     * Send a completion request to the provider and return its response.
     *
     * @param  array<string, mixed>  $options  Provider-specific overrides (e.g. temperature, max_tokens).
     */
    public function complete(string $prompt, array $options = []): LlmResponse;
}
