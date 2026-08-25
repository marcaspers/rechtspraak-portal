<?php

use App\Enums\FeedSource;
use App\Enums\UserRole;
use App\Livewire\Admin\Feeds\Index;
use App\Models\Feed;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => UserRole::Admin]);
});

it('creates a new feed', function () {
    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->call('create')
        ->set('name', 'Rechtspraak.nl testfeed')
        ->set('url', 'https://data.rechtspraak.nl/uitspraken/zoeken?max=10')
        ->set('source', FeedSource::Rechtspraak->value)
        ->set('poll_interval_minutes', 30)
        ->call('save')
        ->assertSet('showingModal', false);

    $feed = Feed::query()->where('name', 'Rechtspraak.nl testfeed')->firstOrFail();
    expect($feed->source)->toBe(FeedSource::Rechtspraak)
        ->and($feed->poll_interval_minutes)->toBe(30);
});

it('requires a valid url', function () {
    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->call('create')
        ->set('name', 'Feed zonder url')
        ->set('url', 'niet-een-url')
        ->set('source', FeedSource::Rechtspraak->value)
        ->call('save')
        ->assertHasErrors(['url']);
});

it('updates an existing feed', function () {
    $feed = Feed::factory()->create(['name' => 'Oude naam', 'poll_interval_minutes' => 60]);

    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->call('edit', $feed->id)
        ->set('name', 'Nieuwe naam')
        ->set('poll_interval_minutes', 15)
        ->call('save');

    expect($feed->fresh()->name)->toBe('Nieuwe naam')
        ->and($feed->fresh()->poll_interval_minutes)->toBe(15);
});

it('toggles a feed active/paused', function () {
    $feed = Feed::factory()->create(['is_active' => true]);

    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->call('togglePause', $feed->id);

    expect($feed->fresh()->is_active)->toBeFalse();
});

it('deletes a feed', function () {
    $feed = Feed::factory()->create();

    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->call('delete', $feed->id);

    expect(Feed::find($feed->id))->toBeNull();
});
