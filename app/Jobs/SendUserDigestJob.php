<?php

namespace App\Jobs;

use App\Mail\RulingDigestMail;
use App\Models\Ruling;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendUserDigestJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    /**
     * @param  array<int, int>  $rulingIds
     */
    public function __construct(
        public readonly User $user,
        public readonly array $rulingIds,
    ) {
    }

    public function handle(): void
    {
        $rulings = Ruling::query()->with('themes')->whereIn('id', $this->rulingIds)->get();

        if ($rulings->isEmpty()) {
            return;
        }

        Mail::to($this->user)->send(new RulingDigestMail($this->user, $rulings));

        // Advance the watermark only after a confirmed send: on failure the next digest:send run
        // re-covers this same window (no missed rulings); on success the window moves past what
        // was just sent (no duplicates). forceFill() is used because last_digest_sent_at is
        // system-managed bookkeeping, not a user-editable attribute — it's deliberately absent
        // from User::$fillable.
        $this->user->forceFill(['last_digest_sent_at' => now()])->save();
    }
}
