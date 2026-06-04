<?php

namespace App\Audio;

use App\Models\AlbumTrack;
use App\Support\PublicAudio;
use Illuminate\Support\Str;

class WaveformGenerator
{
    public const VERSION = 'v4-200-balanced';

    private const PEAK_COUNT = 200;

    private const SAMPLE_RATE = 16000;

    private const MIN_PEAK = 0.01;

    public function audioUrl(AlbumTrack $track): ?string
    {
        $path = $this->audioPath($track);

        if ($path === null) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return PublicAudio::url($track->local_file_path ?: $track->key);
    }

    public function audioPath(AlbumTrack $track): ?string
    {
        $path = trim((string) ($track->local_file_path ?: $track->key));

        if ($path === '') {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return PublicAudio::path($path);
    }

    /**
     * @return list<float>
     */
    public function peaksForTrack(AlbumTrack $track): array
    {
        if (is_array($track->waveform_peaks) && $track->waveform_peaks !== []) {
            $peaks = array_map('floatval', $track->waveform_peaks);

            return count($peaks) > self::PEAK_COUNT
                ? $this->resamplePeaks($peaks, self::PEAK_COUNT)
                : $peaks;
        }

        return $this->placeholderPeaks($track);
    }

    /**
     * @return list<float>
     */
    public function generateForTrack(AlbumTrack $track): array
    {
        $path = $this->audioPath($track);

        if ($path === null || Str::startsWith($path, ['http://', 'https://'])) {
            return $this->placeholderPeaks($track);
        }

        return $this->generateFromFile($path);
    }

    /**
     * @return list<float>
     */
    public function generateFromFile(string $path): array
    {
        $ffmpeg = trim((string) shell_exec('command -v ffmpeg'));

        if ($ffmpeg === '') {
            return $this->placeholderPeaks($path);
        }

        $duration = $this->durationInSeconds($path);
        $peakCount = self::PEAK_COUNT;
        $sampleRate = self::SAMPLE_RATE;
        $samplesPerPeak = $duration !== null
            ? max(1, (int) ceil(($duration * $sampleRate) / $peakCount))
            : 512;

        $command = sprintf(
            '%s -hide_banner -loglevel error -i %s -ac 1 -ar %d -f s16le -',
            escapeshellarg($ffmpeg),
            escapeshellarg($path),
            $sampleRate,
        );

        $process = proc_open(
            $command,
            [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes,
        );

        if (! is_resource($process)) {
            return $this->placeholderPeaks($path);
        }

        fclose($pipes[0]);

        $peaks = [];
        $peak = 0.0;
        $sumSquares = 0.0;
        $samplesInPeak = 0;
        $leftover = '';

        while (! feof($pipes[1])) {
            $chunk = $leftover.fread($pipes[1], 8192);

            if ($chunk === '') {
                continue;
            }

            $leftover = strlen($chunk) % 2 === 1 ? substr($chunk, -1) : '';
            $chunk = $leftover !== '' ? substr($chunk, 0, -1) : $chunk;
            $chunkLength = strlen($chunk);

            for ($offset = 0; $offset < $chunkLength; $offset += 2) {
                $sample = unpack('v', substr($chunk, $offset, 2))[1];
                $signedSample = $sample > 32767 ? $sample - 65536 : $sample;
                $normalizedSample = abs($signedSample) / 32768;
                $peak = max($peak, $normalizedSample);
                $sumSquares += $normalizedSample ** 2;
                $samplesInPeak++;

                if ($samplesInPeak >= $samplesPerPeak) {
                    $peaks[] = $this->visualPeak($peak, $sumSquares, $samplesInPeak);
                    $peak = 0.0;
                    $sumSquares = 0.0;
                    $samplesInPeak = 0;
                }
            }
        }

        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        if ($samplesInPeak > 0) {
            $peaks[] = $this->visualPeak($peak, $sumSquares, $samplesInPeak);
        }

        if ($exitCode !== 0 || $peaks === []) {
            return $this->placeholderPeaks($path);
        }

        if (count($peaks) > $peakCount) {
            $peaks = $this->resamplePeaks($peaks, $peakCount);
        }

        while (count($peaks) < $peakCount) {
            $peaks[] = $peaks[array_key_last($peaks)] ?? self::MIN_PEAK;
        }

        return $this->balancePeaks($peaks);
    }

    private function durationInSeconds(string $path): ?float
    {
        $ffprobe = trim((string) shell_exec('command -v ffprobe'));

        if ($ffprobe === '') {
            return null;
        }

        $command = sprintf(
            '%s -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 %s 2>/dev/null',
            escapeshellarg($ffprobe),
            escapeshellarg($path),
        );

        $duration = trim((string) shell_exec($command));

        return is_numeric($duration) ? (float) $duration : null;
    }

    /**
     * @param  list<float>  $peaks
     * @return list<float>
     */
    private function resamplePeaks(array $peaks, int $targetCount): array
    {
        $sampleCount = count($peaks);
        $samplesPerPeak = max(1, (int) ceil($sampleCount / $targetCount));
        $resampled = [];

        for ($index = 0; $index < $sampleCount; $index += $samplesPerPeak) {
            $resampled[] = max(array_slice($peaks, $index, $samplesPerPeak));
        }

        return array_slice($resampled, 0, $targetCount);
    }

    /**
     * @return list<float>
     */
    public function placeholderPeaks(AlbumTrack|string $source): array
    {
        $seedSource = is_string($source)
            ? $source
            : ($source->file_name.$source->name.$source->id);

        $seed = array_sum(array_map('ord', str_split($seedSource)));
        $peaks = [];

        for ($index = 0; $index < self::PEAK_COUNT; $index++) {
            $seed = ($seed * 9301 + 49297) % 233280;
            $nextPeak = 0.04 + ($seed / 233280) * 0.86;
            $previousPeak = $peaks[$index - 1] ?? $nextPeak;

            $peaks[] = round(($previousPeak * 0.35) + ($nextPeak * 0.65), 4);
        }

        return $peaks;
    }

    /**
     * @param  list<float>  $peaks
     * @return list<float>
     */
    private function balancePeaks(array $peaks): array
    {
        $sortedPeaks = $peaks;
        sort($sortedPeaks);

        $referencePeak = $sortedPeaks[(int) floor((count($sortedPeaks) - 1) * 0.95)] ?? 1.0;
        $scale = match (true) {
            $referencePeak > 0 && $referencePeak < 0.18 => min(1.45, 0.18 / $referencePeak),
            $referencePeak > 0.72 => 0.72 / $referencePeak,
            default => 1.0,
        };

        return array_map(
            fn (float $peak): float => round(min(0.9, max(self::MIN_PEAK, ($peak * $scale) ** 1.08)), 4),
            $peaks,
        );
    }

    private function visualPeak(float $peak, float $sumSquares, int $samplesInPeak): float
    {
        $rms = $samplesInPeak > 0 ? sqrt($sumSquares / $samplesInPeak) : 0.0;
        $visualPeak = max($peak * 0.3, $rms * 1.25);

        return round(min(1.0, max(self::MIN_PEAK, $visualPeak)), 4);
    }
}
