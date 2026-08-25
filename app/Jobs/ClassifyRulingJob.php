<?php

namespace App\Jobs;

use App\Enums\RulingStatus;
use App\Enums\ThemeMatchType;
use App\Models\Ruling;
use App\Services\Classification\KeywordClassifier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ClassifyRulingJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(public readonly Ruling $ruling)
    {
    }

    public function handle(KeywordClassifier $classifier): void
    {
        $matches = $classifier->classify($this->ruling);

        if ($matches->isEmpty()) {
            $this->ruling->update(['status' => RulingStatus::FilteredOut]);

            return;
        }

        foreach ($matches as $match) {
            $this->ruling->themes()->syncWithoutDetaching([
                $match->theme->id => [
                    'match_type' => ThemeMatchType::Keyword,
                    'matched_keywords' => $match->matchedKeywords,
                ],
            ]);
        }

        FetchFullTextJob::dispatch($this->ruling);
    }
}
