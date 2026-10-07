<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex min-w-0 items-center gap-3">
                @php
                    $origin = request('from') === 'monitoring' ? 'monitoring' : null;
                @endphp
                @if ($origin === 'monitoring')
                    <x-back-link :href="route('websites.index', ['tab' => 'monitoring'])" label="Kembali ke Monitoring" />
                @else
                    <x-back-link :href="route('websites.index', ['tab' => 'daftar'])" label="Kembali ke Daftar Website" />
                @endif
                <h1 class="min-w-0 truncate font-heading text-base font-semibold text-slate-900 dark:text-white">{{ $website->website_name }}</h1>
            </div>
            <div class="flex shrink-0 items-center gap-2">
                @can('create', \App\Models\ScanResult::class)
                    <form method="POST" action="{{ route('scan-results.store') }}">
                        @csrf
                        <input type="hidden" name="website_id" value="{{ $website->id }}">
                        <input type="hidden" name="from" value="website">
                        @if ($origin)
                            <input type="hidden" name="origin" value="{{ $origin }}">
                        @endif
                        <x-button variant="primary" type="submit">Scan Sekarang</x-button>
                    </form>
                @endcan
                @can('update', $website)
                    <x-button variant="secondary" onclick="window.location='{{ route('websites.edit', array_filter([$website, 'from' => 'website', 'origin' => $origin])) }}'">Ubah</x-button>
                @endcan
            </div>
        </div>
    </x-slot>

    <x-card class="max-w-4xl">
        <div class="mb-5 flex flex-wrap items-center gap-3">
            <x-badge :color="$website->statusColor()">{{ $website->statusLabel() }}</x-badge>
            <span class="text-sm text-slate-400">Domain: {{ $website->domain }}</span>
        </div>

        @if ($website->status === 'scan_failed')
            <div class="mb-5 rounded-xl border border-danger-200 bg-danger-50 p-4 text-sm text-danger-700 dark:border-danger-500/30 dark:bg-danger-500/10 dark:text-danger-500">
                <p class="font-semibold">Website ini gagal dipindai, sehingga status keamanannya belum dapat dipastikan.</p>
                <p class="mt-1">{{ $website->scan_failure_reason ?? 'Penyebab kegagalan tidak diketahui.' }}</p>
            </div>
        @endif

        <dl class="grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ([
                'Terakhir Scan' => $website->last_scanned_at?->translatedFormat('d M Y H:i') ?? 'Belum pernah',
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
                'IP Server' => $website->ip_server,
                'Hosting' => $website->hosting,
                'CMS' => $website->cms,
                'Versi CMS' => $website->cms_version,
                'Lokasi Server' => $website->server_location,
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
        <x-card class="mt-5 max-w-4xl">
            @php
                // Satu titik per hari (scan selesai terakhir hari itu); scan gagal tidak punya skor sehingga dilewati.
                $trend = $website->scanResults
                    ->where('scan_state', 'completed')
                    ->sortBy(fn ($scan) => [$scan->scan_date->toDateString(), $scan->id])
                    ->groupBy(fn ($scan) => $scan->scan_date->toDateString())
                    ->map->last()
                    ->values();
            @endphp
            <div x-data="riskTrendChart({
                    labels: @js($trend->map(fn ($scan) => $scan->scan_date->translatedFormat('d M'))),
                    scores: @js($trend->pluck('risk_score')),
                })">
                <h2 class="mb-3 font-heading text-sm font-semibold text-slate-900 dark:text-white">Grafik Ancaman (Skor Risiko)</h2>
                <div class="h-56"><canvas x-ref="riskTrendChart"></canvas></div>
            </div>
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
                            <th class="px-4 py-3">Tautan Konten Ilegal</th>
                            <th class="px-4 py-3 text-right">Laporan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($website->scanResults as $scanResult)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                <td class="px-4 py-3.5 text-slate-700 dark:text-slate-200">{{ $scanResult->scan_date->translatedFormat('d M Y') }}</td>
                                <td class="px-4 py-3.5">
                                    <x-badge :color="$scanResult->riskLevelColor()">{{ $scanResult->riskLevelLabel() }}</x-badge>
                                </td>
                                <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400">{{ $scanResult->risk_score }}</td>
                                <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400">{{ $scanResult->judol_link_count }}</td>
                                <td class="px-4 py-3.5 text-right">
                                    @can('view', $scanResult)
                                        <a href="{{ route('scan-results.show', array_filter([$scanResult, 'from' => 'website', 'origin' => $origin])) }}" class="mr-3 text-sm font-medium text-primary-600 hover:underline dark:text-primary-400">Detail</a>
                                    @endcan
                                    @if ($scanResult->reports->isNotEmpty())
                                        <a href="{{ route('reports.show', array_filter([$scanResult->reports->first(), 'from' => 'website', 'origin' => $origin])) }}" class="text-sm font-medium text-primary-600 hover:underline dark:text-primary-400">Lihat Laporan</a>
                                    @elseif ($scanResult->scan_state === 'completed' && Gate::allows('create', \App\Models\Report::class))
                                        <a href="{{ route('reports.create', array_filter(['scan_result_id' => $scanResult->id, 'source' => 'website', 'website' => $website->id, 'origin' => $origin])) }}" class="text-sm font-medium text-primary-600 hover:underline dark:text-primary-400">Buat Laporan</a>
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
