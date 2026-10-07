<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Notification;
use App\Models\Report;
use App\Models\ScanResult;
use App\Models\Website;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /** Tanggal awal aplikasi mulai digunakan; grafik statistik scan dihitung sejak tanggal ini. */
    private const STATISTICS_START_DATE = '2026-08-01';

    public function index(): View
    {
        ScanResult::expireStale();

        $topRisky = $this->topRiskyWebsitesWithPhoto();

        return view('dashboard', [
            'totalWebsites' => Website::count(),
            'safeWebsites' => Website::where('status', 'safe')->count(),
            'flaggedWebsites' => Website::where('status', 'flagged')->count(),
            'needsReviewWebsites' => Website::where('status', 'needs_review')->count(),
            'scanningCount' => ScanResult::inProgress()->count(),
            'totalReports' => Report::count(),
            'scanStats' => $this->scanStatistics(),
            'riskLevels' => [
                'Aman' => Website::where('status', 'safe')->count(),
                'Perlu Pemeriksaan' => Website::where('status', 'needs_review')->count(),
                'Terindikasi' => Website::where('status', 'flagged')->count(),
                'Gagal Dipindai' => Website::where('status', 'scan_failed')->count(),
            ],
            'topRisky' => $topRisky,
            'recentActivity' => ActivityLog::with('user')->latest()->limit(6)->get(),
            'newestWebsites' => $this->newestWebsitesWithPhotoPreference(),
            'threatNotifications' => Notification::whereIn('type', ['threat', 'warning'])->latest()->limit(5)->get(),
            'inProgressScans' => ScanResult::inProgress()->with('website')->latest()->limit(5)->get(),
            'recentCompletedScans' => ScanResult::query()
                ->whereIn('scan_state', ['completed', 'failed'])
                ->with('website')
                ->latest('completed_at')
                ->limit(5)
                ->get(),
        ]);
    }

    /**
     * Jumlah scan per periode untuk grafik statistik, dimulai dari awal aplikasi digunakan.
     * $minPoints menjaga sumbu tetap menampilkan sejumlah periode walau sebagian belum berjalan;
     * periode yang belum berjalan dan belum memiliki data bernilai null agar grafik tidak turun ke nol.
     */
    private function scanStatistics(): array
    {
        $start = Carbon::parse(self::STATISTICS_START_DATE)->startOfDay();

        $series = function (string $unit, string $format, int $minPoints = 1) use ($start) {
            $points = [];
            $cursor = $start->copy()->startOf($unit);

            while ($cursor->lte(now()) || count($points) < $minPoints) {
                $from = $cursor->max($start);
                $to = $cursor->copy()->endOf($unit);

                $count = ScanResult::whereBetween('scan_date', [$from, $to])->count();

                $points[] = [
                    'label' => $from->translatedFormat($format),
                    'count' => $from->isFuture() && $count === 0 ? null : $count,
                ];

                $cursor = $cursor->copy()->add(1, $unit)->startOf($unit);
            }

            return $points;
        };

        return [
            'weekly' => $series('week', 'd M'),
            'monthly' => $series('month', 'M Y', 3),
            'yearly' => $series('year', 'Y', 3),
        ];
    }

    /**
     * Newest websites, preferring ones whose latest scan has a screenshot
     * so the dashboard thumbnail list isn't dominated by empty placeholders.
     */
    private function newestWebsitesWithPhotoPreference()
    {
        $pool = Website::query()
            ->with(['scanResults' => fn ($query) => $query->latest('scan_date')->orderByDesc('id')->limit(1)])
            ->latest()
            ->limit(20)
            ->get();

        [$withPhoto, $withoutPhoto] = $pool->partition(
            fn ($website) => (bool) $website->scanResults->first()?->screenshotUrl()
        );

        return $withPhoto->concat($withoutPhoto)->take(5)->values();
    }

    /**
     * Top 5 riskiest websites that have a screenshot on their latest scan.
     * Websites without a photo (e.g. currently unreachable) are skipped
     * rather than shown with an empty placeholder, still ranked by risk score.
     */
    private function topRiskyWebsitesWithPhoto()
    {
        $pool = Website::query()
            ->with(['scanResults' => fn ($query) => $query->latest('scan_date')->orderByDesc('id')->limit(1)])
            ->orderByDesc('latest_risk_score')
            ->limit(20)
            ->get(['id', 'website_name', 'domain', 'latest_risk_score', 'status']);

        return $pool
            ->filter(fn ($website) => (bool) $website->scanResults->first()?->screenshotUrl())
            ->take(5)
            ->values();
    }
}
