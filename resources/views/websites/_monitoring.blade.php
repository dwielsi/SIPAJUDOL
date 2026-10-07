    @php
        $hasMonitoringSearch = ($monitoringSearch ?? '') !== '';
    @endphp

    <form method="GET" action="{{ route('websites.index') }}" class="mb-4">
        <input type="hidden" name="tab" value="monitoring">
        <div class="relative w-full sm:max-w-xs">
            <svg xmlns="http://www.w3.org/2000/svg" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            <input type="text" name="q" value="{{ $monitoringSearch ?? '' }}" placeholder="Cari website atau OPD..."
                   class="w-full rounded-xl border-0 bg-slate-100 pl-9 text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500/40 dark:bg-slate-900/60 dark:text-slate-200 dark:placeholder-slate-500">
        </div>
    </form>

    @if ($monitoredWebsites->isEmpty())
        <x-card>
            <x-empty-state
                :title="$hasMonitoringSearch ? 'Website tidak ditemukan' : 'Belum ada website terdaftar'"
                :description='$hasMonitoringSearch ? "Tidak ada website atau OPD yang cocok dengan pencarian \"{$monitoringSearch}\"." : null' />
        </x-card>
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
                <x-card x-data="{{ $cardData }}" :padding="false" class="overflow-hidden transition hover:-translate-y-0.5 hover:shadow-lg">
                    <div class="p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate font-heading text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $website->website_name }}</p>
                            <p class="truncate text-xs text-slate-400">{{ $website->domain }}</p>
                        </div>
                        <x-badge :color="$website->statusColor()" class="shrink-0">{{ $website->statusLabel() }}</x-badge>
                    </div>

                    <div class="mt-4 flex items-center justify-between text-xs text-slate-400">
                        <span>Skor Risiko</span>
                        <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $website->latest_risk_score }}/100</span>
                    </div>
                    <div class="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700">
                        <div class="h-full rounded-full transition-all {{ $website->statusColor() === 'danger' ? 'bg-danger-500' : ($website->statusColor() === 'warning' ? 'bg-warning-500' : 'bg-success-500') }}" style="width: {{ $website->latest_risk_score }}%"></div>
                    </div>

                    <p class="mt-3 flex items-center gap-1.5 text-xs text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
                        Terakhir scan: {{ $website->last_scanned_at?->translatedFormat('d M Y H:i') ?? 'Belum pernah' }}
                    </p>

                    <template x-if="isActive()">
                        <div class="mt-3 rounded-xl bg-primary-50 p-3 dark:bg-primary-500/10">
                            <div class="flex items-center justify-between text-xs font-medium text-primary-700 dark:text-primary-400">
                                <span class="truncate" x-text="step || 'Memindai...'"></span>
                                <span x-text="`${percent}%`"></span>
                            </div>
                            <div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-primary-100 dark:bg-primary-500/20">
                                <div class="h-full rounded-full bg-primary-600 transition-all" :style="`width: ${percent}%`"></div>
                            </div>
                        </div>
                    </template>

                    <div class="mt-4 flex items-center gap-2 border-t border-slate-100 pt-4 dark:border-slate-800">
                        <a href="{{ route('websites.show', [$website, 'from' => 'monitoring']) }}" class="flex-1 rounded-full border border-slate-300 px-3 py-1.5 text-center text-xs font-semibold text-slate-600 transition hover:border-primary-300 hover:bg-primary-50 hover:text-primary-700 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                            Detail
                        </a>
                        @can('create', \App\Models\ScanResult::class)
                            <template x-if="!isActive()">
                                <form method="POST" action="{{ route('scan-results.store') }}" class="flex-1">
                                    @csrf
                                    <input type="hidden" name="website_id" value="{{ $website->id }}">
                                    <input type="hidden" name="from" value="monitoring">
                                    <button type="submit" class="w-full rounded-full bg-primary-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-primary-700">Scan Sekarang</button>
                                </form>
                            </template>
                        @endcan
                        @can('delete', $website)
                            <div x-data="{
                                    deleting: false,
                                    async destroyWebsite() {
                                        const result = await Swal.fire({
                                            title: 'Hapus website ini?',
                                            text: '{{ addslashes($website->website_name) }} akan dipindahkan ke arsip.',
                                            icon: 'warning',
                                            showCancelButton: true,
                                            confirmButtonText: 'Ya, hapus',
                                            cancelButtonText: 'Batal',
                                            confirmButtonColor: '#EF4444',
                                        });

                                        if (!result.isConfirmed) return;

                                        this.deleting = true;

                                        const res = await fetch('{{ route('websites.destroy', $website) }}', {
                                            method: 'POST',
                                            headers: {
                                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                                'X-Requested-With': 'XMLHttpRequest',
                                                'Accept': 'application/json',
                                            },
                                            body: (() => { const fd = new FormData(); fd.append('_method', 'DELETE'); return fd; })(),
                                        });

                                        if (res.ok) {
                                            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Website berhasil dihapus', showConfirmButton: false, timer: 3000 });
                                            window.location.reload();
                                        } else {
                                            this.deleting = false;
                                            Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'Gagal menghapus website', showConfirmButton: false, timer: 3000 });
                                        }
                                    },
                                 }">
                                <button type="button" @click="destroyWebsite()" :disabled="deleting"
                                        class="rounded-full border border-slate-300 p-2 text-slate-400 transition hover:border-danger-300 hover:bg-danger-50 hover:text-danger-600 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:hover:bg-danger-500/10"
                                        title="Hapus website">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6h14Z"/></svg>
                                </button>
                            </div>
                        @endcan
                    </div>
                    </div>
                </x-card>
            @endforeach
        </div>
    @endif
