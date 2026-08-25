<?php

use App\Actions\Rulings\DedupeAndStoreRulingAction;
use App\Enums\FeedSource;
use App\Jobs\ClassifyRulingJob;
use App\Jobs\FetchFeedJob;
use App\Models\Feed;
use App\Models\Ruling;
use App\Services\Feeds\FeedParserService;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;

it('does not create duplicate rulings or re-dispatch downstream jobs on a repeated feed fetch', function () {
    Sleep::fake();
    Bus::fake();

    $atom = <<<'XML'
    <?xml version="1.0" encoding="UTF-8"?>
    <feed xmlns="http://www.w3.org/2005/Atom">
        <entry>
            <id>ECLI:NL:RBOVE:2026:5146</id>
            <title>ECLI:NL:RBOVE:2026:5146, Rechtbank Overijssel, ontslag op staande voet</title>
            <updated>2026-08-24T10:00:00+02:00</updated>
            <summary>Arbeidszaak.</summary>
            <link href="https://uitspraken.rechtspraak.nl/details?id=ECLI:NL:RBOVE:2026:5146" />
        </entry>
    </feed>
    XML;

    Http::fake(['data.rechtspraak.nl/*' => Http::response($atom, 200)]);

    $feed = Feed::factory()->create([
        'url' => 'https://data.rechtspraak.nl/uitspraken/zoeken?max=50&sort=DESC',
        'source' => FeedSource::Rechtspraak,
    ]);

    $runJob = fn () => (new FetchFeedJob($feed))->handle(
        app(FeedParserService::class),
        app(DedupeAndStoreRulingAction::class)
    );

    $runJob();
    $runJob();

    expect(Ruling::count())->toBe(1);

    // Only the first run's new ruling should have triggered classification — the second run's
    // encounter with the same ECLI must be recognized as a duplicate and skip downstream jobs,
    // guaranteeing no duplicate LLM cost on a re-run.
    Bus::assertDispatched(ClassifyRulingJob::class, 1);
});
