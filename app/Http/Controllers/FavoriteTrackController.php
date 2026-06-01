<?php

namespace App\Http\Controllers;

use App\Models\AlbumTrack;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FavoriteTrackController extends Controller
{
    public function store(AlbumTrack $albumTrack, Request $request): Response
    {
        $request->user()->favoriteAlbumTracks()->syncWithoutDetaching([$albumTrack->id]);

        return response()->noContent();
    }

    public function destroy(AlbumTrack $albumTrack, Request $request): Response
    {
        $request->user()->favoriteAlbumTracks()->detach($albumTrack->id);

        return response()->noContent();
    }
}
