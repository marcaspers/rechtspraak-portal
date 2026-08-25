<?php

namespace Database\Factories;

use App\Enums\FeedSource;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeedFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->company().' feed',
            'url' => fake()->url(),
            'source' => fake()->randomElement(FeedSource::cases()),
            'category_tag' => null,
            'poll_interval_minutes' => 60,
            'is_active' => true,
            'last_fetched_at' => null,
        ];
    }
}
