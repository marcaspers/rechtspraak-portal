<?php

namespace App\Models;

use App\Enums\FeedFetchStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedFetchLog extends Model
{
    protected $fillable = [
        'feed_id',
        'started_at',
        'finished_at',
        'items_found',
        'items_new',
        'items_duplicate',
        'status',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'status' => FeedFetchStatus::class,
        ];
    }

    /**
     * @return BelongsTo<Feed, $this>
     */
    public function feed(): BelongsTo
    {
        return $this->belongsTo(Feed::class);
    }
}
