<?php

use App\Models\Ruling;
use App\Models\Theme;
use App\Services\Classification\KeywordClassifier;

it('matches a ruling against a theme whose keyword appears in the title', function () {
    $theme = Theme::factory()->create([
        'name' => 'Arbeidsrecht',
        'keywords' => ['ontslag', 'arbeidsovereenkomst'],
    ]);

    $ruling = Ruling::factory()->create([
        'title' => 'Rechtbank oordeelt over ontslag op staande voet',
        'rss_summary' => 'Een zaak over werkweigering.',
    ]);

    $matches = (new KeywordClassifier())->classify($ruling);

    expect($matches)->toHaveCount(1)
        ->and($matches->first()->theme->is($theme))->toBeTrue()
        ->and($matches->first()->matchedKeywords)->toBe(['ontslag']);
});

it('is case-insensitive', function () {
    Theme::factory()->create(['keywords' => ['AVG']]);

    $ruling = Ruling::factory()->create([
        'title' => 'Uitspraak over de avg en persoonsgegevens',
        'rss_summary' => '',
    ]);

    expect((new KeywordClassifier())->classify($ruling))->toHaveCount(1);
});

it('returns no matches when no keywords occur in the title or summary', function () {
    Theme::factory()->create(['keywords' => ['aanbesteding']]);

    $ruling = Ruling::factory()->create([
        'title' => 'Burengeschil over een erfafscheiding',
        'rss_summary' => 'Geschil tussen twee buren.',
    ]);

    expect((new KeywordClassifier())->classify($ruling))->toBeEmpty();
});

it('ignores inactive themes', function () {
    Theme::factory()->create(['keywords' => ['ontslag'], 'is_active' => false]);

    $ruling = Ruling::factory()->create([
        'title' => 'Zaak over ontslag',
        'rss_summary' => '',
    ]);

    expect((new KeywordClassifier())->classify($ruling))->toBeEmpty();
});
