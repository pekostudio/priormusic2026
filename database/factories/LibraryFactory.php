<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Library>
 */
class LibraryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'featured' => fake()->boolean(20),
            'detail' => fake()->paragraphs(2, true),
            'name' => fake()->company().' Library',
            'library_id' => fake()->unique()->bothify('LIB-####'),
            'location' => fake()->city(),
            'website' => fake()->url(),
            'library_logo_url' => null,
            'status' => fake()->boolean() ? true : null,
            'last_updated' => fake()->dateTimeBetween('-2 years', 'now'),
            'codes' => [
                strtoupper(fake()->bothify('??##')),
                strtoupper(fake()->bothify('??##')),
            ],
            'type' => fake()->randomElement(['public', 'private', 'digital', 'academic']),
        ];
    }
}
