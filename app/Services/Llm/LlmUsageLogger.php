<?php

namespace App\Services\Llm;

use App\Enums\LlmTaskKey;
use App\Models\LlmTaskConfig;
use App\Models\LlmUsageLog;
use App\Models\Ruling;
use App\Services\Llm\DTO\LlmResponse;
use Throwable;

class LlmUsageLogger
{
    public function logSuccess(
        LlmTaskKey $taskKey,
        LlmTaskConfig $config,
        LlmResponse $response,
        ?Ruling $ruling,
        float $startedAt,
    ): void {
        LlmUsageLog::create([
            'task_key' => $taskKey,
            'provider' => $config->provider,
            'model' => $config->model,
            'ruling_id' => $ruling?->id,
            'tokens_prompt' => $response->promptTokens,
            'tokens_completion' => $response->completionTokens,
            'tokens_total' => $response->promptTokens + $response->completionTokens,
            'success' => true,
            'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
        ]);
    }

    public function logFailure(
        LlmTaskKey $taskKey,
        LlmTaskConfig $config,
        Throwable $exception,
        ?Ruling $ruling,
        float $startedAt,
    ): void {
        LlmUsageLog::create([
            'task_key' => $taskKey,
            'provider' => $config->provider,
            'model' => $config->model,
            'ruling_id' => $ruling?->id,
            'success' => false,
            'error_message' => $exception->getMessage(),
            'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
        ]);
    }
}
