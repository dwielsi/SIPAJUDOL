@props(['title' => 'Belum ada data', 'description' => null])

<div class="flex flex-col items-center justify-center gap-3 px-6 py-16 text-center">
    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 3H5a2 2 0 0 0-2 2v4m6-6h10a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm-6 6v10a2 2 0 0 0 2 2h4" />
        </svg>
    </div>
    <div>
        <p class="font-heading text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $title }}</p>
        @if ($description)
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $description }}</p>
        @endif
    </div>
    @isset($action)
        <div class="mt-2">{{ $action }}</div>
    @endisset
</div>
