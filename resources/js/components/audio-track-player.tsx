import 'react-h5-audio-player/lib/styles.css';

import { useEffect, useRef } from 'react';
import AudioPlayer, { RHAP_UI } from 'react-h5-audio-player';
import { WaveformPreview } from '@/components/waveform-preview';
import { useAudioPlayer } from '@/hooks/use-audio-player';

export function AudioTrackPlayer() {
    const playerRef = useRef<AudioPlayer>(null);
    const {
        clearPendingSeek,
        currentProgress,
        currentTrack,
        duration,
        isPlaying,
        pendingSeek,
        playRequestId,
        setIsPlaying,
        setPlaybackProgress,
    } = useAudioPlayer();

    useEffect(() => {
        const audio = playerRef.current?.audio.current;

        if (!audio) {
            return;
        }

        if (isPlaying) {
            void audio.play();
        } else {
            audio.pause();
        }
    }, [currentTrack?.audioUrl, isPlaying, playRequestId]);

    useEffect(() => {
        const audio = playerRef.current?.audio.current;

        if (
            !audio ||
            !currentTrack ||
            !pendingSeek ||
            pendingSeek.trackId !== currentTrack.id ||
            audio.currentSrc !==
                new URL(currentTrack.audioUrl, window.location.href).href ||
            !Number.isFinite(audio.duration) ||
            audio.duration <= 0
        ) {
            return;
        }

        const nextTime = audio.duration * pendingSeek.progress;

        Reflect.set(audio, 'currentTime', nextTime);
        setPlaybackProgress(nextTime, audio.duration);
        clearPendingSeek();
    }, [
        clearPendingSeek,
        currentTrack,
        duration,
        pendingSeek,
        setPlaybackProgress,
    ]);

    const syncAudioTime = () => {
        const audio = playerRef.current?.audio.current;

        if (!audio) {
            return;
        }

        setPlaybackProgress(
            audio.currentTime || 0,
            Number.isFinite(audio.duration) ? audio.duration : 0,
        );
    };

    const seekToProgress = (progress: number) => {
        const audio = playerRef.current?.audio.current;

        if (!audio || !Number.isFinite(audio.duration) || audio.duration <= 0) {
            return;
        }

        const nextTime = audio.duration * progress;

        Reflect.set(audio, 'currentTime', nextTime);
        setPlaybackProgress(nextTime, audio.duration);

        if (!isPlaying) {
            setIsPlaying(true);
        }
    };

    const logPlay = () => {
        if (!currentTrack?.playUrl) {
            return;
        }

        const csrfToken = document
            .querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
            ?.getAttribute('content');

        void fetch(currentTrack.playUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({}),
        });
    };

    return (
        <div className="fixed right-0 bottom-0 left-0 z-40 border-t border-border bg-background/95 shadow-2xl backdrop-blur supports-[backdrop-filter]:bg-background/85">
            <div className="grid grid-cols-[minmax(0,1fr)_3rem_minmax(5.5rem,0.9fr)] items-center gap-2 p-2 sm:grid-cols-[minmax(0,1fr)_3.5rem_minmax(8rem,1fr)] md:grid-cols-[minmax(220px,320px)_120px_minmax(220px,1fr)] md:gap-3 md:px-4 md:py-3">
                <div className="flex min-w-0 flex-row items-center gap-2 md:gap-3 xl:min-w-0">
                    <div className="size-10 shrink-0 overflow-hidden rounded-md bg-muted md:size-12">
                        {currentTrack?.coverUrl && (
                            <img
                                src={currentTrack.coverUrl}
                                alt=""
                                className="size-full object-cover"
                            />
                        )}
                    </div>
                    <div className="min-w-0">
                        <p className="truncate text-sm font-medium">
                            {currentTrack?.title ?? 'Select a track'}
                        </p>
                        <p className="mt-0.5 truncate text-xs text-muted-foreground md:mt-1">
                            {currentTrack?.artist ??
                                'Playback will stay here while you browse.'}
                        </p>
                    </div>
                </div>

                {currentTrack ? (
                    <div className="min-w-0 [&_.rhap_button-clear]:m-0 [&_.rhap_container]:min-w-0 [&_.rhap_main-controls]:justify-center [&_.rhap_main-controls-button]:m-0 [&_.rhap_main-controls-button]:size-11 [&_.rhap_main-controls-button]:text-muted-foreground md:[&_.rhap_main-controls-button]:size-14">
                        <AudioPlayer
                            ref={playerRef}
                            src={currentTrack.audioUrl}
                            onPlay={() => {
                                setIsPlaying(true);
                                logPlay();
                            }}
                            onPause={() => setIsPlaying(false)}
                            onEnded={() => setIsPlaying(false)}
                            onLoadedMetaData={syncAudioTime}
                            onListen={syncAudioTime}
                            onSeeked={syncAudioTime}
                            listenInterval={250}
                            showJumpControls={false}
                            customProgressBarSection={[]}
                            customControlsSection={[RHAP_UI.MAIN_CONTROLS]}
                            autoPlayAfterSrcChange={true}
                            style={{
                                background: 'transparent',
                                boxShadow: 'none',
                                padding: 0,
                            }}
                        />
                    </div>
                ) : (
                    <div className="hidden min-h-14 items-center justify-center rounded-md border border-border bg-muted/40 px-4 text-sm text-muted-foreground xl:flex">
                        Select a track to start playback.
                    </div>
                )}

                <div className="min-w-0">
                    {currentTrack ? (
                        <WaveformPreview
                            peaksUrl={currentTrack.peaksUrl}
                            progress={currentProgress}
                            amplitude={1.8}
                            interactive={true}
                            className="h-8 sm:h-9 md:h-12"
                            onSeek={seekToProgress}
                        />
                    ) : (
                        <div className="hidden h-20 items-center justify-center rounded-md border border-dashed border-border text-xs text-muted-foreground xl:flex">
                            Track waveform
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}
