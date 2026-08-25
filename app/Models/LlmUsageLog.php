<?php

namespace App\Models;

use App\Enums\LlmProvider;
use App\Enums\LlmTaskKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LlmUsageLog extends Model
{
    protected $fillable = [
        'task_key',
        'provider',
        'model',
        'ruling_id',
        'tokens_prompt',
        'tokens_completion',
        'tokens_total',
        'cost_estimate',
        'success',
        'error_message',
        'duration_ms',
    ];

    protected function casts(): array
    {
        return [
            'task_key' => LlmTaskKey::class,
            'provider' => LlmProvider::class,
            'success' => 'boolean',
            'cost_estimate' => 'decimal:4',
        ];
    }

    /**
     * @return BelongsTo<Ruling, $this>
     */
    public function ruling(): BelongsTo
    {
        return $this->belongsTo(Ruling::class);
    }
}
