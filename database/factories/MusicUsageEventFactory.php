<?php

namespace Database\Factories;

use App\Models\AlbumTrack;
use App\Models\MusicUsageEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MusicUsageEvent>
 */
class MusicUsageEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'album_track_id' => AlbumTrack::factory(),
            'event_type' => fake()->randomElement([
                MusicUsageEvent::TypeListened,
                MusicUsageEvent::TypeDownloaded,
            ]),
            'occurred_at' => fake()->dateTimeBetween('-1 year'),
            'duration_seconds' => fake()->optional()->numberBetween(30, 600),
            'track_title' => fake()->sentence(3),
            'album_title' => fake()->sentence(3),
            'metadata' => null,
        ];
    }
}
