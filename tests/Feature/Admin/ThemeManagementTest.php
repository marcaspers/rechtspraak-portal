<?php

use App\Enums\UserRole;
use App\Livewire\Admin\Themes\Index;
use App\Models\Theme;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => UserRole::Admin]);
});

it('creates a new theme with keywords parsed from a comma-separated string', function () {
    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->call('create')
        ->set('name', 'Aanbestedingsrecht')
        ->set('keywordsInput', 'aanbesteding, gunning ,  inschrijving')
        ->call('save')
        ->assertSet('showingModal', false);

    $theme = Theme::query()->where('slug', 'aanbestedingsrecht')->firstOrFail();
    expect($theme->keywords)->toBe(['aanbesteding', 'gunning', 'inschrijving']);
});

it('rejects a duplicate theme name (slug collision)', function () {
    Theme::factory()->create(['name' => 'Arbeidsrecht', 'slug' => 'arbeidsrecht']);

    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->call('create')
        ->set('name', 'Arbeidsrecht')
        ->set('keywordsInput', 'ontslag')
        ->call('save')
        ->assertHasErrors(['name']);
});

it('allows renaming a theme to its own current name without a collision error', function () {
    $theme = Theme::factory()->create(['name' => 'Privacy', 'slug' => 'privacy']);

    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->call('edit', $theme->id)
        ->set('keywordsInput', 'avg, gdpr')
        ->call('save')
        ->assertHasNoErrors();

    expect($theme->fresh()->keywords)->toBe(['avg', 'gdpr']);
});

it('toggles a theme active/inactive', function () {
    $theme = Theme::factory()->create(['is_active' => true]);

    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->call('toggleActive', $theme->id);

    expect($theme->fresh()->is_active)->toBeFalse();
});
