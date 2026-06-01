<?php

namespace App\Http\Controllers;

use App\Audio\WaveformGenerator;
use App\Models\AlbumTrack;
use Illuminate\Http\JsonResponse;

class TrackPeaksController extends Controller
{
    public function __invoke(AlbumTrack $albumTrack): JsonResponse
    {
        return response()
            ->json(['peaks' => app(WaveformGenerator::class)->peaksForTrack($albumTrack)])
            ->header('Cache-Control', 'private, max-age=86400');
    }
}
