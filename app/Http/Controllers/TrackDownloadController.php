<?php

namespace App\Http\Controllers;

use App\Audio\WaveformGenerator;
use App\Models\AlbumTrack;
use App\Models\MusicUsageEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TrackDownloadController extends Controller
{
    public function __invoke(AlbumTrack $albumTrack, Request $request, WaveformGenerator $waveformGenerator): BinaryFileResponse
    {
        $path = $waveformGenerator->audioPath($albumTrack);

        abort_if($path === null || Str::startsWith($path, ['http://', 'https://']), Response::HTTP_NOT_FOUND);
        $occurredAt = now();

        $download = $request->user()->trackDownloads()->create([
            'album_track_id' => $albumTrack->id,
            'downloaded_at' => $occurredAt,
        ]);

        $request->user()->musicUsageEvents()->create([
            'album_track_id' => $albumTrack->id,
            'event_type' => MusicUsageEvent::TypeDownloaded,
            'occurred_at' => $occurredAt,
            'track_title' => $albumTrack->name,
            'album_title' => $albumTrack->album?->displaytitle ?: $albumTrack->album?->name,
            'metadata' => [
                'track_download_id' => $download->id,
                'file_name' => $this->downloadFileName($albumTrack),
            ],
        ]);

        return response()->download($path, $this->downloadFileName($albumTrack), [
            'Content-Type' => 'audio/mpeg',
        ]);
    }

    private function downloadFileName(AlbumTrack $albumTrack): string
    {
        $name = trim((string) ($albumTrack->file_name ?: $albumTrack->name));

        if ($name === '') {
            $name = 'track-'.$albumTrack->getKey();
        }

        $name = pathinfo($name, PATHINFO_FILENAME);
        $name = Str::slug($name) ?: 'track-'.$albumTrack->getKey();

        return Str::finish($name, '.mp3');
    }
}
