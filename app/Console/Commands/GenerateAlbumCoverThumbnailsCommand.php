<?php

namespace App\Console\Commands;

use App\Models\Album;
use App\Models\Track;
use App\Support\AlbumCoverThumbnail;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:generate-album-cover-thumbnails {--force : Regenerate thumbnails even when they are current}')]
#[Description('Generate optimized thumbnails for album and track cover images')]
class GenerateAlbumCoverThumbnailsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $force = (bool) $this->option('force');
        $generated = 0;
        $skipped = 0;

        $covers = [];

        foreach ([Album::class, Track::class] as $model) {
            $model::query()
                ->whereNotNull('cover')
                ->select(['id', 'cover'])
                ->orderBy('id')
                ->chunkById(100, function ($records) use (&$covers): void {
                    foreach ($records as $record) {
                        if (is_string($record->cover) && trim($record->cover) !== '') {
                            $covers[$record->cover] = true;
                        }
                    }
                });
        }

        foreach (array_keys($covers) as $cover) {
            $thumbnail = AlbumCoverThumbnail::generate($cover, $force);

            if ($thumbnail === null) {
                $skipped++;

                continue;
            }

            $generated++;
        }

        $this->components->info("Cover thumbnails generated: {$generated}");
        $this->components->info("Cover thumbnails skipped: {$skipped}");

        return self::SUCCESS;
    }
}
