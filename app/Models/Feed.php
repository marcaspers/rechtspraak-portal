<?php

namespace App\Models;

use App\Enums\FeedSource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Feed extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'url',
        'source',
        'category_tag',
        'poll_interval_minutes',
        'is_active',
        'last_fetched_at',
    ];

    protected function casts(): array
    {
        return [
            'source' => FeedSource::class,
            'poll_interval_minutes' => 'integer',
            'is_active' => 'boolean',
            'last_fetched_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<Ruling, $this>
     */
    public function rulings(): HasMany
    {
        return $this->hasMany(Ruling::class);
    }

    /**
     * @return HasMany<FeedFetchLog, $this>
     */
    public function fetchLogs(): HasMany
    {
        return $this->hasMany(FeedFetchLog::class);
    }

    public function isDue(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->last_fetched_at === null) {
            return true;
        }

        return $this->last_fetched_at->addMinutes($this->poll_interval_minutes)->isPast();
    }
}
