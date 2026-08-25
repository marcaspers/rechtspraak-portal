<?php

namespace App\Services\Feeds;

use App\Services\Feeds\DTO\FeedEntryData;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\LazyCollection;
use Laminas\Feed\Reader\Reader;

class FeedParserService
{
    private const ECLI_PATTERN = '/ECLI:[A-Z]{2}:[A-Z0-9.]+:\d{4}:[A-Z0-9.]+/i';

    /**
     * Fetch and parse an RSS/Atom feed, returning normalized entries.
     *
     * @return LazyCollection<int, FeedEntryData>
     */
    public function parse(string $url): LazyCollection
    {
        $response = Http::retry(3, 1000, throw: false)
            ->timeout(30)
            ->withHeaders(['User-Agent' => 'RechtspraakPortal/1.0'])
            ->get($url);

        if ($response->failed()) {
            throw new \RuntimeException("Failed to fetch feed [{$url}]: HTTP {$response->status()}");
        }

        $feed = Reader::importString($response->body());

        return LazyCollection::make(function () use ($feed) {
            foreach ($feed as $entry) {
                $link = (string) $entry->getLink();
                $guid = $entry->getId() ? (string) $entry->getId() : null;
                $title = (string) $entry->getTitle();
                $summary = $entry->getDescription() ? (string) $entry->getDescription() : null;

                $publishedAt = $entry->getDateModified() ?? $entry->getDateCreated();
                $publishedAt = $publishedAt
                    ? CarbonImmutable::instance($publishedAt)
                    : CarbonImmutable::now();

                yield new FeedEntryData(
                    title: $title,
                    link: $link,
                    guid: $guid,
                    summary: $summary,
                    publishedAt: $publishedAt,
                    ecli: $this->extractEcli($title.' '.$guid.' '.$link),
                );
            }
        });
    }

    private function extractEcli(string $haystack): ?string
    {
        if (preg_match(self::ECLI_PATTERN, $haystack, $matches) === 1) {
            return strtoupper($matches[0]);
        }

        return null;
    }
}
