import { Form, Head } from '@inertiajs/react';
import { Download, FileText } from 'lucide-react';
import ReportController from '@/actions/App/Http/Controllers/Settings/ReportController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index } from '@/routes/reports';

type Stats = {
    listened_count: number;
    downloaded_count: number;
    duration_seconds: number;
};

type UsageEvent = {
    id: number;
    event_type: 'listened' | 'downloaded';
    track_title: string | null;
    album_title: string | null;
    duration_seconds: number | null;
    occurred_at: string;
};

type UsageReport = {
    id: number;
    starts_at: string;
    ends_at: string;
    listened_count: number;
    downloaded_count: number;
    duration_seconds: number;
    created_at: string;
    download_url: string;
};

type DefaultRange = {
    starts_at: string;
    ends_at: string;
};

export default function Reports({
    stats,
    recentEvents,
    reports,
    defaultRange,
}: {
    stats: Stats;
    recentEvents: UsageEvent[];
    reports: UsageReport[];
    defaultRange: DefaultRange;
}) {
    return (
        <>
            <Head title="Reports" />

            <h1 className="sr-only">Reports</h1>

            <div className="space-y-8">
                <Heading
                    variant="small"
                    title="Reports"
                    description="Review listening and download activity"
                />

                <div className="grid gap-3 md:grid-cols-3">
                    <StatBlock
                        label="Tracks listened"
                        value={stats.listened_count}
                    />
                    <StatBlock
                        label="Tracks downloaded"
                        value={stats.downloaded_count}
                    />
                    <StatBlock
                        label="Listening time"
                        value={formatDuration(stats.duration_seconds)}
                    />
                </div>

                <section className="space-y-4">
                    <Heading
                        variant="small"
                        title="Generate PDF"
                        description="Choose a date range and save a downloadable usage report"
                    />

                    <Form
                        {...ReportController.store.form()}
                        options={{
                            preserveScroll: true,
                        }}
                        className="grid gap-4 md:grid-cols-[1fr_1fr_auto] md:items-end"
                    >
                        {({ processing, errors }) => (
                            <>
                                <div className="grid gap-2">
                                    <Label htmlFor="starts_at">Start date</Label>
                                    <Input
                                        id="starts_at"
                                        name="starts_at"
                                        type="date"
                                        defaultValue={defaultRange.starts_at}
                                    />
                                    <InputError message={errors.starts_at} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="ends_at">Till date</Label>
                                    <Input
                                        id="ends_at"
                                        name="ends_at"
                                        type="date"
                                        defaultValue={defaultRange.ends_at}
                                    />
                                    <InputError message={errors.ends_at} />
                                </div>

                                <Button disabled={processing}>
                                    <FileText className="h-4 w-4" />
                                    Generate report
                                </Button>
                            </>
                        )}
                    </Form>
                </section>

                <ReportsTable reports={reports} />

                <ActivityTable events={recentEvents} />
            </div>
        </>
    );
}

function StatBlock({ label, value }: { label: string; value: number | string }) {
    return (
        <div className="rounded-lg border bg-card p-4 text-card-foreground">
            <div className="text-sm text-muted-foreground">{label}</div>
            <div className="mt-2 text-2xl font-semibold">{value}</div>
        </div>
    );
}

function ActivityTable({ events }: { events: UsageEvent[] }) {
    return (
        <section className="space-y-4">
            <Heading
                variant="small"
                title="Recent activity"
                description="Latest listened and downloaded tracks"
            />

            <div className="overflow-hidden rounded-lg border">
                <table className="w-full text-left text-sm">
                    <thead className="bg-muted text-muted-foreground">
                        <tr>
                            <th className="px-4 py-3 font-medium">Date</th>
                            <th className="px-4 py-3 font-medium">Type</th>
                            <th className="px-4 py-3 font-medium">Track</th>
                            <th className="px-4 py-3 font-medium">Album</th>
                            <th className="px-4 py-3 font-medium">Time</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y">
                        {events.map((event) => (
                            <tr key={event.id}>
                                <td className="px-4 py-3 whitespace-nowrap">
                                    {formatDate(event.occurred_at)}
                                </td>
                                <td className="px-4 py-3 capitalize">
                                    {event.event_type}
                                </td>
                                <td className="px-4 py-3">
                                    {event.track_title ?? 'Untitled track'}
                                </td>
                                <td className="px-4 py-3 text-muted-foreground">
                                    {event.album_title ?? 'Untitled album'}
                                </td>
                                <td className="px-4 py-3 whitespace-nowrap">
                                    {event.duration_seconds === null
                                        ? '-'
                                        : formatDuration(
                                              event.duration_seconds,
                                          )}
                                </td>
                            </tr>
                        ))}

                        {events.length === 0 && (
                            <tr>
                                <td
                                    colSpan={5}
                                    className="px-4 py-8 text-center text-muted-foreground"
                                >
                                    No usage activity recorded yet.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>
        </section>
    );
}

function ReportsTable({ reports }: { reports: UsageReport[] }) {
    return (
        <section className="space-y-4">
            <Heading
                variant="small"
                title="Generated reports"
                description="Previously generated PDF files"
            />

            <div className="overflow-hidden rounded-lg border">
                <table className="w-full text-left text-sm">
                    <thead className="bg-muted text-muted-foreground">
                        <tr>
                            <th className="px-4 py-3 font-medium">Period</th>
                            <th className="px-4 py-3 font-medium">Listened</th>
                            <th className="px-4 py-3 font-medium">
                                Downloaded
                            </th>
                            <th className="px-4 py-3 font-medium">Generated</th>
                            <th className="px-4 py-3 text-right font-medium">
                                PDF
                            </th>
                        </tr>
                    </thead>
                    <tbody className="divide-y">
                        {reports.map((report) => (
                            <tr key={report.id}>
                                <td className="px-4 py-3 whitespace-nowrap">
                                    {report.starts_at} - {report.ends_at}
                                </td>
                                <td className="px-4 py-3">
                                    {report.listened_count}
                                </td>
                                <td className="px-4 py-3">
                                    {report.downloaded_count}
                                </td>
                                <td className="px-4 py-3 whitespace-nowrap">
                                    {formatDate(report.created_at)}
                                </td>
                                <td className="px-4 py-3 text-right">
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        asChild
                                        aria-label="Download report"
                                    >
                                        <a href={report.download_url}>
                                            <Download className="h-4 w-4" />
                                        </a>
                                    </Button>
                                </td>
                            </tr>
                        ))}

                        {reports.length === 0 && (
                            <tr>
                                <td
                                    colSpan={5}
                                    className="px-4 py-8 text-center text-muted-foreground"
                                >
                                    No reports generated yet.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>
        </section>
    );
}

function formatDuration(seconds: number) {
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = seconds % 60;

    return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
}

function formatDate(value: string) {
    return new Intl.DateTimeFormat(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    }).format(new Date(value));
}

Reports.layout = {
    breadcrumbs: [
        {
            title: 'Reports',
            href: index(),
        },
    ],
};
