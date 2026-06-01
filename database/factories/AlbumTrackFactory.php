<?php

namespace Database\Factories;

use App\Models\Album;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AlbumTrack>
 */
class AlbumTrackFactory extends Factory
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
            'track_number' => fake()->numberBetween(1, 20),
            'name' => fake()->sentence(3),
            'file_name' => fake()->regexify('[a-z0-9]{12}\.mp3'),
            'file_size' => fake()->numberBetween(100_000, 20_000_000),
            'bucket' => fake()->word(),
            'key' => fake()->lexify('albums/??????????.mp3'),
            'download_token' => fake()->uuid(),
            'local_file_path' => fake()->lexify('/tmp/??????????.mp3'),
            'downloaded_at' => fake()->dateTime(),
            'item_type' => fake()->randomElement(['track', 'podcast', 'bonus']),
            'waveform_peaks' => null,
            'waveform_version' => null,
            'waveform_generated_at' => null,
        ];
    }
}
