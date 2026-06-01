<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ReportGenerateRequest;
use App\Models\MusicUsageEvent;
use App\Models\MusicUsageReport;
use App\Support\SimplePdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $events = $user->musicUsageEvents()->latest('occurred_at')->get();

        return Inertia::render('settings/reports', [
            'stats' => $this->statsFor($events),
            'recentEvents' => $events->take(25)->map(fn (MusicUsageEvent $event): array => $this->eventPayload($event)),
            'reports' => $user->musicUsageReports()
                ->latest()
                ->limit(20)
                ->get()
                ->map(fn (MusicUsageReport $report): array => $this->reportPayload($report)),
            'defaultRange' => [
                'starts_at' => now()->subDays(30)->toDateString(),
                'ends_at' => now()->toDateString(),
            ],
        ]);
    }

    public function store(ReportGenerateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();
        $startsAt = Carbon::parse($validated['starts_at'])->startOfDay();
        $endsAt = Carbon::parse($validated['ends_at'])->endOfDay();
        $events = $user->musicUsageEvents()
            ->whereBetween('occurred_at', [$startsAt, $endsAt])
            ->orderBy('occurred_at')
            ->get();
        $stats = $this->statsFor($events);

        $report = $user->musicUsageReports()->create([
            'starts_at' => $startsAt->toDateString(),
            'ends_at' => $endsAt->toDateString(),
            'listened_count' => $stats['listened_count'],
            'downloaded_count' => $stats['downloaded_count'],
            'duration_seconds' => $stats['duration_seconds'],
            'file_path' => 'reports/pending.pdf',
        ]);

        $filePath = "reports/music-usage-report-{$report->id}.pdf";

        Storage::disk('local')->put($filePath, SimplePdf::make($this->reportLines($report, $events)));

        $report->update(['file_path' => $filePath]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Report generated.')]);

        return to_route('reports.index');
    }

    public function download(MusicUsageReport $musicUsageReport, Request $request): BinaryFileResponse
    {
        abort_unless($musicUsageReport->user_id === $request->user()->id, 404);
        abort_unless(Storage::disk('local')->exists($musicUsageReport->file_path), 404);

        return response()->download(
            Storage::disk('local')->path($musicUsageReport->file_path),
            "music-usage-report-{$musicUsageReport->starts_at->toDateString()}-{$musicUsageReport->ends_at->toDateString()}.pdf",
            ['Content-Type' => 'application/pdf']
        );
    }

    private function statsFor(mixed $events): array
    {
        return [
            'listened_count' => $events->where('event_type', MusicUsageEvent::TypeListened)->count(),
            'downloaded_count' => $events->where('event_type', MusicUsageEvent::TypeDownloaded)->count(),
            'duration_seconds' => (int) $events->where('event_type', MusicUsageEvent::TypeListened)->sum('duration_seconds'),
        ];
    }

    private function eventPayload(MusicUsageEvent $event): array
    {
        return [
            'id' => $event->id,
            'event_type' => $event->event_type,
            'track_title' => $event->track_title,
            'album_title' => $event->album_title,
            'duration_seconds' => $event->duration_seconds,
            'occurred_at' => $event->occurred_at->toDateTimeString(),
        ];
    }

    private function reportPayload(MusicUsageReport $report): array
    {
        return [
            'id' => $report->id,
            'starts_at' => $report->starts_at->toDateString(),
            'ends_at' => $report->ends_at->toDateString(),
            'listened_count' => $report->listened_count,
            'downloaded_count' => $report->downloaded_count,
            'duration_seconds' => $report->duration_seconds,
            'created_at' => $report->created_at->toDateTimeString(),
            'download_url' => route('reports.download', $report),
        ];
    }

    private function reportLines(MusicUsageReport $report, mixed $events): array
    {
        return [
            'Music usage report',
            "Period: {$report->starts_at->toFormattedDateString()} - {$report->ends_at->toFormattedDateString()}",
            "Listened: {$report->listened_count}",
            "Downloaded: {$report->downloaded_count}",
            'Listening time: '.$this->formatDuration($report->duration_seconds),
            '',
            'Date | Type | Track | Album | Duration',
            ...$events->map(fn (MusicUsageEvent $event): string => implode(' | ', [
                $event->occurred_at->toDateString(),
                ucfirst($event->event_type),
                $event->track_title ?: 'Untitled track',
                $event->album_title ?: 'Untitled album',
                $event->duration_seconds === null ? '-' : $this->formatDuration($event->duration_seconds),
            ]))->all(),
        ];
    }

    private function formatDuration(int $seconds): string
    {
        $minutes = intdiv($seconds, 60);
        $remainingSeconds = $seconds % 60;

        return sprintf('%d:%02d', $minutes, $remainingSeconds);
    }
}
