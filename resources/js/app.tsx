import '@mantine/core/styles.css';

import { createInertiaApp } from '@inertiajs/react';
import { MantineProvider } from '@mantine/core';
import type { ReactNode } from 'react';
import { Toaster } from '@/components/ui/sonner';
import { TooltipProvider } from '@/components/ui/tooltip';
import { initializeTheme, useAppearance } from '@/hooks/use-appearance';
import { AudioPlayerProvider } from '@/hooks/use-audio-player';
import AppLayout from '@/layouts/app-layout';
import AuthSplitLayout from '@/layouts/auth/auth-split-layout';
import AuthLayout from '@/layouts/auth-layout';
import SettingsLayout from '@/layouts/settings/layout';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
const splitAuthPages = new Set(['auth/login', 'auth/register']);

function AppProviders({ children }: { children: ReactNode }) {
    const { resolvedAppearance } = useAppearance();

    return (
        <MantineProvider forceColorScheme={resolvedAppearance}>
            <TooltipProvider delayDuration={0}>
                <AudioPlayerProvider>
                    {children}
                    <Toaster />
                </AudioPlayerProvider>
            </TooltipProvider>
        </MantineProvider>
    );
}

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case name === 'welcome':
                return null;
            case splitAuthPages.has(name):
                return AuthSplitLayout;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },
    strictMode: true,
    withApp(app) {
        return <AppProviders>{app}</AppProviders>;
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on load...
initializeTheme();
