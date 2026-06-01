import { Head, Link } from '@inertiajs/react';
import { Download, Heart, Pause, Play } from 'lucide-react';
import { useState } from 'react';
import { AddToPlaylistButton } from '@/components/add-to-playlist-button';
import { FavoriteTrackButton } from '@/components/favorite-track-button';
import { TrackWaveformPreview } from '@/components/track-waveform-preview';
import { Button } from '@/components/ui/button';
import { useAudioPlayer } from '@/hooks/use-audio-player';
import { favorites as favoritesRoute } from '@/routes';
import type { BreadcrumbItem, PlaylistSummary } from '@/types';

type FavoriteTrack = {
    id: number;
    title: string;
    name: string;
    artist: string | null;
    version: string | null;
    genre: string | null;
    time: string | null;
    duration_seconds: number | null;
    bpm: number | null;
    keywords: string[];
    cover_url: string | null;
    album: {
        id: number;
        title: string;
        code: string;
        library: string | null;
    } | null;
    audio_url: string | null;
    download_url: string;
    peaks_url: string;
    play_url: string;
    favorite_url: string;
    unfavorite_url: string;
    is_favorite: boolean;
    playlist_url: string;
    playlist_ids: number[];
};

type PaginatedFavoriteTracks = {
    data: FavoriteTrack[];
    prev_page_url: string | null;
    next_page_url: string | null;
    from: number | null;
    to: number | null;
    total: number;
};

type FavoritesIndexProps = {
    favoriteTracks: PaginatedFavoriteTracks;
    playlists: PlaylistSummary[];
};

export default function FavoritesIndex({
    favoriteTracks,
    playlists,
}: FavoritesIndexProps) {
    const { isCurrentTrack, isPlaying, pauseTrack, playTrack } =
        useAudioPlayer();
    const [hiddenTrackIds, setHiddenTrackIds] = useState<Set<number>>(
        () => new Set(),
    );

    const visibleTracks = favoriteTracks.data.filter(
        (track) => !hiddenTrackIds.has(track.id),
    );

    return (
        <>
            <Head title="Favorites" />
            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div>
                    <div className="flex items-center gap-2">
                        <Heart className="size-5" />
                        <h1 className="text-xl font-semibold">Favorites</h1>
                    </div>
                    <p className="mt-1 text-sm text-muted-foreground">
                        {favoriteTracks.total} saved tracks
                    </p>
                </div>
                <div className="overflow-hidden rounded-lg border border-border">
                    <div className="divide-y divide-border">
                        {visibleTracks.map((track) => {
                            const canPlay = Boolean(track.audio_url);
                            const isTrackPlaying =
                                isCurrentTrack(track.id) && isPlaying;

                            return (
                                <div
                                    key={track.id}
                                    className="grid gap-3 p-4 md:grid-cols-[56px_1fr_auto] md:items-center"
                                >
                                    <div className="size-14 overflow-hidden rounded-md bg-muted">
                                        {track.cover_url && (
                                            <img
                                                src={track.cover_url}
                                                alt=""
                                                className="size-full object-cover"
                                                loading="lazy"
                                            />
                                        )}
                                    </div>

                                    <div className="min-w-0">
                                        <div className="flex flex-wrap items-center gap-2">
                                            <p className="truncate font-medium">
                                                {track.title}
                                            </p>
                                            {track.version && (
                                                <span className="rounded bg-secondary px-2 py-0.5 text-xs text-secondary-foreground">
                                                    {track.version}
                                                </span>
                                            )}
                                        </div>
                                        <p className="mt-1 truncate text-sm text-muted-foreground">
                                            {track.artist ??
                                                track.album?.title ??
                                                'No album'}
                                        </p>
                                        <div className="mt-2 flex flex-wrap gap-3 text-xs text-muted-foreground">
                                            {track.album?.title && (
                                                <span>{track.album.title}</span>
                                            )}
                                            {track.time && (
                                                <span>{track.time}</span>
                                            )}
                                            {track.bpm && (
                                                <span>{track.bpm} BPM</span>
                                            )}
                                        </div>
                                        <div className="mt-3">
                                            <TrackWaveformPreview
                                                peaksUrl={track.peaks_url}
                                                track={
                                                    track.audio_url
                                                        ? {
                                                              id: track.id,
                                                              title: track.title,
                                                              artist:
                                                                  track.artist ??
                                                                  track.album
                                                                      ?.title ??
                                                                  null,
                                                              audioUrl:
                                                                  track.audio_url,
                                                              peaksUrl:
                                                                  track.peaks_url,
                                                              playUrl:
                                                                  track.play_url,
                                                              coverUrl:
                                                                  track.cover_url,
                                                          }
                                                        : null
                                                }
                                            />
                                        </div>
                                    </div>

                                    <div className="flex items-center gap-2">
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="icon"
                                            disabled={!canPlay}
                                            aria-label={
                                                isTrackPlaying
                                                    ? `Pause ${track.title}`
                                                    : `Play ${track.title}`
                                            }
                                            onClick={() => {
                                                if (isTrackPlaying) {
                                                    pauseTrack();

                                                    return;
                                                }

                                                if (!track.audio_url) {
                                                    return;
                                                }

                                                playTrack({
                                                    id: track.id,
                                                    title: track.title,
                                                    artist:
                                                        track.artist ??
                                                        track.album?.title ??
                                                        null,
                                                    audioUrl: track.audio_url,
                                                    peaksUrl: track.peaks_url,
                                                    playUrl: track.play_url,
                                                    coverUrl: track.cover_url,
                                                });
                                            }}
                                        >
                                            {isTrackPlaying ? (
                                                <Pause className="size-4" />
                                            ) : (
                                                <Play className="size-4" />
                                            )}
                                        </Button>
                                        <FavoriteTrackButton
                                            isFavorite={track.is_favorite}
                                            favoriteUrl={track.favorite_url}
                                            unfavoriteUrl={track.unfavorite_url}
                                            label={track.title}
                                            onChanged={(isFavorite) => {
                                                if (!isFavorite) {
                                                    setHiddenTrackIds(
                                                        (current) =>
                                                            new Set([
                                                                ...current,
                                                                track.id,
                                                            ]),
                                                    );
                                                }
                                            }}
                                        />
                                        <AddToPlaylistButton
                                            label={track.title}
                                            playlistUrl={track.playlist_url}
                                            playlistIds={track.playlist_ids}
                                            playlists={playlists}
                                        />
                                        <Button
                                            asChild
                                            variant="outline"
                                            size="icon"
                                            aria-label={`Download ${track.title}`}
                                        >
                                            <a href={track.download_url}>
                                                <Download className="size-4" />
                                            </a>
                                        </Button>
                                    </div>
                                </div>
                            );
                        })}

                        {visibleTracks.length === 0 && (
                            <div className="p-8 text-center text-sm text-muted-foreground">
                                No favorite tracks yet.
                            </div>
                        )}
                    </div>
                </div>

                <div className="flex items-center justify-between">
                    <p className="text-sm text-muted-foreground">
                        {favoriteTracks.from !== null &&
                        favoriteTracks.to !== null
                            ? `Showing ${favoriteTracks.from}-${favoriteTracks.to}`
                            : 'No results'}
                    </p>
                    <div className="flex gap-2">
                        <Button
                            asChild
                            variant="outline"
                            disabled={!favoriteTracks.prev_page_url}
                        >
                            <Link href={favoriteTracks.prev_page_url ?? '#'}>
                                Previous
                            </Link>
                        </Button>
                        <Button
                            asChild
                            variant="outline"
                            disabled={!favoriteTracks.next_page_url}
                        >
                            <Link href={favoriteTracks.next_page_url ?? '#'}>
                                Next
                            </Link>
                        </Button>
                    </div>
                </div>
            </div>
        </>
    );
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Favorites',
        href: favoritesRoute(),
    },
];

FavoritesIndex.layout = {
    breadcrumbs,
};
