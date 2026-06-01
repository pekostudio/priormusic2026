import { Head, Link, router } from '@inertiajs/react';
import { Disc3, Search } from 'lucide-react';
import { useState } from 'react';
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
    };
};

export default function AlbumsIndex({ albums, filters }: AlbumsIndexProps) {
    const [search, setSearch] = useState(filters.search);

    const applyFilters = () => {
        router.get(
            albumsIndex.url(),
            { search },
            { preserveState: true, replace: true },
        );
    };

    const clearFilters = () => {
        setSearch('');
        router.get(
            albumsIndex.url(),
            {},
            { preserveState: true, replace: true },
        );
    };

    return (
        <>
            <Head title="Albums" />
            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div className="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <div>
                        <div className="flex items-center gap-2">
                            <Disc3 className="size-5" />
                            <h1 className="text-xl font-semibold">Albums</h1>
                        </div>
                        <p className="mt-1 text-sm text-muted-foreground">
                            {albums.total} albums in the library
                        </p>
                    </div>
                    <div className="grid gap-2 md:grid-cols-[280px_auto_auto]">
                        <div className="relative">
                            <Search className="pointer-events-none absolute top-2.5 left-2.5 size-4 text-muted-foreground" />
                            <Input
                                value={search}
                                onChange={(event) =>
                                    setSearch(event.target.value)
                                }
                                onKeyDown={(event) => {
                                    if (event.key === 'Enter') {
                                        applyFilters();
                                    }
                                }}
                                className="pl-8"
                                placeholder="Search albums"
                            />
                        </div>
                        <Button type="button" onClick={applyFilters}>
                            Apply
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={clearFilters}
                        >
                            Clear
                        </Button>
                    </div>
                </div>

                <div className="grid grid-cols-3 gap-4 sm:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 2xl:grid-cols-8">
                    {albums.data.map((album) => (
                        <Link
                            key={album.id}
                            href={album.show_url}
                            className="group block min-w-0"
                            prefetch
                        >
                            <div className="aspect-square overflow-hidden rounded-lg border border-border bg-muted">
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
                                <p className="w-fit truncate rounded bg-secondary px-2 py-0.5 text-xs text-secondary-foreground">
                                    {album.library ?? album.code}
                                </p>
                            </div>
                        </Link>
                    ))}
                </div>

                {albums.data.length === 0 && (
                    <div className="rounded-lg border border-border p-8 text-center text-sm text-muted-foreground">
                        No albums match the current search.
                    </div>
                )}

                <div className="flex items-center justify-between">
                    <p className="text-sm text-muted-foreground">
                        {albums.from !== null && albums.to !== null
                            ? `Showing ${albums.from}-${albums.to}`
                            : 'No results'}
                    </p>
                    <div className="flex gap-2">
                        <Button
                            asChild
                            variant="outline"
                            disabled={!albums.prev_page_url}
                        >
                            <Link href={albums.prev_page_url ?? '#'}>
                                Previous
                            </Link>
                        </Button>
                        <Button
                            asChild
                            variant="outline"
                            disabled={!albums.next_page_url}
                        >
                            <Link href={albums.next_page_url ?? '#'}>Next</Link>
                        </Button>
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
