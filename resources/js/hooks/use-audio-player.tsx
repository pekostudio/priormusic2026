import {
    createContext,
    useCallback,
    useContext,
    useMemo,
    useState,
} from 'react';
import type { ReactNode } from 'react';

export type PlayerTrack = {
    id: number;
    title: string;
    artist?: string | null;
    audioUrl: string;
    peaksUrl: string;
    playUrl?: string | null;
    coverUrl?: string | null;
};

type PendingSeek = {
    trackId: number;
    progress: number;
    requestId: number;
};

type AudioPlayerContextValue = {
    currentTrack: PlayerTrack | null;
    currentTime: number;
    duration: number;
    currentProgress: number;
    isPlaying: boolean;
    pendingSeek: PendingSeek | null;
    playRequestId: number;
    playTrack: (track: PlayerTrack) => void;
    pauseTrack: () => void;
    seekTrackToProgress: (track: PlayerTrack, progress: number) => void;
    setPlaybackProgress: (currentTime: number, duration: number) => void;
    clearPendingSeek: () => void;
    setIsPlaying: (isPlaying: boolean) => void;
    isCurrentTrack: (trackId: number) => boolean;
};

const AudioPlayerContext = createContext<AudioPlayerContextValue | null>(null);

export function AudioPlayerProvider({ children }: { children: ReactNode }) {
    const [currentTrack, setCurrentTrack] = useState<PlayerTrack | null>(null);
    const [currentTime, setCurrentTime] = useState(0);
    const [duration, setDuration] = useState(0);
    const [isPlaying, setIsPlaying] = useState(false);
    const [pendingSeek, setPendingSeek] = useState<PendingSeek | null>(null);
    const [playRequestId, setPlayRequestId] = useState(0);

    const playTrack = useCallback((track: PlayerTrack) => {
        setCurrentTrack(track);
        setCurrentTime(0);
        setDuration(0);
        setPendingSeek(null);
        setIsPlaying(true);
        setPlayRequestId((current) => current + 1);
    }, []);

    const pauseTrack = useCallback(() => {
        setIsPlaying(false);
    }, []);

    const isCurrentTrack = useCallback(
        (trackId: number) => currentTrack?.id === trackId,
        [currentTrack],
    );

    const seekTrackToProgress = useCallback(
        (track: PlayerTrack, progress: number) => {
            const boundedProgress = Math.max(0, Math.min(1, progress));

            setCurrentTrack(track);
            setIsPlaying(true);
            setPendingSeek((current) => ({
                trackId: track.id,
                progress: boundedProgress,
                requestId: (current?.requestId ?? 0) + 1,
            }));

            if (currentTrack?.id !== track.id) {
                setCurrentTime(0);
                setDuration(0);
                setPlayRequestId((current) => current + 1);
            }
        },
        [currentTrack],
    );

    const setPlaybackProgress = useCallback(
        (currentTime: number, duration: number) => {
            setCurrentTime(currentTime);
            setDuration(duration);
        },
        [],
    );

    const clearPendingSeek = useCallback(() => {
        setPendingSeek(null);
    }, []);

    const currentProgress =
        duration > 0 ? Math.max(0, Math.min(1, currentTime / duration)) : 0;

    const value = useMemo(
        () => ({
            currentTrack,
            currentTime,
            duration,
            currentProgress,
            isPlaying,
            pendingSeek,
            playRequestId,
            playTrack,
            pauseTrack,
            seekTrackToProgress,
            setPlaybackProgress,
            clearPendingSeek,
            setIsPlaying,
            isCurrentTrack,
        }),
        [
            clearPendingSeek,
            currentTrack,
            currentTime,
            duration,
            currentProgress,
            isCurrentTrack,
            isPlaying,
            pendingSeek,
            pauseTrack,
            playRequestId,
            playTrack,
            seekTrackToProgress,
            setPlaybackProgress,
        ],
    );

    return (
        <AudioPlayerContext.Provider value={value}>
            {children}
        </AudioPlayerContext.Provider>
    );
}

export function useAudioPlayer() {
    const context = useContext(AudioPlayerContext);

    if (!context) {
        throw new Error(
            'useAudioPlayer must be used within AudioPlayerProvider',
        );
    }

    return context;
}
