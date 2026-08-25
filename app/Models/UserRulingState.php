<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserRulingState extends Model
{
    protected $fillable = [
        'user_id',
        'ruling_id',
        'is_read',
        'is_favorite',
        'is_archived',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'is_favorite' => 'boolean',
            'is_archived' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Ruling, $this>
     */
    public function ruling(): BelongsTo
    {
        return $this->belongsTo(Ruling::class);
    }
}
