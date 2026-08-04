<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h1 class="truncate font-heading text-base font-semibold text-slate-900 dark:text-white">{{ $website->website_name }}</h1>
            <div class="flex items-center gap-2">
                @can('create', \App\Models\ScanResult::class)
                    <form method="POST" action="{{ route('scan-results.store') }}">
                        @csrf
                        <input type="hidden" name="website_id" value="{{ $website->id }}">
                        <x-button variant="primary" type="submit">Scan Sekarang</x-button>
                    </form>
                @endcan
                @can('update', $website)
                    <x-button variant="secondary" onclick="window.location='{{ route('websites.edit', $website) }}'">Ubah</x-button>
                @endcan
            </div>
        </div>
    </x-slot>

    <x-card class="max-w-4xl">
        <div class="mb-5 flex flex-wrap items-center gap-3">
            <x-badge :color="$website->statusColor()">{{ $website->statusLabel() }}</x-badge>
            <span class="text-sm text-slate-400">Domain: {{ $website->domain }}</span>
        </div>

        <dl class="grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ([
                'Terakhir Scan' => $website->last_scanned_at?->diffForHumans() ?? 'Belum pernah',
                'Response Time' => $website->response_time_ms ? "{$website->response_time_ms} ms" : '—',
                'SSL' => $website->ssl_valid === null ? 'Belum diperiksa' : ($website->ssl_valid ? 'Valid' : 'Tidak valid'),
                'Skor Risiko' => "{$website->latest_risk_score}/100",
            ] as $label => $value)
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ $label }}</dt>
                    <dd class="mt-1 text-sm text-slate-700 dark:text-slate-200">{{ $value }}</dd>
                </div>
            @endforeach
        </dl>

        <dl class="mt-5 grid grid-cols-1 gap-x-6 gap-y-5 border-t border-slate-200 pt-5 sm:grid-cols-2 dark:border-slate-800">
            @foreach ([
                'Nama OPD' => $website->opd_name,
                'Nama Website' => $website->website_name,
                'Domain' => $website->domain,
                'Subdomain' => $website->subdomain,
                'IP Server' => $website->ip_server,
                'Hosting' => $website->hosting,
                'CMS' => $website->cms,
                'Versi CMS' => $website->cms_version,
                'Lokasi Server' => $website->server_location,
                'Admin Website' => $website->admin_name,
                'Email Admin' => $website->admin_email,
                'Nomor HP Admin' => $website->admin_phone,
            ] as $label => $value)
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ $label }}</dt>
                    <dd class="mt-1 text-sm text-slate-700 dark:text-slate-200">{{ $value ?: '—' }}</dd>
                </div>
            @endforeach

            @if ($website->notes)
                <div class="sm:col-span-2">
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Catatan</dt>
                    <dd class="mt-1 text-sm text-slate-700 dark:text-slate-200">{{ $website->notes }}</dd>
                </div>
            @endif
        </dl>
    </x-card>

    @if ($website->scanResults->isNotEmpty())
        <x-card class="mt-5 max-w-4xl"
                x-data="riskTrendChart({
                    labels: @js($website->scanResults->sortBy('scan_date')->pluck('scan_date')->map(fn ($d) => $d->translatedFormat('d M'))->values()),
                    scores: @js($website->scanResults->sortBy('scan_date')->pluck('risk_score')->values()),
                })">
            <h2 class="mb-3 font-heading text-sm font-semibold text-slate-900 dark:text-white">Grafik Ancaman (Skor Risiko)</h2>
            <div class="h-56"><canvas x-ref="riskTrendChart"></canvas></div>
        </x-card>
    @endif

    <x-card :padding="false" class="mt-5 max-w-4xl">
        <div class="border-b border-slate-200 p-4 dark:border-slate-800">
            <h2 class="font-heading text-sm font-semibold text-slate-900 dark:text-white">Riwayat Pemindaian &amp; Laporan</h2>
        </div>

        @if ($website->scanResults->isEmpty())
            <x-empty-state title="Belum ada riwayat pemindaian" description="Hasil pemindaian website ini akan muncul di sini." />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                        <tr>
                            <th class="px-4 py-3">Tanggal Pemindaian</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Skor Risiko</th>
                            <th class="px-4 py-3">Tautan Judol</th>
                            <th class="px-4 py-3 text-right">Laporan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($website->scanResults as $scanResult)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                <td class="px-4 py-3.5 text-slate-700 dark:text-slate-200">{{ $scanResult->scan_date->translatedFormat('d M Y') }}</td>
                                <td class="px-4 py-3.5">
                                    <x-badge :color="match($scanResult->status) { 'flagged' => 'danger', 'needs_review' => 'warning', default => 'success' }">
                                        {{ match($scanResult->status) { 'flagged' => 'Terindikasi', 'needs_review' => 'Perlu Pemeriksaan', default => 'Aman' } }}
                                    </x-badge>
                                </td>
                                <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400">{{ $scanResult->risk_score }}</td>
                                <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400">{{ $scanResult->judol_link_count }}</td>
                                <td class="px-4 py-3.5 text-right">
                                    @can('view', $scanResult)
                                        <a href="{{ route('scan-results.show', $scanResult) }}" class="mr-3 text-sm font-medium text-primary-600 hover:underline dark:text-primary-400">Detail</a>
                                    @endcan
                                    @if ($scanResult->reports->isNotEmpty())
                                        <a href="{{ route('reports.show', $scanResult->reports->first()) }}" class="text-sm font-medium text-primary-600 hover:underline dark:text-primary-400">Lihat Laporan</a>
                                    @elseif (Gate::allows('create', \App\Models\Report::class))
                                        <a href="{{ route('reports.create', ['scan_result_id' => $scanResult->id]) }}" class="text-sm font-medium text-primary-600 hover:underline dark:text-primary-400">Buat Laporan</a>
                                    @else
                                        <span class="text-sm text-slate-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-card>
</x-app-layout>
