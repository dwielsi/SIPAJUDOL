<x-app-layout>
    <x-slot name="header">
        <div class="flex min-w-0 items-center gap-3">
            @php
                [$backUrl, $backLabel] = match (request('from')) {
                    'monitoring' => [route('websites.index', ['tab' => 'monitoring']), 'Kembali ke Monitoring'],
                    'website' => [route('websites.show', array_filter([$scanResult->website_id, 'from' => request('origin') === 'monitoring' ? 'monitoring' : null])), 'Kembali ke Detail Website'],
                    default => [route('websites.index', ['tab' => 'riwayat']), 'Kembali ke Riwayat Scan'],
                };
            @endphp
            <x-back-link :href="$backUrl" :label="$backLabel" />
            <div class="min-w-0 flex-1">
                <div class="flex items-center justify-between gap-3">
                    <h1 class="min-w-0 truncate font-heading text-base font-semibold text-slate-900 dark:text-white">Detail Hasil Scan</h1>
                    @can('create', \App\Models\Report::class)
                        @if ($scanResult->scan_state === 'completed')
                            <x-button variant="primary" onclick="window.location='{{ route('reports.create', array_filter(['scan_result_id' => $scanResult->id, 'from' => request('from'), 'origin' => request('origin')])) }}'">Buat Laporan</x-button>
                        @endif
                    @endcan
                </div>
                <p class="truncate text-xs text-slate-400">{{ $scanResult->website?->website_name }} &middot; {{ $scanResult->scan_date->translatedFormat('d M Y') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="space-y-5"
         x-data="scanProgress('{{ route('scan-results.status', $scanResult) }}', {
             scan_state: '{{ $scanResult->scan_state }}',
             progress_percent: {{ $scanResult->progress_percent }},
             current_step: {{ Js::from($scanResult->current_step) }},
             risk_score: {{ $scanResult->risk_score }},
             status: '{{ $scanResult->status }}',
         })">

        <template x-if="isActive()">
            <x-card>
                <div class="flex items-center justify-between text-sm">
                    <span class="font-medium text-slate-700 dark:text-slate-200">Pemindaian sedang berjalan&hellip;</span>
                    <span class="text-slate-400" x-text="percent + '%'"></span>
                </div>
                <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700">
                    <div class="h-full rounded-full bg-primary-600 transition-all" :style="`width: ${percent}%`"></div>
                </div>
                <p class="mt-2 text-xs text-slate-400" x-text="step"></p>
            </x-card>
        </template>

        @if ($scanResult->scan_state === 'failed')
            <div class="rounded-xl border border-danger-200 bg-danger-50 p-4 text-sm text-danger-700 dark:border-danger-500/30 dark:bg-danger-500/10 dark:text-danger-500">
                <p class="font-semibold">Pemindaian gagal &mdash; status keamanan website belum dapat dipastikan (bukan berarti aman).</p>
                <p class="mt-1">{{ $scanResult->failure_reason ?? $scanResult->current_step ?? 'Penyebab kegagalan tidak diketahui.' }}</p>
            </div>
        @endif

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-card>
                <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Risk Score</p>
                <p class="mt-1 font-heading text-2xl font-semibold text-slate-900 dark:text-white">{{ $scanResult->risk_score }}/100</p>
                <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700">
                    <div class="h-full rounded-full {{ $scanResult->riskLevelColor() === 'danger' ? 'bg-danger-500' : ($scanResult->riskLevelColor() === 'warning' ? 'bg-warning-500' : 'bg-success-500') }}" style="width: {{ $scanResult->risk_score }}%"></div>
                </div>
            </x-card>
            <x-card>
                <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Status</p>
                <div class="mt-2"><x-badge :color="$scanResult->riskLevelColor()">{{ $scanResult->riskLevelLabel() }}</x-badge></div>
            </x-card>
            <x-card>
                <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Prioritas</p>
                <p class="mt-1 font-heading text-lg font-semibold text-slate-900 dark:text-white">{{ $scanResult->ai_priority ?? '—' }}</p>
            </x-card>
            <x-card>
                <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Halaman Terinfeksi</p>
                <p class="mt-1 font-heading text-lg font-semibold text-slate-900 dark:text-white">{{ $scanResult->infected_pages }}</p>
            </x-card>
        </div>

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @foreach ([
                'Link Konten Ilegal' => $scanResult->judol_link_count,
                'Redirect' => $scanResult->redirect_count,
                'Malware' => $scanResult->malware_count,
                'Link Eksternal' => $scanResult->external_link_count,
            ] as $label => $value)
                <x-card>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ $label }}</p>
                    <p class="mt-1 font-heading text-xl font-semibold text-slate-900 dark:text-white">{{ $value }}</p>
                </x-card>
            @endforeach
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <x-card class="lg:col-span-2">
                <h2 class="mb-3 font-heading text-sm font-semibold text-slate-900 dark:text-white">Screenshot Website</h2>
                @if ($scanResult->screenshot_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($scanResult->screenshot_path))
                    <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($scanResult->screenshot_path) }}" target="_blank" rel="noopener">
                        <img
                            src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($scanResult->screenshot_path) }}"
                            alt="Screenshot {{ $scanResult->website?->website_name }}"
                            class="w-full rounded-xl border border-slate-200 object-cover object-top dark:border-slate-700"
                        >
                    </a>
                @elseif (in_array($scanResult->scan_state, ['completed', 'failed'], true))
                    <div class="flex h-64 items-center justify-center rounded-xl border border-dashed border-slate-300 text-center text-sm text-slate-400 dark:border-slate-700">
                        Tangkapan layar tidak tersedia untuk pemindaian ini.
                    </div>
                @else
                    <div class="flex h-64 items-center justify-center rounded-xl border border-dashed border-slate-300 text-center text-sm text-slate-400 dark:border-slate-700">
                        Tangkapan layar akan tersedia setelah pemindaian selesai.
                    </div>
                @endif
            </x-card>

            <x-card>
                <h2 class="mb-3 font-heading text-sm font-semibold text-slate-900 dark:text-white">Timeline</h2>
                <ol class="space-y-4 border-l border-slate-200 pl-4 text-sm dark:border-slate-800">
                    @if ($scanResult->started_at)
                        <li>
                            <p class="text-xs text-slate-400">{{ $scanResult->started_at->format('H:i') }}</p>
                            <p class="text-slate-700 dark:text-slate-200">Scan dimulai</p>
                        </li>
                    @endif
                    @foreach ([
                        'judol_link_count' => 'Tautan/kata kunci konten ilegal ditemukan',
                        'redirect_count' => 'Redirect mencurigakan ditemukan',
                        'malware_count' => 'Malware, skrip, atau iframe berbahaya ditemukan',
                        'external_link_count' => 'Tautan eksternal tidak wajar ditemukan',
                    ] as $column => $label)
                        @if ($scanResult->{$column} > 0)
                            <li>
                                <p class="text-xs text-slate-400">&mdash;</p>
                                <p class="text-slate-700 dark:text-slate-200">{{ $label }} ({{ $scanResult->{$column} }})</p>
                            </li>
                        @endif
                    @endforeach
                    @if ($scanResult->completed_at)
                        <li>
                            <p class="text-xs text-slate-400">{{ $scanResult->completed_at->format('H:i') }}</p>
                            <p class="text-slate-700 dark:text-slate-200">Scan selesai</p>
                        </li>
                    @endif
                </ol>
            </x-card>
        </div>

        @if ($scanResult->ai_summary)
            <x-card>
                <h2 class="mb-3 font-heading text-sm font-semibold text-slate-900 dark:text-white">Analisis Otomatis</h2>
                <dl class="space-y-4 text-sm">
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Ringkasan</dt>
                        <dd class="mt-1 text-slate-700 dark:text-slate-200">{{ $scanResult->ai_summary }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Kesimpulan</dt>
                        <dd class="mt-1 text-slate-700 dark:text-slate-200">{!! preg_replace(
                            ['/\bTERINDIKASI\b/', '/\bPERLU PEMERIKSAAN\b/', '/\bAMAN\b/'],
                            [
                                '<span class="font-semibold text-danger-600">TERINDIKASI</span>',
                                '<span class="font-semibold text-warning-600">PERLU PEMERIKSAAN</span>',
                                '<span class="font-semibold text-success-600">AMAN</span>',
                            ],
                            e($scanResult->ai_conclusion),
                        ) !!}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Rekomendasi</dt>
                        <dd class="mt-1 text-slate-700 dark:text-slate-200">{{ $scanResult->ai_recommendation }}</dd>
                    </div>
                </dl>
            </x-card>
        @endif

        <x-card :padding="false">
            <div class="px-5 pt-5 pb-3">
                <h2 class="font-heading text-base font-semibold text-slate-900 dark:text-white">Detail Temuan</h2>
            </div>
            @if ($scanResult->findings->isEmpty())
                <x-empty-state title="Tidak ada temuan" description="Pemindaian tidak menemukan indikasi ancaman." />
            @else
                <div class="overflow-x-auto px-5 pb-3">
                    <table class="w-full min-w-[40rem] table-fixed text-left text-sm">
                        <colgroup>
                            <col class="w-[17%]">
                            <col class="w-[13%]">
                            <col>
                            <col class="w-[17%]">
                        </colgroup>
                        <thead class="border-b border-slate-200 text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:border-slate-800">
                            <tr>
                                <th class="py-3 pr-4">Jenis Ancaman</th>
                                <th class="py-3 pr-4">Tingkat</th>
                                <th class="py-3 pr-4">Pesan</th>
                                <th class="py-3">Lokasi Script</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach ($scanResult->findings as $finding)
                                @php
                                    $location = $finding->location ?? $finding->page_url;
                                    $isFilePath = $location && ! str_starts_with($location, 'http') && preg_match('/\.\w{2,5}$/', $location);
                                @endphp
                                <tr class="align-top">
                                    <td class="py-4 pr-4 font-semibold text-slate-800 dark:text-slate-100">{{ str($finding->category)->headline() }}</td>
                                    <td class="py-4 pr-4"><x-badge :color="$finding->severityColor()">{{ ucfirst($finding->severity) }}</x-badge></td>
                                    <td class="py-4 pr-4">
                                        <p class="line-clamp-2 text-slate-600 dark:text-slate-300" title="{{ $finding->message }}">{{ $finding->message }}</p>
                                        @if ($finding->evidence)
                                            <p class="mt-1 truncate text-xs text-slate-400" title="{{ $finding->evidence }}">{{ $finding->evidence }}</p>
                                        @endif
                                    </td>
                                    <td class="py-4">
                                        <p @class([
                                            'truncate text-xs',
                                            'font-mono text-slate-600 dark:text-slate-300' => $isFilePath,
                                            'text-slate-500 dark:text-slate-400' => ! $isFilePath,
                                        ]) title="{{ $location }}">{{ $location ?? '—' }}</p>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-card>
    </div>
</x-app-layout>
