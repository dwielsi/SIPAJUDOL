<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="truncate font-heading text-base font-semibold text-slate-900 dark:text-white">Activity Log</h1>
            <p class="text-xs text-slate-400">Riwayat aktivitas seluruh pengguna sistem</p>
        </div>
    </x-slot>

    <div x-data="serverTable('{{ route('activity-logs.index') }}', ['user_name', 'action', 'description', 'created_at_label'])" class="space-y-4">
        <x-card :padding="false">
            <div class="flex flex-col gap-3 border-b border-slate-200 p-4 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800">
                <div class="relative w-full sm:max-w-xs">
                    <svg xmlns="http://www.w3.org/2000/svg" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    <input type="text" x-model="search" placeholder="Cari aktivitas..."
                           class="w-full rounded-lg border-slate-300 pl-9 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:placeholder-slate-500">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="sticky top-0 bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                        <tr>
                            <th class="px-4 py-3">Pengguna</th>
                            <th class="px-4 py-3">Aksi</th>
                            <th class="px-4 py-3">Deskripsi</th>
                            <th class="px-4 py-3">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <template x-if="loading">
                            <template x-for="i in 8" :key="i">
                                <tr class="animate-pulse">
                                    <td class="px-4 py-4"><div class="h-3 w-24 rounded bg-slate-200 dark:bg-slate-700"></div></td>
                                    <td class="px-4 py-4"><div class="h-3 w-24 rounded bg-slate-200 dark:bg-slate-700"></div></td>
                                    <td class="px-4 py-4"><div class="h-3 w-48 rounded bg-slate-200 dark:bg-slate-700"></div></td>
                                    <td class="px-4 py-4"><div class="h-3 w-28 rounded bg-slate-200 dark:bg-slate-700"></div></td>
                                </tr>
                            </template>
                        </template>

                        <template x-if="!loading && rows.length === 0">
                            <tr>
                                <td colspan="4">
                                    <x-empty-state title="Belum ada aktivitas tercatat" />
                                </td>
                            </tr>
                        </template>

                        <template x-if="!loading" x-for="row in rows" :key="row.id">
                            <tr class="transition-colors hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                <td class="px-4 py-3.5 font-medium text-slate-700 dark:text-slate-200" x-text="row.user_name"></td>
                                <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400" x-text="row.action"></td>
                                <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400" x-text="row.description"></td>
                                <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400" x-text="row.created_at_label"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col items-center justify-between gap-3 border-t border-slate-200 p-4 text-sm sm:flex-row dark:border-slate-800" x-show="!loading && filtered > 0">
                <p class="text-slate-500 dark:text-slate-400">
                    Menampilkan <span x-text="((page - 1) * perPage) + 1"></span>&ndash;<span x-text="Math.min(page * perPage, filtered)"></span>
                    dari <span x-text="filtered"></span> aktivitas
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
