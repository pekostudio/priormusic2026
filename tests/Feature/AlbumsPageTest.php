<?php

use App\Models\Album;
use App\Models\AlbumTrack;
use App\Models\Library;
use App\Models\Track;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    File::deleteDirectory(public_path('audio/albums-page-test'));
});

afterEach(function () {
    File::deleteDirectory(public_path('audio/albums-page-test'));
});

test('guests are redirected from albums pages', function () {
    $album = Album::factory()->create();

    $this->get(route('albums.index'))->assertRedirect(route('login'));
    $this->get(route('albums.show', $album))->assertRedirect(route('login'));
});

test('authenticated users can browse albums', function () {
    $library = Library::factory()->create(['name' => 'Prior Library']);
    $album = Album::factory()->for($library)->create([
        'displaytitle' => 'Bright Album',
        'name' => 'Bright Album',
        'code' => 'BR001',
        'cover' => null,
    ]);
    AlbumTrack::factory()->count(2)->for($album)->create();
    Album::factory()->create(['name' => 'Hidden Album', 'displaytitle' => 'Hidden Album']);

    $response = $this
        ->actingAs(User::factory()->create())
        ->get(route('albums.index', ['search' => 'Bright']));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('albums/index')
            ->where('filters.search', 'Bright')
            ->where('albums.total', 1)
            ->where('albums.data.0.title', 'Bright Album')
            ->where('albums.data.0.library', 'Prior Library')
            ->where('albums.data.0.tracks_count', 2));
});

test('authenticated users can view an album with playable tracks', function () {
    File::ensureDirectoryExists(public_path('audio/albums-page-test'));
    File::put(public_path('audio/albums-page-test/song.mp3'), 'fake mp3 bytes');

    $album = Album::factory()->create([
        'displaytitle' => 'Focused Album',
        'name' => 'Focused Album',
        'cover' => null,
    ]);
    $albumTrack = AlbumTrack::factory()->for($album)->create([
        'name' => 'Track Shell',
        'local_file_path' => 'audio/albums-page-test/song.mp3',
        'key' => 'audio/albums-page-test/song.mp3',
    ]);
    Track::factory()->for($album)->for($albumTrack, 'albumTrack')->create([
        'name' => 'Playable Track',
        'display_title' => 'Playable Track Display',
        'composer' => 'Composer Name',
        'keywords' => 'bright; confident',
        'bpm' => 0,
        'lenght_seconds' => 125,
        'time' => '02:05',
    ]);

    $user = User::factory()->create();
    $user->favoriteAlbumTracks()->attach($albumTrack);

    $response = $this
        ->actingAs($user)
        ->get(route('albums.show', $album));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('albums/show')
            ->where('album.title', 'Focused Album')
            ->where('album.tracks.0.title', 'Playable Track Display')
            ->where('album.tracks.0.artist', 'Composer Name')
            ->where('album.tracks.0.bpm', null)
            ->where('album.tracks.0.keywords.0', 'bright')
            ->where('album.tracks.0.keywords.1', 'confident')
            ->where('album.tracks.0.is_favorite', true)
            ->has('album.tracks.0.audio_url'));
});
