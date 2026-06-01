<?php

namespace Database\Factories;

use App\Models\Album;
use App\Models\Library;
use App\Models\Track;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AlternateTrack>
 */
class AlternateTrackFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'track_id' => Track::factory(),
            'alternate_track_id' => fake()->boolean(50) ? Track::factory() : null,
            'mood' => fake()->randomElement(['uplifting', 'dark', 'energetic']),
            'music_for' => fake()->randomElement(['trailers', 'documentary', 'ads']),
            'track_number' => fake()->numberBetween(1, 20),
            'time' => fake()->randomElement(['02:34', '03:12', '04:05']),
            'lenght_seconds' => fake()->numberBetween(60, 360),
            'comment' => fake()->sentence(),
            'composer' => fake()->name(),
            'publisher' => fake()->company(),
            'artist' => fake()->name(),
            'name' => fake()->sentence(3),
            'album_id' => Album::factory(),
            'library_id' => Library::factory(),
            'keywords' => implode(',', fake()->words(3)),
            'lyrics' => fake()->paragraph(),
            'display_title' => fake()->sentence(3),
            'genre' => fake()->word(),
            'tempo' => fake()->randomElement(['slow', 'medium', 'fast']),
            'instrumentation' => fake()->sentence(6),
            'bpm' => fake()->numberBetween(60, 180),
            'frequency' => fake()->randomElement([44100, 48000]),
            'bitrate' => fake()->randomElement([128, 256, 320]),
            'date_ingested' => fake()->dateTime(),
            'version' => fake()->randomElement(['Original', 'Instrumental', 'No Lead']),
            'status' => fake()->randomElement(['active', 'draft', null]),
            'cd_code' => fake()->bothify('CD-####'),
            'is_alternate' => fake()->boolean(),
            'is_cached' => fake()->boolean(),
            'stem_count' => fake()->randomElement([null, 2, 4, 8]),
            'library_featured' => fake()->boolean(),
            'highlighted' => fake()->boolean(),
            'originator' => fake()->name(),
            'has_lyrics' => fake()->boolean(),
            'is_explicit' => fake()->boolean(),
            'release_date' => fake()->date(),
        ];
    }
}
