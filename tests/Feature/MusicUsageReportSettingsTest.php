<?php

use App\Models\MusicUsageEvent;
use App\Models\MusicUsageReport;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

test('reports page displays usage stats and recent events', function () {
    $user = User::factory()->create();

    MusicUsageEvent::factory()->for($user)->create([
        'event_type' => MusicUsageEvent::TypeListened,
        'track_title' => 'Listened Track',
        'duration_seconds' => 90,
        'occurred_at' => now(),
    ]);
    MusicUsageEvent::factory()->for($user)->create([
        'event_type' => MusicUsageEvent::TypeDownloaded,
        'track_title' => 'Downloaded Track',
        'duration_seconds' => null,
        'occurred_at' => now()->subMinute(),
    ]);

    $this
        ->actingAs($user)
        ->get(route('reports.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/reports')
            ->where('stats.listened_count', 1)
            ->where('stats.downloaded_count', 1)
            ->where('stats.duration_seconds', 90)
            ->where('recentEvents.0.track_title', 'Listened Track'));
});

test('usage report can be generated and downloaded', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    MusicUsageEvent::factory()->for($user)->create([
        'event_type' => MusicUsageEvent::TypeListened,
        'track_title' => 'Licensed Track',
        'duration_seconds' => 120,
        'occurred_at' => '2026-05-10 10:00:00',
    ]);
    MusicUsageEvent::factory()->for($user)->create([
        'event_type' => MusicUsageEvent::TypeDownloaded,
        'track_title' => 'Downloaded Track',
        'occurred_at' => '2026-05-11 10:00:00',
    ]);
    MusicUsageEvent::factory()->for($user)->create([
        'event_type' => MusicUsageEvent::TypeDownloaded,
        'occurred_at' => '2026-04-01 10:00:00',
    ]);

    $this
        ->actingAs($user)
        ->post(route('reports.store'), [
            'starts_at' => '2026-05-01',
            'ends_at' => '2026-05-31',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('reports.index'));

    $report = MusicUsageReport::query()->sole();

    expect($report->listened_count)->toBe(1)
        ->and($report->downloaded_count)->toBe(1)
        ->and($report->duration_seconds)->toBe(120);

    Storage::disk('local')->assertExists($report->file_path);

    $this
        ->actingAs($user)
        ->get(route('reports.download', $report))
        ->assertDownload('music-usage-report-2026-05-01-2026-05-31.pdf');
});

test('users cannot download another users report', function () {
    Storage::fake('local');
    Storage::disk('local')->put('reports/private.pdf', '%PDF-1.4');

    $report = MusicUsageReport::factory()->create([
        'file_path' => 'reports/private.pdf',
    ]);

    $this
        ->actingAs(User::factory()->create())
        ->get(route('reports.download', $report))
        ->assertNotFound();
});
