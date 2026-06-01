<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\AlbumTrack;
use App\Support\AlbumCoverThumbnail;
use App\Support\AlbumTrackPayload;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AlbumController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString();

        $albums = Album::query()
            ->with('library')
            ->withCount('albumTracks')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('displaytitle', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhereHas('library', fn ($query) => $query->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(100)
            ->withQueryString()
            ->through(fn (Album $album): array => [
                'id' => $album->id,
                'title' => $album->displaytitle ?: $album->name,
                'name' => $album->name,
                'code' => $album->code,
                'detail' => $album->detail,
                'cover_url' => AlbumCoverThumbnail::url($album->cover),
                'tracks_count' => $album->album_tracks_count,
                'library' => $album->library?->name,
                'show_url' => route('albums.show', $album),
            ]);

        return Inertia::render('albums/index', [
            'albums' => $albums,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function show(Album $album, Request $request, AlbumTrackPayload $albumTrackPayload): Response
    {
        $album->load([
            'library',
            'albumTracks' => fn ($query) => $query
                ->with(['tracks' => fn ($query) => $query->orderBy('track_number')->orderBy('name')])
                ->orderBy('track_number')
                ->orderBy('name'),
        ]);

        return Inertia::render('albums/show', [
            'album' => [
                'id' => $album->id,
                'title' => $album->displaytitle ?: $album->name,
                'name' => $album->name,
                'code' => $album->code,
                'detail' => $album->detail,
                'releasedate' => $album->releasedate?->toDateString(),
                'cover_url' => AlbumCoverThumbnail::url($album->cover),
                'library' => $album->library?->name,
                'tracks' => $album->albumTracks
                    ->map(fn (AlbumTrack $albumTrack): array => $albumTrackPayload->make($albumTrack, $request->user()))
                    ->values(),
            ],
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
