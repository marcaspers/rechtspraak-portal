<?php

namespace App\Jobs;

use App\Actions\Rulings\DedupeAndStoreRulingAction;
use App\Enums\FeedFetchStatus;
use App\Models\Feed;
use App\Models\FeedFetchLog;
use App\Services\Feeds\FeedParserService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class FetchFeedJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function backoff(): array
    {
        return [10, 60, 300];
    }

    public function __construct(public readonly Feed $feed)
    {
    }

    public function handle(FeedParserService $parser, DedupeAndStoreRulingAction $dedupe): void
    {
        $startedAt = now();

        // Set before parsing so a slow/retrying job doesn't get re-dispatched by the next
        // feeds:fetch-due tick while this attempt is still in flight.
        $this->feed->update(['last_fetched_at' => $startedAt]);

        $itemsFound = 0;
        $itemsNew = 0;
        $itemsDuplicate = 0;
        $itemsErrored = 0;
        $status = FeedFetchStatus::Success;
        $errorMessage = null;

        try {
            foreach ($parser->parse($this->feed->url) as $entry) {
                $itemsFound++;

                try {
                    $ruling = $dedupe->execute($this->feed, $entry);

                    if ($ruling->wasRecentlyCreated) {
                        $itemsNew++;
                        ClassifyRulingJob::dispatch($ruling);
                    } else {
                        $itemsDuplicate++;
                    }
                } catch (Throwable $entryException) {
                    $itemsErrored++;
                    Log::warning('Failed to process feed entry', [
                        'feed_id' => $this->feed->id,
                        'entry_title' => $entry->title,
                        'error' => $entryException->getMessage(),
                    ]);
                }
            }
        } catch (Throwable $feedException) {
            $status = FeedFetchStatus::Failed;
            $errorMessage = $feedException->getMessage();

            Log::error('Feed fetch failed', [
                'feed_id' => $this->feed->id,
                'error' => $errorMessage,
            ]);
        }

        if ($status === FeedFetchStatus::Success && $itemsErrored > 0) {
            $status = FeedFetchStatus::Partial;
        }

        FeedFetchLog::create([
            'feed_id' => $this->feed->id,
            'started_at' => $startedAt,
            'finished_at' => now(),
            'items_found' => $itemsFound,
            'items_new' => $itemsNew,
            'items_duplicate' => $itemsDuplicate + $itemsErrored,
            'status' => $status,
            'error_message' => $errorMessage ?? ($itemsErrored > 0 ? "{$itemsErrored} entr(y/ies) failed to process, see logs." : null),
        ]);

        if ($status === FeedFetchStatus::Failed) {
            throw new \RuntimeException($errorMessage);
        }
    }
}
