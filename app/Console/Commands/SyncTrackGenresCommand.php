<?php

namespace App\Console\Commands;

use App\Actions\SyncTrackGenres;
use App\Models\Track;
use Illuminate\Console\Command;

class SyncTrackGenresCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tracks:sync-genres {--chunk=500 : Number of tracks to process per chunk}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Normalize raw track genre strings into the genre filter tables.';

    /**
     * Execute the console command.
     */
    public function handle(SyncTrackGenres $syncTrackGenres): int
    {
        $chunkSize = max(1, (int) $this->option('chunk'));
        $processed = 0;

        Track::query()
            ->select(['id', 'genre'])
            ->orderBy('id')
            ->chunkById($chunkSize, function ($tracks) use (&$processed, $syncTrackGenres): void {
                foreach ($tracks as $track) {
                    $syncTrackGenres->sync($track);
                    $processed++;
                }

                $this->components->info("Processed {$processed} tracks...");
            });

        $this->components->info("Synced genres for {$processed} tracks.");

        return self::SUCCESS;
    }
}
