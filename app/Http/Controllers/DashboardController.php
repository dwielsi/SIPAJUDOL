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
    public function index(): View
    {
        $months = collect(range(5, 0))->map(fn ($i) => now()->subMonths($i)->startOfMonth());

        $monthlyScans = $months->map(function (Carbon $month) {
            return [
                'label' => $month->translatedFormat('M Y'),
                'count' => ScanResult::whereBetween('scan_date', [
                    $month->copy()->startOfMonth(),
                    $month->copy()->endOfMonth(),
                ])->count(),
            ];
        });

        $topRisky = Website::query()
            ->with(['scanResults' => fn ($query) => $query->latest('scan_date')->limit(1)])
            ->orderByDesc('latest_risk_score')
            ->limit(5)
            ->get(['id', 'website_name', 'domain', 'latest_risk_score', 'status']);

        return view('dashboard', [
            'totalWebsites' => Website::count(),
            'safeWebsites' => Website::where('status', 'safe')->count(),
            'flaggedWebsites' => Website::where('status', 'flagged')->count(),
            'needsReviewWebsites' => Website::where('status', 'needs_review')->count(),
            'scanningCount' => ScanResult::inProgress()->count(),
            'totalReports' => Report::count(),
            'monthlyScans' => $monthlyScans,
            'riskLevels' => [
                'Aman' => Website::where('status', 'safe')->count(),
                'Perlu Pemeriksaan' => Website::where('status', 'needs_review')->count(),
                'Terindikasi' => Website::where('status', 'flagged')->count(),
            ],
            'topRisky' => $topRisky,
            'recentActivity' => ActivityLog::with('user')->latest()->limit(6)->get(),
            'newestWebsites' => Website::query()
                ->with(['scanResults' => fn ($query) => $query->latest('scan_date')->limit(1)])
                ->latest()
                ->limit(5)
                ->get(),
            'threatNotifications' => Notification::whereIn('type', ['threat', 'warning'])->latest()->limit(5)->get(),
            'inProgressScans' => ScanResult::inProgress()->with('website')->latest()->limit(5)->get(),
        ]);
    }
}
