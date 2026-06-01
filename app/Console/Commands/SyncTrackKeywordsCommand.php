<?php

namespace App\Console\Commands;

use App\Actions\SyncTrackKeywords;
use App\Models\Track;
use Illuminate\Console\Command;

class SyncTrackKeywordsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tracks:sync-keywords {--chunk=500 : Number of tracks to process per chunk}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Normalize raw track keyword strings into the keyword filter tables.';

    /**
     * Execute the console command.
     */
    public function handle(SyncTrackKeywords $syncTrackKeywords): int
    {
        $chunkSize = max(1, (int) $this->option('chunk'));
        $processed = 0;

        Track::query()
            ->select(['id', 'keywords'])
            ->orderBy('id')
            ->chunkById($chunkSize, function ($tracks) use (&$processed, $syncTrackKeywords): void {
                foreach ($tracks as $track) {
                    $syncTrackKeywords->sync($track);
                    $processed++;
                }

                $this->components->info("Processed {$processed} tracks...");
            });

        $this->components->info("Synced keywords for {$processed} tracks.");

        return self::SUCCESS;
    }
}
