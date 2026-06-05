import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type PaginationProps = {
    links: PaginationLink[];
};

const normalizeLabel = (label: string): string => {
    return label
        .replace('&laquo;', '')
        .replace('&raquo;', '')
        .replace('&hellip;', '...')
        .trim();
};

export function Pagination({ links }: PaginationProps) {
    if (links.length <= 3) {
        return null;
    }

    return (
        <nav
            className="flex flex-wrap items-center justify-end gap-2"
            aria-label="Pagination"
        >
            {links.map((link, index) => {
                const label = normalizeLabel(link.label);
                const isSeparator = label === '...';
                const key = `${label}-${index}`;

                if (isSeparator) {
                    return (
                        <span
                            key={key}
                            className="flex h-9 min-w-9 items-center justify-center px-2 text-sm text-muted-foreground"
                            aria-hidden="true"
                        >
                            ...
                        </span>
                    );
                }

                if (link.url === null) {
                    return (
                        <Button
                            key={key}
                            type="button"
                            variant="outline"
                            disabled
                            className="min-w-9 px-3"
                        >
                            {label}
                        </Button>
                    );
                }

                return (
                    <Button
                        key={key}
                        asChild
                        variant={link.active ? 'default' : 'outline'}
                        aria-current={link.active ? 'page' : undefined}
                        className={cn(
                            'min-w-9 px-3',
                            link.active && 'shadow-none',
                        )}
                    >
                        <Link href={link.url} preserveScroll>
                            {label}
                        </Link>
                    </Button>
                );
            })}
        </nav>
    );
}
