<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="truncate font-heading text-base font-semibold text-slate-900 dark:text-white">Database Keyword</h1>
                <p class="text-xs text-slate-400">Kata kunci yang digunakan oleh mesin pemindai</p>
            </div>
            @can('create', \App\Models\Keyword::class)
                <x-button variant="primary" x-data @click="$dispatch('open-modal', 'create-keyword')">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                    Tambah Keyword
                </x-button>
            @endcan
        </div>
    </x-slot>

    <x-card :padding="false">
        @if ($keywords->isEmpty())
            <x-empty-state title="Belum ada kata kunci" description="Tambahkan kata kunci judi online untuk digunakan mesin pemindai." />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                        <tr>
                            <th class="px-4 py-3">Kata Kunci</th>
                            <th class="px-4 py-3">Kategori</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($keywords as $keyword)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                <td class="px-4 py-3.5 font-medium text-slate-700 dark:text-slate-200">{{ $keyword->keyword }}</td>
                                <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400">{{ $keyword->category }}</td>
                                <td class="px-4 py-3.5">
                                    <x-badge :color="$keyword->active ? 'success' : 'slate'">{{ $keyword->active ? 'Aktif' : 'Nonaktif' }}</x-badge>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center justify-end gap-1">
                                        @can('update', $keyword)
                                            <button type="button" x-data @click="$dispatch('open-modal', 'edit-keyword-{{ $keyword->id }}')" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-primary-600 dark:hover:bg-slate-700" title="Ubah">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5Z"/></svg>
                                            </button>
                                        @endcan
                                        @can('delete', $keyword)
                                            <form method="POST" action="{{ route('keywords.destroy', $keyword) }}" onsubmit="return confirm('Hapus kata kunci ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-lg p-1.5 text-slate-400 hover:bg-danger-50 hover:text-danger-600 dark:hover:bg-danger-500/10" title="Hapus">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6h14Z"/></svg>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>

                            <x-modal name="edit-keyword-{{ $keyword->id }}" maxWidth="md">
                                <form method="POST" action="{{ route('keywords.update', $keyword) }}" class="p-6">
                                    @csrf
                                    @method('PUT')
                                    <h3 class="font-heading text-base font-semibold text-slate-900 dark:text-white">Ubah Kata Kunci</h3>
                                    <div class="mt-4 space-y-4">
                                        <div>
                                            <x-input-label value="Kata Kunci" />
                                            <x-text-input name="keyword" class="w-full" :value="$keyword->keyword" required />
                                        </div>
                                        <div>
                                            <x-input-label value="Kategori" />
                                            <x-text-input name="category" class="w-full" :value="$keyword->category" required />
                                        </div>
                                        <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                                            <input type="checkbox" name="active" value="1" @checked($keyword->active) class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                                            Aktif
                                        </label>
                                    </div>
                                    <div class="mt-6 flex justify-end gap-3">
                                        <x-button variant="secondary" type="button" x-data @click="$dispatch('close-modal', 'edit-keyword-{{ $keyword->id }}')">Batal</x-button>
                                        <x-button variant="primary" type="submit">Simpan</x-button>
                                    </div>
                                </form>
                            </x-modal>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-card>

    <div class="mt-4">{{ $keywords->links() }}</div>

    <x-modal name="create-keyword" maxWidth="md">
        <form method="POST" action="{{ route('keywords.store') }}" class="p-6">
            @csrf
            <h3 class="font-heading text-base font-semibold text-slate-900 dark:text-white">Tambah Kata Kunci</h3>
            <div class="mt-4 space-y-4">
                <div>
                    <x-input-label value="Kata Kunci" />
                    <x-text-input name="keyword" class="w-full" :value="old('keyword')" required />
                    <x-input-error :messages="$errors->get('keyword')" class="mt-2" />
                </div>
                <div>
                    <x-input-label value="Kategori" />
                    <x-text-input name="category" class="w-full" :value="old('category', 'judol')" required />
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                    <input type="checkbox" name="active" value="1" checked class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                    Aktif
                </label>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <x-button variant="secondary" type="button" x-data @click="$dispatch('close-modal', 'create-keyword')">Batal</x-button>
                <x-button variant="primary" type="submit">Simpan</x-button>
            </div>
        </form>
    </x-modal>
</x-app-layout>
