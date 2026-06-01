<?php

namespace App\Console\Commands;

use Illuminate\Console\ConfirmableTrait;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

#[Signature('app:seed-stress-data
    {--albums=5000 : Number of albums to create}
    {--tracks=100000 : Number of tracks and backing album tracks to create}
    {--libraries=5 : Number of libraries to spread albums across}
    {--genres=30 : Number of reusable genre tags to create}
    {--keywords=80 : Number of reusable keyword tags to create}
    {--chunk=1000 : Number of tracks to insert per batch}
    {--fresh : Delete existing stress-test data before seeding}
    {--force : Allow the command to run in production}')]
#[Description('Seed a large local music catalog for stress testing track and album pages.')]
class SeedStressDataCommand extends Command
{
    use ConfirmableTrait;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! $this->confirmToProceed()) {
            return self::FAILURE;
        }

        $albums = max(1, (int) $this->option('albums'));
        $tracks = max(1, (int) $this->option('tracks'));
        $libraries = max(1, (int) $this->option('libraries'));
        $genres = max(0, (int) $this->option('genres'));
        $keywords = max(0, (int) $this->option('keywords'));
        $chunk = max(1, (int) $this->option('chunk'));
        $batch = now()->format('YmdHis');

        if ($this->option('fresh')) {
            $this->deleteStressData();
        }

        $this->info("Seeding {$albums} albums and {$tracks} tracks for local stress testing.");

        $libraryIds = $this->seedLibraries($libraries, $batch);
        $albumIds = $this->seedAlbums($albums, $libraryIds, $batch, $chunk);
        $genreIds = $this->seedTags('genres', $genres);
        $keywordIds = $this->seedTags('keywords', $keywords);

        $this->seedTracks($tracks, $albumIds, $genreIds, $keywordIds, $batch, $chunk);

        $this->newLine();
        $this->info('Stress data is ready.');
        $this->line('Try: /tracks, /tracks?search=Stress Track 999, /tracks?bpm_min=120, /albums');

        return self::SUCCESS;
    }

    /**
     * Delete stress data created by this command.
     */
    private function deleteStressData(): void
    {
        $this->warn('Deleting existing stress-test data...');

        DB::table('albums')
            ->where('source_metadata', 'like', '%stress-test%')
            ->delete();

        DB::table('libraries')
            ->where('source_metadata', 'like', '%stress-test%')
            ->delete();

        DB::table('genres')
            ->where('slug', 'like', 'stress-genre-%')
            ->delete();

        DB::table('keywords')
            ->where('slug', 'like', 'stress-keyword-%')
            ->delete();
    }

    /**
     * @return list<int>
     */
    private function seedLibraries(int $count, string $batch): array
    {
        $now = now();
        $rows = [];
        $libraryKeys = [];

        for ($number = 1; $number <= $count; $number++) {
            $libraryKey = "STRESS-LIB-{$batch}-{$number}";
            $libraryKeys[] = $libraryKey;

            $rows[] = [
                'featured' => $number === 1,
                'detail' => 'Generated library for local stress testing.',
                'name' => "Stress Library {$number}",
                'library_id' => $libraryKey,
                'location' => 'Local',
                'website' => 'https://example.test',
                'library_logo_url' => null,
                'status' => true,
                'last_updated' => $now,
                'codes' => json_encode(["SL{$number}"]),
                'type' => 'stress',
                'source_metadata' => $this->sourceMetadata($batch),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('libraries')->insert($rows);

        return DB::table('libraries')
            ->whereIn('library_id', $libraryKeys)
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->all();
    }

    /**
     * @param  list<int>  $libraryIds
     * @return list<int>
     */
    private function seedAlbums(int $count, array $libraryIds, string $batch, int $chunk): array
    {
        $this->line('Creating albums...');

        $albumIds = [];
        $progress = $this->output->createProgressBar($count);
        $progress->start();

        for ($offset = 0; $offset < $count; $offset += $chunk) {
            $now = now();
            $rows = [];
            $codes = [];
            $limit = min($chunk, $count - $offset);

            for ($index = 1; $index <= $limit; $index++) {
                $number = $offset + $index;
                $code = "STRESS-ALB-{$batch}-{$number}";
                $codes[] = $code;

                $rows[] = [
                    'library_id' => $libraryIds[($number - 1) % count($libraryIds)],
                    'displaytitle' => "Stress Album {$number}",
                    'featured' => $number % 20 === 0,
                    'releasedate' => now()->subDays($number % 3650)->toDateString(),
                    'code' => $code,
                    'detail' => 'Generated album for local stress testing.',
                    'cover' => "https://picsum.photos/seed/stress-album-{$number}/640/640",
                    'name' => "stress-album-{$number}",
                    'status' => true,
                    'libraryfeatured' => $number % 25 === 0,
                    'source_metadata' => $this->sourceMetadata($batch),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::table('albums')->insert($rows);

            $albumIds = [
                ...$albumIds,
                ...DB::table('albums')
                    ->whereIn('code', $codes)
                    ->orderBy('id')
                    ->pluck('id')
                    ->map(fn ($id): int => (int) $id)
                    ->all(),
            ];

            $progress->advance($limit);
        }

        $progress->finish();
        $this->newLine(2);

        return $albumIds;
    }

    /**
     * @return list<int>
     */
    private function seedTags(string $table, int $count): array
    {
        if ($count === 0) {
            return [];
        }

        $singular = Str::singular($table);
        $now = now();
        $rows = [];
        $slugs = [];

        for ($number = 1; $number <= $count; $number++) {
            $slug = "stress-{$singular}-{$number}";
            $slugs[] = $slug;

            $rows[] = [
                'name' => "Stress {$singular} {$number}",
                'slug' => $slug,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table($table)->upsert($rows, ['slug'], ['name', 'updated_at']);

        return DB::table($table)
            ->whereIn('slug', $slugs)
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->all();
    }

    /**
     * @param  list<int>  $albumIds
     * @param  list<int>  $genreIds
     * @param  list<int>  $keywordIds
     */
    private function seedTracks(int $count, array $albumIds, array $genreIds, array $keywordIds, string $batch, int $chunk): void
    {
        $this->line('Creating album tracks, tracks, and tag pivots...');

        $progress = $this->output->createProgressBar($count);
        $progress->start();

        for ($offset = 0; $offset < $count; $offset += $chunk) {
            $now = now();
            $limit = min($chunk, $count - $offset);
            $albumTrackRows = [];
            $fileNames = [];

            for ($index = 1; $index <= $limit; $index++) {
                $number = $offset + $index;
                $albumId = $albumIds[($number - 1) % count($albumIds)];
                $fileName = "stress-track-{$batch}-{$number}.mp3";
                $fileNames[] = $fileName;

                $albumTrackRows[] = [
                    'album_id' => $albumId,
                    'track_number' => (($number - 1) % 20) + 1,
                    'name' => "Stress Track {$number}",
                    'file_name' => $fileName,
                    'file_size' => 3_000_000 + ($number * 97) % 15_000_000,
                    'bucket' => 'local-stress',
                    'key' => "stress/{$batch}/{$fileName}",
                    'download_token' => (string) Str::uuid(),
                    'local_file_path' => "/tmp/{$fileName}",
                    'downloaded_at' => $now,
                    'item_type' => 'track',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::table('album_tracks')->insert($albumTrackRows);

            $albumTrackIds = DB::table('album_tracks')
                ->whereIn('file_name', $fileNames)
                ->pluck('id', 'file_name');

            $trackRows = [];
            $genreTrackRows = [];
            $keywordTrackRows = [];

            for ($index = 1; $index <= $limit; $index++) {
                $number = $offset + $index;
                $albumId = $albumIds[($number - 1) % count($albumIds)];
                $genreName = count($genreIds) > 0 ? 'Stress genre '.(($number - 1) % count($genreIds) + 1) : null;
                $keywordName = count($keywordIds) > 0 ? 'Stress keyword '.(($number - 1) % count($keywordIds) + 1) : null;
                $fileName = "stress-track-{$batch}-{$number}.mp3";

                $trackRows[] = [
                    'album_id' => $albumId,
                    'album_track_id' => (int) $albumTrackIds[$fileName],
                    'track_number' => (($number - 1) % 20) + 1,
                    'name' => "Stress Track {$number}",
                    'display_title' => "Stress Track {$number}",
                    'version' => $number % 7 === 0 ? 'Instrumental' : 'Original',
                    'time' => sprintf('%02d:%02d', 2 + ($number % 4), $number % 60),
                    'lenght_seconds' => 120 + ($number % 260),
                    'genre' => $genreName,
                    'tempo' => ['slow', 'medium', 'fast'][$number % 3],
                    'bpm' => 60 + ($number % 121),
                    'composer' => "Stress Composer ".(($number % 200) + 1),
                    'publisher' => "Stress Publisher ".(($number % 50) + 1),
                    'instrumentation' => 'Piano, drums, bass, synth',
                    'cd_code' => sprintf('ST-%06d', $number),
                    'comment' => 'Generated track for local stress testing.',
                    'cover' => "https://picsum.photos/seed/stress-track-{$number}/640/640",
                    'release_date' => now()->subDays($number % 3650)->toDateString(),
                    'status' => 'active',
                    'keywords' => $keywordName,
                    'stem_count' => [null, 2, 4, 8][$number % 4],
                    'is_alternative' => $number % 12 === 0,
                    'api_status' => 'ready',
                    'source_metadata' => $this->sourceMetadata($batch),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::table('tracks')->insert($trackRows);

            $trackIds = DB::table('tracks')
                ->whereIn('album_track_id', $albumTrackIds->values())
                ->orderBy('id')
                ->pluck('id')
                ->map(fn ($id): int => (int) $id)
                ->all();

            foreach ($trackIds as $position => $trackId) {
                $number = $offset + $position + 1;

                if (count($genreIds) > 0) {
                    $genreTrackRows[] = [
                        'genre_id' => $genreIds[($number - 1) % count($genreIds)],
                        'track_id' => $trackId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if (count($keywordIds) > 0) {
                    $keywordTrackRows[] = [
                        'keyword_id' => $keywordIds[($number - 1) % count($keywordIds)],
                        'track_id' => $trackId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            if ($genreTrackRows !== []) {
                DB::table('genre_track')->insert($genreTrackRows);
            }

            if ($keywordTrackRows !== []) {
                DB::table('keyword_track')->insert($keywordTrackRows);
            }

            $progress->advance($limit);
        }

        $progress->finish();
    }

    private function sourceMetadata(string $batch): string
    {
        return json_encode([
            'seed' => 'stress-test',
            'batch' => $batch,
        ]);
    }
}
