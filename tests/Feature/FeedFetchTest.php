<?php

use App\Actions\Rulings\DedupeAndStoreRulingAction;
use App\Enums\FeedFetchStatus;
use App\Enums\FeedSource;
use App\Jobs\ClassifyRulingJob;
use App\Jobs\FetchFeedJob;
use App\Models\Feed;
use App\Models\FeedFetchLog;
use App\Models\Ruling;
use App\Services\Feeds\FeedParserService;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;

beforeEach(function () {
    Sleep::fake();
});

function atomFeedFixture(): string
{
    return <<<'XML'
    <?xml version="1.0" encoding="UTF-8"?>
    <feed xmlns="http://www.w3.org/2005/Atom">
        <title>Rechtspraak Open Data (Uitspraken)</title>
        <updated>2026-08-24T11:00:00+02:00</updated>
        <entry>
            <id>ECLI:NL:RBOVE:2026:5146</id>
            <title>ECLI:NL:RBOVE:2026:5146, Rechtbank Overijssel, ontslag op staande voet</title>
            <updated>2026-08-24T10:00:00+02:00</updated>
            <summary>Arbeidszaak over ontslag op staande voet wegens werkweigering.</summary>
            <link href="https://uitspraken.rechtspraak.nl/details?id=ECLI:NL:RBOVE:2026:5146" />
        </entry>
        <entry>
            <id>ECLI:NL:RBMNE:2026:5818</id>
            <title>ECLI:NL:RBMNE:2026:5818, Rechtbank Midden-Nederland, burengeschil</title>
            <updated>2026-08-24T09:00:00+02:00</updated>
            <summary>Geschil tussen buren over een erfafscheiding.</summary>
            <link href="https://uitspraken.rechtspraak.nl/details?id=ECLI:NL:RBMNE:2026:5818" />
        </entry>
    </feed>
    XML;
}

it('parses feed entries and stores them as rulings', function () {
    Bus::fake();
    Http::fake(['data.rechtspraak.nl/*' => Http::response(atomFeedFixture(), 200)]);

    $feed = Feed::factory()->create([
        'url' => 'https://data.rechtspraak.nl/uitspraken/zoeken?max=50&sort=DESC',
        'source' => FeedSource::Rechtspraak,
    ]);

    (new FetchFeedJob($feed))->handle(app(FeedParserService::class), app(DedupeAndStoreRulingAction::class));

    expect(Ruling::count())->toBe(2);

    $ruling = Ruling::query()->where('ecli', 'ECLI:NL:RBOVE:2026:5146')->firstOrFail();
    expect($ruling->title)->toContain('ontslag op staande voet');

    Bus::assertDispatched(ClassifyRulingJob::class, 2);

    $log = FeedFetchLog::query()->where('feed_id', $feed->id)->firstOrFail();
    expect($log->status)->toBe(FeedFetchStatus::Success)
        ->and($log->items_found)->toBe(2)
        ->and($log->items_new)->toBe(2);
});

it('marks the feed fetch log as failed when the feed cannot be reached', function () {
    Bus::fake();
    Http::fake(['data.rechtspraak.nl/*' => Http::response('', 500)]);

    $feed = Feed::factory()->create([
        'url' => 'https://data.rechtspraak.nl/uitspraken/zoeken?max=50&sort=DESC',
        'source' => FeedSource::Rechtspraak,
    ]);

    expect(fn () => (new FetchFeedJob($feed))->handle(
        app(FeedParserService::class),
        app(DedupeAndStoreRulingAction::class)
    ))->toThrow(RuntimeException::class);

    $log = FeedFetchLog::query()->where('feed_id', $feed->id)->firstOrFail();
    expect($log->status)->toBe(FeedFetchStatus::Failed);

    Bus::assertNotDispatched(ClassifyRulingJob::class);
});
