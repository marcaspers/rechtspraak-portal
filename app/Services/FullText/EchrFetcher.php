<?php

namespace App\Services\FullText;

use App\Models\Ruling;
use App\Services\FullText\Contracts\FullTextFetcherInterface;
use App\Services\FullText\Exceptions\FullTextFetchNotSupportedException;

/**
 * EHRM/HUDOC does not yet have a full-text fetch implementation for the MVP — see CuriaFetcher
 * for the same rationale. The interface is kept real so a future implementation is a drop-in
 * replacement.
 */
class EchrFetcher implements FullTextFetcherInterface
{
    public function fetch(Ruling $ruling): string
    {
        throw new FullTextFetchNotSupportedException(
            "Full-text fetching for EHRM is not yet implemented (ruling [{$ruling->id}])."
        );
    }
}
