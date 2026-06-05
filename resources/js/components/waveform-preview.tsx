import { useCallback, useEffect, useRef, useState } from 'react';
import { cn } from '@/lib/utils';

type WaveformPreviewProps = {
    peaksUrl: string;
    progress?: number;
    interactive?: boolean;
    amplitude?: number;
    size?: 'preview' | 'player';
    className?: string;
    onSeek?: (progress: number) => void;
};

export function WaveformPreview({
    peaksUrl,
    progress = 0,
    interactive = false,
    amplitude = 1,
    size = interactive ? 'player' : 'preview',
    className,
    onSeek,
}: WaveformPreviewProps) {
    const canvasRef = useRef<HTMLCanvasElement>(null);
    const [shouldLoadPeaks, setShouldLoadPeaks] = useState(
        () =>
            typeof window !== 'undefined' &&
            !('IntersectionObserver' in window),
    );
    const [peaks, setPeaks] = useState<number[]>([]);

    const seekFromPointer = useCallback(
        (clientX: number) => {
            const canvas = canvasRef.current;

            if (!canvas || !onSeek) {
                return;
            }

            const rect = canvas.getBoundingClientRect();
            const nextProgress = Math.max(
                0,
                Math.min(1, (clientX - rect.left) / rect.width),
            );

            onSeek(nextProgress);
        },
        [onSeek],
    );

    const handlePointerDown = (
        event: React.PointerEvent<HTMLCanvasElement>,
    ) => {
        if (!interactive) {
            return;
        }

        event.currentTarget.setPointerCapture(event.pointerId);
        seekFromPointer(event.clientX);
    };

    const handlePointerMove = (
        event: React.PointerEvent<HTMLCanvasElement>,
    ) => {
        if (!interactive || event.buttons !== 1) {
            return;
        }

        seekFromPointer(event.clientX);
    };

    const handleKeyDown = (event: React.KeyboardEvent<HTMLCanvasElement>) => {
        if (!interactive || !onSeek) {
            return;
        }

        if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') {
            return;
        }

        event.preventDefault();
        onSeek(
            Math.max(
                0,
                Math.min(
                    1,
                    progress + (event.key === 'ArrowRight' ? 0.05 : -0.05),
                ),
            ),
        );
    };

    useEffect(() => {
        const canvas = canvasRef.current;

        if (!canvas) {
            return;
        }

        if (!('IntersectionObserver' in window)) {
            return;
        }

        const observer = new IntersectionObserver(
            ([entry]) => {
                if (entry?.isIntersecting) {
                    setShouldLoadPeaks(true);
                    observer.disconnect();
                }
            },
            { rootMargin: '300px 0px' },
        );

        observer.observe(canvas);

        return () => observer.disconnect();
    }, []);

    useEffect(() => {
        if (!shouldLoadPeaks) {
            return;
        }

        let cancelled = false;
        const timeoutId = window.setTimeout(() => {
            fetch(peaksUrl, {
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then((response) => (response.ok ? response.json() : null))
                .then((data: { peaks?: number[] } | null) => {
                    if (!cancelled) {
                        setPeaks(Array.isArray(data?.peaks) ? data.peaks : []);
                    }
                })
                .catch(() => {
                    if (!cancelled) {
                        setPeaks([]);
                    }
                });
        }, 100);

        return () => {
            cancelled = true;
            window.clearTimeout(timeoutId);
        };
    }, [peaksUrl, shouldLoadPeaks]);

    useEffect(() => {
        const canvas = canvasRef.current;

        if (!canvas) {
            return;
        }

        const context = canvas.getContext('2d');

        if (!context) {
            return;
        }

        const draw = () => {
            const pixelRatio = window.devicePixelRatio || 1;
            const width = canvas.clientWidth;
            const height = canvas.clientHeight;

            canvas.width = Math.max(1, Math.floor(width * pixelRatio));
            canvas.height = Math.max(1, Math.floor(height * pixelRatio));
            context.setTransform(pixelRatio, 0, 0, pixelRatio, 0, 0);
            context.clearRect(0, 0, width, height);

            const styles = getComputedStyle(document.documentElement);
            const muted = styles.getPropertyValue('--muted').trim();
            const blue500 =
                styles.getPropertyValue('--color-blue-700').trim() || '#3b82f6';
            const foreground = styles.getPropertyValue('--foreground').trim();

            context.fillStyle = muted ? `oklch(${muted})` : '#e5e7eb';
            context.globalAlpha = 0.7;
            context.fillRect(0, Math.floor(height / 2), width, 1);
            context.globalAlpha = 1;
            context.fillStyle = blue500;

            const detailedPeaks = interpolatePeaks(
                peaks,
                Math.min(Math.floor(width / 2), peaks.length * 3),
            );
            const barCount = detailedPeaks.length;
            const step = width / Math.max(1, barCount);
            const barWidth = Math.max(1, step * 0.52);
            const centerY = height / 2;
            const playedWidth = width * Math.max(0, Math.min(1, progress));

            for (let index = 0; index < barCount; index++) {
                const peak = Math.max(
                    0.02,
                    Math.min(1, (detailedPeaks[index] ?? 0) * amplitude),
                );
                const barHeight = Math.max(2, peak * height * 0.92);
                const x = index * step;
                const y = centerY - barHeight / 2;

                context.fillStyle =
                    x <= playedWidth
                        ? blue500
                        : foreground
                          ? `oklch(${foreground})`
                          : '#111827';
                context.globalAlpha = x <= playedWidth ? 1 : 0.42;
                context.fillRect(x, y, barWidth, barHeight);
            }

            context.globalAlpha = 1;

            if (interactive) {
                context.fillStyle = blue500;
                context.fillRect(playedWidth - 1, 0, 2, height);
            }
        };

        const interpolatePeaks = (peaks: number[], targetCount: number) => {
            if (peaks.length === 0 || targetCount <= peaks.length) {
                return peaks;
            }

            return Array.from({ length: targetCount }, (_, index) => {
                const position =
                    (index / Math.max(1, targetCount - 1)) *
                    Math.max(0, peaks.length - 1);
                const leftIndex = Math.floor(position);
                const rightIndex = Math.min(peaks.length - 1, leftIndex + 1);
                const ratio = position - leftIndex;
                const leftPeak = peaks[leftIndex] ?? 0;
                const rightPeak = peaks[rightIndex] ?? leftPeak;

                return leftPeak + (rightPeak - leftPeak) * ratio;
            });
        };

        draw();

        window.addEventListener('resize', draw);

        return () => window.removeEventListener('resize', draw);
    }, [amplitude, interactive, peaks, progress]);

    return (
        <canvas
            ref={canvasRef}
            className={cn(
                size === 'player' ? 'h-12' : 'h-12',
                'w-full',
                interactive &&
                    'cursor-pointer touch-none rounded-md focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none',
                className,
            )}
            role={interactive ? 'slider' : undefined}
            tabIndex={interactive ? 0 : undefined}
            aria-hidden={interactive ? undefined : true}
            aria-label={interactive ? 'Seek track position' : undefined}
            aria-valuemin={interactive ? 0 : undefined}
            aria-valuemax={interactive ? 100 : undefined}
            aria-valuenow={interactive ? Math.round(progress * 100) : undefined}
            onKeyDown={handleKeyDown}
            onPointerDown={handlePointerDown}
            onPointerMove={handlePointerMove}
        />
    );
}
