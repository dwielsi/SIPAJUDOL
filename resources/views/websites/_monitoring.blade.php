    @if ($monitoredWebsites->isEmpty())
        <x-card><x-empty-state title="Belum ada website terdaftar" /></x-card>
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($monitoredWebsites as $website)
                @php
                    $latestScan = $website->scanResults->first();
                    $hasActiveScan = $latestScan && in_array($latestScan->scan_state, ['queued', 'running']);
                    $cardData = $hasActiveScan
                        ? "scanProgress('" . route('scan-results.status', $latestScan) . "', { scan_state: '{$latestScan->scan_state}', progress_percent: {$latestScan->progress_percent} })"
                        : "{ isActive: () => false, percent: 0, step: '' }";
                @endphp
                <x-card x-data="{{ $cardData }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $website->website_name }}</p>
                            <p class="truncate text-xs text-slate-400">{{ $website->domain }}</p>
                        </div>
                        <span class="h-2.5 w-2.5 shrink-0 rounded-full {{ $website->riskDotColor() }}"></span>
                    </div>

                    <div class="mt-3 flex items-center justify-between text-xs text-slate-400">
                        <span>Skor Risiko</span>
                        <span class="font-semibold text-slate-600 dark:text-slate-300">{{ $website->latest_risk_score }}/100</span>
                    </div>
                    <div class="mt-1 h-1.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700">
                        <div class="h-full rounded-full {{ $website->statusColor() === 'danger' ? 'bg-danger-500' : ($website->statusColor() === 'warning' ? 'bg-warning-500' : 'bg-success-500') }}" style="width: {{ $website->latest_risk_score }}%"></div>
                    </div>

                    <p class="mt-3 text-xs text-slate-400">
                        Terakhir scan: {{ $website->last_scanned_at?->diffForHumans() ?? 'Belum pernah' }}
                    </p>

                    <template x-if="isActive()">
                        <div class="mt-3">
                            <div class="h-1.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700">
                                <div class="h-full rounded-full bg-primary-600 transition-all" :style="`width: ${percent}%`"></div>
                            </div>
                            <p class="mt-1 truncate text-xs text-primary-600 dark:text-primary-400" x-text="step || 'Memindai...'"></p>
                        </div>
                    </template>

                    <div class="mt-4 flex items-center gap-2">
                        <a href="{{ route('websites.show', $website) }}" class="flex-1 rounded-lg border border-slate-300 px-3 py-1.5 text-center text-xs font-semibold text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                            Detail
                        </a>
                        @can('create', \App\Models\ScanResult::class)
                            <template x-if="!isActive()">
                                <form method="POST" action="{{ route('scan-results.store') }}" class="flex-1">
                                    @csrf
                                    <input type="hidden" name="website_id" value="{{ $website->id }}">
                                    <button type="submit" class="w-full rounded-lg bg-primary-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-primary-700">Scan Sekarang</button>
                                </form>
                            </template>
                        @endcan
                    </div>
                </x-card>
            @endforeach
        </div>
    @endif
