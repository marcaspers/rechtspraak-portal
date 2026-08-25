<?php

use App\Enums\FeedSource;
use App\Services\FullText\CuriaFetcher;
use App\Services\FullText\EchrFetcher;
use App\Services\FullText\RechtspraakFetcher;

return [

    /*
    |--------------------------------------------------------------------------
    | Full-text fetcher class map
    |--------------------------------------------------------------------------
    |
    | Maps a feed's `source` value to the class that fetches the full ruling text for it.
    | Adding a new source is one class + one entry here (open/closed principle) — see
    | App\Services\FullText\Contracts\FullTextFetcherInterface.
    |
    */
    'fetchers' => [
        FeedSource::Rechtspraak->value => RechtspraakFetcher::class,
        FeedSource::Curia->value => CuriaFetcher::class,
        FeedSource::Echr->value => EchrFetcher::class,
    ],

];
