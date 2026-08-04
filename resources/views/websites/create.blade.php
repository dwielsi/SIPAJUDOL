<x-app-layout>
    <x-slot name="header">
        <h1 class="truncate font-heading text-base font-semibold text-slate-900 dark:text-white">Tambah Website</h1>
    </x-slot>

    <x-card class="max-w-4xl">
        <form method="POST" action="{{ route('websites.store') }}">
            @csrf

            @include('websites._form')

            <div class="mt-6 flex items-center justify-end gap-3 border-t border-slate-200 pt-5 dark:border-slate-800">
                <x-button variant="secondary" onclick="window.location='{{ route('websites.index') }}'">Batal</x-button>
                <x-button type="submit" variant="primary">Simpan Website</x-button>
            </div>
        </form>
    </x-card>
</x-app-layout>
