@props(['href', 'label' => 'Kembali'])

<a href="{{ $href }}" title="{{ $label }}" aria-label="{{ $label }}"
   {{ $attributes->merge(['class' => 'inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-slate-300 bg-white text-slate-600 shadow-soft transition hover:bg-slate-50 hover:text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700']) }}>
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
</a>
