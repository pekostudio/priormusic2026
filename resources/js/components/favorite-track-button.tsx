import { Heart } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';

type FavoriteTrackButtonProps = {
    isFavorite: boolean;
    favoriteUrl: string;
    unfavoriteUrl: string;
    label: string;
    onChanged?: (isFavorite: boolean) => void;
};

export function FavoriteTrackButton({
    isFavorite,
    favoriteUrl,
    unfavoriteUrl,
    label,
    onChanged,
}: FavoriteTrackButtonProps) {
    const [favorite, setFavorite] = useState(isFavorite);
    const [isSaving, setIsSaving] = useState(false);

    const toggle = async () => {
        if (isSaving) {
            return;
        }

        const nextFavorite = !favorite;
        setFavorite(nextFavorite);
        setIsSaving(true);
        onChanged?.(nextFavorite);

        const csrfToken = document
            .querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
            ?.getAttribute('content');

        try {
            const response = await fetch(
                nextFavorite ? favoriteUrl : unfavoriteUrl,
                {
                    method: nextFavorite ? 'POST' : 'DELETE',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
                    },
                },
            );

            if (!response.ok) {
                throw new Error('Unable to update favorite state.');
            }
        } catch {
            setFavorite(favorite);
            onChanged?.(favorite);
        } finally {
            setIsSaving(false);
        }
    };

    return (
        <Button
            type="button"
            variant="outline"
            size="icon"
            aria-label={
                favorite
                    ? `Remove ${label} from favorites`
                    : `Add ${label} to favorites`
            }
            disabled={isSaving}
            onClick={toggle}
        >
            <Heart
                className={
                    favorite
                        ? 'size-4 fill-red-500 text-red-500'
                        : 'size-4 text-muted-foreground'
                }
            />
        </Button>
    );
}
