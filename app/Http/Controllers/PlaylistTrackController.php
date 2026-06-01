<?php

namespace App\Http\Controllers;

use App\Models\AlbumTrack;
use App\Models\Playlist;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PlaylistTrackController extends Controller
{
    public function store(AlbumTrack $albumTrack, Request $request): Response
    {
        $validated = $request->validate([
            'playlist_id' => ['required', 'integer'],
        ]);

        $playlist = $request->user()->playlists()->findOrFail($validated['playlist_id']);
        $playlist->albumTracks()->syncWithoutDetaching([$albumTrack->id]);

        return response()->noContent();
    }

    public function destroy(Playlist $playlist, AlbumTrack $albumTrack, Request $request): Response
    {
        abort_unless($playlist->user()->is($request->user()), 404);

        $playlist->albumTracks()->detach($albumTrack->id);

        return response()->noContent();
    }
}
