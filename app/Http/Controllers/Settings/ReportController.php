<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ReportGenerateRequest;
use App\Models\AlbumTrack;
use App\Models\MusicUsageEvent;
use App\Models\MusicUsageReport;
use App\Models\Track;
use App\Support\SimplePdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $events = $user->musicUsageEvents()->latest('occurred_at')->get();
        $recentEvents = $user->musicUsageEvents()
            ->latest('occurred_at')
            ->paginate(20, ['*'], 'activity_page')
            ->withQueryString()
            ->through(fn (MusicUsageEvent $event): array => $this->eventPayload($event));

        return Inertia::render('settings/reports', [
            'stats' => $this->statsFor($events),
            'recentEvents' => $recentEvents,
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
            ->with(['albumTrack.album', 'albumTrack.tracks'])
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

        Storage::disk('local')->put($filePath, SimplePdf::makeLithuanianUsageReport(
            $this->periodLabel($startsAt, $endsAt),
            $this->reportRows($events),
            $user->contact_person ?: $user->name,
        ));

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

    public function destroy(MusicUsageReport $musicUsageReport, Request $request): RedirectResponse
    {
        abort_unless($musicUsageReport->user_id === $request->user()->id, 404);

        $this->deleteReportFile($musicUsageReport);

        $musicUsageReport->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Report deleted.')]);

        return to_route('reports.index');
    }

    private function deleteReportFile(MusicUsageReport $report): void
    {
        collect([
            $report->file_path,
            Str::after($report->file_path, 'storage/app/private/'),
            Str::after($report->file_path, 'app/private/'),
            Str::after($report->file_path, 'private/'),
        ])
            ->filter(fn (string $path): bool => filled($path))
            ->unique()
            ->each(fn (string $path): bool => Storage::disk('local')->delete($path));
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

    /**
     * @param  Collection<int, MusicUsageEvent>  $events
     * @return list<array<string, string|int>>
     */
    private function reportRows(Collection $events): array
    {
        return $events
            ->groupBy(fn (MusicUsageEvent $event): string => implode('|', [
                $event->track_title,
                $event->album_title,
                $event->album_track_id,
                $this->metadataValue($event, ['usage_type', 'usageType', 'naudojimo_budas']),
            ]))
            ->values()
            ->map(function (Collection $events, int $index): array {
                /** @var MusicUsageEvent $event */
                $event = $events->first();
                $albumTrack = $event->albumTrack;
                $track = $this->trackFor($albumTrack, $event);
                $duration = (int) $events->sum(fn (MusicUsageEvent $usageEvent): int => (int) $usageEvent->duration_seconds);

                return [
                    'number' => $index + 1,
                    'title' => $event->track_title ?: $albumTrack?->name ?: $track?->display_title ?: $track?->name ?: '-',
                    'soloists' => $this->metadataValue($event, ['soloists', 'soloistai']) ?: '-',
                    'performers' => $this->metadataValue($event, ['performers', 'performer', 'artists', 'artist', 'atlikejai']) ?: '-',
                    'music_authors' => $this->metadataValue($event, ['music_authors', 'musicAuthor', 'composer', 'authors', 'author'])
                        ?: $track?->composer
                        ?: '-',
                    'text_authors' => $this->metadataValue($event, ['text_authors', 'textAuthor', 'lyrics_author', 'lyricist']) ?: '-',
                    'album' => $this->metadataValue($event, ['album_code', 'catalog', 'catalog_number', 'cd_code'])
                        ?: $track?->cd_code
                        ?: $albumTrack?->album?->code
                        ?: $event->album_title
                        ?: '-',
                    'label' => $this->metadataValue($event, ['label', 'producer', 'publisher', 'gamintojas'])
                        ?: $track?->publisher
                        ?: '-',
                    'minutes' => $duration > 0 ? intdiv($duration, 60) : '',
                    'seconds' => $duration > 0 ? $duration % 60 : '',
                    'broadcast_count' => $this->metadataValue($event, ['broadcast_count', 'repeat_count', 'transliavimu_skaicius'])
                        ?: $events->count(),
                    'usage_type' => $this->metadataValue($event, ['usage_type', 'usageType', 'naudojimo_budas']) ?: 'foninė',
                ];
            })
            ->all();
    }

    private function trackFor(?AlbumTrack $albumTrack, MusicUsageEvent $event): ?Track
    {
        if ($albumTrack === null) {
            return null;
        }

        return $albumTrack->tracks
            ->first(fn (Track $track): bool => $track->name === $event->track_title || $track->display_title === $event->track_title)
            ?: $albumTrack->tracks->first();
    }

    /**
     * @param  list<string>  $keys
     */
    private function metadataValue(MusicUsageEvent $event, array $keys): ?string
    {
        $metadata = $event->metadata ?? [];

        foreach ($keys as $key) {
            $value = data_get($metadata, $key) ?? data_get($event->albumTrack?->album?->source_metadata ?? [], $key);

            if (is_array($value)) {
                $value = implode('/', array_filter($value, fn (mixed $item): bool => filled($item)));
            }

            if (filled($value)) {
                return (string) $value;
            }
        }

        return null;
    }

    private function periodLabel(Carbon $startsAt, Carbon $endsAt): string
    {
        if ($startsAt->isSameMonth($endsAt)) {
            return $startsAt->format('Y').' m. '.$startsAt->format('m').' mėn.';
        }

        return $startsAt->toDateString().' - '.$endsAt->toDateString();
    }

    private function formatDuration(int $seconds): string
    {
        $minutes = intdiv($seconds, 60);
        $remainingSeconds = $seconds % 60;

        return sprintf('%d:%02d', $minutes, $remainingSeconds);
    }
}
