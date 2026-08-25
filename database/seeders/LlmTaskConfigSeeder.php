<?php

namespace Database\Seeders;

use App\Enums\LlmProvider;
use App\Enums\LlmTaskKey;
use App\Models\LlmTaskConfig;
use Illuminate\Database\Seeder;

class LlmTaskConfigSeeder extends Seeder
{
    /**
     * Seeds the runtime-editable provider/model per task (briefing §2.4). Model identifiers
     * below are examples — verify against each provider's current model list before relying on
     * them; this is exactly why the mapping lives in the database rather than in code.
     */
    public function run(): void
    {
        LlmTaskConfig::query()->updateOrCreate(
            ['task_key' => LlmTaskKey::Classification],
            [
                'provider' => LlmProvider::OpenAi,
                'model' => 'gpt-4o-mini',
                'endpoint' => null,
                'extra_params' => ['temperature' => 0.1],
                // Off by default: MVP relies on keyword classification only (briefing §2.2)
                // to avoid LLM cost on every ingested ruling. An admin can flip this on later.
                'is_active' => false,
            ]
        );

        LlmTaskConfig::query()->updateOrCreate(
            ['task_key' => LlmTaskKey::Summarization],
            [
                'provider' => LlmProvider::Anthropic,
                'model' => 'claude-sonnet-4-5-20250929',
                'endpoint' => null,
                'extra_params' => ['temperature' => 0.3, 'max_tokens' => 1024],
                'is_active' => true,
            ]
        );
    }
}
