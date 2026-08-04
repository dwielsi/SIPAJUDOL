@php
    $report = $report ?? null;
    $selected = old('scan_result_id', $selectedScanResultId ?? $report?->scan_result_id);
@endphp

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <x-input-label for="scan_result_id" value="Hasil Pemindaian Terkait" />
        <select id="scan_result_id" name="scan_result_id" class="block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            <option value="">Tanpa hasil pemindaian</option>
            @foreach ($scanResults as $scanResult)
                <option value="{{ $scanResult->id }}" @selected((string) $selected === (string) $scanResult->id)>
                    {{ $scanResult->website->website_name ?? 'Website tidak diketahui' }} &mdash; {{ $scanResult->scan_date->translatedFormat('d M Y') }} ({{ $scanResult->threat_type ?: $scanResult->status }})
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('scan_result_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="report_date" value="Tanggal Laporan" />
        <x-text-input id="report_date" name="report_date" type="date" class="w-full" :value="old('report_date', optional($report?->report_date)->format('Y-m-d') ?? now()->format('Y-m-d'))" required />
        <x-input-error :messages="$errors->get('report_date')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="analyst" value="Nama Analis" />
        <x-text-input id="analyst" name="analyst" class="w-full" :value="old('analyst', $report?->analyst ?? auth()->user()->name)" required />
        <x-input-error :messages="$errors->get('analyst')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="status" value="Status Laporan" />
        <select id="status" name="status" class="block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            @foreach (['draft' => 'Draf', 'final' => 'Final', 'submitted' => 'Diserahkan'] as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $report?->status ?? 'draft') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('status')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="summary" value="Ringkasan Temuan" />
        <textarea id="summary" name="summary" rows="3" class="block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:placeholder-slate-500">{{ old('summary', $report?->summary) }}</textarea>
        <x-input-error :messages="$errors->get('summary')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="conclusion" value="Kesimpulan" />
        <textarea id="conclusion" name="conclusion" rows="3" class="block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:placeholder-slate-500">{{ old('conclusion', $report?->conclusion) }}</textarea>
        <x-input-error :messages="$errors->get('conclusion')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="recommendation" value="Rekomendasi" />
        <textarea id="recommendation" name="recommendation" rows="3" class="block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:placeholder-slate-500">{{ old('recommendation', $report?->recommendation) }}</textarea>
        <x-input-error :messages="$errors->get('recommendation')" class="mt-2" />
    </div>
</div>
