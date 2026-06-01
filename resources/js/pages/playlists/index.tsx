import { Form, Head, Link, router } from '@inertiajs/react';
import { ListPlus, Trash2 } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { index as playlistsIndex, store } from '@/routes/playlists';
import type { BreadcrumbItem, PlaylistSummary } from '@/types';

type PlaylistsIndexProps = {
    playlists: Required<PlaylistSummary>[];
};

export default function PlaylistsIndex({ playlists }: PlaylistsIndexProps) {
    return (
        <>
            <Head title="Playlists" />
            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div className="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                    <div>
                        <div className="flex items-center gap-2">
                            <ListPlus className="size-5" />
                            <h1 className="text-xl font-semibold">Playlists</h1>
                        </div>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Create custom playlists and keep selected tracks
                            together.
                        </p>
                    </div>

                    <Form
                        {...store.form()}
                        resetOnSuccess
                        className="grid gap-2 md:grid-cols-[280px_auto]"
                    >
                        {({ errors, processing }) => (
                            <>
                                <div>
                                    <Input
                                        name="name"
                                        placeholder="New playlist"
                                        aria-invalid={Boolean(errors.name)}
                                    />
                                    {errors.name && (
                                        <p className="mt-1 text-xs text-destructive">
                                            {errors.name}
                                        </p>
                                    )}
                                </div>
                                <Button type="submit" disabled={processing}>
                                    Create
                                </Button>
                            </>
                        )}
                    </Form>
                </div>

                <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                    {playlists.map((playlist) => (
                        <div
                            key={playlist.id}
                            className="rounded-lg border border-border p-4"
                        >
                            <div className="flex items-start justify-between gap-3">
                                <div className="min-w-0">
                                    <Link
                                        href={playlist.show_url}
                                        className="truncate font-medium hover:underline"
                                        prefetch
                                    >
                                        {playlist.name}
                                    </Link>
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        {playlist.tracks_count} tracks
                                    </p>
                                </div>
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="icon"
                                    aria-label={`Delete ${playlist.name}`}
                                    onClick={() => {
                                        if (
                                            confirm(
                                                'Delete this playlist? Tracks will only be removed from this playlist.',
                                            )
                                        ) {
                                            router.delete(playlist.delete_url, {
                                                preserveScroll: true,
                                            });
                                        }
                                    }}
                                >
                                    <Trash2 className="size-4" />
                                </Button>
                            </div>
                        </div>
                    ))}
                </div>

                {playlists.length === 0 && (
                    <div className="rounded-lg border border-dashed border-border p-8 text-center text-sm text-muted-foreground">
                        No playlists yet. Create your first playlist above.
                    </div>
                )}
            </div>
        </>
    );
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Playlists',
        href: playlistsIndex(),
    },
];

PlaylistsIndex.layout = {
    breadcrumbs,
};
