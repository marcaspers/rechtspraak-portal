<?php

namespace App\Services\Llm;

use App\Enums\LlmTaskKey;
use App\Models\LlmTaskConfig;
use App\Models\Ruling;
use App\Services\Llm\Contracts\LlmProviderInterface;
use App\Services\Llm\DTO\LlmResponse;
use App\Services\Llm\Exceptions\LlmTaskNotConfiguredException;
use Illuminate\Support\Facades\Cache;
use Throwable;

class LlmManager
{
    public function __construct(
        private readonly LlmUsageLogger $usageLogger,
    ) {
    }

    /**
     * Run a completion for the given task, resolving provider/model from the
     * database-stored per-task configuration and logging usage regardless of
     * success or failure.
     *
     * @param  array<string, mixed>  $options
     */
    public function run(LlmTaskKey $taskKey, string $prompt, array $options = [], ?Ruling $ruling = null): LlmResponse
    {
        $config = $this->resolveConfig($taskKey);
        $provider = $this->makeProvider($config);
        $startedAt = microtime(true);

        try {
            $response = $provider->complete($prompt, $options);
            $this->usageLogger->logSuccess($taskKey, $config, $response, $ruling, $startedAt);

            return $response;
        } catch (Throwable $exception) {
            $this->usageLogger->logFailure($taskKey, $config, $exception, $ruling, $startedAt);

            throw $exception;
        }
    }

    public function resolveConfig(LlmTaskKey $taskKey): LlmTaskConfig
    {
        $config = Cache::remember(
            "llm_task_config:{$taskKey->value}",
            config('llm.config_cache_ttl'),
            fn () => LlmTaskConfig::query()
                ->where('task_key', $taskKey)
                ->where('is_active', true)
                ->first(),
        );

        if (! $config instanceof LlmTaskConfig) {
            throw new LlmTaskNotConfiguredException(
                "No active LLM configuration found for task [{$taskKey->value}]."
            );
        }

        return $config;
    }

    public function makeProvider(LlmTaskConfig $config): LlmProviderInterface
    {
        $providerClass = config("llm.providers.{$config->provider->value}");

        if (! $providerClass) {
            throw new LlmTaskNotConfiguredException(
                "No provider class registered for provider [{$config->provider->value}]."
            );
        }

        return new $providerClass($config->model, $config->endpoint, $config->extra_params ?? []);
    }
}
