import { Head } from '@inertiajs/react';
import { MultiSelect } from '@mantine/core';
import { Download, Pause, Play, Search } from 'lucide-react';
import { useMemo, useState } from 'react';
import { AddToPlaylistButton } from '@/components/add-to-playlist-button';
import { FavoriteTrackButton } from '@/components/favorite-track-button';
import { TrackWaveformPreview } from '@/components/track-waveform-preview';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
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
    styles: {
        id: number;
        name: string;
    }[];
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
        library_id: number;
        library: string | null;
        tracks: AlbumTrack[];
    };
    filters: {
        styles: string[];
        library_ids: string[];
    };
    filterOptions: {
        libraries: {
            value: string;
            label: string;
        }[];
    };
    playlists: PlaylistSummary[];
};

export default function AlbumsShow({
    album,
    filters,
    filterOptions,
    playlists,
}: AlbumShowProps) {
    const { isCurrentTrack, isPlaying, pauseTrack, playTrack } =
        useAudioPlayer();
    const [search, setSearch] = useState('');
    const [styles, setStyles] = useState<string[]>(filters.styles);
    const [libraryIds, setLibraryIds] = useState<string[]>(filters.library_ids);
    const [appliedSearch, setAppliedSearch] = useState('');
    const [appliedStyles, setAppliedStyles] = useState<string[]>(
        filters.styles,
    );
    const [appliedLibraryIds, setAppliedLibraryIds] = useState<string[]>(
        filters.library_ids,
    );

    const styleOptions = useMemo(() => {
        const styles = new Map<string, string>();

        album.tracks.forEach((track) => {
            track.styles.forEach((style) => {
                const key = style.id.toString();

                if (!styles.has(key)) {
                    styles.set(key, style.name);
                }
            });
        });

        return Array.from(styles.entries())
            .sort((firstStyle, secondStyle) =>
                firstStyle[1].localeCompare(secondStyle[1]),
            )
            .map(([id, name]) => ({
                value: id,
                label: name,
            }));
    }, [album.tracks]);

    const visibleTracks = useMemo(() => {
        const normalizedSearch = appliedSearch.trim().toLocaleLowerCase();
        const selectedStyles = new Set(appliedStyles);
        const selectedLibraryIds = new Set(appliedLibraryIds);

        return album.tracks.filter((track) => {
            const matchesSearch =
                normalizedSearch === '' ||
                [
                    track.title,
                    track.name,
                    track.artist,
                    track.version,
                    track.genre,
                    ...track.keywords,
                ]
                    .filter(
                        (value): value is string => typeof value === 'string',
                    )
                    .some((value) =>
                        value.toLocaleLowerCase().includes(normalizedSearch),
                    );

            const matchesStyles =
                selectedStyles.size === 0 ||
                track.styles.some((style) =>
                    selectedStyles.has(style.id.toString()),
                );
            const matchesLibraries =
                selectedLibraryIds.size === 0 ||
                selectedLibraryIds.has(album.library_id.toString());

            return matchesSearch && matchesStyles && matchesLibraries;
        });
    }, [
        album.library_id,
        album.tracks,
        appliedLibraryIds,
        appliedSearch,
        appliedStyles,
    ]);

    const applyFilters = () => {
        setAppliedSearch(search);
        setAppliedStyles(styles);
        setAppliedLibraryIds(libraryIds);
    };

    const clearFilters = () => {
        setSearch('');
        setStyles([]);
        setLibraryIds([]);
        setAppliedSearch('');
        setAppliedStyles([]);
        setAppliedLibraryIds([]);
    };

    return (
        <>
            <Head title={album.title} />
            <div className="flex h-full flex-1 flex-col gap-4 p-4 lg:flex-row">
                <aside className="flex w-full flex-col gap-3 lg:w-2/12">
                    <div className="relative">
                        <Search className="pointer-events-none absolute top-2.5 left-2.5 size-4 text-muted-foreground" />
                        <Input
                            value={search}
                            onChange={(event) => setSearch(event.target.value)}
                            onKeyDown={(event) => {
                                if (event.key === 'Enter') {
                                    applyFilters();
                                }
                            }}
                            className="pl-8"
                            placeholder="Search tracks"
                        />
                    </div>
                    <MultiSelect
                        value={styles}
                        onChange={setStyles}
                        data={styleOptions}
                        placeholder="All styles"
                        clearable
                        searchable
                        nothingFoundMessage="No styles found"
                        size="sm"
                    />
                    <MultiSelect
                        value={libraryIds}
                        onChange={setLibraryIds}
                        data={filterOptions.libraries}
                        placeholder="All libraries"
                        clearable
                        searchable
                        nothingFoundMessage="No libraries found"
                        size="sm"
                    />
                    <div className="grid gap-2 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
                        <Button
                            type="button"
                            className="hover:cursor-pointer"
                            onClick={applyFilters}
                        >
                            Apply
                        </Button>
                        <Button
                            className="hover:cursor-pointer"
                            type="button"
                            variant="outline"
                            onClick={clearFilters}
                        >
                            Clear
                        </Button>
                    </div>
                </aside>

                <main className="flex w-full min-w-0 flex-col gap-4 lg:w-10/12">
                    <div className="flex flex-col gap-4 md:flex-row">
                        <div className="aspect-square max-w-48 min-w-48 overflow-hidden">
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
                            <div className="mt-2 flex flex-wrap gap-2 text-sm">
                                <span>{album.code}</span>
                                {album.library && <span>{album.library}</span>}
                                {album.releasedate && (
                                    <span>{album.releasedate}</span>
                                )}
                            </div>
                            {album.detail && (
                                <p className="mt-4 text-sm">{album.detail}</p>
                            )}
                        </div>
                    </div>

                    <div className="overflow-hidden rounded-lg border border-border">
                        <div className="flex items-center justify-between border-b border-border px-4 py-3">
                            <h2 className="font-semibold">Tracks</h2>
                            <span className="text-sm text-muted-foreground">
                                {visibleTracks.length} of {album.tracks.length}{' '}
                                tracks
                            </span>
                        </div>

                        <div className="divide-y divide-border">
                            {visibleTracks.map((track) => {
                                const canPlay = Boolean(track.audio_url);
                                const isTrackPlaying =
                                    isCurrentTrack(track.id) && isPlaying;

                                const toggleTrackPlayback = () => {
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
                                        artist: track.artist ?? album.title,
                                        audioUrl: track.audio_url,
                                        peaksUrl: track.peaks_url,
                                        playUrl: track.play_url,
                                        coverUrl: track.cover_url,
                                    });
                                };

                                return (
                                    <div
                                        key={track.id}
                                        className="grid gap-2 px-4 py-4 md:grid-cols-[56px_1fr_auto] md:items-center xl:py-2"
                                    >
                                        <div className="hidden size-12 overflow-hidden xl:block">
                                            {track.cover_url && (
                                                <img
                                                    src={track.cover_url}
                                                    alt=""
                                                    className="size-full object-cover hover:cursor-pointer"
                                                    loading="lazy"
                                                    onClick={
                                                        toggleTrackPlayback
                                                    }
                                                />
                                            )}
                                        </div>

                                        <div className="flex min-w-0 flex-col justify-between gap-4 overflow-x-hidden xl:flex-row xl:items-center xl:overflow-x-visible">
                                            <div className="flex flex-col gap-2 xl:max-w-140 xl:min-w-140 xl:gap-1">
                                                <p
                                                    className="truncate text-sm font-medium hover:cursor-pointer xl:text-base"
                                                    onClick={
                                                        toggleTrackPlayback
                                                    }
                                                >
                                                    {track.title}
                                                </p>
                                                <p className="w-fit rounded bg-secondary px-2 py-0.5 text-[9px] text-secondary-foreground xl:text-[10px]">
                                                    {track.version ??
                                                        track.artist ??
                                                        album.title}
                                                </p>
                                                {track.genre && (
                                                    <span className="text-xs">
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
                                                className="hover:cursor-pointer"
                                                type="button"
                                                variant="outline"
                                                size="icon"
                                                disabled={!canPlay}
                                                aria-label={
                                                    isTrackPlaying
                                                        ? `Pause ${track.title}`
                                                        : `Play ${track.title}`
                                                }
                                                onClick={toggleTrackPlayback}
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
                                                unfavoriteUrl={
                                                    track.unfavorite_url
                                                }
                                                label={track.title}
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

                            {album.tracks.length === 0 && (
                                <div className="p-8 text-center text-sm text-muted-foreground">
                                    No tracks have been added to this album.
                                </div>
                            )}

                            {album.tracks.length > 0 &&
                                visibleTracks.length === 0 && (
                                    <div className="p-8 text-center text-sm text-muted-foreground">
                                        No tracks match the current filters.
                                    </div>
                                )}
                        </div>
                    </div>
                </main>
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
