<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="truncate font-heading text-base font-semibold text-slate-900 dark:text-white">Riwayat Scan</h1>
                <p class="text-xs text-slate-400">Seluruh riwayat pemindaian website</p>
            </div>
        </div>
    </x-slot>

    @include('scan-results._table')

    @can('create', \App\Models\ScanResult::class)
        @include('scan-results._scan-modal', ['websites' => \App\Models\Website::orderBy('website_name')->get()])
    @endcan
</x-app-layout>
