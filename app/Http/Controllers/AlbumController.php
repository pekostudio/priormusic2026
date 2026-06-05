<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\AlbumTrack;
use App\Models\Genre;
use App\Models\Library;
use App\Support\AlbumCoverThumbnail;
use App\Support\AlbumTrackPayload;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;

class AlbumController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString();
        $libraryIds = collect(Arr::wrap($request->input('library_ids', $request->input('library_id', []))))
            ->map(fn (mixed $library): int => (int) $library)
            ->filter(fn (int $library): bool => $library > 0)
            ->unique()
            ->values()
            ->all();
        $styles = collect(Arr::wrap($request->input('styles', [])))
            ->map(fn (mixed $style): int => (int) $style)
            ->filter(fn (int $style): bool => $style > 0)
            ->unique()
            ->values()
            ->all();

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
            ->when($styles !== [], fn ($query) => $query->whereHas(
                'tracks.genreTags',
                fn ($query) => $query->whereKey($styles),
            ))
            ->when($libraryIds !== [], fn ($query) => $query->whereIn('library_id', $libraryIds))
            ->latest()
            ->paginate(200)
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
                'styles' => array_map('strval', $styles),
                'library_ids' => array_map('strval', $libraryIds),
            ],
            'filterOptions' => [
                'styles' => Genre::query()
                    ->orderBy('name')
                    ->get(['id', 'name'])
                    ->map(fn (Genre $genre): array => [
                        'value' => (string) $genre->id,
                        'label' => $genre->name,
                    ])
                    ->values()
                    ->all(),
                'libraries' => Library::query()
                    ->orderBy('name')
                    ->get(['id', 'name'])
                    ->map(fn (Library $library): array => [
                        'value' => (string) $library->id,
                        'label' => $library->name,
                    ])
                    ->values()
                    ->all(),
            ],
        ]);
    }

    public function show(Album $album, Request $request, AlbumTrackPayload $albumTrackPayload): Response
    {
        $libraryIds = collect(Arr::wrap($request->input('library_ids', $request->input('library_id', []))))
            ->map(fn (mixed $library): int => (int) $library)
            ->filter(fn (int $library): bool => $library > 0)
            ->unique()
            ->values()
            ->all();
        $styles = collect(Arr::wrap($request->input('styles', [])))
            ->map(fn (mixed $style): int => (int) $style)
            ->filter(fn (int $style): bool => $style > 0)
            ->unique()
            ->values()
            ->all();

        $album->load([
            'library',
            'albumTracks' => fn ($query) => $query
                ->with(['tracks' => fn ($query) => $query->with('genreTags')->orderBy('track_number')->orderBy('name')])
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
                'library_id' => $album->library_id,
                'library' => $album->library?->name,
                'tracks' => $album->albumTracks
                    ->map(fn (AlbumTrack $albumTrack): array => $albumTrackPayload->make($albumTrack, $request->user()))
                    ->values(),
            ],
            'filters' => [
                'styles' => array_map('strval', $styles),
                'library_ids' => array_map('strval', $libraryIds),
            ],
            'filterOptions' => [
                'libraries' => Library::query()
                    ->orderBy('name')
                    ->get(['id', 'name'])
                    ->map(fn (Library $library): array => [
                        'value' => (string) $library->id,
                        'label' => $library->name,
                    ])
                    ->values()
                    ->all(),
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
