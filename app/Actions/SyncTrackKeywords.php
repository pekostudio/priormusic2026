<?php

namespace App\Actions;

use App\Models\Keyword;
use App\Models\Track;
use App\Support\CommaSeparatedTagParser;

class SyncTrackKeywords
{
    public function __construct(private readonly CommaSeparatedTagParser $parser) {}

    public function sync(Track $track): void
    {
        $keywordIds = $this->parser
            ->parse($track->keywords)
            ->map(function (array $keyword): int {
                return Keyword::query()
                    ->firstOrCreate(
                        ['slug' => $keyword['slug']],
                        ['name' => $keyword['name']],
                    )
                    ->id;
            });

        $track->keywordTags()->sync($keywordIds);
    }
}
