<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="truncate font-heading text-base font-semibold text-slate-900 dark:text-white">{{ $report->report_number }}</h1>
                <p class="text-xs text-slate-400">Laporan tanggal {{ $report->report_date->translatedFormat('d M Y') }}</p>
            </div>
            <div class="flex items-center gap-2">
                @can('print', $report)
                    <x-button variant="secondary" onclick="window.open('{{ route('reports.pdf', $report) }}', '_blank')">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v8H6v-8Z"/></svg>
                        Cetak Laporan
                    </x-button>
                @endcan
                @can('send', $report)
                    <x-button variant="secondary" x-data @click="$dispatch('open-modal', 'send-report')">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
                        Kirim ke Instansi
                    </x-button>
                @endcan
                @can('update', $report)
                    <x-button variant="secondary" onclick="window.location='{{ route('reports.edit', $report) }}'">Ubah</x-button>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="grid max-w-4xl grid-cols-1 gap-5">
        <x-card>
            <div class="mb-5 flex items-center gap-3">
                <x-badge :color="match($report->status) { 'final' => 'success', 'submitted' => 'primary', default => 'slate' }">
                    {{ match($report->status) { 'final' => 'Final', 'submitted' => 'Diserahkan', default => 'Draf' } }}
                </x-badge>
                <span class="text-sm text-slate-400">Analis: {{ $report->analyst }}</span>
                @if ($report->sent_at)
                    <span class="inline-flex items-center gap-1 text-sm text-success-600 dark:text-success-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                        Terkirim ke {{ $report->sent_to }} &middot; {{ $report->sent_at->translatedFormat('d M Y H:i') }}
                    </span>
                @endif
            </div>

            @if ($report->scanResult)
                <dl class="mb-5 grid grid-cols-1 gap-x-6 gap-y-5 border-b border-slate-200 pb-5 sm:grid-cols-2 dark:border-slate-800">
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Website</dt>
                        <dd class="mt-1 text-sm text-slate-700 dark:text-slate-200">{{ $report->scanResult->website->website_name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Domain</dt>
                        <dd class="mt-1 text-sm text-slate-700 dark:text-slate-200">{{ $report->scanResult->website->domain ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Tanggal Pemindaian</dt>
                        <dd class="mt-1 text-sm text-slate-700 dark:text-slate-200">{{ $report->scanResult->scan_date->translatedFormat('d M Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Skor Risiko</dt>
                        <dd class="mt-1 text-sm text-slate-700 dark:text-slate-200">{{ $report->scanResult->risk_score }}</dd>
                    </div>
                </dl>
            @endif

            <dl class="grid grid-cols-1 gap-y-5">
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Ringkasan Temuan</dt>
                    <dd class="mt-1 whitespace-pre-line text-sm text-slate-700 dark:text-slate-200">{{ $report->summary ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Kesimpulan</dt>
                    <dd class="mt-1 whitespace-pre-line text-sm text-slate-700 dark:text-slate-200">{{ $report->conclusion ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Rekomendasi</dt>
                    <dd class="mt-1 whitespace-pre-line text-sm text-slate-700 dark:text-slate-200">{{ $report->recommendation ?: '—' }}</dd>
                </div>
            </dl>
        </x-card>

        @if ($report->scanResult && $report->scanResult->findings->isNotEmpty())
            <x-card :padding="false">
                <div class="border-b border-slate-200 p-4 dark:border-slate-800">
                    <h2 class="font-heading text-sm font-semibold text-slate-900 dark:text-white">Temuan Pemindaian</h2>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach ($report->scanResult->findings as $finding)
                        <div class="flex items-start gap-3 p-4">
                            <x-badge :color="match($finding->severity) { 'critical', 'high' => 'danger', 'medium' => 'warning', default => 'slate' }">
                                {{ ucfirst($finding->severity) }}
                            </x-badge>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ $finding->message }}</p>
                                <p class="text-xs text-slate-400">{{ $finding->category }}</p>
                                @if ($finding->evidence)
                                    <p class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400">{{ $finding->evidence }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-card>
        @endif
    </div>

    @can('send', $report)
        <x-modal name="send-report" maxWidth="md" :show="$errors->has('email') || $errors->has('note')">
            <form method="POST" action="{{ route('reports.send', $report) }}" class="p-6">
                @csrf
                <h3 class="font-heading text-base font-semibold text-slate-900 dark:text-white">Kirim Laporan ke Instansi</h3>
                <p class="mt-1 text-xs text-slate-400">Laporan PDF akan dilampirkan dan dikirim melalui email ke pengelola website terkait.</p>

                <div class="mt-4 space-y-4">
                    <div>
                        <x-input-label for="send_email" value="Email Tujuan" />
                        <x-text-input id="send_email" name="email" type="email" class="w-full" :value="$report->sent_to ?? $report->scanResult?->website?->admin_email" required />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="send_note" value="Catatan Tambahan (opsional)" />
                        <textarea id="send_note" name="note" rows="3" class="block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:placeholder-slate-500"></textarea>
                        <x-input-error :messages="$errors->get('note')" class="mt-2" />
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <x-button variant="secondary" type="button" x-data @click="$dispatch('close-modal', 'send-report')">Batal</x-button>
                    <x-button variant="primary" type="submit">Kirim</x-button>
                </div>
            </form>
        </x-modal>
    @endcan
</x-app-layout>
