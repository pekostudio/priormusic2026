import { Head, Link, router } from '@inertiajs/react';
import { RangeSlider, Select } from '@mantine/core';
import { Download, ListMusic, Pause, Play, Search } from 'lucide-react';
import { useState } from 'react';
import { AddToPlaylistButton } from '@/components/add-to-playlist-button';
import { FavoriteTrackButton } from '@/components/favorite-track-button';
import { TrackWaveformPreview } from '@/components/track-waveform-preview';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useAudioPlayer } from '@/hooks/use-audio-player';
import { tracks as tracksRoute } from '@/routes';
import type { BreadcrumbItem, PlaylistSummary } from '@/types';

type TrackRow = {
    id: number;
    title: string;
    name: string;
    version: string | null;
    genre: string | null;
    time: string | null;
    bpm: number | null;
    keywords: string | null;
    composer: string | null;
    publisher: string | null;
    cover_url: string | null;
    album: {
        id: number;
        title: string;
        code: string;
        library: string | null;
    } | null;
    album_track: {
        id: number;
        audio_url: string | null;
        download_url: string;
        peaks_url: string;
        play_url: string;
        favorite_url: string;
        unfavorite_url: string;
        is_favorite: boolean;
        playlist_url: string;
        playlist_ids: number[];
    } | null;
};

type PaginatedTracks = {
    data: TrackRow[];
    prev_page_url: string | null;
    next_page_url: string | null;
    from: number | null;
    to: number | null;
    total: number;
};

type TracksIndexProps = {
    tracks: PaginatedTracks;
    filters: {
        search: string;
        genre_id: number | null;
        keyword_id: number | null;
        bpm_min: number | null;
        bpm_max: number | null;
    };
    filterOptions: {
        genres: {
            id: number;
            name: string;
        }[];
        keywords: {
            id: number;
            name: string;
        }[];
    };
    playlists: PlaylistSummary[];
};

const BPM_MIN = 1;
const BPM_MAX = 300;

const normalizeBpmValue = (value: number | null, fallback: number): number => {
    if (value === null) {
        return fallback;
    }

    return Math.min(Math.max(value, BPM_MIN), BPM_MAX);
};

export default function TracksIndex({
    tracks,
    filters,
    filterOptions,
    playlists,
}: TracksIndexProps) {
    const { isCurrentTrack, isPlaying, pauseTrack, playTrack } =
        useAudioPlayer();
    const [search, setSearch] = useState(filters.search);
    const [genreId, setGenreId] = useState(
        filters.genre_id?.toString() ?? null,
    );
    const [keywordId, setKeywordId] = useState(
        filters.keyword_id?.toString() ?? null,
    );
    const [bpmRange, setBpmRange] = useState<[number, number]>([
        normalizeBpmValue(filters.bpm_min, BPM_MIN),
        normalizeBpmValue(filters.bpm_max, BPM_MAX),
    ]);

    const applyFilters = () => {
        router.get(
            tracksRoute.url(),
            {
                search,
                genre_id: genreId,
                keyword_id: keywordId,
                bpm_min: bpmRange[0] === BPM_MIN ? '' : bpmRange[0],
                bpm_max: bpmRange[1] === BPM_MAX ? '' : bpmRange[1],
            },
            { preserveState: true, replace: true },
        );
    };

    const clearFilters = () => {
        setSearch('');
        setGenreId(null);
        setKeywordId(null);
        setBpmRange([BPM_MIN, BPM_MAX]);
        router.get(
            tracksRoute.url(),
            {},
            { preserveState: true, replace: true },
        );
    };

    return (
        <>
            <Head title="Tracks" />

            <div className="w-full p-4">
                <div className="flex items-center gap-2">
                    <ListMusic className="size-5" />
                    <h1 className="text-xl font-semibold">Tracks</h1>
                </div>
                <p className="mt-1 text-sm text-muted-foreground">
                    {tracks.total} tracks in the library
                </p>
            </div>

            <div className="flex h-full flex-1 flex-col gap-4 p-4 lg:flex-row">
                <div className="flex w-full flex-col gap-3 lg:w-2/12">
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
                    <Select
                        value={genreId}
                        onChange={setGenreId}
                        data={filterOptions.genres.map((genre) => ({
                            value: genre.id.toString(),
                            label: genre.name,
                        }))}
                        placeholder="All genres"
                        clearable
                        searchable
                        nothingFoundMessage="No genres found"
                        size="sm"
                    />
                    <Select
                        value={keywordId}
                        onChange={setKeywordId}
                        data={filterOptions.keywords.map((keyword) => ({
                            value: keyword.id.toString(),
                            label: keyword.name,
                        }))}
                        placeholder="All keywords"
                        clearable
                        searchable
                        nothingFoundMessage="No keywords found"
                        size="sm"
                    />
                    <div className="flex flex-col gap-3 rounded-md border border-border px-3 pt-3 pb-8">
                        <div className="flex items-center justify-between gap-3 text-sm">
                            <span className="font-medium">BPM</span>
                            <span className="text-muted-foreground">
                                {bpmRange[0] === BPM_MIN &&
                                bpmRange[1] === BPM_MAX
                                    ? 'Max'
                                    : `${bpmRange[0]}-${bpmRange[1]} BPM`}
                            </span>
                        </div>
                        <RangeSlider
                            value={bpmRange}
                            onChange={setBpmRange}
                            min={BPM_MIN}
                            max={BPM_MAX}
                            step={1}
                            minRange={0}
                            label={(value) => `${value} BPM`}
                            thumbFromLabel="Minimum BPM"
                            thumbToLabel="Maximum BPM"
                            marks={[
                                { value: BPM_MIN, label: `${BPM_MIN}` },
                                { value: 100, label: '100' },
                                { value: 200, label: '200' },
                                { value: BPM_MAX, label: `${BPM_MAX}` },
                            ]}
                        />
                    </div>
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
                </div>

                <div className="flex w-full flex-col gap-4 lg:w-10/12">
                    <div className="overflow-hidden rounded-lg border border-border">
                        <div className="divide-y divide-border">
                            {tracks.data.map((track) => {
                                const canPlay =
                                    track.album_track?.audio_url !== null &&
                                    track.album_track?.audio_url !== undefined;
                                const isTrackPlaying =
                                    track.album_track !== null &&
                                    track.album_track !== undefined &&
                                    isCurrentTrack(track.album_track.id) &&
                                    isPlaying;

                                return (
                                    <div
                                        key={track.id}
                                        className="grid gap-2 px-4 py-2 md:grid-cols-[56px_1fr_auto] md:items-center"
                                    >
                                        <div className="size-12 overflow-hidden rounded-md bg-muted">
                                            {track.cover_url && (
                                                <img
                                                    src={track.cover_url}
                                                    alt=""
                                                    className="size-full object-cover"
                                                />
                                            )}
                                        </div>

                                        <div className="flex min-w-0 flex-row items-center justify-between gap-4">
                                            <div className="flex max-w-90 min-w-90 flex-col gap-0 overflow-x-hidden">
                                                <p className="truncate font-medium">
                                                    {track.title}
                                                </p>
                                                <p className="w-fit truncate rounded bg-secondary px-2 py-0.5 text-xs text-secondary-foreground">
                                                    {track.album?.title ??
                                                        'No album'}
                                                    {track.album?.library
                                                        ? ` · ${track.album.library}`
                                                        : ''}
                                                </p>
                                                <span className="mt-1 text-xs">
                                                    {track.genre}
                                                </span>
                                            </div>
                                            <div className="flex w-full flex-row gap-4">
                                                {track.album_track && (
                                                    <div className="w-full">
                                                        <TrackWaveformPreview
                                                            peaksUrl={
                                                                track
                                                                    .album_track
                                                                    .peaks_url
                                                            }
                                                            track={
                                                                track
                                                                    .album_track
                                                                    .audio_url
                                                                    ? {
                                                                          id: track
                                                                              .album_track
                                                                              .id,
                                                                          title: track.title,
                                                                          artist:
                                                                              track
                                                                                  .album
                                                                                  ?.title ??
                                                                              null,
                                                                          audioUrl:
                                                                              track
                                                                                  .album_track
                                                                                  .audio_url,
                                                                          peaksUrl:
                                                                              track
                                                                                  .album_track
                                                                                  .peaks_url,
                                                                          playUrl:
                                                                              track
                                                                                  .album_track
                                                                                  .play_url,
                                                                          coverUrl:
                                                                              track.cover_url,
                                                                      }
                                                                    : null
                                                            }
                                                        />
                                                    </div>
                                                )}
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
                                                onClick={() => {
                                                    if (!track.album_track) {
                                                        return;
                                                    }

                                                    if (isTrackPlaying) {
                                                        pauseTrack();

                                                        return;
                                                    }

                                                    if (
                                                        !track.album_track
                                                            .audio_url
                                                    ) {
                                                        return;
                                                    }

                                                    playTrack({
                                                        id: track.album_track
                                                            .id,
                                                        title: track.title,
                                                        artist:
                                                            track.album
                                                                ?.title ?? null,
                                                        audioUrl:
                                                            track.album_track
                                                                .audio_url,
                                                        peaksUrl:
                                                            track.album_track
                                                                .peaks_url,
                                                        playUrl:
                                                            track.album_track
                                                                .play_url,
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
                                            {track.album_track && (
                                                <FavoriteTrackButton
                                                    isFavorite={
                                                        track.album_track
                                                            .is_favorite
                                                    }
                                                    favoriteUrl={
                                                        track.album_track
                                                            .favorite_url
                                                    }
                                                    unfavoriteUrl={
                                                        track.album_track
                                                            .unfavorite_url
                                                    }
                                                    label={track.title}
                                                />
                                            )}
                                            {track.album_track && (
                                                <AddToPlaylistButton
                                                    label={track.title}
                                                    playlistUrl={
                                                        track.album_track
                                                            .playlist_url
                                                    }
                                                    playlistIds={
                                                        track.album_track
                                                            .playlist_ids
                                                    }
                                                    playlists={playlists}
                                                />
                                            )}
                                            {track.album_track && (
                                                <Button
                                                    asChild
                                                    variant="outline"
                                                    size="icon"
                                                    aria-label={`Download ${track.title}`}
                                                >
                                                    <a
                                                        href={
                                                            track.album_track
                                                                .download_url
                                                        }
                                                    >
                                                        <Download className="size-4" />
                                                    </a>
                                                </Button>
                                            )}
                                        </div>
                                    </div>
                                );
                            })}

                            {tracks.data.length === 0 && (
                                <div className="p-8 text-center text-sm text-muted-foreground">
                                    No tracks match the current filters.
                                </div>
                            )}
                        </div>
                    </div>
                    <div className="flex items-center justify-between">
                        <p className="text-sm text-muted-foreground">
                            {tracks.from !== null && tracks.to !== null
                                ? `Showing ${tracks.from}-${tracks.to}`
                                : 'No results'}
                        </p>
                        <div className="flex gap-2">
                            <Button
                                asChild
                                variant="outline"
                                disabled={!tracks.prev_page_url}
                            >
                                <Link href={tracks.prev_page_url ?? '#'}>
                                    Previous
                                </Link>
                            </Button>
                            <Button
                                asChild
                                variant="outline"
                                disabled={!tracks.next_page_url}
                            >
                                <Link href={tracks.next_page_url ?? '#'}>
                                    Next
                                </Link>
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Tracks',
        href: tracksRoute(),
    },
];

TracksIndex.layout = {
    breadcrumbs,
};
