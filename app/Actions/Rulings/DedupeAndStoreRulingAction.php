<?php

namespace App\Actions\Rulings;

use App\Enums\RulingStatus;
use App\Models\Feed;
use App\Models\Ruling;
use App\Services\Feeds\DTO\FeedEntryData;
use Illuminate\Database\QueryException;

class DedupeAndStoreRulingAction
{
    /**
     * Store a feed entry as a ruling if it doesn't already exist. Dedup key is the ECLI number
     * when present, falling back to the feed's GUID for entries without a parseable ECLI. The
     * unique database indexes on both columns are the hard concurrency guard; this action's own
     * lookup is an optimization to avoid a failed insert in the common case.
     *
     * The caller should only dispatch downstream processing when `$ruling->wasRecentlyCreated`
     * is true, so re-running a feed fetch never re-triggers classification/full-text/summary
     * jobs (and their LLM cost) for a ruling that already exists.
     */
    public function execute(Feed $feed, FeedEntryData $entry): Ruling
    {
        $existing = $this->findExisting($entry);

        if ($existing) {
            return $existing;
        }

        try {
            return Ruling::create([
                'feed_id' => $feed->id,
                'ecli' => $entry->ecli,
                'source_guid' => $entry->ecli ? null : $entry->guid,
                'title' => $entry->title,
                'instantie' => $feed->category_tag,
                'rechtsgebied' => $feed->category_tag,
                'published_at' => $entry->publishedAt,
                'source_url' => $entry->link,
                'rss_summary' => $entry->summary,
                'status' => RulingStatus::Pending,
            ]);
        } catch (QueryException $exception) {
            // Concurrent job run inserted the same ecli/source_guid first — the unique index is
            // the real dedup guard, this is just the app-level lookup losing a race.
            $existing = $this->findExisting($entry);

            if ($existing) {
                return $existing;
            }

            throw $exception;
        }
    }

    private function findExisting(FeedEntryData $entry): ?Ruling
    {
        if ($entry->ecli) {
            $existing = Ruling::query()->where('ecli', $entry->ecli)->first();

            if ($existing) {
                return $existing;
            }
        }

        if ($entry->guid) {
            return Ruling::query()->where('source_guid', $entry->guid)->first();
        }

        return null;
    }
}
