<?php

namespace App\Jobs;

use App\Actions\Summaries\GenerateSummaryAction;
use App\Enums\RulingStatus;
use App\Models\Ruling;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SummarizeRulingJob implements ShouldQueue
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

    public function handle(GenerateSummaryAction $action): void
    {
        $action->execute($this->ruling);
    }

    public function failed(Throwable $exception): void
    {
        $this->ruling->update([
            'status' => RulingStatus::Failed,
            'error_message' => $exception->getMessage(),
        ]);
    }
}
