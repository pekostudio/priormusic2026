<?php

use App\Http\Controllers\AlbumController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\FavoriteTrackController;
use App\Http\Controllers\PlaylistController;
use App\Http\Controllers\PlaylistTrackController;
use App\Http\Controllers\TrackController;
use App\Http\Controllers\TrackDownloadController;
use App\Http\Controllers\TrackPeaksController;
use App\Http\Controllers\TrackPlayController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/tracks')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('albums', [AlbumController::class, 'index'])->name('albums.index');
    Route::get('albums/{album}', [AlbumController::class, 'show'])->name('albums.show');
    Route::get('favorites', [FavoriteController::class, 'index'])->name('favorites');
    Route::get('playlists', [PlaylistController::class, 'index'])->name('playlists.index');
    Route::post('playlists', [PlaylistController::class, 'store'])->name('playlists.store');
    Route::get('playlists/{playlist}', [PlaylistController::class, 'show'])->name('playlists.show');
    Route::delete('playlists/{playlist}', [PlaylistController::class, 'destroy'])->name('playlists.destroy');
    Route::delete('playlists/{playlist}/tracks/{albumTrack}', [PlaylistTrackController::class, 'destroy'])->name('playlists.tracks.destroy');
    Route::get('tracks', [TrackController::class, 'index'])->name('tracks');
    Route::get('tracks/{albumTrack}/download', TrackDownloadController::class)->name('tracks.download');
    Route::get('tracks/{albumTrack}/peaks', TrackPeaksController::class)->name('tracks.peaks');
    Route::post('tracks/{albumTrack}/plays', TrackPlayController::class)->name('tracks.plays.store');
    Route::post('tracks/{albumTrack}/favorite', [FavoriteTrackController::class, 'store'])->name('tracks.favorite.store');
    Route::delete('tracks/{albumTrack}/favorite', [FavoriteTrackController::class, 'destroy'])->name('tracks.favorite.destroy');
    Route::post('tracks/{albumTrack}/playlists', [PlaylistTrackController::class, 'store'])->name('tracks.playlists.store');
});

require __DIR__.'/settings.php';
