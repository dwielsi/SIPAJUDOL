<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="truncate font-heading text-base font-semibold text-slate-900 dark:text-white">SPK Prioritas Penanganan</h1>
            <p class="text-xs text-slate-400">Perankingan website terindikasi menggunakan metode SAW (Simple Additive Weighting)</p>
        </div>
    </x-slot>

    <div class="space-y-4">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <x-stat-card label="Prioritas Tinggi" :value="$ranked->where('priorityLevel', 'tinggi')->count()" color="danger" />
            <x-stat-card label="Prioritas Sedang" :value="$ranked->where('priorityLevel', 'sedang')->count()" color="warning" />
            <x-stat-card label="Prioritas Rendah" :value="$ranked->where('priorityLevel', 'rendah')->count()" color="success" />
        </div>

        <x-card :padding="false">
            <div class="border-b border-slate-200 p-4 dark:border-slate-800">
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Bobot kriteria:
                    @foreach ($criteria as $key => $c)
                        {{ $c['label'] }} ({{ $c['weight'] * 100 }}%){{ ! $loop->last ? ',' : '' }}
                    @endforeach
                </p>
            </div>

            @if ($ranked->isEmpty())
                <x-empty-state title="Belum ada data untuk dirangking" description="Website yang sudah discan akan muncul di sini." />
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                            <tr>
                                <th class="px-4 py-3">Rank</th>
                                <th class="px-4 py-3">OPD / Website</th>
                                <th class="px-4 py-3">Skor SAW</th>
                                <th class="px-4 py-3">Prioritas</th>
                                <th class="px-4 py-3 text-right">Detail</th>
                            </tr>
                        </thead>
                        @foreach ($ranked as $item)
                            <tbody x-data="{ open: false }" class="divide-y divide-slate-100 border-b border-slate-100 dark:divide-slate-800 dark:border-slate-800">
                                <tr>
                                    <td class="px-4 py-3.5 font-semibold text-slate-700 dark:text-slate-200">#{{ $item->rank }}</td>
                                    <td class="px-4 py-3.5">
                                        <p class="font-medium text-slate-700 dark:text-slate-200">{{ $item->website->opd_name }}</p>
                                        <p class="text-xs text-slate-400">{{ $item->website->website_name }}</p>
                                    </td>
                                    <td class="px-4 py-3.5 text-slate-600 dark:text-slate-300">{{ $item->scorePercent() }}%</td>
                                    <td class="px-4 py-3.5">
                                        <x-badge :color="match ($item->priorityLevel) { 'tinggi' => 'danger', 'sedang' => 'warning', default => 'success' }">
                                            {{ ucfirst($item->priorityLevel) }}
                                        </x-badge>
                                    </td>
                                    <td class="px-4 py-3.5 text-right">
                                        <button type="button" @click="open = !open" class="text-xs font-semibold text-primary-600 hover:underline">
                                            <span x-text="open ? 'Sembunyikan' : 'Lihat rincian'"></span>
                                        </button>
                                    </td>
                                </tr>
                                <tr x-show="open" x-cloak>
                                    <td colspan="5" class="bg-slate-50 px-4 py-4 dark:bg-slate-900/40">
                                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                                            @foreach ($item->breakdown as $b)
                                                <div class="rounded-lg border border-slate-200 bg-white p-3 dark:border-slate-800 dark:bg-slate-800">
                                                    <p class="text-xs text-slate-400">{{ $b['label'] }}</p>
                                                    <p class="mt-1 text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $b['raw'] }}</p>
                                                    <p class="text-xs text-slate-400">bobot {{ $b['weight'] * 100 }}% &middot; kontribusi {{ number_format($b['contribution'] * 100, 1) }}%</p>
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        @endforeach
                    </table>
                </div>
            @endif
        </x-card>

        @if ($unscanned->isNotEmpty())
            <x-card>
                <p class="mb-3 text-sm font-semibold text-slate-700 dark:text-slate-200">Belum Pernah Discan ({{ $unscanned->count() }})</p>
                <ul class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($unscanned as $website)
                        <li class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-500 dark:border-slate-800 dark:text-slate-400">
                            {{ $website->website_name }}
                        </li>
                    @endforeach
                </ul>
            </x-card>
        @endif
    </div>
</x-app-layout>
