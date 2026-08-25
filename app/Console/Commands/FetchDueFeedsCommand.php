<?php

namespace App\Console\Commands;

use App\Jobs\FetchFeedJob;
use App\Models\Feed;
use Illuminate\Console\Command;

class FetchDueFeedsCommand extends Command
{
    protected $signature = 'feeds:fetch-due';

    protected $description = 'Dispatch a fetch job for every active feed whose poll interval has elapsed';

    public function handle(): int
    {
        $dueFeeds = Feed::query()->where('is_active', true)->get()->filter->isDue();

        foreach ($dueFeeds as $feed) {
            FetchFeedJob::dispatch($feed);
        }

        $this->info("Dispatched {$dueFeeds->count()} feed fetch job(s).");

        return self::SUCCESS;
    }
}
