<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Theme extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'keywords',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'keywords' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsToMany<Ruling, $this, RulingTheme>
     */
    public function rulings(): BelongsToMany
    {
        return $this->belongsToMany(Ruling::class, 'ruling_theme')
            ->using(RulingTheme::class)
            ->withPivot(['match_type', 'relevance_score', 'relevance_reason', 'matched_keywords'])
            ->withTimestamps();
    }
}
