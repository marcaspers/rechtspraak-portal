<?php

use App\Enums\DigestFrequency;
use App\Enums\RulingStatus;
use App\Jobs\SendUserDigestJob;
use App\Mail\RulingDigestMail;
use App\Models\Ruling;
use App\Models\User;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;

it('dispatches a digest job only for users who are due and have new rulings', function () {
    config(['digest.send_hour' => now()->hour]);

    Bus::fake();

    $dueUser = User::factory()->create(['digest_frequency' => DigestFrequency::Daily, 'last_digest_sent_at' => null]);
    $optedOutUser = User::factory()->create(['digest_frequency' => DigestFrequency::None]);

    Ruling::factory()->create(['status' => RulingStatus::Summarized, 'published_at' => now()->subHour()]);
    Ruling::factory()->create(['status' => RulingStatus::Pending, 'published_at' => now()->subHour()]);

    $this->artisan('digest:send')->assertSuccessful();

    Bus::assertDispatched(SendUserDigestJob::class, function (SendUserDigestJob $job) use ($dueUser) {
        return $job->user->is($dueUser) && count($job->rulingIds) === 1;
    });

    Bus::assertNotDispatched(SendUserDigestJob::class, fn (SendUserDigestJob $job) => $job->user->is($optedOutUser));
});

it('does nothing outside the configured send hour', function () {
    config(['digest.send_hour' => (now()->hour + 5) % 24]);

    Bus::fake();

    User::factory()->create(['digest_frequency' => DigestFrequency::Daily, 'last_digest_sent_at' => null]);
    Ruling::factory()->create(['status' => RulingStatus::Summarized, 'published_at' => now()->subHour()]);

    $this->artisan('digest:send')->assertSuccessful();

    Bus::assertNotDispatched(SendUserDigestJob::class);
});

it('advances the watermark only after a confirmed send, so a failed send is retried', function () {
    Mail::fake();

    $user = User::factory()->create(['last_digest_sent_at' => null]);
    $ruling = Ruling::factory()->create(['status' => RulingStatus::Summarized]);

    (new SendUserDigestJob($user, [$ruling->id]))->handle();

    Mail::assertSent(RulingDigestMail::class);
    expect($user->fresh()->last_digest_sent_at)->not->toBeNull();
});

it('sends nothing and leaves the watermark untouched when there are no rulings', function () {
    Mail::fake();

    $user = User::factory()->create(['last_digest_sent_at' => null]);

    (new SendUserDigestJob($user, []))->handle();

    Mail::assertNothingSent();
    expect($user->fresh()->last_digest_sent_at)->toBeNull();
});
