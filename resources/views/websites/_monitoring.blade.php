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
                <x-card x-data="{{ $cardData }}" :padding="false">
                    <div class="p-4">
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
                                        class="rounded-lg border border-slate-300 p-1.5 text-slate-400 hover:border-danger-300 hover:bg-danger-50 hover:text-danger-600 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:hover:bg-danger-500/10"
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
