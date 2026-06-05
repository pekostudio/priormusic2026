import { Head, Link, router } from '@inertiajs/react';
import { MultiSelect } from '@mantine/core';
import { Disc3, Search } from 'lucide-react';
import { useState } from 'react';
import { Pagination } from '@/components/pagination';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { index as albumsIndex } from '@/routes/albums';
import type { BreadcrumbItem } from '@/types';

type AlbumCard = {
    id: number;
    title: string;
    name: string;
    code: string;
    detail: string | null;
    cover_url: string | null;
    tracks_count: number;
    library: string | null;
    show_url: string;
};

type PaginatedAlbums = {
    data: AlbumCard[];
    links: {
        url: string | null;
        label: string;
        active: boolean;
    }[];
    prev_page_url: string | null;
    next_page_url: string | null;
    from: number | null;
    to: number | null;
    total: number;
};

type AlbumsIndexProps = {
    albums: PaginatedAlbums;
    filters: {
        search: string;
        styles: string[];
        library_ids: string[];
    };
    filterOptions: {
        styles: {
            value: string;
            label: string;
        }[];
        libraries: {
            value: string;
            label: string;
        }[];
    };
};

export default function AlbumsIndex({
    albums,
    filters,
    filterOptions,
}: AlbumsIndexProps) {
    const [search, setSearch] = useState(filters.search);
    const [styles, setStyles] = useState<string[]>(filters.styles);
    const [libraryIds, setLibraryIds] = useState<string[]>(filters.library_ids);

    const applyFilters = () => {
        router.get(
            albumsIndex.url(),
            { search, styles, library_ids: libraryIds },
            { preserveState: true, replace: true },
        );
    };

    const clearFilters = () => {
        setSearch('');
        setStyles([]);
        setLibraryIds([]);
        router.get(
            albumsIndex.url(),
            {},
            { preserveState: true, replace: true },
        );
    };

    const albumShowUrl = (showUrl: string): string => {
        const query = new URLSearchParams();

        filters.styles.forEach((style, index) => {
            query.append(`styles[${index}]`, style);
        });
        filters.library_ids.forEach((libraryId, index) => {
            query.append(`library_ids[${index}]`, libraryId);
        });

        const queryString = query.toString();

        return queryString === '' ? showUrl : `${showUrl}?${queryString}`;
    };

    return (
        <>
            <Head title="Albums" />

            <div className="w-full p-4">
                <div className="flex items-center gap-2">
                    <Disc3 className="size-5" />
                    <h1 className="text-xl font-semibold">Albums</h1>
                </div>
                <p className="mt-1 text-sm text-muted-foreground">
                    {albums.total} albums in the library
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
                            placeholder="Search albums"
                        />
                    </div>
                    <MultiSelect
                        value={styles}
                        onChange={setStyles}
                        data={filterOptions.styles}
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
                </div>

                <div className="flex w-full flex-col gap-4 lg:w-10/12">
                    <div className="grid grid-cols-3 gap-4 sm:grid-cols-4 lg:grid-cols-5 xl:grid-cols-7 2xl:grid-cols-9">
                        {albums.data.map((album) => (
                            <Link
                                key={album.id}
                                href={albumShowUrl(album.show_url)}
                                className="group mb-4 block min-w-0"
                                prefetch
                            >
                                <div className="aspect-square overflow-hidden">
                                    {album.cover_url && (
                                        <img
                                            src={album.cover_url}
                                            alt=""
                                            className="size-full object-cover transition group-hover:scale-105"
                                            loading="lazy"
                                        />
                                    )}
                                </div>
                                <div className="mt-3 min-w-0">
                                    <p className="truncate text-sm font-medium group-hover:underline">
                                        {album.title}
                                    </p>
                                    <p className="truncate overflow-x-hidden rounded bg-secondary px-2 py-0.5 text-[10px] text-secondary-foreground">
                                        {album.library ?? album.code}
                                    </p>
                                </div>
                            </Link>
                        ))}
                    </div>

                    {albums.data.length === 0 && (
                        <div className="rounded-lg border border-border p-8 text-center text-sm text-muted-foreground">
                            No albums match the current filters.
                        </div>
                    )}

                    <div className="flex items-center justify-between">
                        <p className="text-sm text-muted-foreground">
                            {albums.from !== null && albums.to !== null
                                ? `Showing ${albums.from}-${albums.to}`
                                : 'No results'}
                        </p>
                        <Pagination links={albums.links} />
                    </div>
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
];

AlbumsIndex.layout = {
    breadcrumbs,
};
