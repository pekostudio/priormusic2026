<?php

namespace App\Support;

use App\Audio\WaveformGenerator;
use App\Models\AlbumTrack;
use App\Models\Track;
use App\Models\User;
use Illuminate\Support\Str;

class AlbumTrackPayload
{
    public function __construct(private readonly WaveformGenerator $waveformGenerator) {}

    /**
     * @return array<string, mixed>
     */
    public function make(AlbumTrack $albumTrack, ?User $user = null): array
    {
        $track = $albumTrack->tracks->first();

        return [
            'id' => $albumTrack->id,
            'title' => $track?->display_title ?: $track?->name ?: $albumTrack->name,
            'name' => $albumTrack->name,
            'artist' => $this->artistName($track),
            'version' => $track?->version,
            'genre' => $track?->genre,
            'styles' => $this->styles($track),
            'time' => $track?->time,
            'duration_seconds' => $this->durationSeconds($track),
            'bpm' => $this->bpm($track),
            'keywords' => $this->keywords($track?->keywords),
            'cover_url' => AlbumCoverThumbnail::url($track?->cover ?: $albumTrack->album?->cover),
            'album' => $albumTrack->album !== null ? [
                'id' => $albumTrack->album->id,
                'title' => $albumTrack->album->displaytitle ?: $albumTrack->album->name,
                'code' => $albumTrack->album->code,
                'library' => $albumTrack->album->library?->name,
            ] : null,
            'audio_url' => $this->waveformGenerator->audioUrl($albumTrack),
            'download_url' => route('tracks.download', $albumTrack),
            'peaks_url' => route('tracks.peaks', $albumTrack),
            'play_url' => route('tracks.plays.store', $albumTrack),
            'favorite_url' => route('tracks.favorite.store', $albumTrack),
            'unfavorite_url' => route('tracks.favorite.destroy', $albumTrack),
            'is_favorite' => $this->isFavorite($albumTrack, $user),
            'playlist_url' => route('tracks.playlists.store', $albumTrack),
            'playlist_ids' => $this->playlistIds($albumTrack, $user),
        ];
    }

    /**
     * @return list<array{id:int,name:string}>
     */
    private function styles(?Track $track): array
    {
        if ($track === null || ! $track->relationLoaded('genreTags')) {
            return [];
        }

        return $track->genreTags
            ->map(fn ($genre): array => [
                'id' => $genre->id,
                'name' => $genre->name,
            ])
            ->values()
            ->all();
    }

    private function isFavorite(AlbumTrack $albumTrack, ?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        if ($albumTrack->relationLoaded('favoredByUsers')) {
            return $albumTrack->favoredByUsers->contains('id', $user->id);
        }

        return $user->favoriteAlbumTracks()
            ->whereKey($albumTrack->id)
            ->exists();
    }

    /**
     * @return list<int>
     */
    private function playlistIds(AlbumTrack $albumTrack, ?User $user): array
    {
        if ($user === null) {
            return [];
        }

        if ($albumTrack->relationLoaded('playlists')) {
            return $albumTrack->playlists
                ->pluck('id')
                ->values()
                ->all();
        }

        return $user->playlists()
            ->whereHas('albumTracks', fn ($query) => $query->whereKey($albumTrack->id))
            ->pluck('playlists.id')
            ->values()
            ->all();
    }

    private function artistName(?Track $track): ?string
    {
        if ($track === null) {
            return null;
        }

        $metadataArtist = $track->source_metadata['TRACK: Artist'] ?? null;

        return is_string($metadataArtist) && trim($metadataArtist) !== ''
            ? $metadataArtist
            : $track->composer;
    }

    private function durationSeconds(?Track $track): ?int
    {
        if ($track?->lenght_seconds !== null) {
            return $track->lenght_seconds;
        }

        if ($track?->time === null) {
            return null;
        }

        $parts = array_map('intval', explode(':', $track->time));

        return match (count($parts)) {
            2 => ($parts[0] * 60) + $parts[1],
            3 => ($parts[0] * 3600) + ($parts[1] * 60) + $parts[2],
            default => null,
        };
    }

    private function bpm(?Track $track): ?int
    {
        return $track !== null && $track->bpm > 0 ? $track->bpm : null;
    }

    /**
     * @return list<string>
     */
    private function keywords(?string $keywords): array
    {
        if ($keywords === null || trim($keywords) === '') {
            return [];
        }

        return Str::of($keywords)
            ->replace(['|', ';'], ',')
            ->explode(',')
            ->map(fn (string $part): string => trim($part))
            ->filter(fn (string $part): bool => $part !== '')
            ->values()
            ->all();
    }
}
