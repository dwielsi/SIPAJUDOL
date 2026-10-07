<x-app-layout>
    <x-slot name="header">
        <h1 class="truncate font-heading text-base font-semibold text-slate-900 dark:text-white">Pengaturan</h1>
        <p class="text-xs text-slate-400">Profil akun{{ $canManageSettings ? ', profil instansi,' : '' }} dan keamanan</p>
    </x-slot>

    <div class="max-w-5xl space-y-6">
        @if ($canManageSettings)
            <x-card>
                <h2 class="mb-4 font-heading text-sm font-semibold text-slate-900 dark:text-white">Profil Instansi</h2>
                <form method="POST" action="{{ route('settings.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <x-input-label for="instansi_name" value="Nama Instansi" />
                            <x-text-input id="instansi_name" name="instansi_name" class="w-full" :value="old('instansi_name', $setting->instansi_name)" required />
                            <x-input-error :messages="$errors->get('instansi_name')" class="mt-2" />
                        </div>
                        <div class="sm:col-span-2">
                            <x-input-label for="address" value="Alamat" />
                            <x-text-input id="address" name="address" class="w-full" :value="old('address', $setting->address)" />
                            <x-input-error :messages="$errors->get('address')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="head_name" value="Nama Kepala Instansi" />
                            <x-text-input id="head_name" name="head_name" class="w-full" :value="old('head_name', $setting->head_name)" />
                            <x-input-error :messages="$errors->get('head_name')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="nip" value="NIP" />
                            <x-text-input id="nip" name="nip" class="w-full" :value="old('nip', $setting->nip)" />
                            <x-input-error :messages="$errors->get('nip')" class="mt-2" />
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <x-button type="submit" variant="primary">Simpan Pengaturan</x-button>
                    </div>
                </form>
            </x-card>
        @endif

        <x-card>
            @include('profile.partials.update-profile-information-form')
        </x-card>
    </div>
</x-app-layout>
