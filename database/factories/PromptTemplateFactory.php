<?php

namespace Database\Factories;

use App\Enums\LlmTaskKey;
use Illuminate\Database\Eloquent\Factories\Factory;

class PromptTemplateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'task_key' => fake()->randomElement(LlmTaskKey::cases()),
            'name' => fake()->words(3, true),
            'content' => fake()->paragraph(),
            'version' => 1,
            'is_active' => false,
        ];
    }
}
