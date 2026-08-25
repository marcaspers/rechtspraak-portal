<?php

namespace App\Console\Commands;

use App\Enums\DigestFrequency;
use App\Jobs\SendUserDigestJob;
use App\Models\Ruling;
use App\Models\User;
use Illuminate\Console\Command;

class SendDigestsCommand extends Command
{
    protected $signature = 'digest:send';

    protected $description = 'Send the daily/weekly ruling digest e-mail to users who are due for one';

    public function handle(): int
    {
        $sendHour = (int) config('digest.send_hour');

        if (now()->hour !== $sendHour) {
            $this->info("Not the configured digest hour ({$sendHour}:00), skipping.");

            return self::SUCCESS;
        }

        $dispatched = 0;

        User::query()
            ->where('digest_frequency', '!=', DigestFrequency::None)
            ->each(function (User $user) use (&$dispatched) {
                if ($user->digest_frequency === DigestFrequency::Weekly && ! now()->isMonday()) {
                    return;
                }

                $rulingIds = Ruling::query()
                    ->visible()
                    ->when(
                        $user->last_digest_sent_at,
                        fn ($query, $since) => $query->where('published_at', '>', $since)
                    )
                    ->orderBy('published_at')
                    ->pluck('id')
                    ->all();

                if ($rulingIds === []) {
                    return;
                }

                SendUserDigestJob::dispatch($user, $rulingIds);
                $dispatched++;
            });

        $this->info("Dispatched {$dispatched} digest(s).");

        return self::SUCCESS;
    }
}
