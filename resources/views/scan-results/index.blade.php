<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="truncate font-heading text-base font-semibold text-slate-900 dark:text-white">Riwayat Scan</h1>
                <p class="text-xs text-slate-400">Seluruh riwayat pemindaian website</p>
            </div>
        </div>
    </x-slot>

    <div x-data="{
            ...serverTable('{{ route('scan-results.index') }}', ['website_name', 'domain', 'scan_date_label', 'badge', 'state_badge', 'actions'], @js(array_filter(['state' => request('state')]))),
            async destroy(row) {
                const result = await Swal.fire({
                    title: 'Hapus riwayat scan ini?',
                    text: 'Data hasil pemindaian akan dihapus.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#EF4444',
                });

                if (!result.isConfirmed) return;

                const res = await fetch(row.actions.destroy_url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: (() => { const fd = new FormData(); fd.append('_method', 'DELETE'); return fd; })(),
                });

                if (res.ok) {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Riwayat scan berhasil dihapus', showConfirmButton: false, timer: 3000 });
                    this.fetchRows();
                } else {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'Gagal menghapus riwayat scan', showConfirmButton: false, timer: 3000 });
                }
            }
         }"
         class="space-y-4">

        @if (request('state') === 'in_progress')
            <div class="flex items-center gap-2 rounded-lg bg-primary-50 px-4 py-2.5 text-sm text-primary-700 dark:bg-primary-500/10 dark:text-primary-400">
                <span>Menampilkan filter: <strong>Sedang Scan</strong></span>
                <a href="{{ route('scan-results.index') }}" class="ml-auto inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs font-medium hover:bg-primary-100 dark:hover:bg-primary-500/20">
                    Hapus filter
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </a>
            </div>
        @endif

        <x-card :padding="false">
            <div class="flex flex-col gap-3 border-b border-slate-200 p-4 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800">
                <div class="relative w-full sm:max-w-xs">
                    <svg xmlns="http://www.w3.org/2000/svg" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    <input type="text" x-model="search" placeholder="Cari website atau domain..."
                           class="w-full rounded-lg border-slate-300 pl-9 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:placeholder-slate-500">
                </div>

                @can('create', \App\Models\ScanResult::class)
                    <x-button variant="primary" onclick="window.location='{{ route('scan-results.create') }}'">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                        Scan Website
                    </x-button>
                @endcan
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="sticky top-0 bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                        <tr>
                            <th class="px-4 py-3">Website</th>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Progres</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <template x-if="loading">
                            <template x-for="i in 5" :key="i">
                                <tr class="animate-pulse">
                                    <td class="px-4 py-4"><div class="h-3 w-32 rounded bg-slate-200 dark:bg-slate-700"></div></td>
                                    <td class="px-4 py-4"><div class="h-3 w-24 rounded bg-slate-200 dark:bg-slate-700"></div></td>
                                    <td class="px-4 py-4"><div class="h-5 w-20 rounded-full bg-slate-200 dark:bg-slate-700"></div></td>
                                    <td class="px-4 py-4"><div class="h-5 w-20 rounded-full bg-slate-200 dark:bg-slate-700"></div></td>
                                    <td class="px-4 py-4"><div class="ml-auto h-3 w-16 rounded bg-slate-200 dark:bg-slate-700"></div></td>
                                </tr>
                            </template>
                        </template>

                        <template x-if="!loading && rows.length === 0">
                            <tr>
                                <td colspan="5">
                                    <x-empty-state title="Belum ada riwayat scan" description="Jalankan pemindaian pertama untuk melihat hasilnya di sini." />
                                </td>
                            </tr>
                        </template>

                        <template x-if="!loading">
                        <template x-for="row in rows" :key="row.id">
                            <tr class="transition-colors hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                <td class="px-4 py-3.5">
                                    <p class="font-medium text-slate-700 dark:text-slate-200" x-text="row.website_name"></p>
                                    <p class="text-xs text-slate-400" x-text="row.domain"></p>
                                </td>
                                <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400" x-text="row.scan_date_label"></td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium"
                                          :class="{
                                              'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-500': row.badge.color === 'success',
                                              'bg-warning-50 text-warning-700 dark:bg-warning-500/10 dark:text-warning-500': row.badge.color === 'warning',
                                              'bg-danger-50 text-danger-700 dark:bg-danger-500/10 dark:text-danger-500': row.badge.color === 'danger',
                                          }"
                                          x-text="row.badge.label"></span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium"
                                          :class="{
                                              'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-500': row.state_badge.color === 'success',
                                              'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-400': row.state_badge.color === 'primary',
                                              'bg-danger-50 text-danger-700 dark:bg-danger-500/10 dark:text-danger-500': row.state_badge.color === 'danger',
                                              'bg-slate-100 text-slate-600 dark:bg-slate-700/50 dark:text-slate-300': row.state_badge.color === 'slate',
                                          }"
                                          x-text="row.state_badge.label"></span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center justify-end gap-1">
                                        <a :href="row.actions.show_url" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-primary-600 dark:hover:bg-slate-700" title="Lihat">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </a>
                                        <button x-show="row.actions.can_delete" @click="destroy(row)" type="button" class="rounded-lg p-1.5 text-slate-400 hover:bg-danger-50 hover:text-danger-600 dark:hover:bg-danger-500/10" title="Hapus">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6h14Z"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col items-center justify-between gap-3 border-t border-slate-200 p-4 text-sm sm:flex-row dark:border-slate-800" x-show="!loading && filtered > 0">
                <p class="text-slate-500 dark:text-slate-400">
                    Menampilkan <span x-text="((page - 1) * perPage) + 1"></span>&ndash;<span x-text="Math.min(page * perPage, filtered)"></span>
                    dari <span x-text="filtered"></span> scan
                </p>
                <div class="flex items-center gap-2">
                    <button @click="goToPage(page - 1)" :disabled="page <= 1" class="rounded-lg border border-slate-300 px-3 py-1.5 text-slate-600 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-700 dark:text-slate-300">Sebelumnya</button>
                    <span class="text-slate-500 dark:text-slate-400">Hal. <span x-text="page"></span> / <span x-text="lastPage"></span></span>
                    <button @click="goToPage(page + 1)" :disabled="page >= lastPage" class="rounded-lg border border-slate-300 px-3 py-1.5 text-slate-600 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-700 dark:text-slate-300">Berikutnya</button>
                </div>
            </div>
        </x-card>
    </div>
</x-app-layout>
