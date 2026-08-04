<x-app-layout>
    <x-slot name="header">
        <h1 class="truncate font-heading text-base font-semibold text-slate-900 dark:text-white">Scan Website</h1>
    </x-slot>

    <x-card class="max-w-2xl">
        <form method="POST" action="{{ route('scan-results.store') }}">
            @csrf

            <div>
                <x-input-label for="website_id" value="Pilih Website" />
                <select id="website_id" name="website_id" class="block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" required>
                    <option value="">-- Pilih website --</option>
                    @foreach ($websites as $website)
                        <option value="{{ $website->id }}" @selected(old('website_id') == $website->id)>
                            {{ $website->website_name }} ({{ $website->domain }})
                        </option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('website_id')" class="mt-2" />
                <p class="mt-2 text-xs text-slate-400">
                    Pemindaian akan berjalan di latar belakang melalui antrean (queue) dan dapat dipantau secara realtime setelah dimulai.
                </p>
            </div>

            <div class="mt-6 flex items-center justify-end gap-3 border-t border-slate-200 pt-5 dark:border-slate-800">
                <x-button variant="secondary" onclick="window.location='{{ route('scan-results.index') }}'">Batal</x-button>
                <x-button type="submit" variant="primary">Mulai Scan</x-button>
            </div>
        </form>
    </x-card>
</x-app-layout>
