<?php

use App\Models\Album;
use App\Models\AlbumTrack;
use App\Models\Genre;
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

test('authenticated users can filter albums by styles', function () {
    $classical = Genre::factory()->create([
        'name' => 'Classical',
        'slug' => 'classical',
    ]);
    $drama = Genre::factory()->create([
        'name' => 'Drama',
        'slug' => 'drama',
    ]);

    $classicalAlbum = Album::factory()->create([
        'displaytitle' => 'Classical Album',
        'name' => 'Classical Album',
    ]);
    $classicalTrack = Track::factory()->for($classicalAlbum)->create([
        'genre' => 'Classical, chamber',
    ]);
    $classicalTrack->genreTags()->attach($classical);

    $dramaAlbum = Album::factory()->create([
        'displaytitle' => 'Drama Album',
        'name' => 'Drama Album',
    ]);
    $dramaTrack = Track::factory()->for($dramaAlbum)->create([
        'genre' => 'Drama',
    ]);
    $dramaTrack->genreTags()->attach($drama);

    $response = $this
        ->actingAs(User::factory()->create())
        ->get(route('albums.index', ['styles' => [(string) $classical->id]]));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('albums/index')
            ->where('filters.styles.0', (string) $classical->id)
            ->where('filterOptions.styles.0.value', (string) $classical->id)
            ->where('filterOptions.styles.0.label', 'Classical')
            ->where('filterOptions.styles.1.value', (string) $drama->id)
            ->where('filterOptions.styles.1.label', 'Drama')
            ->where('albums.total', 1)
            ->where('albums.data.0.title', 'Classical Album'));
});

test('authenticated users can filter albums by libraries', function () {
    $targetLibrary = Library::factory()->create(['name' => 'MediaTracks Classics']);
    $secondLibrary = Library::factory()->create(['name' => 'Prior Library']);
    $otherLibrary = Library::factory()->create(['name' => 'Zulu Library']);

    Album::factory()->for($targetLibrary)->create([
        'displaytitle' => 'Classics Album',
        'name' => 'Classics Album',
        'created_at' => now()->addMinute(),
    ]);
    Album::factory()->for($secondLibrary)->create([
        'displaytitle' => 'Prior Album',
        'name' => 'Prior Album',
        'created_at' => now(),
    ]);
    Album::factory()->for($otherLibrary)->create([
        'displaytitle' => 'Hidden Album',
        'name' => 'Hidden Album',
    ]);

    $response = $this
        ->actingAs(User::factory()->create())
        ->get(route('albums.index', [
            'library_ids' => [
                (string) $targetLibrary->id,
                (string) $secondLibrary->id,
            ],
        ]));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('albums/index')
            ->where('filters.library_ids.0', (string) $targetLibrary->id)
            ->where('filters.library_ids.1', (string) $secondLibrary->id)
            ->where('filterOptions.libraries.0.value', (string) $targetLibrary->id)
            ->where('filterOptions.libraries.0.label', 'MediaTracks Classics')
            ->where('filterOptions.libraries.1.value', (string) $secondLibrary->id)
            ->where('filterOptions.libraries.1.label', 'Prior Library')
            ->where('albums.total', 2)
            ->where('albums.data.0.title', 'Classics Album')
            ->where('albums.data.0.library', 'MediaTracks Classics')
            ->where('albums.data.1.title', 'Prior Album')
            ->where('albums.data.1.library', 'Prior Library'));
});

test('authenticated users can view an album with playable tracks', function () {
    File::ensureDirectoryExists(public_path('audio/albums-page-test'));
    File::put(public_path('audio/albums-page-test/song.mp3'), 'fake mp3 bytes');
    $classical = Genre::factory()->create([
        'name' => 'Classical',
        'slug' => 'classical-show',
    ]);
    $library = Library::factory()->create(['name' => 'MediaTracks Classics']);

    $album = Album::factory()->for($library)->create([
        'displaytitle' => 'Focused Album',
        'name' => 'Focused Album',
        'cover' => null,
    ]);
    $albumTrack = AlbumTrack::factory()->for($album)->create([
        'name' => 'Track Shell',
        'local_file_path' => 'audio/albums-page-test/song.mp3',
        'key' => 'audio/albums-page-test/song.mp3',
    ]);
    $track = Track::factory()->for($album)->for($albumTrack, 'albumTrack')->create([
        'name' => 'Playable Track',
        'display_title' => 'Playable Track Display',
        'composer' => 'Composer Name',
        'keywords' => 'bright; confident',
        'bpm' => 0,
        'lenght_seconds' => 125,
        'time' => '02:05',
    ]);
    $track->genreTags()->attach($classical);

    $user = User::factory()->create();
    $user->favoriteAlbumTracks()->attach($albumTrack);

    $response = $this
        ->actingAs($user)
        ->get(route('albums.show', [
            'album' => $album,
            'styles' => [(string) $classical->id],
            'library_ids' => [(string) $library->id],
        ]));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('albums/show')
            ->where('album.title', 'Focused Album')
            ->where('album.library_id', $library->id)
            ->where('album.tracks.0.title', 'Playable Track Display')
            ->where('album.tracks.0.artist', 'Composer Name')
            ->where('album.tracks.0.bpm', null)
            ->where('album.tracks.0.styles.0.id', $classical->id)
            ->where('album.tracks.0.styles.0.name', 'Classical')
            ->where('album.tracks.0.keywords.0', 'bright')
            ->where('album.tracks.0.keywords.1', 'confident')
            ->where('album.tracks.0.is_favorite', true)
            ->where('filters.styles.0', (string) $classical->id)
            ->where('filters.library_ids.0', (string) $library->id)
            ->where('filterOptions.libraries.0.value', (string) $library->id)
            ->where('filterOptions.libraries.0.label', 'MediaTracks Classics')
            ->has('album.tracks.0.audio_url'));
});
