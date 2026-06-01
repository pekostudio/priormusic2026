<?php

namespace App\Http\Controllers;

use App\Models\Playlist;
use App\Support\AlbumTrackPayload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PlaylistController extends Controller
{
    public function index(Request $request): Response
    {
        $playlists = $request->user()
            ->playlists()
            ->withCount('albumTracks')
            ->latest()
            ->get()
            ->map(fn (Playlist $playlist): array => [
                'id' => $playlist->id,
                'name' => $playlist->name,
                'tracks_count' => $playlist->album_tracks_count,
                'show_url' => route('playlists.show', $playlist),
                'delete_url' => route('playlists.destroy', $playlist),
            ])
            ->values();

        return Inertia::render('playlists/index', [
            'playlists' => $playlists,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('playlists', 'name')->where('user_id', Auth::id()),
            ],
        ]);

        $request->user()->playlists()->create($validated);

        return back();
    }

    public function show(Playlist $playlist, Request $request, AlbumTrackPayload $albumTrackPayload): Response
    {
        abort_unless($playlist->user()->is($request->user()), 404);

        $playlist->load([
            'albumTracks' => fn ($query) => $query
                ->with([
                    'album.library',
                    'tracks' => fn ($query) => $query->orderBy('track_number')->orderBy('name'),
                ])
                ->orderByPivot('created_at', 'desc'),
        ]);

        return Inertia::render('playlists/show', [
            'playlist' => [
                'id' => $playlist->id,
                'name' => $playlist->name,
                'tracks_count' => $playlist->albumTracks->count(),
                'delete_url' => route('playlists.destroy', $playlist),
                'tracks' => $playlist->albumTracks
                    ->map(fn ($albumTrack): array => array_merge(
                        $albumTrackPayload->make($albumTrack, $request->user()),
                        ['remove_from_playlist_url' => route('playlists.tracks.destroy', [$playlist, $albumTrack])],
                    ))
                    ->values(),
            ],
        ]);
    }

    public function destroy(Playlist $playlist, Request $request)
    {
        abort_unless($playlist->user()->is($request->user()), 404);

        $playlist->delete();

        return to_route('playlists.index');
    }
}
