<?php

namespace Database\Factories;

use App\Models\MusicUsageReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MusicUsageReport>
 */
class MusicUsageReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = fake()->dateTimeBetween('-1 year', '-1 month');
        $endsAt = fake()->dateTimeBetween($startsAt, 'now');

        return [
            'user_id' => User::factory(),
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'listened_count' => fake()->numberBetween(0, 20),
            'downloaded_count' => fake()->numberBetween(0, 20),
            'duration_seconds' => fake()->numberBetween(0, 7200),
            'file_path' => 'reports/example.pdf',
        ];
    }
}
