<?php

namespace App\Actions\Summaries;

use App\Enums\LlmTaskKey;
use App\Enums\RulingStatus;
use App\Models\Ruling;
use App\Models\Summary;
use App\Services\Llm\LlmManager;
use App\Services\Llm\PromptTemplateRepository;
use App\Services\Search\RulingSearchIndexer;
use Illuminate\Support\Facades\DB;

class GenerateSummaryAction
{
    public function __construct(
        private readonly LlmManager $llmManager,
        private readonly PromptTemplateRepository $promptTemplates,
        private readonly RulingSearchIndexer $searchIndexer,
    ) {
    }

    public function execute(Ruling $ruling): Summary
    {
        $template = $this->promptTemplates->active(LlmTaskKey::Summarization);

        $prompt = $this->promptTemplates->render($template, [
            'title' => $ruling->title,
            'ecli' => $ruling->ecli ?? 'onbekend',
            'instantie' => $ruling->instantie ?? 'onbekend',
            'published_at' => $ruling->published_at->format('d-m-Y'),
            'themes' => $ruling->themes->pluck('name')->implode(', ') ?: 'geen',
            'full_text' => $ruling->full_text ?? $ruling->rss_summary ?? '',
        ]);

        $taskConfig = $this->llmManager->resolveConfig(LlmTaskKey::Summarization);
        $response = $this->llmManager->run(LlmTaskKey::Summarization, $prompt, ruling: $ruling);

        [$content, $relevanceNote] = $this->parseResponse($response->content);

        $summary = DB::transaction(function () use ($ruling, $template, $taskConfig, $response, $content, $relevanceNote) {
            Summary::query()->where('ruling_id', $ruling->id)->where('is_current', true)->update(['is_current' => false]);

            $summary = Summary::create([
                'ruling_id' => $ruling->id,
                'prompt_template_id' => $template->id,
                'content' => $content,
                'relevance_note' => $relevanceNote,
                'provider' => $taskConfig->provider,
                'model' => $taskConfig->model,
                'tokens_prompt' => $response->promptTokens,
                'tokens_completion' => $response->completionTokens,
                'is_current' => true,
                'generated_at' => now(),
            ]);

            $ruling->update(['status' => RulingStatus::Summarized]);

            return $summary;
        });

        $this->searchIndexer->index($ruling->fresh());

        return $summary;
    }

    /**
     * @return array{0: string, 1: ?string}
     */
    private function parseResponse(string $raw): array
    {
        if (preg_match('/SAMENVATTING:\s*(.*?)\s*RELEVANTIE:\s*(.*)/is', $raw, $matches) === 1) {
            return [trim($matches[1]), trim($matches[2])];
        }

        return [trim($raw), null];
    }
}
