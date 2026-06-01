<?php

namespace App\Console\Commands;

use App\Actions\ImportAudioLibrary;
use Illuminate\Console\Command;

class ImportAudioLibraryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-audio {path? : Base path containing album folders}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import libraries, albums, and tracks from local album folders';

    public function __construct(private readonly ImportAudioLibrary $importAudioLibrary)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $inputPath = $this->argument('path');
        $resolvedPath = is_string($inputPath) && $inputPath !== ''
            ? $this->resolveImportPath($inputPath)
            : public_path('audio');

        try {
            $summary = ($this->importAudioLibrary)(
                $resolvedPath,
                function (string $message): void {
                    $this->line($message);
                },
            );
        } catch (\RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->newLine();
        $this->table(
            ['Metric', 'Count'],
            [
                ['Albums processed', (string) $summary['albums_processed']],
                ['Albums skipped', (string) $summary['albums_skipped']],
                ['Tracks processed', (string) $summary['tracks_processed']],
                ['Tracks skipped', (string) $summary['tracks_skipped']],
            ],
        );

        if ($summary['warnings'] !== []) {
            $this->newLine();
            $this->warn('Warnings:');

            foreach ($summary['warnings'] as $warning) {
                $this->line('- '.$warning);
            }
        }

        $this->info('Import complete.');

        return self::SUCCESS;
    }

    private function resolveImportPath(string $inputPath): string
    {
        if (str_starts_with($inputPath, '/')) {
            return $inputPath;
        }

        $publicPath = public_path($inputPath);

        if (is_dir($publicPath)) {
            return $publicPath;
        }

        return base_path($inputPath);
    }
}
