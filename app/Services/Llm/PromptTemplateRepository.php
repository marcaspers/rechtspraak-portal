<?php

namespace App\Services\Llm;

use App\Enums\LlmTaskKey;
use App\Models\PromptTemplate;
use App\Services\Llm\Exceptions\LlmTaskNotConfiguredException;

class PromptTemplateRepository
{
    public function active(LlmTaskKey $taskKey): PromptTemplate
    {
        $template = PromptTemplate::query()
            ->where('task_key', $taskKey)
            ->where('is_active', true)
            ->first();

        if (! $template) {
            throw new LlmTaskNotConfiguredException(
                "No active prompt template found for task [{$taskKey->value}]."
            );
        }

        return $template;
    }

    /**
     * Render a template's content by replacing `{{placeholder}}` tokens with plain string
     * substitution — never Blade — since template content is admin-editable and must not be
     * able to execute arbitrary PHP.
     *
     * @param  array<string, string>  $placeholders
     */
    public function render(PromptTemplate $template, array $placeholders): string
    {
        $search = array_map(fn (string $key) => '{{'.$key.'}}', array_keys($placeholders));

        return str_replace($search, array_values($placeholders), $template->content);
    }
}
