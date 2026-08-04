@props(['padding' => true])

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-slate-200 bg-white shadow-card dark:border-slate-800 dark:bg-slate-800/60 ' . ($padding ? 'p-5 sm:p-6' : '')]) }}>
    {{ $slot }}
</div>
