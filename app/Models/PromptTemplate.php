<?php

namespace App\Models;

use App\Enums\LlmTaskKey;
use Illuminate\Database\Eloquent\Model;

class PromptTemplate extends Model
{
    protected $fillable = [
        'task_key',
        'name',
        'content',
        'version',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'task_key' => LlmTaskKey::class,
            'is_active' => 'boolean',
        ];
    }
}
