import { Check, ChevronDown, LoaderCircle } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import type { PlaylistSummary } from '@/types';

type AddToPlaylistButtonProps = {
    label: string;
    playlistUrl: string;
    playlistIds: number[];
    playlists: PlaylistSummary[];
};

export function AddToPlaylistButton({
    label,
    playlistUrl,
    playlistIds,
    playlists,
}: AddToPlaylistButtonProps) {
    const [selectedPlaylistIds, setSelectedPlaylistIds] = useState(
        () => new Set(playlistIds),
    );
    const [isSaving, setIsSaving] = useState(false);

    const addToPlaylist = async (playlistId: number) => {
        const playlist = playlists.find((item) => item.id === playlistId);

        if (isSaving || selectedPlaylistIds.has(playlistId)) {
            return;
        }

        const csrfToken = document
            .querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
            ?.getAttribute('content');

        setIsSaving(true);

        try {
            const response = await fetch(playlistUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
                },
                body: JSON.stringify({ playlist_id: playlistId }),
            });

            if (!response.ok) {
                throw new Error('Unable to add track to playlist.');
            }

            setSelectedPlaylistIds(
                (current) => new Set([...current, playlistId]),
            );
            toast.success(
                playlist
                    ? `Added "${label}" to "${playlist.name}".`
                    : `Added "${label}" to playlist.`,
            );
        } catch (error) {
            toast.error(
                error instanceof Error
                    ? error.message
                    : 'Unable to add track to playlist.',
            );
        } finally {
            setIsSaving(false);
        }
    };

    if (playlists.length === 0) {
        return null;
    }

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button
                    type="button"
                    variant="outline"
                    disabled={isSaving}
                    aria-label={`Add ${label} to playlist`}
                    className="min-w-36 justify-between"
                >
                    <span>Add to playlist</span>
                    {isSaving ? (
                        <LoaderCircle className="size-4 animate-spin" />
                    ) : (
                        <ChevronDown className="size-4" />
                    )}
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" className="w-56">
                <DropdownMenuLabel>Choose playlist</DropdownMenuLabel>
                <DropdownMenuSeparator />
                {playlists.map((playlist) => {
                    const isAdded = selectedPlaylistIds.has(playlist.id);

                    return (
                        <DropdownMenuItem
                            key={playlist.id}
                            disabled={isAdded || isSaving}
                            onSelect={() => void addToPlaylist(playlist.id)}
                        >
                            <span className="min-w-0 flex-1 truncate">
                                {playlist.name}
                            </span>
                            {isAdded && <Check className="size-4" />}
                        </DropdownMenuItem>
                    );
                })}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
