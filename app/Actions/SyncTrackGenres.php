<?php

namespace App\Actions;

use App\Models\Genre;
use App\Models\Track;
use App\Support\CommaSeparatedTagParser;

class SyncTrackGenres
{
    public function __construct(private readonly CommaSeparatedTagParser $parser) {}

    public function sync(Track $track): void
    {
        $genreIds = $this->parser
            ->parse($track->genre)
            ->map(function (array $genre): int {
                return Genre::query()
                    ->firstOrCreate(
                        ['slug' => $genre['slug']],
                        ['name' => $genre['name']],
                    )
                    ->id;
            });

        $track->genreTags()->sync($genreIds);
    }
}
