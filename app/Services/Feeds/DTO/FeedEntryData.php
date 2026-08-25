<?php

namespace App\Services\Feeds\DTO;

use Carbon\CarbonImmutable;

final class FeedEntryData
{
    public function __construct(
        public readonly string $title,
        public readonly string $link,
        public readonly ?string $guid,
        public readonly ?string $summary,
        public readonly CarbonImmutable $publishedAt,
        public readonly ?string $ecli,
    ) {
    }
}
