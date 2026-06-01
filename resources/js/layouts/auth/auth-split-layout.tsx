import { Link, usePage } from '@inertiajs/react';
import AppLogoIcon from '@/components/app-logo-icon';
import { home } from '@/routes';
import type { AuthLayoutProps } from '@/types';

export default function AuthSplitLayout({
    children,
    title,
    description,
}: AuthLayoutProps) {
    const { name } = usePage().props;

    return (
        <div className="grid min-h-svh bg-background lg:grid-cols-[minmax(0,1fr)_minmax(420px,520px)]">
            <div className="relative hidden min-h-svh flex-col overflow-hidden bg-blue-900 p-10 text-white lg:flex dark:border-r">
                <div className="absolute inset-0 bg-blue-900/55" />
                <Link
                    href={home()}
                    className="relative z-20 flex items-center gap-3 text-lg font-medium"
                >
                    <img
                        className="h-12"
                        src="/images/priormusica-logo-white.svg"
                        alt="Prior Musica"
                    />
                </Link>
                <div className="relative z-20 mt-auto max-w-6xl space-y-4">
                    <p className="text-white">
                        Prior Musica yra tarptautiniu mastu veikianti muzikos
                        kompanija. Teikianti įrašų studijos paslaugas,
                        atstovaujanti daugiau kaip 63.000 muzikos kūrinių iš
                        įvairių Europos šalių.
                    </p>
                </div>
            </div>
            <div className="flex min-h-svh w-full items-center px-6 py-10 sm:px-8 lg:px-12">
                <div className="mx-auto flex w-full max-w-sm flex-col justify-center space-y-6">
                    <Link
                        href={home()}
                        className="relative z-20 flex items-center justify-center lg:hidden"
                    >
                        <AppLogoIcon className="h-10 fill-current text-black sm:h-12 dark:text-white" />
                    </Link>
                    <div className="flex flex-col items-start gap-2 text-left sm:items-center sm:text-center">
                        <h1 className="text-xl font-medium">{title}</h1>
                        <p className="text-sm text-balance text-muted-foreground">
                            {description}
                        </p>
                    </div>
                    {children}
                </div>
            </div>
        </div>
    );
}
