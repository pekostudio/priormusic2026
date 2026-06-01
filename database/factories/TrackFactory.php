<?php

namespace Database\Factories;

use App\Models\Album;
use App\Models\AlbumTrack;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Track>
 */
class TrackFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'album_id' => Album::factory(),
            'album_track_id' => fake()->boolean(70) ? AlbumTrack::factory() : null,
            'track_number' => fake()->numberBetween(1, 20),
            'name' => fake()->sentence(3),
            'display_title' => fake()->sentence(3),
            'version' => fake()->randomElement(['Original', 'Instrumental', 'Extended']),
            'time' => fake()->randomElement(['02:48', '03:15', '04:20']),
            'lenght_seconds' => fake()->numberBetween(120, 400),
            'genre' => fake()->word(),
            'tempo' => fake()->randomElement(['slow', 'medium', 'fast']),
            'bpm' => fake()->numberBetween(60, 180),
            'composer' => fake()->name(),
            'publisher' => fake()->company(),
            'instrumentation' => fake()->sentence(6),
            'cd_code' => fake()->bothify('CD-####'),
            'comment' => fake()->sentence(),
            'cover' => fake()->imageUrl(640, 640, 'music', true, 'track'),
            'release_date' => fake()->date(),
            'status' => fake()->randomElement(['active', null]),
            'keywords' => implode(', ', fake()->words(3)),
            'stem_count' => fake()->randomElement([null, 2, 4, 8]),
            'is_alternative' => fake()->numberBetween(0, 1),
            'api_status' => fake()->randomElement(['queued', 'ready', 'failed']),
        ];
    }
}
