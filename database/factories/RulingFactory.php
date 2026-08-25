<?php

namespace Database\Factories;

use App\Enums\RulingStatus;
use App\Models\Feed;
use Illuminate\Database\Eloquent\Factories\Factory;

class RulingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'feed_id' => Feed::factory(),
            'ecli' => 'ECLI:NL:'.strtoupper(fake()->lexify('????')).':'.fake()->year().':'.fake()->numberBetween(1, 9999),
            'source_guid' => null,
            'title' => fake()->sentence(),
            'instantie' => fake()->randomElement(['Rechtbank Amsterdam', 'Gerechtshof Den Haag', 'Hoge Raad']),
            'rechtsgebied' => null,
            'published_at' => fake()->dateTimeBetween('-1 year'),
            'source_url' => fake()->url(),
            'rss_summary' => fake()->paragraph(),
            'status' => RulingStatus::Pending,
        ];
    }
}
