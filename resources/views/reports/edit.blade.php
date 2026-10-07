<x-app-layout>
    <x-slot name="header">
        @php
            $backQuery = array_filter(['from' => request('from') === 'website' ? 'website' : null, 'origin' => request('origin') === 'monitoring' ? 'monitoring' : null]);
        @endphp
        <div class="flex min-w-0 items-center gap-3">
            <x-back-link :href="route('reports.show', [$report, ...$backQuery])" label="Kembali ke Detail Laporan" />
            <h1 class="min-w-0 truncate font-heading text-base font-semibold text-slate-900 dark:text-white">Ubah Laporan {{ $report->report_number }}</h1>
        </div>
    </x-slot>

    <x-card class="max-w-4xl">
        <form method="POST" action="{{ route('reports.update', [$report, ...$backQuery]) }}">
            @csrf
            @method('PUT')

            @include('reports._form')

            <div class="mt-6 flex items-center justify-end gap-3 border-t border-slate-200 pt-5 dark:border-slate-800">
                <x-button variant="secondary" onclick="window.location='{{ route('reports.show', [$report, ...$backQuery]) }}'">Batal</x-button>
                <x-button type="submit" variant="primary">Simpan Perubahan</x-button>
            </div>
        </form>
    </x-card>
</x-app-layout>
