<?php

namespace App\Models;

use App\Enums\ThemeMatchType;
use Illuminate\Database\Eloquent\Relations\Pivot;

class RulingTheme extends Pivot
{
    public $incrementing = true;

    protected $table = 'ruling_theme';

    protected $fillable = [
        'ruling_id',
        'theme_id',
        'match_type',
        'relevance_score',
        'relevance_reason',
        'matched_keywords',
    ];

    protected function casts(): array
    {
        return [
            'match_type' => ThemeMatchType::class,
            'relevance_score' => 'float',
            'matched_keywords' => 'array',
        ];
    }
}
