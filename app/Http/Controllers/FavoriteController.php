<?php

namespace App\Http\Controllers;

use App\Models\AlbumTrack;
use App\Support\AlbumTrackPayload;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FavoriteController extends Controller
{
    public function index(Request $request, AlbumTrackPayload $albumTrackPayload): Response
    {
        $favoriteTracks = $request->user()
            ->favoriteAlbumTracks()
            ->with([
                'album.library',
                'tracks' => fn ($query) => $query->orderBy('track_number')->orderBy('name'),
            ])
            ->orderByPivot('created_at', 'desc')
            ->paginate(25)
            ->withQueryString()
            ->through(fn (AlbumTrack $albumTrack): array => $albumTrackPayload->make($albumTrack, $request->user()));

        return Inertia::render('favorites/index', [
            'favoriteTracks' => $favoriteTracks,
            'playlists' => $request->user()
                ->playlists()
                ->withCount('albumTracks')
                ->latest()
                ->get(['id', 'name'])
                ->map(fn ($playlist): array => [
                    'id' => $playlist->id,
                    'name' => $playlist->name,
                    'tracks_count' => $playlist->album_tracks_count,
                ])
                ->values(),
        ]);
    }
}
