<?php

namespace App\Console\Commands;

use App\Audio\WaveformGenerator;
use App\Models\AlbumTrack;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

#[Signature('app:generate-waveforms {--force : Regenerate waveforms even when the current version exists}')]
#[Description('Generate waveform peak data for local album track audio files')]
class GenerateWaveformsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(WaveformGenerator $waveformGenerator): int
    {
        $force = (bool) $this->option('force');
        $generated = 0;
        $skipped = 0;
        $failed = 0;

        AlbumTrack::query()
            ->orderBy('id')
            ->chunkById(100, function ($tracks) use ($force, $waveformGenerator, &$generated, &$skipped, &$failed): void {
                foreach ($tracks as $track) {
                    if (
                        ! $force
                        && $track->waveform_version === WaveformGenerator::VERSION
                        && is_array($track->waveform_peaks)
                        && $track->waveform_peaks !== []
                    ) {
                        $skipped++;

                        continue;
                    }

                    if ($waveformGenerator->audioPath($track) === null) {
                        $skipped++;

                        continue;
                    }

                    try {
                        $track->forceFill([
                            'waveform_peaks' => $waveformGenerator->generateForTrack($track),
                            'waveform_version' => WaveformGenerator::VERSION,
                            'waveform_generated_at' => Carbon::now(),
                        ])->save();

                        $generated++;
                    } catch (\Throwable $exception) {
                        report($exception);

                        $failed++;
                    }
                }
            });

        $this->components->info("Waveforms generated: {$generated}");
        $this->components->info("Waveforms skipped: {$skipped}");

        if ($failed > 0) {
            $this->components->warn("Waveforms failed: {$failed}");

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
