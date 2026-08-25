<?php

namespace App\Actions\Llm;

use App\Models\PromptTemplate;
use Illuminate\Support\Facades\DB;

class ActivatePromptTemplateAction
{
    /**
     * Make the given template the single active one for its task, deactivating any sibling
     * first — required by the partial unique index on prompt_templates(task_key) WHERE is_active.
     */
    public function execute(PromptTemplate $template): void
    {
        DB::transaction(function () use ($template) {
            PromptTemplate::query()
                ->where('task_key', $template->task_key)
                ->where('id', '!=', $template->id)
                ->update(['is_active' => false]);

            $template->update(['is_active' => true]);
        });
    }
}
