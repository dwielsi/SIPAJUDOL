@props(['variant' => 'primary', 'type' => 'button'])

@php
    $variants = [
        'primary' => 'bg-primary-600 text-white shadow-soft hover:bg-primary-700 focus:ring-primary-500',
        'secondary' => 'border border-slate-300 bg-white text-slate-700 shadow-soft hover:bg-slate-50 focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700',
        'danger' => 'bg-danger-600 text-white shadow-soft hover:bg-danger-700 focus:ring-danger-500',
        'ghost' => 'text-slate-600 hover:bg-slate-100 focus:ring-primary-500 dark:text-slate-300 dark:hover:bg-slate-800',
    ];
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => 'inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 ' . $variants[$variant]]) }}>
    {{ $slot }}
</button>
