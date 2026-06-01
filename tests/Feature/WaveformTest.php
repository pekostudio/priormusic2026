<?php

use App\Audio\WaveformGenerator;
use App\Models\AlbumTrack;
use App\Models\User;
use Illuminate\Support\Facades\File;

test('waveform command stores waveform data for album tracks with audio files', function () {
    File::ensureDirectoryExists(public_path('audio/tests'));
    File::put(public_path('audio/tests/generated.mp3'), 'not-a-real-mp3');

    $track = AlbumTrack::factory()->create([
        'local_file_path' => 'tests/generated.mp3',
        'waveform_peaks' => null,
        'waveform_version' => null,
        'waveform_generated_at' => null,
    ]);

    try {
        $this->artisan('app:generate-waveforms')
            ->assertSuccessful();

        $track->refresh();

        expect($track->waveform_peaks)
            ->toBeArray()
            ->toHaveCount(200);

        expect($track->waveform_version)->toBe(WaveformGenerator::VERSION);
        expect($track->waveform_generated_at)->not->toBeNull();
    } finally {
        File::deleteDirectory(public_path('audio/tests'));
    }
});

test('waveform command skips current waveform data unless forced', function () {
    File::ensureDirectoryExists(public_path('audio/tests'));
    File::put(public_path('audio/tests/current.mp3'), 'not-a-real-mp3');

    $track = AlbumTrack::factory()->create([
        'local_file_path' => 'tests/current.mp3',
        'waveform_peaks' => [0.25, 0.5],
        'waveform_version' => WaveformGenerator::VERSION,
        'waveform_generated_at' => now(),
    ]);

    try {
        $this->artisan('app:generate-waveforms')
            ->assertSuccessful();

        expect($track->refresh()->waveform_peaks)->toBe([0.25, 0.5]);

        $this->artisan('app:generate-waveforms --force')
            ->assertSuccessful();

        expect($track->refresh()->waveform_peaks)
            ->toBeArray()
            ->toHaveCount(200);
    } finally {
        File::deleteDirectory(public_path('audio/tests'));
    }
});

test('waveform peaks endpoint returns stored peaks', function () {
    $track = AlbumTrack::factory()->create([
        'waveform_peaks' => [0.12, 0.34, 0.56],
        'waveform_version' => WaveformGenerator::VERSION,
    ]);

    $this
        ->actingAs(User::factory()->create())
        ->getJson(route('tracks.peaks', $track))
        ->assertOk()
        ->assertJson([
            'peaks' => [0.12, 0.34, 0.56],
        ]);
});
