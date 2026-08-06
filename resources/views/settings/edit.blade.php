<x-app-layout>
    <x-slot name="header">
        <h1 class="truncate font-heading text-base font-semibold text-slate-900 dark:text-white">Pengaturan</h1>
        <p class="text-xs text-slate-400">Profil instansi dan konfigurasi SMTP untuk pengiriman laporan</p>
    </x-slot>

    <div class="max-w-2xl space-y-6">
        <form method="POST" action="{{ route('settings.update') }}">
            @csrf
            @method('PUT')

            <x-card>
                <h2 class="mb-4 font-heading text-sm font-semibold text-slate-900 dark:text-white">Profil Instansi</h2>
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
            </x-card>

            <x-card class="mt-6">
                <h2 class="font-heading text-sm font-semibold text-slate-900 dark:text-white">Konfigurasi SMTP</h2>
                <p class="mt-1 text-xs text-slate-400">Digunakan untuk mengirim laporan PDF ke instansi/pengelola website yang teridentifikasi.</p>

                <div class="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <x-input-label for="smtp_host" value="SMTP Host" />
                        <x-text-input id="smtp_host" name="smtp_host" class="w-full" placeholder="smtp.gmail.com" :value="old('smtp_host', $setting->smtp_host)" />
                        <x-input-error :messages="$errors->get('smtp_host')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="smtp_port" value="SMTP Port" />
                        <x-text-input id="smtp_port" name="smtp_port" type="number" class="w-full" placeholder="587" :value="old('smtp_port', $setting->smtp_port)" />
                        <x-input-error :messages="$errors->get('smtp_port')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="smtp_encryption" value="Enkripsi" />
                        <select id="smtp_encryption" name="smtp_encryption" class="block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                            @foreach (['tls' => 'TLS (587)', 'ssl' => 'SSL (465)', 'none' => 'Tanpa Enkripsi'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('smtp_encryption', $setting->smtp_encryption ?? 'tls') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('smtp_encryption')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="smtp_username" value="SMTP Username / Email Pengirim" />
                        <x-text-input id="smtp_username" name="smtp_username" class="w-full" placeholder="laporan@instansi.go.id" :value="old('smtp_username', $setting->smtp_username)" />
                        <x-input-error :messages="$errors->get('smtp_username')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="smtp_password" value="SMTP Password" />
                        <x-text-input id="smtp_password" name="smtp_password" type="password" class="w-full" placeholder="{{ $setting->smtp_password ? 'Biarkan kosong jika tidak diubah' : '' }}" />
                        <x-input-error :messages="$errors->get('smtp_password')" class="mt-2" />
                    </div>
                </div>
            </x-card>

            <div class="mt-6 flex justify-end">
                <x-button type="submit" variant="primary">Simpan Pengaturan</x-button>
            </div>
        </form>

        <x-card x-data="{ email: '{{ auth()->user()->email }}', sending: false, result: null }">
            <h2 class="font-heading text-sm font-semibold text-slate-900 dark:text-white">Uji Coba Konfigurasi SMTP</h2>
            <p class="mt-1 text-xs text-slate-400">Simpan pengaturan SMTP di atas terlebih dahulu, lalu kirim email uji coba untuk memastikan konfigurasi berfungsi.</p>

            <div class="mt-4 flex flex-col gap-3 sm:flex-row">
                <input type="email" x-model="email" placeholder="Email tujuan" class="block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white sm:max-w-xs" />
                <x-button type="button" variant="secondary" x-bind:disabled="sending" @click="
                    sending = true;
                    result = null;
                    fetch('{{ route('settings.test-email') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ email: email }),
                    })
                    .then(async (res) => ({ ok: res.ok, data: await res.json() }))
                    .then(({ ok, data }) => { result = { ok, message: data.message }; })
                    .catch(() => { result = { ok: false, message: 'Terjadi kesalahan saat mengirim permintaan.' }; })
                    .finally(() => { sending = false; })
                ">
                    <span x-show="!sending">Kirim Email Uji Coba</span>
                    <span x-show="sending">Mengirim...</span>
                </x-button>
            </div>

            <p class="mt-3 text-sm" x-show="result" x-cloak :class="result && result.ok ? 'text-success-600' : 'text-danger-600'" x-text="result && result.message"></p>
        </x-card>
    </div>
</x-app-layout>
