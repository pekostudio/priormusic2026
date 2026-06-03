<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use App\Models\Keyword;
use App\Models\Track;
use App\Support\AlbumCoverThumbnail;
use App\Support\AlbumTrackPayload;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TrackController extends Controller
{
    public function index(Request $request, AlbumTrackPayload $albumTrackPayload): Response
    {
        $user = $request->user();

        $filters = [
            'search' => $request->string('search')->trim()->toString(),
            'genre_id' => $request->integer('genre_id') > 0 ? $request->integer('genre_id') : null,
            'keyword_id' => $request->integer('keyword_id') > 0 ? $request->integer('keyword_id') : null,
            'bpm_min' => $request->integer('bpm_min') > 0 ? $request->integer('bpm_min') : null,
            'bpm_max' => $request->integer('bpm_max') > 0 ? $request->integer('bpm_max') : null,
        ];

        $tracks = Track::query()
            ->with([
                'album.library',
                'albumTrack' => fn ($query) => $query->with([
                    'album.library',
                    'tracks' => fn ($query) => $query->orderBy('track_number')->orderBy('name'),
                    'favoredByUsers' => fn ($query) => $query->whereKey($user->id),
                    'playlists' => fn ($query) => $query->where('user_id', $user->id),
                ]),
            ])
            ->when($filters['search'] !== '', function ($query) use ($filters) {
                $query->where(function ($query) use ($filters) {
                    $query
                        ->where('name', 'like', "%{$filters['search']}%")
                        ->orWhere('display_title', 'like', "%{$filters['search']}%")
                        ->orWhere('keywords', 'like', "%{$filters['search']}%")
                        ->orWhere('composer', 'like', "%{$filters['search']}%")
                        ->orWhere('publisher', 'like', "%{$filters['search']}%");
                });
            })
            ->when($filters['genre_id'] !== null, fn ($query) => $query->whereHas(
                'genreTags',
                fn ($query) => $query->whereKey($filters['genre_id']),
            ))
            ->when($filters['keyword_id'] !== null, fn ($query) => $query->whereHas(
                'keywordTags',
                fn ($query) => $query->whereKey($filters['keyword_id']),
            ))
            ->when($filters['bpm_min'] !== null, fn ($query) => $query->where('bpm', '>=', $filters['bpm_min']))
            ->when($filters['bpm_max'] !== null, fn ($query) => $query->where('bpm', '<=', $filters['bpm_max']))
            ->orderByDesc('id')
            ->paginate(100)
            ->withQueryString()
            ->through(fn (Track $track): array => [
                'id' => $track->id,
                'title' => $track->display_title ?: $track->name,
                'name' => $track->name,
                'version' => $track->version,
                'genre' => $track->genre,
                'time' => $track->time,
                'bpm' => $track->bpm > 0 ? $track->bpm : null,
                'keywords' => $track->keywords,
                'composer' => $track->composer,
                'publisher' => $track->publisher,
                'cover_url' => AlbumCoverThumbnail::url($track->cover ?: $track->album?->cover),
                'album' => $track->album !== null ? [
                    'id' => $track->album->id,
                    'title' => $track->album->displaytitle ?: $track->album->name,
                    'code' => $track->album->code,
                    'library' => $track->album->library?->name,
                ] : null,
                'album_track' => $track->albumTrack !== null
                    ? $albumTrackPayload->make($track->albumTrack, $user)
                    : null,
            ]);

        return Inertia::render('tracks/index', [
            'tracks' => $tracks,
            'filters' => $filters,
            'playlists' => $user
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
            'filterOptions' => [
                'genres' => Genre::query()
                    ->orderBy('name')
                    ->get(['id', 'name'])
                    ->map(fn (Genre $genre): array => [
                        'id' => $genre->id,
                        'name' => $genre->name,
                    ])
                    ->values(),
                'keywords' => Keyword::query()
                    ->orderBy('name')
                    ->get(['id', 'name'])
                    ->map(fn (Keyword $keyword): array => [
                        'id' => $keyword->id,
                        'name' => $keyword->name,
                    ])
                    ->values(),
            ],
        ]);
    }
}
