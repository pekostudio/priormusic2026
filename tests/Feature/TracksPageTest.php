<?php

use App\Models\Album;
use App\Models\AlbumTrack;
use App\Models\Genre;
use App\Models\Keyword;
use App\Models\Library;
use App\Models\MusicUsageEvent;
use App\Models\Playlist;
use App\Models\Track;
use App\Models\TrackDownload;
use App\Models\TrackPlay;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    File::deleteDirectory(public_path('audio/tracks-page-test'));
});

afterEach(function () {
    File::deleteDirectory(public_path('audio/tracks-page-test'));
});

test('guests are redirected from tracks page', function () {
    $this->get(route('tracks'))->assertRedirect(route('login'));
});

test('authenticated users can view tracks page with custom filters', function () {
    File::ensureDirectoryExists(public_path('audio/tracks-page-test'));
    File::put(public_path('audio/tracks-page-test/song.mp3'), 'fake mp3 bytes');

    $library = Library::factory()->create(['name' => 'Prior Library']);
    $album = Album::factory()->for($library)->create([
        'displaytitle' => 'Prior Album',
        'cover' => null,
    ]);
    $albumTrack = AlbumTrack::factory()->for($album)->create([
        'name' => 'Bright Song',
        'local_file_path' => 'audio/tracks-page-test/song.mp3',
        'key' => 'audio/tracks-page-test/song.mp3',
    ]);

    $genre = Genre::factory()->create([
        'name' => 'Corporate',
        'slug' => 'corporate',
    ]);
    $keyword = Keyword::factory()->create([
        'name' => 'Bright',
        'slug' => 'bright',
    ]);

    $track = Track::factory()->for($album)->for($albumTrack, 'albumTrack')->create([
        'name' => 'Bright Song',
        'display_title' => 'Bright Song Display',
        'genre' => 'Corporate',
        'bpm' => 118,
        'keywords' => 'bright, opener',
    ]);
    $track->genreTags()->attach($genre);
    $track->keywordTags()->attach($keyword);

    $user = User::factory()->create();
    $user->favoriteAlbumTracks()->attach($albumTrack);
    $playlist = Playlist::factory()->for($user)->create([
        'name' => 'Campaign shortlist',
    ]);
    $playlist->albumTracks()->attach($albumTrack);

    Track::factory()->create([
        'name' => 'Other Song',
        'genre' => 'Corporate',
        'bpm' => 90,
    ]);

    Model::preventLazyLoading();

    try {
        $response = $this
            ->actingAs($user)
            ->get(route('tracks', [
                'search' => 'Bright',
                'genre_id' => $genre->id,
                'keyword_id' => $keyword->id,
                'bpm_min' => 100,
                'bpm_max' => 130,
            ]));
    } finally {
        Model::preventLazyLoading(false);
    }

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('tracks/index')
            ->where('filters.search', 'Bright')
            ->where('filters.genre_id', $genre->id)
            ->where('filters.keyword_id', $keyword->id)
            ->where('filters.bpm_min', 100)
            ->where('filters.bpm_max', 130)
            ->where('tracks.total', 1)
            ->where('tracks.data.0.title', 'Bright Song Display')
            ->where('tracks.data.0.album.title', 'Prior Album')
            ->where('tracks.data.0.album.library_id', $library->id)
            ->where('tracks.data.0.genre_tag.id', $genre->id)
            ->where('tracks.data.0.genre_tag.name', 'Corporate')
            ->where('tracks.data.0.album_track.is_favorite', true)
            ->where('tracks.data.0.album_track.playlist_ids', [$playlist->id])
            ->has('tracks.data.0.album_track.audio_url')
            ->where('filterOptions.genres.0.name', 'Corporate')
            ->where('filterOptions.keywords.0.name', 'Bright'));
});

test('track play endpoint records play history', function () {
    $user = User::factory()->create();
    $album = Album::factory()->create([
        'displaytitle' => 'Report Album',
    ]);
    $albumTrack = AlbumTrack::factory()->for($album)->create([
        'name' => 'Report Track',
    ]);

    $this
        ->actingAs($user)
        ->post(route('tracks.plays.store', $albumTrack), [
            'duration_seconds' => 42,
        ])
        ->assertNoContent();

    $play = TrackPlay::query()->sole();

    expect($play->user_id)->toBe($user->id)
        ->and($play->album_track_id)->toBe($albumTrack->id)
        ->and($play->duration_seconds)->toBe(42);

    $usageEvent = MusicUsageEvent::query()->sole();

    expect($usageEvent->user_id)->toBe($user->id)
        ->and($usageEvent->album_track_id)->toBe($albumTrack->id)
        ->and($usageEvent->event_type)->toBe(MusicUsageEvent::TypeListened)
        ->and($usageEvent->duration_seconds)->toBe(42)
        ->and($usageEvent->track_title)->toBe('Report Track')
        ->and($usageEvent->album_title)->toBe('Report Album')
        ->and($usageEvent->metadata)->toBe([
            'track_play_id' => $play->id,
        ]);
});

test('track download endpoint records download usage event', function () {
    File::ensureDirectoryExists(public_path('audio/tracks-page-test'));
    File::put(public_path('audio/tracks-page-test/download.mp3'), 'fake mp3 bytes');

    $user = User::factory()->create();
    $album = Album::factory()->create([
        'displaytitle' => 'Download Album',
    ]);
    $albumTrack = AlbumTrack::factory()->for($album)->create([
        'name' => 'Download Track',
        'file_name' => 'Original Download.mp3',
        'local_file_path' => 'audio/tracks-page-test/download.mp3',
        'key' => 'audio/tracks-page-test/download.mp3',
    ]);

    $this
        ->actingAs($user)
        ->get(route('tracks.download', $albumTrack))
        ->assertDownload('original-download.mp3');

    $download = TrackDownload::query()->sole();

    expect($download->user_id)->toBe($user->id)
        ->and($download->album_track_id)->toBe($albumTrack->id);

    $usageEvent = MusicUsageEvent::query()->sole();

    expect($usageEvent->user_id)->toBe($user->id)
        ->and($usageEvent->album_track_id)->toBe($albumTrack->id)
        ->and($usageEvent->event_type)->toBe(MusicUsageEvent::TypeDownloaded)
        ->and($usageEvent->track_title)->toBe('Download Track')
        ->and($usageEvent->album_title)->toBe('Download Album')
        ->and($usageEvent->metadata)->toBe([
            'track_download_id' => $download->id,
            'file_name' => 'original-download.mp3',
        ]);
});

test('tracks page returns null for unknown zero bpm values', function () {
    $track = Track::factory()->create([
        'bpm' => 0,
    ]);

    $response = $this
        ->actingAs(User::factory()->create())
        ->get(route('tracks'));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('tracks/index')
            ->where('tracks.data.0.id', $track->id)
            ->where('tracks.data.0.bpm', null));
});
