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
            ->where('recentEvents.data.0.track_title', 'Listened Track'));
});

test('recent activity is paginated at twenty events per page', function () {
    $user = User::factory()->create();

    foreach (range(1, 21) as $index) {
        MusicUsageEvent::factory()->for($user)->create([
            'event_type' => MusicUsageEvent::TypeListened,
            'track_title' => "Activity {$index}",
            'occurred_at' => now()->subMinutes(21 - $index),
        ]);
    }

    $this
        ->actingAs($user)
        ->get(route('reports.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/reports')
            ->has('recentEvents.data', 20)
            ->where('recentEvents.total', 21)
            ->where('recentEvents.per_page', 20)
            ->where('recentEvents.data.0.track_title', 'Activity 21'));
});

test('usage report can be generated and downloaded', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    MusicUsageEvent::factory()->for($user)->create([
        'event_type' => MusicUsageEvent::TypeListened,
        'track_title' => 'Licensed Track',
        'album_title' => 'Report Album',
        'duration_seconds' => 120,
        'occurred_at' => '2026-05-10 10:00:00',
        'metadata' => [
            'performer' => 'Simone Morbidelli',
            'composer' => 'Simone Morbidelli',
            'catalog' => 'OSCD-038',
            'label' => 'Opensound',
            'broadcast_count' => 2,
            'usage_type' => 'foninė',
        ],
    ]);
    MusicUsageEvent::factory()->for($user)->create([
        'event_type' => MusicUsageEvent::TypeDownloaded,
        'track_title' => 'Downloaded Track',
        'occurred_at' => '2026-05-11 10:00:00',
    ]);
    MusicUsageEvent::factory()->for($user)->create([
        'event_type' => MusicUsageEvent::TypeDownloaded,
        'track_title' => 'Outside Range Track',
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

    $pdf = Storage::disk('local')->get($report->file_path);

    expect($pdf)->toContain('/MediaBox [0 0 842 595]')
        ->and($pdf)->toContain(' re S')
        ->and($pdf)->toContain('FONOGRAMU AR JU KOPIJU PANAUDOJIMO ATASKAITA')
        ->and($pdf)->toContain('PASTABA: Butina uzpildyti visus lenteles laukus!')
        ->and($pdf)->toContain('Licensed Track')
        ->and($pdf)->not->toContain('Outside Range Track')
        ->and($pdf)->toContain('Simone Morbidelli')
        ->and($pdf)->toContain('Opensound')
        ->and($pdf)->toContain('fonine');

    $this
        ->actingAs($user)
        ->get(route('reports.download', $report))
        ->assertDownload('music-usage-report-2026-05-01-2026-05-31.pdf');
});

test('usage reports can be deleted by their owner', function () {
    Storage::fake('local');
    Storage::disk('local')->put('reports/deletable.pdf', '%PDF-1.4');

    $user = User::factory()->create();
    $report = MusicUsageReport::factory()->for($user)->create([
        'file_path' => 'reports/deletable.pdf',
    ]);

    $this
        ->actingAs($user)
        ->delete(route('reports.destroy', $report))
        ->assertRedirect(route('reports.index'));

    expect($report->fresh())->toBeNull();

    Storage::disk('local')->assertMissing('reports/deletable.pdf');
});

test('usage reports can be deleted through a spoofed post request', function () {
    Storage::fake('local');
    Storage::disk('local')->put('reports/spoofed.pdf', '%PDF-1.4');

    $user = User::factory()->create();
    $report = MusicUsageReport::factory()->for($user)->create([
        'file_path' => 'reports/spoofed.pdf',
    ]);

    $this
        ->actingAs($user)
        ->post(route('reports.destroy', $report), [
            '_method' => 'DELETE',
        ])
        ->assertRedirect(route('reports.index'));

    expect($report->fresh())->toBeNull();

    Storage::disk('local')->assertMissing('reports/spoofed.pdf');
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

test('users cannot delete another users report', function () {
    Storage::fake('local');
    Storage::disk('local')->put('reports/private.pdf', '%PDF-1.4');

    $report = MusicUsageReport::factory()->create([
        'file_path' => 'reports/private.pdf',
    ]);

    $this
        ->actingAs(User::factory()->create())
        ->delete(route('reports.destroy', $report))
        ->assertNotFound();

    expect($report->fresh())->not->toBeNull();

    Storage::disk('local')->assertExists('reports/private.pdf');
});
