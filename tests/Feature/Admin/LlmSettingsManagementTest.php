<?php

use App\Enums\LlmProvider;
use App\Enums\LlmTaskKey;
use App\Enums\UserRole;
use App\Livewire\Admin\LlmSettings\Index;
use App\Models\LlmTaskConfig;
use App\Models\PromptTemplate;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => UserRole::Admin]);
});

it('creates a task config where none existed yet', function () {
    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->set('taskConfigs.summarization.provider', LlmProvider::OpenAi->value)
        ->set('taskConfigs.summarization.model', 'gpt-4o')
        ->set('taskConfigs.summarization.is_active', true)
        ->call('saveTaskConfig', 'summarization');

    $config = LlmTaskConfig::query()->where('task_key', LlmTaskKey::Summarization)->firstOrFail();
    expect($config->provider)->toBe(LlmProvider::OpenAi)
        ->and($config->model)->toBe('gpt-4o')
        ->and($config->is_active)->toBeTrue();
});

it('updates an existing task config', function () {
    LlmTaskConfig::factory()->create([
        'task_key' => LlmTaskKey::Classification,
        'provider' => LlmProvider::Anthropic,
        'model' => 'old-model',
        'is_active' => false,
    ]);

    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->set('taskConfigs.classification.model', 'claude-haiku')
        ->set('taskConfigs.classification.is_active', true)
        ->call('saveTaskConfig', 'classification');

    $config = LlmTaskConfig::query()->where('task_key', LlmTaskKey::Classification)->firstOrFail();
    expect($config->model)->toBe('claude-haiku')
        ->and($config->is_active)->toBeTrue();
});

it('rejects an invalid endpoint url', function () {
    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->set('taskConfigs.summarization.model', 'claude-sonnet-4-5')
        ->set('taskConfigs.summarization.endpoint', 'not-a-url')
        ->call('saveTaskConfig', 'summarization')
        ->assertHasErrors(['taskConfigs.summarization.endpoint']);
});

it('creates a new prompt template version starting inactive', function () {
    PromptTemplate::factory()->create([
        'task_key' => LlmTaskKey::Summarization,
        'version' => 1,
        'is_active' => true,
    ]);

    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->call('createTemplate', LlmTaskKey::Summarization->value)
        ->set('templateName', 'Verbeterde samenvatting')
        ->set('templateContent', 'Nieuwe inhoud met {{title}}')
        ->call('saveTemplate');

    $newTemplate = PromptTemplate::query()->where('name', 'Verbeterde samenvatting')->firstOrFail();
    expect($newTemplate->version)->toBe(2)
        ->and($newTemplate->is_active)->toBeFalse();
});

it('activating a template deactivates the sibling for the same task', function () {
    $active = PromptTemplate::factory()->create([
        'task_key' => LlmTaskKey::Summarization,
        'version' => 1,
        'is_active' => true,
    ]);
    $candidate = PromptTemplate::factory()->create([
        'task_key' => LlmTaskKey::Summarization,
        'version' => 2,
        'is_active' => false,
    ]);

    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->call('activateTemplate', $candidate->id);

    expect($active->fresh()->is_active)->toBeFalse()
        ->and($candidate->fresh()->is_active)->toBeTrue();
});

it('refuses to delete the currently active template', function () {
    $active = PromptTemplate::factory()->create(['is_active' => true]);

    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->call('deleteTemplate', $active->id)
        ->assertHasErrors();

    expect(PromptTemplate::find($active->id))->not->toBeNull();
});
