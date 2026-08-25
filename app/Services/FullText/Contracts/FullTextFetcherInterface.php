<?php

namespace App\Services\FullText\Contracts;

use App\Models\Ruling;

interface FullTextFetcherInterface
{
    /**
     * Fetch and return the plain-text body of a ruling from its source.
     *
     * @throws \App\Services\FullText\Exceptions\FullTextFetchException
     */
    public function fetch(Ruling $ruling): string;
}
