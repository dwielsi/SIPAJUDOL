<x-app-layout>
    <x-slot name="header">
        @php
            [$backUrl, $backLabel] = match (true) {
                request('source') === 'website' && request()->integer('website') > 0 => [
                    route('websites.show', array_filter([request()->integer('website'), 'from' => request('origin') === 'monitoring' ? 'monitoring' : null])),
                    'Kembali ke Detail Website',
                ],
                (bool) request('scan_result_id') => [
                    route('scan-results.show', array_filter([request('scan_result_id'), 'from' => request('from'), 'origin' => request('origin')])),
                    'Kembali ke Detail Hasil Scan',
                ],
                default => [route('reports.index'), 'Kembali ke Laporan'],
            };
        @endphp
        <div class="flex min-w-0 items-center gap-3">
            <x-back-link :href="$backUrl" :label="$backLabel" />
            <h1 class="min-w-0 truncate font-heading text-base font-semibold text-slate-900 dark:text-white">Buat Laporan</h1>
        </div>
    </x-slot>

    <x-card class="max-w-4xl">
        <form method="POST" action="{{ route('reports.store', request('source') === 'website' ? array_filter(['from' => 'website', 'origin' => request('origin') === 'monitoring' ? 'monitoring' : null]) : []) }}">
            @csrf

            @include('reports._form')

            <div class="mt-6 flex items-center justify-end gap-3 border-t border-slate-200 pt-5 dark:border-slate-800">
                <x-button variant="secondary" onclick="window.location='{{ $backUrl }}'">Batal</x-button>
                <x-button type="submit" variant="primary">Simpan Laporan</x-button>
            </div>
        </form>
    </x-card>
</x-app-layout>
