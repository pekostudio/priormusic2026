import { ActionIcon } from '@mantine/core';
import { Check, ListPlus } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';
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

    const selectedPlaylistCount = selectedPlaylistIds.size;
    const isInPlaylist = selectedPlaylistCount > 0;
    const playlistStatusLabel = isInPlaylist
        ? `${label} is in ${selectedPlaylistCount} ${
              selectedPlaylistCount === 1 ? 'playlist' : 'playlists'
          }. Add to playlist`
        : `Add ${label} to playlist`;

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <ActionIcon
                    type="button"
                    variant={isInPlaylist ? 'light' : 'default'}
                    color={isInPlaylist ? 'green' : undefined}
                    size={36}
                    radius="md"
                    disabled={isSaving}
                    aria-label={playlistStatusLabel}
                    title={playlistStatusLabel}
                    className="relative overflow-visible bg-yellow-100"
                    loading={isSaving}
                    loaderProps={{ size: 16 }}
                >
                    <ListPlus className="size-4" />
                    {selectedPlaylistCount > 1 && (
                        <span className="absolute top-0.5 right-0.5 flex min-h-3 min-w-3 items-center justify-center rounded-full bg-green-600 px-1 text-[8px] leading-none font-semibold text-white shadow-sm">
                            {selectedPlaylistCount}
                        </span>
                    )}
                </ActionIcon>
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
