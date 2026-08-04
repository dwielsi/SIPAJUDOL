<section class="space-y-6">
    <header>
        <h2 class="font-heading text-base font-semibold text-slate-900 dark:text-white">
            Hapus Akun
        </h2>

        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
            Setelah akun dihapus, seluruh data terkait akan dihapus secara permanen. Pastikan Anda benar-benar yakin sebelum melanjutkan.
        </p>
    </header>

    <x-button
        variant="danger"
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >Hapus Akun</x-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="font-heading text-base font-semibold text-slate-900 dark:text-white">
                Apakah Anda yakin ingin menghapus akun ini?
            </h2>

            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Setelah akun dihapus, seluruh data terkait akan dihapus secara permanen. Masukkan password Anda untuk mengonfirmasi.
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="Password" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="block w-3/4"
                    placeholder="Password"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-button type="button" variant="secondary" x-on:click="$dispatch('close')">
                    Batal
                </x-button>

                <x-button type="submit" variant="danger">
                    Hapus Akun
                </x-button>
            </div>
        </form>
    </x-modal>
</section>
