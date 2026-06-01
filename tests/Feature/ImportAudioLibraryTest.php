<?php

use App\Actions\ImportAudioLibrary;
use App\Models\Album;
use App\Models\AlbumTrack;
use App\Models\Genre;
use App\Models\Keyword;
use App\Models\Library;
use App\Models\Track;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    File::deleteDirectory(public_path('audio/import-test'));
});

afterEach(function () {
    File::deleteDirectory(public_path('audio/import-test'));
});

test('it imports libraries albums tracks audio paths and cover metadata from metadata csv', function () {
    $basePath = public_path('audio/import-test');
    $albumPath = $basePath.'/Album One';
    $metadataPath = $albumPath.'/Album One_HM_STANDARD';

    File::ensureDirectoryExists($metadataPath);
    File::put($albumPath.'/Song One.mp3', 'fake mp3 bytes');
    File::put($metadataPath.'/albumart.jpg', 'fake image bytes');
    File::put($metadataPath.'/album_metadata.csv', implode("\n", [
        implode(',', [
            'LIBRARY: Name',
            'ALBUM: Code',
            'ALBUM: Title',
            'ALBUM: Display Title',
            'ALBUM: Description',
            'ALBUM: Release Date',
            'ALBUM: Artwork Filename',
            'TRACK: Number',
            'TRACK: Title',
            'TRACK: Display Title',
            'TRACK: Version',
            'TRACK: Duration',
            'TRACK: Genre',
            'TRACK: Tempo',
            'TRACK: BPM',
            'TRACK: Composer(s)',
            'TRACK: Publisher(s)',
            'TRACK: Instrumentation',
            'TRACK: Keywords',
            'TRACK: Audio Filename',
            'CODE: ISRC',
            'ATTRIBUTE: Mood',
        ]),
        implode(',', [
            'Prior Library',
            'PRIOR001',
            'Album One',
            'Album One Display',
            'Album description',
            '2026-05-01',
            'albumart.jpg',
            '1',
            'Song One',
            'Song One Display',
            'Main',
            '125',
            '"Pop, Dance"',
            'Medium',
            '120',
            'Composer Name',
            'Publisher Name',
            'Piano',
            'happy bright',
            'Song One.mp3',
            'ISRC123',
            'Bright',
        ]),
    ]));

    $summary = app(ImportAudioLibrary::class)($basePath);

    expect($summary)
        ->albums_processed->toBe(1)
        ->albums_skipped->toBe(0)
        ->tracks_processed->toBe(1)
        ->tracks_skipped->toBe(0)
        ->warnings->toBe([]);

    $library = Library::query()->sole();
    $album = Album::query()->sole();
    $albumTrack = AlbumTrack::query()->sole();
    $track = Track::query()->sole();
    $genres = Genre::query()->orderBy('name')->pluck('name')->all();
    $keyword = Keyword::query()->sole();

    expect($library->name)->toBe('Prior Library')
        ->and($album->library_id)->toBe($library->id)
        ->and($album->code)->toBe('PRIOR001')
        ->and($album->cover)->toBe('audio/import-test/Album One/Album One_HM_STANDARD/albumart.jpg')
        ->and($albumTrack->track_number)->toBe(1)
        ->and($albumTrack->local_file_path)->toBe('Album One/Song One.mp3')
        ->and($albumTrack->file_size)->toBe(strlen('fake mp3 bytes'))
        ->and($track->album_track_id)->toBe($albumTrack->id)
        ->and($track->name)->toBe('Song One')
        ->and($track->lenght_seconds)->toBe(125)
        ->and($track->time)->toBe('02:05')
        ->and($track->genre)->toBe('Pop, Dance')
        ->and($genres)->toBe(['Dance', 'Pop'])
        ->and($track->genreTags()->orderBy('name')->pluck('genres.name')->all())->toBe(['Dance', 'Pop'])
        ->and($keyword->name)->toBe('happy bright')
        ->and($track->keywordTags()->pluck('keywords.id')->all())->toBe([$keyword->id])
        ->and($track->source_metadata)->toHaveKey('ATTRIBUTE: Mood', 'Bright');
});

test('import command does not warm legacy track filter cache', function () {
    $basePath = public_path('audio/import-test');
    $albumPath = $basePath.'/Album One';
    $metadataPath = $albumPath.'/Album One_HM_STANDARD';

    File::ensureDirectoryExists($metadataPath);
    File::put($albumPath.'/Song One.mp3', 'fake mp3 bytes');
    File::put($metadataPath.'/album_metadata.csv', implode("\n", [
        'LIBRARY: Name,ALBUM: Code,ALBUM: Title,ALBUM: Display Title,ALBUM: Release Date,TRACK: Number,TRACK: Title,TRACK: Duration,TRACK: Audio Filename',
        'Prior Library,PRIOR001,Album One,Album One Display,2026-05-01,1,Song One,125,Song One.mp3',
    ]));

    $this->artisan('app:import-audio', ['path' => $basePath])
        ->expectsOutputToContain('Import complete.')
        ->doesntExpectOutputToContain('Track filter options cache warmed.')
        ->assertSuccessful();
});
