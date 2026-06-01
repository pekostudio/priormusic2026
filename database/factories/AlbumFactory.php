<?php

namespace Database\Factories;

use App\Models\Library;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Album>
 */
class AlbumFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'library_id' => Library::factory(),
            'displaytitle' => fake()->sentence(3),
            'featured' => fake()->numberBetween(0, 1),
            'releasedate' => fake()->date(),
            'code' => strtoupper(fake()->bothify('ALB-###??')),
            'detail' => fake()->paragraph(),
            'cover' => fake()->imageUrl(640, 640, 'music', true, 'album'),
            'name' => fake()->words(2, true),
            'status' => fake()->boolean(75) ? true : null,
            'libraryfeatured' => fake()->numberBetween(0, 1),
        ];
    }
}
