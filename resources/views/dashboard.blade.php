<x-app-layout>
    <x-slot name="header">
        <h1 class="truncate font-heading text-base font-semibold text-slate-900 dark:text-white">Dashboard</h1>
    </x-slot>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
        <x-stat-card
            label="Total Website"
            :value="$totalWebsites"
            color="primary"
            :href="route('websites.index')"
            icon='<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="14" rx="2"/><path d="M3 9h18M8 4v5"/></svg>'
        />
        <x-stat-card
            label="Website Aman"
            :value="$safeWebsites"
            color="success"
            :href="route('websites.index', ['status' => 'safe'])"
            icon='<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/></svg>'
        />
        <x-stat-card
            label="Website Terindikasi"
            :value="$flaggedWebsites"
            color="danger"
            :href="route('websites.index', ['status' => 'flagged'])"
            icon='<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4M12 17h.01"/><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/></svg>'
        />
        <x-stat-card
            label="Perlu Pemeriksaan"
            :value="$needsReviewWebsites"
            color="warning"
            :href="route('websites.index', ['status' => 'needs_review'])"
            icon='<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 8v5m0 3h.01"/></svg>'
        />
        <x-stat-card
            label="Sedang Scan"
            :value="$scanningCount"
            color="primary"
            :href="route('scan-results.index', ['state' => 'in_progress'])"
            icon='<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>'
        />
        <x-stat-card
            label="Total Laporan"
            :value="$totalReports"
            color="primary"
            :href="route('reports.index')"
            icon='<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6M9 13h6M9 17h6M9 9h1"/></svg>'
        />
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-3"
         x-data="dashboardCharts({
             monthly: @js($monthlyScans),
             riskLevels: @js($riskLevels),
             statusCounts: { safe: {{ $safeWebsites }}, needs_review: {{ $needsReviewWebsites }}, flagged: {{ $flaggedWebsites }} },
         })">
        <x-card class="lg:col-span-2">
            <h2 class="mb-4 font-heading text-sm font-semibold text-slate-900 dark:text-white">Statistik Scan Bulanan</h2>
            <div class="h-64"><canvas x-ref="monthlyChart"></canvas></div>
        </x-card>

        <x-card>
            <h2 class="mb-4 font-heading text-sm font-semibold text-slate-900 dark:text-white">Website Aman vs Terindikasi</h2>
            <div class="h-64"><canvas x-ref="statusChart"></canvas></div>
        </x-card>

        <x-card class="lg:col-span-2">
            <h2 class="mb-4 font-heading text-sm font-semibold text-slate-900 dark:text-white">Risk Level</h2>
            <div class="h-56"><canvas x-ref="riskChart"></canvas></div>
        </x-card>

        <x-card :padding="false">
            <div class="border-b border-slate-200 p-4 dark:border-slate-800">
                <h2 class="font-heading text-sm font-semibold text-slate-900 dark:text-white">Top Website Berisiko</h2>
            </div>
            @if ($topRisky->isEmpty())
                <x-empty-state title="Belum ada data risiko" />
            @else
                <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach ($topRisky as $website)
                        <li class="flex items-center justify-between gap-3 px-4 py-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-slate-700 dark:text-slate-200">{{ $website->website_name }}</p>
                                <p class="truncate text-xs text-slate-400">{{ $website->domain }}</p>
                            </div>
                            <x-badge :color="$website->statusColor()">{{ $website->latest_risk_score }}</x-badge>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-card>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2 xl:grid-cols-4">
        <x-card :padding="false">
            <div class="border-b border-slate-200 p-4 dark:border-slate-800">
                <h2 class="font-heading text-sm font-semibold text-slate-900 dark:text-white">Aktivitas Terbaru</h2>
            </div>
            @if ($recentActivity->isEmpty())
                <x-empty-state title="Belum ada aktivitas" />
            @else
                <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach ($recentActivity as $log)
                        <li class="px-4 py-3">
                            <p class="text-sm text-slate-700 dark:text-slate-200">{{ $log->description }}</p>
                            <p class="mt-0.5 text-xs text-slate-400">{{ $log->user?->name ?? 'Sistem' }} &middot; {{ $log->created_at->diffForHumans() }}</p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-card>

        <x-card :padding="false">
            <div class="border-b border-slate-200 p-4 dark:border-slate-800">
                <h2 class="font-heading text-sm font-semibold text-slate-900 dark:text-white">Website Baru</h2>
            </div>
            @if ($newestWebsites->isEmpty())
                <x-empty-state title="Belum ada website" />
            @else
                <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach ($newestWebsites as $website)
                        <li class="flex items-center justify-between gap-3 px-4 py-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-slate-700 dark:text-slate-200">{{ $website->website_name }}</p>
                                <p class="truncate text-xs text-slate-400">{{ $website->opd_name }}</p>
                            </div>
                            <span class="h-2 w-2 shrink-0 rounded-full {{ $website->riskDotColor() }}"></span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-card>

        <x-card :padding="false">
            <div class="border-b border-slate-200 p-4 dark:border-slate-800">
                <h2 class="font-heading text-sm font-semibold text-slate-900 dark:text-white">Notifikasi Ancaman</h2>
            </div>
            @if ($threatNotifications->isEmpty())
                <x-empty-state title="Tidak ada notifikasi ancaman" />
            @else
                <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach ($threatNotifications as $notification)
                        <li class="px-4 py-3">
                            <p class="text-sm text-slate-700 dark:text-slate-200">{{ $notification->typeIcon() }} {{ $notification->title }}</p>
                            <p class="mt-0.5 text-xs text-slate-400">{{ $notification->created_at->diffForHumans() }}</p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-card>

        <x-card :padding="false">
            <div class="border-b border-slate-200 p-4 dark:border-slate-800">
                <h2 class="font-heading text-sm font-semibold text-slate-900 dark:text-white">Progress Scan</h2>
            </div>
            @if ($inProgressScans->isEmpty())
                <x-empty-state title="Tidak ada scan berjalan" />
            @else
                <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach ($inProgressScans as $scan)
                        <li class="px-4 py-3" x-data="scanProgress('{{ route('scan-results.status', $scan) }}', { scan_state: '{{ $scan->scan_state }}', progress_percent: {{ $scan->progress_percent }} })">
                            <p class="truncate text-sm font-medium text-slate-700 dark:text-slate-200">{{ $scan->website?->website_name }}</p>
                            <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700">
                                <div class="h-full rounded-full bg-primary-600 transition-all" :style="`width: ${percent}%`"></div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-card>
    </div>
</x-app-layout>
