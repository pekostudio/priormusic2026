<?php

use App\Models\Album;
use App\Models\AlbumTrack;
use App\Models\Track;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    File::deleteDirectory(public_path('audio/favorites-page-test'));
});

afterEach(function () {
    File::deleteDirectory(public_path('audio/favorites-page-test'));
});

test('guests are redirected from favorites routes', function () {
    $albumTrack = AlbumTrack::factory()->create();

    $this->get(route('favorites'))->assertRedirect(route('login'));
    $this->post(route('tracks.favorite.store', $albumTrack))->assertRedirect(route('login'));
    $this->delete(route('tracks.favorite.destroy', $albumTrack))->assertRedirect(route('login'));
});

test('authenticated users can favorite and unfavorite album tracks', function () {
    $user = User::factory()->create();
    $albumTrack = AlbumTrack::factory()->create();

    $this
        ->actingAs($user)
        ->post(route('tracks.favorite.store', $albumTrack))
        ->assertNoContent();

    $this->assertDatabaseHas('favorite_tracks', [
        'user_id' => $user->id,
        'album_track_id' => $albumTrack->id,
    ]);

    $this
        ->actingAs($user)
        ->delete(route('tracks.favorite.destroy', $albumTrack))
        ->assertNoContent();

    $this->assertDatabaseMissing('favorite_tracks', [
        'user_id' => $user->id,
        'album_track_id' => $albumTrack->id,
    ]);
});

test('favorites page only shows current user favorite tracks', function () {
    File::ensureDirectoryExists(public_path('audio/favorites-page-test'));
    File::put(public_path('audio/favorites-page-test/song.mp3'), 'fake mp3 bytes');

    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $album = Album::factory()->create(['displaytitle' => 'Atmospherics']);
    $favoriteTrack = AlbumTrack::factory()->for($album)->create([
        'name' => 'Favorite Shell',
        'local_file_path' => 'audio/favorites-page-test/song.mp3',
        'key' => 'audio/favorites-page-test/song.mp3',
    ]);
    $otherTrack = AlbumTrack::factory()->create(['name' => 'Not Mine']);

    Track::factory()->for($album)->for($favoriteTrack, 'albumTrack')->create([
        'name' => 'On Safari Main',
        'display_title' => 'On Safari',
        'composer' => 'Garry Judd',
        'time' => '02:38',
        'lenght_seconds' => 158,
        'bpm' => 124,
    ]);

    $user->favoriteAlbumTracks()->attach($favoriteTrack);
    $otherUser->favoriteAlbumTracks()->attach($otherTrack);

    $response = $this
        ->actingAs($user)
        ->get(route('favorites'));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('favorites/index')
            ->where('favoriteTracks.total', 1)
            ->where('favoriteTracks.data.0.title', 'On Safari')
            ->where('favoriteTracks.data.0.artist', 'Garry Judd')
            ->where('favoriteTracks.data.0.album.title', 'Atmospherics')
            ->where('favoriteTracks.data.0.is_favorite', true)
            ->has('favoriteTracks.data.0.audio_url'));
});
