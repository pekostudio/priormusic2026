import { Head, router } from '@inertiajs/react';
import { Download, Pause, Play, Trash2, X } from 'lucide-react';
import { useState } from 'react';
import { FavoriteTrackButton } from '@/components/favorite-track-button';
import { TrackWaveformPreview } from '@/components/track-waveform-preview';
import { Button } from '@/components/ui/button';
import { useAudioPlayer } from '@/hooks/use-audio-player';
import { index as playlistsIndex } from '@/routes/playlists';
import type { AlbumTrackPayload, BreadcrumbItem } from '@/types';

type PlaylistTrack = AlbumTrackPayload & {
    remove_from_playlist_url: string;
};

type PlaylistShowProps = {
    playlist: {
        id: number;
        name: string;
        tracks_count: number;
        delete_url: string;
        tracks: PlaylistTrack[];
    };
};

export default function PlaylistsShow({ playlist }: PlaylistShowProps) {
    const { isCurrentTrack, isPlaying, pauseTrack, playTrack } =
        useAudioPlayer();
    const [hiddenTrackIds, setHiddenTrackIds] = useState<Set<number>>(
        () => new Set(),
    );

    const visibleTracks = playlist.tracks.filter(
        (track) => !hiddenTrackIds.has(track.id),
    );

    const removeTrack = async (track: PlaylistTrack) => {
        const csrfToken = document
            .querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
            ?.getAttribute('content');

        const response = await fetch(track.remove_from_playlist_url, {
            method: 'DELETE',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
            },
        });

        if (response.ok) {
            setHiddenTrackIds((current) => new Set([...current, track.id]));
        }
    };

    return (
        <>
            <Head title={playlist.name} />
            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                    <div>
                        <h1 className="text-xl font-semibold">
                            {playlist.name}
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            {visibleTracks.length} tracks
                        </p>
                    </div>
                    <Button
                        type="button"
                        variant="outline"
                        onClick={() => {
                            if (
                                confirm(
                                    'Delete this playlist? Tracks will only be removed from this playlist.',
                                )
                            ) {
                                router.delete(playlist.delete_url);
                            }
                        }}
                    >
                        <Trash2 className="size-4" />
                        Delete
                    </Button>
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
                                        <p className="truncate font-medium">
                                            {track.title}
                                        </p>
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
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="icon"
                                            aria-label={`Remove ${track.title} from playlist`}
                                            onClick={() =>
                                                void removeTrack(track)
                                            }
                                        >
                                            <X className="size-4" />
                                        </Button>
                                    </div>
                                </div>
                            );
                        })}

                        {visibleTracks.length === 0 && (
                            <div className="p-8 text-center text-sm text-muted-foreground">
                                No tracks in this playlist yet.
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </>
    );
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Playlists',
        href: playlistsIndex(),
    },
    {
        title: 'Playlist',
        href: playlistsIndex(),
    },
];

PlaylistsShow.layout = {
    breadcrumbs,
};
