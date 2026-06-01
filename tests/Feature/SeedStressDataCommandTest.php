<?php

use App\Models\Album;
use App\Models\AlbumTrack;
use App\Models\Genre;
use App\Models\Keyword;
use App\Models\Library;
use App\Models\Track;
use Illuminate\Support\Facades\DB;

test('it seeds a local stress catalog at the requested scale', function () {
    $this->artisan('app:seed-stress-data', [
        '--albums' => 3,
        '--tracks' => 7,
        '--libraries' => 2,
        '--genres' => 2,
        '--keywords' => 3,
        '--chunk' => 3,
        '--fresh' => true,
    ])->assertSuccessful();

    expect(Library::count())->toBe(2)
        ->and(Album::count())->toBe(3)
        ->and(AlbumTrack::count())->toBe(7)
        ->and(Track::count())->toBe(7)
        ->and(Genre::count())->toBe(2)
        ->and(Keyword::count())->toBe(3)
        ->and(DB::table('genre_track')->count())->toBe(7)
        ->and(DB::table('keyword_track')->count())->toBe(7);

    $track = Track::query()->with(['album', 'albumTrack', 'genreTags', 'keywordTags'])->firstOrFail();

    expect($track->album)->not->toBeNull()
        ->and($track->albumTrack)->not->toBeNull()
        ->and($track->genreTags)->toHaveCount(1)
        ->and($track->keywordTags)->toHaveCount(1)
        ->and($track->source_metadata['seed'])->toBe('stress-test');
});
