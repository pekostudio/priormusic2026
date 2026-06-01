<?php

namespace Database\Factories;

use App\Models\AlbumTrack;
use App\Models\TrackDownload;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrackDownload>
 */
class TrackDownloadFactory extends Factory
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
            'downloaded_at' => fake()->dateTimeBetween('-1 year'),
        ];
    }
}
