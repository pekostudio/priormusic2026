<?php

use App\Audio\WaveformGenerator;
use App\Models\AlbumTrack;
use App\Support\AlbumCoverThumbnail;
use App\Support\PublicAudio;
use Illuminate\Support\Facades\File;

test('public audio resolves relative paths to the audio browser path', function () {
    File::ensureDirectoryExists(public_path('audio/public-audio-test'));
    File::put(public_path('audio/public-audio-test/song.mp3'), 'fake mp3 bytes');

    try {
        expect(PublicAudio::browserPath('public-audio-test/song.mp3'))
            ->toBe('audio/public-audio-test/song.mp3')
            ->and(PublicAudio::path('public-audio-test/song.mp3'))
            ->toBe(public_path('audio/public-audio-test/song.mp3'));
    } finally {
        File::deleteDirectory(public_path('audio/public-audio-test'));
    }
});

test('waveform generator exposes audio urls through the public audio path', function () {
    File::ensureDirectoryExists(public_path('audio/public-audio-test'));
    File::put(public_path('audio/public-audio-test/song.mp3'), 'fake mp3 bytes');

    $track = AlbumTrack::factory()->create([
        'local_file_path' => 'public-audio-test/song.mp3',
    ]);

    try {
        expect(app(WaveformGenerator::class)->audioUrl($track))
            ->toBe(asset('audio/public-audio-test/song.mp3'));
    } finally {
        File::deleteDirectory(public_path('audio/public-audio-test'));
    }
});

test('album cover urls do not expose absolute filesystem paths', function () {
    File::ensureDirectoryExists(public_path('audio/public-audio-test'));
    File::put(public_path('audio/public-audio-test/cover.jpg'), 'not-an-image');

    try {
        expect(AlbumCoverThumbnail::url(public_path('audio/public-audio-test/cover.jpg')))
            ->toBe(asset('audio/public-audio-test/cover.jpg'));
    } finally {
        File::deleteDirectory(public_path('audio/public-audio-test'));
    }
});
