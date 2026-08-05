<x-app-layout>
    <x-slot name="header">
        <h1 class="truncate font-heading text-base font-semibold text-slate-900 dark:text-white">Tambah Website</h1>
    </x-slot>

    <x-card class="max-w-2xl">
        <p class="mb-5 text-sm text-slate-500 dark:text-slate-400">
            Masukkan nama instansi/OPD dan domain website. Sistem akan langsung melakukan analisis otomatis
            untuk memeriksa apakah website terindikasi judi online.
        </p>

        <form method="POST" action="{{ route('websites.store') }}">
            @csrf

            <div class="grid grid-cols-1 gap-5">
                <div>
                    <x-input-label for="opd_name" value="Nama Instansi / OPD" />
                    <x-text-input id="opd_name" name="opd_name" class="w-full" :value="old('opd_name')" required autofocus />
                    <x-input-error :messages="$errors->get('opd_name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="domain" value="Domain" />
                    <x-text-input id="domain" name="domain" class="w-full" placeholder="contoh.kuburayakab.go.id" :value="old('domain')" required />
                    <x-input-error :messages="$errors->get('domain')" class="mt-2" />
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end gap-3 border-t border-slate-200 pt-5 dark:border-slate-800">
                <x-button variant="secondary" onclick="window.location='{{ route('websites.index') }}'">Batal</x-button>
                <x-button type="submit" variant="primary">Simpan &amp; Analisis</x-button>
            </div>
        </form>
    </x-card>
</x-app-layout>
