<?php

namespace App\Services\Search;

use App\Models\Ruling;
use Illuminate\Support\Facades\DB;

class RulingSearchIndexer
{
    /**
     * Refresh the Postgres `search_vector` column for a ruling from its title, ECLI, and
     * current summary content. Called explicitly after summarization rather than via a DB
     * trigger, keeping the indexing logic testable in plain PHP.
     */
    public function index(Ruling $ruling): void
    {
        $currentSummary = $ruling->currentSummary()->first();
        $summaryContent = $currentSummary !== null ? $currentSummary->content : '';

        DB::statement(
            "UPDATE rulings SET search_vector = to_tsvector('dutch', ?) WHERE id = ?",
            [trim($ruling->title.' '.$ruling->ecli.' '.$summaryContent), $ruling->id]
        );
    }
}
