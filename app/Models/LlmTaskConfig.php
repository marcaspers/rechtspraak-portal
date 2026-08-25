<?php

namespace App\Models;

use App\Enums\LlmProvider;
use App\Enums\LlmTaskKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class LlmTaskConfig extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saved(fn (self $config) => Cache::forget("llm_task_config:{$config->task_key->value}"));
        static::deleted(fn (self $config) => Cache::forget("llm_task_config:{$config->task_key->value}"));
    }

    protected $fillable = [
        'task_key',
        'provider',
        'model',
        'endpoint',
        'extra_params',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'task_key' => LlmTaskKey::class,
            'provider' => LlmProvider::class,
            'extra_params' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
