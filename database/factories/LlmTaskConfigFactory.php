<?php

namespace Database\Factories;

use App\Enums\LlmProvider;
use App\Enums\LlmTaskKey;
use Illuminate\Database\Eloquent\Factories\Factory;

class LlmTaskConfigFactory extends Factory
{
    public function definition(): array
    {
        return [
            'task_key' => fake()->randomElement(LlmTaskKey::cases()),
            'provider' => LlmProvider::Anthropic,
            'model' => 'claude-test-model',
            'endpoint' => null,
            'extra_params' => [],
            'is_active' => true,
        ];
    }
}
