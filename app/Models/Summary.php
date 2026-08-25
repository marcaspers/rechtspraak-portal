<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Summary extends Model
{
    use HasFactory;

    protected $fillable = [
        'ruling_id',
        'prompt_template_id',
        'content',
        'relevance_note',
        'provider',
        'model',
        'tokens_prompt',
        'tokens_completion',
        'is_current',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'is_current' => 'boolean',
            'generated_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Ruling, $this>
     */
    public function ruling(): BelongsTo
    {
        return $this->belongsTo(Ruling::class);
    }

    /**
     * @return BelongsTo<PromptTemplate, $this>
     */
    public function promptTemplate(): BelongsTo
    {
        return $this->belongsTo(PromptTemplate::class);
    }
}
