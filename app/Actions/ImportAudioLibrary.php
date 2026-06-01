<?php

namespace App\Actions;

use App\Models\Album;
use App\Models\AlbumTrack;
use App\Models\Library;
use App\Models\Track;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

class ImportAudioLibrary
{
    /**
     * Import metadata and audio files from album directories.
     *
     * @param  callable(string):void|null  $progress
     * @return array{
     *     albums_processed:int,
     *     albums_skipped:int,
     *     tracks_processed:int,
     *     tracks_skipped:int,
     *     warnings:list<string>
     * }
     */
    public function __invoke(string $basePath, ?callable $progress = null): array
    {
        if (! File::isDirectory($basePath)) {
            throw new RuntimeException("Import path does not exist: {$basePath}");
        }

        $syncTrackGenres = app(SyncTrackGenres::class);
        $syncTrackKeywords = app(SyncTrackKeywords::class);

        $summary = [
            'albums_processed' => 0,
            'albums_skipped' => 0,
            'tracks_processed' => 0,
            'tracks_skipped' => 0,
            'warnings' => [],
        ];

        $albumDirectories = collect(File::directories($basePath))
            ->filter(static fn (string $directory): bool => ! str_starts_with(basename($directory), '.'))
            ->values();

        foreach ($albumDirectories as $albumDirectory) {
            if ($progress !== null) {
                $progress('Processing '.basename($albumDirectory));
            }

            $metadataDirectory = $this->findMetadataDirectory($albumDirectory);
            if ($metadataDirectory === null) {
                $summary['albums_skipped']++;
                $summary['warnings'][] = 'Skipped '.basename($albumDirectory).': metadata directory not found.';

                continue;
            }

            $metadataCsvPath = $this->findMetadataCsvPath($metadataDirectory);
            if ($metadataCsvPath === null) {
                $summary['albums_skipped']++;
                $summary['warnings'][] = 'Skipped '.basename($albumDirectory).': metadata CSV not found.';

                continue;
            }

            $rows = $this->readMetadataRows($metadataCsvPath);
            if ($rows === []) {
                $summary['albums_skipped']++;
                $summary['warnings'][] = 'Skipped '.basename($albumDirectory).': metadata CSV has no rows.';

                continue;
            }

            $firstRow = $rows[0];
            $library = $this->upsertLibrary(
                $this->toTrimmedOrNull($firstRow['LIBRARY: Name'] ?? null) ?? 'Unknown Library',
                $firstRow,
            );
            $album = $this->upsertAlbum($library, $firstRow, $metadataDirectory);
            $audioFiles = $this->indexAudioFiles($albumDirectory);

            $processedTracksForAlbum = 0;

            foreach ($rows as $row) {
                $trackNumber = $this->toIntOrNull($row['TRACK: Number'] ?? null);
                $trackName = trim((string) ($row['TRACK: Title'] ?? ''));
                if ($trackNumber === null || $trackName === '') {
                    $summary['tracks_skipped']++;

                    continue;
                }

                $audioFileName = trim((string) ($row['TRACK: Audio Filename'] ?? ''));
                $audioPath = $audioFileName !== '' ? ($audioFiles[strtolower($audioFileName)] ?? null) : null;
                $fileSize = $audioPath !== null && File::exists($audioPath) ? File::size($audioPath) : null;

                $albumTrack = AlbumTrack::query()->updateOrCreate(
                    [
                        'album_id' => $album->id,
                        'track_number' => $trackNumber,
                    ],
                    [
                        'name' => $trackName,
                        'file_name' => $audioFileName !== '' ? $audioFileName : ($audioPath !== null ? basename($audioPath) : $trackName.'.mp3'),
                        'file_size' => $fileSize,
                        'bucket' => null,
                        'key' => $audioPath !== null ? $this->toRelativeImportPath($audioPath, $basePath) : null,
                        'download_token' => null,
                        'local_file_path' => $audioPath !== null ? $this->toRelativeImportPath($audioPath, $basePath) : null,
                        'downloaded_at' => null,
                        'item_type' => 'track',
                    ],
                );

                $lengthSeconds = $this->toIntOrNull($row['TRACK: Duration'] ?? null);
                $releaseDate = $this->toDateOrNull($row['ALBUM: Release Date'] ?? null);

                $track = Track::query()->updateOrCreate(
                    [
                        'album_id' => $album->id,
                        'track_number' => $trackNumber,
                    ],
                    [
                        'album_track_id' => $albumTrack->id,
                        'name' => $trackName,
                        'display_title' => $this->toTrimmedOrNull($row['TRACK: Display Title'] ?? null),
                        'version' => $this->toTrimmedOrNull($row['TRACK: Version'] ?? null),
                        'time' => $lengthSeconds !== null ? $this->secondsToClock($lengthSeconds) : null,
                        'lenght_seconds' => $lengthSeconds,
                        'genre' => $this->toTrimmedOrNull($row['TRACK: Genre'] ?? null),
                        'tempo' => $this->toTrimmedOrNull($row['TRACK: Tempo'] ?? null),
                        'bpm' => $this->toIntOrNull($row['TRACK: BPM'] ?? null),
                        'composer' => $this->toTrimmedOrNull($row['TRACK: Composer(s)'] ?? null),
                        'publisher' => $this->toTrimmedOrNull($row['TRACK: Publisher(s)'] ?? null),
                        'instrumentation' => $this->toTrimmedOrNull($row['TRACK: Instrumentation'] ?? null),
                        'cd_code' => $this->toTrimmedOrNull($row['CODE: ISRC'] ?? null),
                        'comment' => $this->toTrimmedOrNull($row['TRACK: Description'] ?? null),
                        'cover' => $album->cover,
                        'release_date' => $releaseDate,
                        'status' => 'active',
                        'keywords' => $this->toTrimmedOrNull($row['TRACK: Keywords'] ?? null),
                        'stem_count' => null,
                        'is_alternative' => 0,
                        'api_status' => null,
                        'source_metadata' => $this->extractTrackSourceMetadata($row),
                    ],
                );
                $syncTrackGenres->sync($track);
                $syncTrackKeywords->sync($track);

                $summary['tracks_processed']++;
                $processedTracksForAlbum++;
            }

            if ($processedTracksForAlbum > 0) {
                $summary['albums_processed']++;
            } else {
                $summary['albums_skipped']++;
                $summary['warnings'][] = 'Skipped '.basename($albumDirectory).': no valid tracks found.';
            }
        }

        return $summary;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function upsertLibrary(string $libraryName, array $row): Library
    {
        $normalizedName = trim($libraryName) !== '' ? trim($libraryName) : 'Unknown Library';
        $libraryIdentifier = 'LIB-'.strtoupper(substr(sha1($normalizedName), 0, 12));

        return Library::query()->updateOrCreate(
            [
                'library_id' => $libraryIdentifier,
            ],
            [
                'featured' => false,
                'detail' => null,
                'name' => $normalizedName,
                'location' => null,
                'website' => null,
                'library_logo_url' => null,
                'status' => true,
                'last_updated' => now(),
                'codes' => null,
                'type' => 'music',
                'source_metadata' => $this->extractLibrarySourceMetadata($row),
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function upsertAlbum(Library $library, array $row, string $metadataDirectory): Album
    {
        $albumCode = $this->toTrimmedOrNull($row['ALBUM: Code'] ?? null) ?? strtoupper(Str::random(10));
        $albumName = $this->toTrimmedOrNull($row['ALBUM: Title'] ?? null) ?? $albumCode;
        $albumDisplayTitle = $this->toTrimmedOrNull($row['ALBUM: Display Title'] ?? null) ?? $albumName;
        $releaseDate = $this->toDateOrNull($row['ALBUM: Release Date'] ?? null) ?? now()->toDateString();
        $album = Album::query()
            ->where('library_id', $library->id)
            ->where('code', $albumCode)
            ->first();

        if ($album === null) {
            $album = Album::query()
                ->where('code', $albumCode)
                ->whereHas('library', static fn ($query) => $query->where('name', 'Unknown Library'))
                ->first();
        }

        if ($album === null) {
            $album = new Album();
        }

        $album->fill([
            'library_id' => $library->id,
            'displaytitle' => $albumDisplayTitle,
            'featured' => 0,
            'releasedate' => $releaseDate,
            'code' => $albumCode,
            'detail' => $this->toTrimmedOrNull($row['ALBUM: Description'] ?? null),
            'cover' => $this->resolveAlbumCoverPath($metadataDirectory, $row['ALBUM: Artwork Filename'] ?? null),
            'name' => $albumName,
            'status' => true,
            'libraryfeatured' => 0,
            'source_metadata' => $this->extractAlbumSourceMetadata($row),
        ]);
        $album->save();

        return $album;
    }

    private function findMetadataDirectory(string $albumDirectory): ?string
    {
        $candidate = collect(File::directories($albumDirectory))
            ->first(static fn (string $directory): bool => str_contains(strtolower(basename($directory)), '_hm_standard'));

        if (is_string($candidate)) {
            return $candidate;
        }

        $fallback = collect(File::directories($albumDirectory))
            ->first(function (string $directory): bool {
                return collect(File::files($directory))
                    ->contains(static fn (\SplFileInfo $file): bool => str_ends_with(strtolower($file->getFilename()), '_metadata.csv'));
            });

        return is_string($fallback) ? $fallback : null;
    }

    private function findMetadataCsvPath(string $metadataDirectory): ?string
    {
        $file = collect(File::files($metadataDirectory))
            ->first(static fn (\SplFileInfo $file): bool => str_ends_with(strtolower($file->getFilename()), '_metadata.csv'));

        return $file?->getPathname();
    }

    /**
     * @return array<int, array<string, string|null>>
     */
    private function readMetadataRows(string $metadataCsvPath): array
    {
        $handle = fopen($metadataCsvPath, 'rb');
        if ($handle === false) {
            return [];
        }

        $header = fgetcsv($handle, 0, ',', '"', '\\');
        if (! is_array($header)) {
            fclose($handle);

            return [];
        }

        $normalizedHeader = array_map(
            fn (string $column): string => $this->normalizeHeaderColumn($column),
            $header,
        );

        $rows = [];

        while (($record = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
            if (! is_array($record)) {
                continue;
            }

            $paddedRecord = array_pad($record, count($normalizedHeader), null);
            $combined = array_combine($normalizedHeader, $paddedRecord);

            if ($combined === false) {
                continue;
            }

            /** @var array<string, string|null> $combined */
            $rows[] = $combined;
        }

        fclose($handle);

        return $rows;
    }

    /**
     * @return array<string, string>
     */
    private function indexAudioFiles(string $albumDirectory): array
    {
        $indexedFiles = [];

        foreach (File::allFiles($albumDirectory) as $file) {
            if (strtolower($file->getExtension()) !== 'mp3') {
                continue;
            }

            $indexedFiles[strtolower($file->getFilename())] = $file->getPathname();
        }

        return $indexedFiles;
    }

    private function resolveAlbumCoverPath(string $metadataDirectory, mixed $metadataArtworkFilename): ?string
    {
        $metadataArtwork = $this->toTrimmedOrNull($metadataArtworkFilename);
        if ($metadataArtwork !== null) {
            $exactMatch = collect(File::files($metadataDirectory))
                ->first(static fn (\SplFileInfo $file): bool => strtolower($file->getFilename()) === strtolower($metadataArtwork));

            if ($exactMatch !== null) {
                return $this->toBrowserPath($exactMatch->getPathname());
            }
        }

        $fallback = collect(File::files($metadataDirectory))
            ->first(function (\SplFileInfo $file): bool {
                $filename = strtolower($file->getFilename());
                $extension = strtolower($file->getExtension());

                return str_contains($filename, 'albumart') && in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true);
            });

        return $fallback !== null ? $this->toBrowserPath($fallback->getPathname()) : null;
    }

    private function toBrowserPath(string $absolutePath): string
    {
        $publicRoot = rtrim(public_path(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

        if (str_starts_with($absolutePath, $publicRoot)) {
            return Str::after($absolutePath, $publicRoot);
        }

        return $this->toProjectRelativePath($absolutePath);
    }

    private function toProjectRelativePath(string $absolutePath): string
    {
        $projectRoot = rtrim(base_path(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

        if (str_starts_with($absolutePath, $projectRoot)) {
            return Str::after($absolutePath, $projectRoot);
        }

        return $absolutePath;
    }

    private function toRelativeImportPath(string $absolutePath, string $basePath): string
    {
        $importRoot = rtrim($basePath, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

        if (str_starts_with($absolutePath, $importRoot)) {
            return Str::after($absolutePath, $importRoot);
        }

        return $absolutePath;
    }

    private function toTrimmedOrNull(mixed $value): ?string
    {
        $trimmed = trim((string) $value);

        return $trimmed !== '' ? $trimmed : null;
    }

    private function toIntOrNull(mixed $value): ?int
    {
        $trimmed = trim((string) $value);
        if ($trimmed === '') {
            return null;
        }

        if (! preg_match('/^\d+$/', $trimmed)) {
            return null;
        }

        return (int) $trimmed;
    }

    private function toDateOrNull(mixed $value): ?string
    {
        $trimmed = trim((string) $value);
        if ($trimmed === '') {
            return null;
        }

        try {
            return Carbon::parse($trimmed)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function secondsToClock(int $seconds): string
    {
        $minutes = intdiv($seconds, 60);
        $remainingSeconds = $seconds % 60;

        return sprintf('%02d:%02d', $minutes, $remainingSeconds);
    }

    private function normalizeHeaderColumn(string $column): string
    {
        $normalized = preg_replace('/^\x{FEFF}/u', '', $column) ?? $column;
        $normalized = preg_replace('/^\xEF\xBB\xBF/', '', $normalized) ?? $normalized;
        $normalized = trim($normalized);

        if (str_starts_with($normalized, '"') && str_ends_with($normalized, '"')) {
            $normalized = substr($normalized, 1, -1);
        }

        return trim($normalized);
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, string>|null
     */
    private function extractLibrarySourceMetadata(array $row): ?array
    {
        return $this->extractSourceMetadata(
            $row,
            ['LIBRARY:'],
            ['LIBRARY: Name'],
        );
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, string>|null
     */
    private function extractAlbumSourceMetadata(array $row): ?array
    {
        return $this->extractSourceMetadata(
            $row,
            ['ALBUM:'],
            [
                'ALBUM: Code',
                'ALBUM: Title',
                'ALBUM: Display Title',
                'ALBUM: Description',
                'ALBUM: Release Date',
                'ALBUM: Artwork Filename',
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, string>|null
     */
    private function extractTrackSourceMetadata(array $row): ?array
    {
        return $this->extractSourceMetadata(
            $row,
            ['TRACK:', 'ARTIST:', 'WRITER:', 'PUBLISHER:', 'ATTRIBUTE:', 'CODE:'],
            [
                'TRACK: Title',
                'TRACK: Display Title',
                'TRACK: Description',
                'TRACK: Number',
                'TRACK: Version',
                'TRACK: Duration',
                'TRACK: BPM',
                'TRACK: Tempo',
                'TRACK: Genre',
                'TRACK: Instrumentation',
                'TRACK: Keywords',
                'TRACK: Composer(s)',
                'TRACK: Publisher(s)',
                'TRACK: Audio Filename',
                'CODE: ISRC',
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<int, string>  $prefixes
     * @param  array<int, string>  $excludedKeys
     * @return array<string, string>|null
     */
    private function extractSourceMetadata(array $row, array $prefixes, array $excludedKeys): ?array
    {
        $excludedLookup = array_flip($excludedKeys);
        $metadata = [];

        foreach ($row as $key => $value) {
            if (! is_string($key)) {
                continue;
            }

            if (! $this->startsWithAnyPrefix($key, $prefixes)) {
                continue;
            }

            if (isset($excludedLookup[$key])) {
                continue;
            }

            $normalizedValue = $this->toTrimmedOrNull($value);
            if ($normalizedValue === null) {
                continue;
            }

            $metadata[$key] = $normalizedValue;
        }

        return $metadata !== [] ? $metadata : null;
    }

    /**
     * @param  array<int, string>  $prefixes
     */
    private function startsWithAnyPrefix(string $value, array $prefixes): bool
    {
        foreach ($prefixes as $prefix) {
            if (str_starts_with($value, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
