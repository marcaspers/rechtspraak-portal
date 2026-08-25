<?php

namespace App\Services\Llm\DTO;

final class LlmResponse
{
    public function __construct(
        public readonly string $content,
        public readonly int $promptTokens,
        public readonly int $completionTokens,
        public readonly array $raw = [],
    ) {
    }
}
