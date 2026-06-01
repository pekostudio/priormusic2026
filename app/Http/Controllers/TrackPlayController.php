<?php

namespace App\Http\Controllers;

use App\Models\AlbumTrack;
use App\Models\MusicUsageEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TrackPlayController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(AlbumTrack $albumTrack, Request $request): Response
    {
        $validated = $request->validate([
            'duration_seconds' => ['nullable', 'integer', 'min:0'],
        ]);
        $occurredAt = now();

        $play = $request->user()->trackPlays()->create([
            'album_track_id' => $albumTrack->id,
            'played_at' => $occurredAt,
            'duration_seconds' => $validated['duration_seconds'] ?? null,
        ]);

        $request->user()->musicUsageEvents()->create([
            'album_track_id' => $albumTrack->id,
            'event_type' => MusicUsageEvent::TypeListened,
            'occurred_at' => $occurredAt,
            'duration_seconds' => $validated['duration_seconds'] ?? null,
            'track_title' => $albumTrack->name,
            'album_title' => $albumTrack->album?->displaytitle ?: $albumTrack->album?->name,
            'metadata' => [
                'track_play_id' => $play->id,
            ],
        ]);

        return response()->noContent();
    }
}
