<?php

namespace App\Jobs;

use App\Enums\RulingStatus;
use App\Models\Ruling;
use App\Services\FullText\Exceptions\FullTextFetchNotSupportedException;
use App\Services\FullText\FullTextFetcherFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class FetchFullTextJob implements ShouldQueue
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

    public function __construct(public readonly Ruling $ruling)
    {
    }

    public function handle(FullTextFetcherFactory $fetcherFactory): void
    {
        try {
            $fetcher = $fetcherFactory->for($this->ruling->feed->source);
            $text = $fetcher->fetch($this->ruling);
        } catch (FullTextFetchNotSupportedException $exception) {
            // Not a transient failure — retrying won't help, fail immediately.
            $this->fail($exception);

            return;
        }

        $this->ruling->update([
            'full_text' => $text,
            'full_text_fetched_at' => now(),
            'status' => RulingStatus::TextFetched,
        ]);

        SummarizeRulingJob::dispatch($this->ruling);
    }

    public function failed(Throwable $exception): void
    {
        $this->ruling->update([
            'status' => RulingStatus::Failed,
            'error_message' => $exception->getMessage(),
        ]);
    }
}
