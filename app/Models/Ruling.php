<?php

namespace App\Models;

use App\Enums\RulingStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Ruling extends Model
{
    use HasFactory;

    protected $fillable = [
        'feed_id',
        'ecli',
        'source_guid',
        'title',
        'instantie',
        'rechtsgebied',
        'published_at',
        'source_url',
        'rss_summary',
        'status',
        'full_text',
        'full_text_fetched_at',
        'error_message',
    ];

    protected $hidden = [
        'full_text',
    ];

    protected function casts(): array
    {
        return [
            'status' => RulingStatus::class,
            'published_at' => 'datetime',
            'full_text_fetched_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Feed, $this>
     */
    public function feed(): BelongsTo
    {
        return $this->belongsTo(Feed::class);
    }

    /**
     * @return BelongsToMany<Theme, $this, RulingTheme>
     */
    public function themes(): BelongsToMany
    {
        return $this->belongsToMany(Theme::class, 'ruling_theme')
            ->using(RulingTheme::class)
            ->withPivot(['match_type', 'relevance_score', 'relevance_reason', 'matched_keywords'])
            ->withTimestamps();
    }

    /**
     * @return HasMany<Summary, $this>
     */
    public function summaries(): HasMany
    {
        return $this->hasMany(Summary::class);
    }

    /**
     * @return HasOne<Summary, $this>
     */
    public function currentSummary(): HasOne
    {
        return $this->hasOne(Summary::class)->where('is_current', true);
    }

    /**
     * @return HasMany<LlmUsageLog, $this>
     */
    public function usageLogs(): HasMany
    {
        return $this->hasMany(LlmUsageLog::class);
    }

    /**
     * @return HasMany<UserRulingState, $this>
     */
    public function userStates(): HasMany
    {
        return $this->hasMany(UserRulingState::class);
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('status', RulingStatus::Summarized);
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->whereRaw(
            "search_vector @@ websearch_to_tsquery('dutch', ?)",
            [$term]
        );
    }
}
