<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="truncate font-heading text-base font-semibold text-slate-900 dark:text-white">Website</h1>
                <p class="text-xs text-slate-400">Monitoring, master data, dan riwayat pemindaian website OPD</p>
            </div>

            @can('create', \App\Models\ScanResult::class)
                <form method="POST" action="{{ route('scan-results.scan-all') }}" x-data
                      @submit.prevent="Swal.fire({
                          title: 'Scan semua website sekarang?',
                          text: 'Pemindaian akan dijalankan untuk semua website yang sedang tidak diproses.',
                          icon: 'question',
                          showCancelButton: true,
                          confirmButtonText: 'Ya, scan semua',
                          cancelButtonText: 'Batal',
                          confirmButtonColor: '#2563EB',
                      }).then((result) => { if (result.isConfirmed) { $el.submit(); } })">
                    @csrf
                    <x-button type="submit" variant="secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 0 1 15.5-6.36M21 12a9 9 0 0 1-15.5 6.36"/><path d="M18 3v5h-5M6 21v-5h5"/></svg>
                        Scan Semua Website
                    </x-button>
                </form>
            @endcan
        </div>
    </x-slot>

    @php
        $initialTab = in_array(request('tab'), ['monitoring', 'daftar', 'riwayat'], true) ? request('tab') : 'monitoring';
    @endphp

    <div x-data="{ tab: '{{ $initialTab }}' }" class="space-y-4">
        <div class="inline-flex flex-wrap items-center gap-1.5 rounded-xl border border-slate-200 bg-slate-100 p-1.5 dark:border-slate-700 dark:bg-slate-800/60">
            @can('monitoring.view')
                <button type="button" @click="tab = 'monitoring'"
                        class="whitespace-nowrap rounded-lg px-4 py-2 text-sm font-semibold transition"
                        :class="tab === 'monitoring' ? 'bg-white text-primary-600 shadow-soft dark:bg-slate-700 dark:text-primary-400' : 'text-slate-500 hover:bg-white hover:text-slate-700 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-slate-200'">
                    Monitoring
                </button>
            @endcan
            <button type="button" @click="tab = 'daftar'"
                    class="whitespace-nowrap rounded-lg px-4 py-2 text-sm font-semibold transition"
                    :class="tab === 'daftar' ? 'bg-white text-primary-600 shadow-soft dark:bg-slate-700 dark:text-primary-400' : 'text-slate-500 hover:bg-white hover:text-slate-700 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-slate-200'">
                Daftar Website
            </button>
            @can('viewAny', \App\Models\ScanResult::class)
                <button type="button" @click="tab = 'riwayat'"
                        class="whitespace-nowrap rounded-lg px-4 py-2 text-sm font-semibold transition"
                        :class="tab === 'riwayat' ? 'bg-white text-primary-600 shadow-soft dark:bg-slate-700 dark:text-primary-400' : 'text-slate-500 hover:bg-white hover:text-slate-700 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-slate-200'">
                    Riwayat Scan
                </button>
            @endcan
        </div>

        @can('monitoring.view')
            <div x-show="tab === 'monitoring'">
                @include('websites._monitoring')
            </div>
        @endcan

        <div x-show="tab === 'daftar'" x-cloak>
            @include('websites._table')
        </div>

        @can('viewAny', \App\Models\ScanResult::class)
            <div x-show="tab === 'riwayat'" x-cloak>
                @include('scan-results._table')
            </div>
        @endcan
    </div>

    @can('create', \App\Models\ScanResult::class)
        @include('scan-results._scan-modal', ['websites' => $scannableWebsites])
    @endcan
</x-app-layout>
