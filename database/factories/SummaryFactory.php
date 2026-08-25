<?php

namespace Database\Factories;

use App\Models\Ruling;
use Illuminate\Database\Eloquent\Factories\Factory;

class SummaryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'ruling_id' => Ruling::factory(),
            'prompt_template_id' => null,
            'content' => fake()->paragraph(),
            'relevance_note' => fake()->sentence(),
            'provider' => 'anthropic',
            'model' => 'claude-sonnet-4-5-20250929',
            'tokens_prompt' => fake()->numberBetween(200, 2000),
            'tokens_completion' => fake()->numberBetween(50, 400),
            'is_current' => true,
            'generated_at' => now(),
        ];
    }
}
