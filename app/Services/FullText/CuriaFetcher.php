<?php

namespace App\Services\FullText;

use App\Models\Ruling;
use App\Services\FullText\Contracts\FullTextFetcherInterface;
use App\Services\FullText\Exceptions\FullTextFetchNotSupportedException;

/**
 * HvJ EU / Curia does not yet have a full-text fetch implementation — its result pages require
 * session/cookie handling that is out of scope for the MVP (only Rechtspraak.nl needs to work
 * end-to-end). The interface is kept real so a future implementation is a drop-in replacement.
 */
class CuriaFetcher implements FullTextFetcherInterface
{
    public function fetch(Ruling $ruling): string
    {
        throw new FullTextFetchNotSupportedException(
            "Full-text fetching for Curia/HvJ EU is not yet implemented (ruling [{$ruling->id}])."
        );
    }
}
