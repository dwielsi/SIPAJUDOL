@props(['padding' => true])

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-slate-200 bg-white shadow-card dark:border-slate-700/60 dark:bg-gradient-to-b dark:from-slate-700 dark:to-slate-900 ' . ($padding ? 'p-5 sm:p-6' : '')]) }}>
    {{ $slot }}
</div>
