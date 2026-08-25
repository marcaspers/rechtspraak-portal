<?php

namespace App\Services\FullText;

use App\Enums\FeedSource;
use App\Services\FullText\Contracts\FullTextFetcherInterface;
use App\Services\FullText\Exceptions\FullTextFetchNotSupportedException;

class FullTextFetcherFactory
{
    public function for(FeedSource $source): FullTextFetcherInterface
    {
        $fetcherClass = config("fulltext.fetchers.{$source->value}");

        if (! $fetcherClass) {
            throw new FullTextFetchNotSupportedException(
                "No full-text fetcher registered for feed source [{$source->value}]."
            );
        }

        return app($fetcherClass);
    }
}
