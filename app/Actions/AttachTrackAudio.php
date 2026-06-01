<?php

namespace App\Actions;

use App\Models\Album;
use App\Models\AlbumTrack;
use App\Models\Track;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AttachTrackAudio
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function handle(array $data, ?Track $track = null): array
    {
        $audioPath = $this->uploadedAudioPath($data['audio_upload'] ?? null);
        unset($data['audio_upload']);

        if ($audioPath === null) {
            return $data;
        }

        $albumTrack = $this->storeAlbumTrack($data, $audioPath, $track);
        $data['album_track_id'] = $albumTrack->id;

        return $data;
    }

    public static function folderNameForAlbumId(mixed $albumId): string
    {
        $album = filled($albumId)
            ? Album::query()->find($albumId)
            : null;

        return self::safePathSegment($album?->name ?: $album?->code ?: 'Unassigned');
    }

    public static function fileNameForTrack(string $trackName, UploadedFile $file): string
    {
        $name = trim($trackName) !== ''
            ? $trackName
            : pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        return self::safePathSegment($name).'.mp3';
    }

    public static function audioUploadStateForTrack(?Track $track): ?string
    {
        $path = trim((string) ($track?->albumTrack?->local_file_path ?: $track?->albumTrack?->key));

        if ($path === '') {
            return null;
        }

        $publicAudioRoot = rtrim(public_path('audio'), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        $publicRoot = rtrim(public_path(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

        if (str_starts_with($path, $publicAudioRoot)) {
            return ltrim(Str::after($path, $publicAudioRoot), '/');
        }

        if (str_starts_with($path, $publicRoot)) {
            $path = Str::after($path, $publicRoot);
        }

        return Str::startsWith($path, 'audio/')
            ? Str::after($path, 'audio/')
            : ltrim($path, '/');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function storeAlbumTrack(array $data, string $audioPath, ?Track $track): AlbumTrack
    {
        $albumTrack = $track?->albumTrack;

        if ($albumTrack === null && filled($data['album_track_id'] ?? null)) {
            $albumTrack = AlbumTrack::query()->find($data['album_track_id']);
        }

        if ($albumTrack === null) {
            $albumTrack = AlbumTrack::query()->firstOrNew([
                'album_id' => $data['album_id'],
                'track_number' => $data['track_number'],
            ]);
        }

        $fullAudioPath = public_path('audio/'.$audioPath);

        $albumTrack->fill([
            'album_id' => $data['album_id'],
            'track_number' => $data['track_number'],
            'name' => $data['name'],
            'file_name' => basename($audioPath),
            'file_size' => File::exists($fullAudioPath) ? File::size($fullAudioPath) : null,
            'bucket' => null,
            'key' => $audioPath,
            'download_token' => null,
            'local_file_path' => $audioPath,
            'downloaded_at' => null,
            'item_type' => 'track',
        ])->save();

        return $albumTrack;
    }

    private function uploadedAudioPath(mixed $state): ?string
    {
        if (is_array($state)) {
            $state = collect($state)->filter()->first();
        }

        if (! is_string($state) || trim($state) === '') {
            return null;
        }

        return ltrim($state, '/');
    }

    private static function safePathSegment(string $value): string
    {
        $segment = Str::of($value)
            ->ascii()
            ->replaceMatches('/[^A-Za-z0-9]+/', '_')
            ->trim('_')
            ->toString();

        return $segment !== '' ? $segment : (string) Str::ulid();
    }
}
