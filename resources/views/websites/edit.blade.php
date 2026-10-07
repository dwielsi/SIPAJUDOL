<x-app-layout>
    <x-slot name="header">
        @php
            $backQuery = array_filter(['from' => request('from') === 'website' ? 'website' : null, 'origin' => request('origin') === 'monitoring' ? 'monitoring' : null]);
            [$backUrl, $backLabel] = isset($backQuery['from'])
                ? [route('websites.show', array_filter([$website, 'from' => $backQuery['origin'] ?? null])), 'Kembali ke Detail Website']
                : [route('websites.index', ['tab' => 'daftar']), 'Kembali ke Daftar Website'];
        @endphp
        <div class="flex min-w-0 items-center gap-3">
            <x-back-link :href="$backUrl" :label="$backLabel" />
            <h1 class="min-w-0 truncate font-heading text-base font-semibold text-slate-900 dark:text-white">Ubah Website</h1>
        </div>
    </x-slot>

    <x-card class="max-w-4xl">
        <form method="POST" action="{{ route('websites.update', [$website, ...$backQuery]) }}">
            @csrf
            @method('PUT')

            @include('websites._form')

            <div class="mt-6 flex items-center justify-end gap-3 border-t border-slate-200 pt-5 dark:border-slate-800">
                <x-button variant="secondary" onclick="window.location='{{ $backUrl }}'">Batal</x-button>
                <x-button type="submit" variant="primary">Simpan Perubahan</x-button>
            </div>
        </form>
    </x-card>
</x-app-layout>
