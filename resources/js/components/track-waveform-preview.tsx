import { WaveformPreview } from '@/components/waveform-preview';
import { useAudioPlayer } from '@/hooks/use-audio-player';
import type { PlayerTrack } from '@/hooks/use-audio-player';

type TrackWaveformPreviewProps = {
    peaksUrl: string;
    track: PlayerTrack | null;
    amplitude?: number;
};

export function TrackWaveformPreview({
    peaksUrl,
    track,
    amplitude = 1.8,
}: TrackWaveformPreviewProps) {
    const { currentProgress, isCurrentTrack, seekTrackToProgress } =
        useAudioPlayer();

    return (
        <WaveformPreview
            peaksUrl={peaksUrl}
            progress={track && isCurrentTrack(track.id) ? currentProgress : 0}
            amplitude={amplitude}
            interactive={track !== null}
            onSeek={
                track
                    ? (progress) => seekTrackToProgress(track, progress)
                    : undefined
            }
        />
    );
}
