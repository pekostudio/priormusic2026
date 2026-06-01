<?php

use App\Models\Album;
use App\Models\AlbumTrack;
use App\Models\Playlist;
use App\Models\Track;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    File::deleteDirectory(public_path('audio/playlists-page-test'));
});

afterEach(function () {
    File::deleteDirectory(public_path('audio/playlists-page-test'));
});

test('guests are redirected from playlists routes', function () {
    $playlist = Playlist::factory()->create();
    $albumTrack = AlbumTrack::factory()->create();

    $this->get(route('playlists.index'))->assertRedirect(route('login'));
    $this->get(route('playlists.show', $playlist))->assertRedirect(route('login'));
    $this->post(route('playlists.store'), ['name' => 'Drama'])->assertRedirect(route('login'));
    $this->post(route('tracks.playlists.store', $albumTrack), ['playlist_id' => $playlist->id])->assertRedirect(route('login'));
});

test('authenticated users can create and list playlists', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    Playlist::factory()->for($otherUser)->create(['name' => 'Not mine']);

    $this
        ->actingAs($user)
        ->post(route('playlists.store'), ['name' => 'Drama cues'])
        ->assertRedirect();

    $this->assertDatabaseHas('playlists', [
        'user_id' => $user->id,
        'name' => 'Drama cues',
    ]);

    $this
        ->actingAs($user)
        ->get(route('playlists.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('playlists/index')
            ->where('playlists.0.name', 'Drama cues')
            ->where('playlists.0.tracks_count', 0));
});

test('authenticated users can view their playlist tracks', function () {
    File::ensureDirectoryExists(public_path('audio/playlists-page-test'));
    File::put(public_path('audio/playlists-page-test/song.mp3'), 'fake mp3 bytes');

    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $album = Album::factory()->create(['displaytitle' => 'Atmospherics']);
    $playlist = Playlist::factory()->for($user)->create(['name' => 'Drama cues']);
    $otherPlaylist = Playlist::factory()->for($otherUser)->create(['name' => 'Not mine']);
    $albumTrack = AlbumTrack::factory()->for($album)->create([
        'name' => 'Playlist Shell',
        'local_file_path' => 'audio/playlists-page-test/song.mp3',
        'key' => 'audio/playlists-page-test/song.mp3',
    ]);
    $otherTrack = AlbumTrack::factory()->create(['name' => 'Other user track']);

    Track::factory()->for($album)->for($albumTrack, 'albumTrack')->create([
        'name' => 'On Safari Main',
        'display_title' => 'On Safari',
        'composer' => 'Garry Judd',
        'time' => '02:38',
        'lenght_seconds' => 158,
        'bpm' => 124,
    ]);

    $playlist->albumTracks()->attach($albumTrack);
    $otherPlaylist->albumTracks()->attach($otherTrack);

    $this
        ->actingAs($user)
        ->get(route('playlists.show', $playlist))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('playlists/show')
            ->where('playlist.name', 'Drama cues')
            ->where('playlist.tracks.0.title', 'On Safari')
            ->where('playlist.tracks.0.artist', 'Garry Judd')
            ->where('playlist.tracks.0.album.title', 'Atmospherics')
            ->has('playlist.tracks.0.audio_url')
            ->has('playlist.tracks.0.remove_from_playlist_url'));
});

test('users can add and remove tracks from their playlists', function () {
    $user = User::factory()->create();
    $playlist = Playlist::factory()->for($user)->create();
    $albumTrack = AlbumTrack::factory()->create();

    $this
        ->actingAs($user)
        ->post(route('tracks.playlists.store', $albumTrack), [
            'playlist_id' => $playlist->id,
        ])
        ->assertNoContent();

    $this->assertDatabaseHas('playlist_tracks', [
        'playlist_id' => $playlist->id,
        'album_track_id' => $albumTrack->id,
    ]);

    $this
        ->actingAs($user)
        ->delete(route('playlists.tracks.destroy', [$playlist, $albumTrack]))
        ->assertNoContent();

    $this->assertDatabaseMissing('playlist_tracks', [
        'playlist_id' => $playlist->id,
        'album_track_id' => $albumTrack->id,
    ]);
});

test('users cannot manage another users playlist', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $playlist = Playlist::factory()->for($otherUser)->create();
    $albumTrack = AlbumTrack::factory()->create();

    $this
        ->actingAs($user)
        ->get(route('playlists.show', $playlist))
        ->assertNotFound();

    $this
        ->actingAs($user)
        ->post(route('tracks.playlists.store', $albumTrack), [
            'playlist_id' => $playlist->id,
        ])
        ->assertNotFound();

    $this->assertDatabaseMissing('playlist_tracks', [
        'playlist_id' => $playlist->id,
        'album_track_id' => $albumTrack->id,
    ]);
});
