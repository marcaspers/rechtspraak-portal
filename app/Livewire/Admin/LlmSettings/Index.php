<?php

namespace App\Livewire\Admin\LlmSettings;

use App\Actions\Llm\ActivatePromptTemplateAction;
use App\Enums\LlmProvider;
use App\Enums\LlmTaskKey;
use App\Models\LlmTaskConfig;
use App\Models\PromptTemplate;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Index extends Component
{
    /** @var array<string, array<string, mixed>> */
    public array $taskConfigs = [];

    public bool $showingTemplateModal = false;

    public ?int $editingTemplateId = null;

    public string $templateTaskKey = '';

    public string $templateName = '';

    public string $templateContent = '';

    public function mount(): void
    {
        foreach (LlmTaskKey::cases() as $taskKey) {
            $config = LlmTaskConfig::query()->where('task_key', $taskKey)->first();

            $this->taskConfigs[$taskKey->value] = $config === null
                ? [
                    'id' => null,
                    'provider' => LlmProvider::Anthropic->value,
                    'model' => '',
                    'endpoint' => null,
                    'temperature' => 0.3,
                    'max_tokens' => 1024,
                    'is_active' => false,
                ]
                : [
                    'id' => $config->id,
                    'provider' => $config->provider->value,
                    'model' => $config->model,
                    'endpoint' => $config->endpoint,
                    'temperature' => $config->extra_params['temperature'] ?? 0.3,
                    'max_tokens' => $config->extra_params['max_tokens'] ?? 1024,
                    'is_active' => $config->is_active,
                ];
        }
    }

    public function saveTaskConfig(string $taskKey): void
    {
        $data = $this->taskConfigs[$taskKey];

        $this->validate([
            "taskConfigs.{$taskKey}.provider" => 'required|in:'.implode(',', array_column(LlmProvider::cases(), 'value')),
            "taskConfigs.{$taskKey}.model" => 'required|string|max:255',
            "taskConfigs.{$taskKey}.endpoint" => 'nullable|url|max:2048',
            "taskConfigs.{$taskKey}.temperature" => 'required|numeric|min:0|max:2',
            "taskConfigs.{$taskKey}.max_tokens" => 'required|integer|min:1|max:32000',
        ]);

        LlmTaskConfig::query()->updateOrCreate(
            ['task_key' => $taskKey],
            [
                'provider' => $data['provider'],
                'model' => $data['model'],
                'endpoint' => $data['endpoint'] ?: null,
                'extra_params' => [
                    'temperature' => (float) $data['temperature'],
                    'max_tokens' => (int) $data['max_tokens'],
                ],
                'is_active' => (bool) $data['is_active'],
            ]
        );

        // The LlmTaskConfig model already forgets its own cache entry on save, but re-mounting
        // here keeps this component's local state (including a freshly-assigned id) in sync.
        $this->mount();
    }

    public function createTemplate(string $taskKey): void
    {
        $this->reset(['editingTemplateId', 'templateName', 'templateContent']);
        $this->templateTaskKey = $taskKey;
        $this->showingTemplateModal = true;
    }

    public function editTemplate(int $templateId): void
    {
        $template = PromptTemplate::findOrFail($templateId);

        $this->editingTemplateId = $template->id;
        $this->templateTaskKey = $template->task_key->value;
        $this->templateName = $template->name;
        $this->templateContent = $template->content;
        $this->showingTemplateModal = true;
    }

    public function saveTemplate(): void
    {
        $this->validate([
            'templateName' => 'required|string|max:255',
            'templateContent' => 'required|string',
        ]);

        if ($this->editingTemplateId) {
            PromptTemplate::findOrFail($this->editingTemplateId)->update([
                'name' => $this->templateName,
                'content' => $this->templateContent,
            ]);
        } else {
            $nextVersion = 1 + (int) PromptTemplate::query()
                ->where('task_key', $this->templateTaskKey)
                ->max('version');

            PromptTemplate::create([
                'task_key' => $this->templateTaskKey,
                'name' => $this->templateName,
                'content' => $this->templateContent,
                'version' => $nextVersion,
                'is_active' => false,
            ]);
        }

        $this->showingTemplateModal = false;
    }

    public function activateTemplate(int $templateId, ActivatePromptTemplateAction $action): void
    {
        $action->execute(PromptTemplate::findOrFail($templateId));
    }

    public function deleteTemplate(int $templateId): void
    {
        $template = PromptTemplate::findOrFail($templateId);

        if ($template->is_active) {
            throw ValidationException::withMessages([
                'templateName' => __('Activeer eerst een andere template voor deze taak voordat je deze verwijdert.'),
            ]);
        }

        $template->delete();
    }

    public function render()
    {
        return view('livewire.admin.llm-settings.index', [
            'taskKeys' => LlmTaskKey::cases(),
            'providers' => LlmProvider::cases(),
            'templatesByTask' => PromptTemplate::query()
                ->orderByDesc('version')
                ->get()
                ->groupBy(fn (PromptTemplate $template) => $template->task_key->value),
        ]);
    }
}
