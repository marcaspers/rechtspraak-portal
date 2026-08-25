<?php

use App\Enums\LlmProvider;
use App\Enums\LlmTaskKey;
use App\Models\LlmTaskConfig;
use App\Models\LlmUsageLog;
use App\Services\Llm\Exceptions\LlmRequestException;
use App\Services\Llm\Exceptions\LlmTaskNotConfiguredException;
use App\Services\Llm\LlmManager;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;

beforeEach(function () {
    Sleep::fake();
});

it('resolves the configured provider from the database and logs successful usage', function () {
    LlmTaskConfig::factory()->create([
        'task_key' => LlmTaskKey::Summarization,
        'provider' => LlmProvider::Anthropic,
        'model' => 'claude-test-model',
        'is_active' => true,
    ]);

    Http::fake([
        'api.anthropic.com/*' => Http::response([
            'content' => [['type' => 'text', 'text' => 'SAMENVATTING: test']],
            'usage' => ['input_tokens' => 120, 'output_tokens' => 40],
        ], 200),
    ]);

    $response = app(LlmManager::class)->run(LlmTaskKey::Summarization, 'Vat deze uitspraak samen.');

    expect($response->content)->toBe('SAMENVATTING: test')
        ->and($response->promptTokens)->toBe(120)
        ->and($response->completionTokens)->toBe(40);

    $log = LlmUsageLog::query()->where('task_key', LlmTaskKey::Summarization)->firstOrFail();
    expect($log->success)->toBeTrue()
        ->and($log->provider)->toBe(LlmProvider::Anthropic)
        ->and($log->tokens_total)->toBe(160);
});

it('logs a failed usage entry and rethrows when the provider request fails', function () {
    LlmTaskConfig::factory()->create([
        'task_key' => LlmTaskKey::Summarization,
        'provider' => LlmProvider::Anthropic,
        'model' => 'claude-test-model',
        'is_active' => true,
    ]);

    Http::fake(['api.anthropic.com/*' => Http::response('server error', 500)]);

    expect(fn () => app(LlmManager::class)->run(LlmTaskKey::Summarization, 'prompt'))
        ->toThrow(LlmRequestException::class);

    $log = LlmUsageLog::query()->where('task_key', LlmTaskKey::Summarization)->firstOrFail();
    expect($log->success)->toBeFalse();
});

it('throws when no active configuration exists for the requested task', function () {
    expect(fn () => app(LlmManager::class)->run(LlmTaskKey::Classification, 'prompt'))
        ->toThrow(LlmTaskNotConfiguredException::class);
});
