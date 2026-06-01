import { Head } from '@inertiajs/react';
import { Download, Pause, Play } from 'lucide-react';
import { AddToPlaylistButton } from '@/components/add-to-playlist-button';
import { FavoriteTrackButton } from '@/components/favorite-track-button';
import { TrackWaveformPreview } from '@/components/track-waveform-preview';
import { Button } from '@/components/ui/button';
import { useAudioPlayer } from '@/hooks/use-audio-player';
import { index as albumsIndex } from '@/routes/albums';
import type { BreadcrumbItem, PlaylistSummary } from '@/types';

type AlbumTrack = {
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

type AlbumShowProps = {
    album: {
        id: number;
        title: string;
        name: string;
        code: string;
        detail: string | null;
        releasedate: string | null;
        cover_url: string | null;
        library: string | null;
        tracks: AlbumTrack[];
    };
    playlists: PlaylistSummary[];
};

export default function AlbumsShow({ album, playlists }: AlbumShowProps) {
    const { isCurrentTrack, isPlaying, pauseTrack, playTrack } =
        useAudioPlayer();

    return (
        <>
            <Head title={album.title} />
            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div className="grid gap-6 xl:grid-cols-[280px_minmax(0,1fr)]">
                    <aside className="min-w-0">
                        <div className="aspect-square overflow-hidden rounded-lg border border-border bg-muted">
                            {album.cover_url && (
                                <img
                                    src={album.cover_url}
                                    alt=""
                                    className="size-full object-cover"
                                />
                            )}
                        </div>
                        <div className="mt-4">
                            <h1 className="text-2xl font-semibold">
                                {album.title}
                            </h1>
                            <div className="mt-2 flex flex-wrap gap-2 text-sm text-muted-foreground">
                                <span>{album.code}</span>
                                {album.library && <span>{album.library}</span>}
                                {album.releasedate && (
                                    <span>{album.releasedate}</span>
                                )}
                            </div>
                            {album.detail && (
                                <p className="mt-4 text-sm leading-6 text-muted-foreground">
                                    {album.detail}
                                </p>
                            )}
                        </div>
                    </aside>

                    <main className="flex min-w-0 flex-col gap-4">
                        <div className="overflow-hidden rounded-lg border border-border">
                            <div className="flex items-center justify-between border-b border-border px-4 py-3">
                                <h2 className="font-semibold">Tracks</h2>
                                <span className="text-sm text-muted-foreground">
                                    {album.tracks.length} tracks
                                </span>
                            </div>

                            <div className="divide-y divide-border">
                                {album.tracks.map((track) => {
                                    const canPlay = Boolean(track.audio_url);
                                    const isTrackPlaying =
                                        isCurrentTrack(track.id) && isPlaying;

                                    return (
                                        <div
                                            key={track.id}
                                            className="grid gap-3 px-4 py-2 md:grid-cols-[56px_1fr_auto] md:items-center"
                                        >
                                            <div className="size-12 overflow-hidden rounded-md bg-muted">
                                                {track.cover_url && (
                                                    <img
                                                        src={track.cover_url}
                                                        alt=""
                                                        className="size-full object-cover"
                                                        loading="lazy"
                                                    />
                                                )}
                                            </div>

                                            <div className="flex min-w-0 flex-row items-center justify-between gap-2">
                                                <div className="flex flex-col gap-0">
                                                    <p className="truncate font-medium">
                                                        {track.title}
                                                    </p>
                                                    <p className="w-fit truncate rounded bg-secondary px-2 py-0.5 text-xs text-secondary-foreground">
                                                        {track.version ??
                                                            track.artist ??
                                                            album.title}
                                                    </p>
                                                    {track.genre && (
                                                        <span className="mt-1 text-xs">
                                                            {track.genre}
                                                        </span>
                                                    )}
                                                </div>
                                                <div className="flex flex-row gap-4">
                                                    <div className="w-200">
                                                        <TrackWaveformPreview
                                                            peaksUrl={
                                                                track.peaks_url
                                                            }
                                                            track={
                                                                track.audio_url
                                                                    ? {
                                                                          id: track.id,
                                                                          title: track.title,
                                                                          artist:
                                                                              track.artist ??
                                                                              album.title,
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
                                                    <div className="flex flex-col justify-center gap-0 text-xs xl:w-24">
                                                        {track.bpm && (
                                                            <span>
                                                                {track.bpm} BPM
                                                            </span>
                                                        )}
                                                        {track.time && (
                                                            <span>
                                                                {track.time}
                                                            </span>
                                                        )}
                                                    </div>
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
                                                                album.title,
                                                            audioUrl:
                                                                track.audio_url,
                                                            peaksUrl:
                                                                track.peaks_url,
                                                            playUrl:
                                                                track.play_url,
                                                            coverUrl:
                                                                track.cover_url,
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
                                                    isFavorite={
                                                        track.is_favorite
                                                    }
                                                    favoriteUrl={
                                                        track.favorite_url
                                                    }
                                                    unfavoriteUrl={
                                                        track.unfavorite_url
                                                    }
                                                    label={track.title}
                                                />
                                                <AddToPlaylistButton
                                                    label={track.title}
                                                    playlistUrl={
                                                        track.playlist_url
                                                    }
                                                    playlistIds={
                                                        track.playlist_ids
                                                    }
                                                    playlists={playlists}
                                                />
                                                <Button
                                                    asChild
                                                    variant="outline"
                                                    size="icon"
                                                    aria-label={`Download ${track.title}`}
                                                >
                                                    <a
                                                        href={
                                                            track.download_url
                                                        }
                                                    >
                                                        <Download className="size-4" />
                                                    </a>
                                                </Button>
                                            </div>
                                        </div>
                                    );
                                })}

                                {album.tracks.length === 0 && (
                                    <div className="p-8 text-center text-sm text-muted-foreground">
                                        No tracks have been added to this album.
                                    </div>
                                )}
                            </div>
                        </div>
                    </main>
                </div>
            </div>
        </>
    );
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Albums',
        href: albumsIndex(),
    },
    {
        title: 'Album',
        href: albumsIndex(),
    },
];

AlbumsShow.layout = {
    breadcrumbs,
};
