<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="truncate font-heading text-base font-semibold text-slate-900 dark:text-white">Detail Hasil Scan</h1>
                <p class="text-xs text-slate-400">{{ $scanResult->website?->website_name }} &middot; {{ $scanResult->scan_date->translatedFormat('d M Y') }}</p>
            </div>
            @can('create', \App\Models\Report::class)
                @if ($scanResult->scan_state === 'completed')
                    <x-button variant="primary" onclick="window.location='{{ route('reports.create', ['scan_result_id' => $scanResult->id]) }}'">Buat Laporan</x-button>
                @endif
            @endcan
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

        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
            @foreach ([
                'Keyword' => $scanResult->keyword_count,
                'Link Judol' => $scanResult->judol_link_count,
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
                @elseif ($scanResult->scan_state === 'completed')
                    <div class="flex h-48 items-center justify-center rounded-xl border border-dashed border-slate-300 text-center text-sm text-slate-400 dark:border-slate-700">
                        Tangkapan layar tidak tersedia untuk pemindaian ini.
                    </div>
                @else
                    <div class="flex h-48 items-center justify-center rounded-xl border border-dashed border-slate-300 text-center text-sm text-slate-400 dark:border-slate-700">
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
                    @if ($scanResult->keyword_count > 0)
                        <li>
                            <p class="text-xs text-slate-400">&mdash;</p>
                            <p class="text-slate-700 dark:text-slate-200">Kata kunci judi online ditemukan</p>
                        </li>
                    @endif
                    @if ($scanResult->redirect_count > 0)
                        <li>
                            <p class="text-xs text-slate-400">&mdash;</p>
                            <p class="text-slate-700 dark:text-slate-200">Redirect mencurigakan ditemukan</p>
                        </li>
                    @endif
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
                        <dd class="mt-1 text-slate-700 dark:text-slate-200">{{ $scanResult->ai_conclusion }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Rekomendasi</dt>
                        <dd class="mt-1 text-slate-700 dark:text-slate-200">{{ $scanResult->ai_recommendation }}</dd>
                    </div>
                </dl>
            </x-card>
        @endif

        <x-card :padding="false">
            <div class="border-b border-slate-200 p-4 dark:border-slate-800">
                <h2 class="font-heading text-sm font-semibold text-slate-900 dark:text-white">Detail Temuan</h2>
            </div>
            @if ($scanResult->findings->isEmpty())
                <x-empty-state title="Tidak ada temuan" description="Pemindaian tidak menemukan indikasi ancaman." />
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                            <tr>
                                <th class="px-4 py-3">Jenis Ancaman</th>
                                <th class="px-4 py-3">Tingkat</th>
                                <th class="px-4 py-3">Pesan</th>
                                <th class="px-4 py-3">Lokasi Script</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach ($scanResult->findings as $finding)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                    <td class="px-4 py-3.5 font-medium text-slate-700 dark:text-slate-200">{{ str($finding->category)->headline() }}</td>
                                    <td class="px-4 py-3.5"><x-badge :color="$finding->severityColor()">{{ ucfirst($finding->severity) }}</x-badge></td>
                                    <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400">
                                        {{ $finding->message }}
                                        @if ($finding->evidence)
                                            <p class="mt-0.5 truncate text-xs text-slate-400">{{ $finding->evidence }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-xs text-slate-400">{{ $finding->location ?? $finding->page_url ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-card>
    </div>
</x-app-layout>
