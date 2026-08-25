<?php

use App\Enums\LlmProvider;
use App\Services\Llm\Providers\AnthropicProvider;
use App\Services\Llm\Providers\OllamaProvider;
use App\Services\Llm\Providers\OpenAiProvider;

return [

    /*
    |--------------------------------------------------------------------------
    | Provider class map
    |--------------------------------------------------------------------------
    |
    | Maps the `provider` value stored on a llm_task_configs row to the concrete
    | class that implements LlmProviderInterface. Adding a new provider only
    | requires a new class + a new entry here — no changes to LlmManager or
    | any caller are needed (open/closed principle).
    |
    */
    'providers' => [
        LlmProvider::Anthropic->value => AnthropicProvider::class,
        LlmProvider::OpenAi->value => OpenAiProvider::class,
        LlmProvider::Ollama->value => OllamaProvider::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Task config cache TTL (seconds)
    |--------------------------------------------------------------------------
    */
    'config_cache_ttl' => 300,

];
