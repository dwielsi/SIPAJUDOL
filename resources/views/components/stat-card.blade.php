@props(['label', 'value', 'color' => 'primary', 'icon' => null])

@php
    $colors = [
        'primary' => 'bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400',
        'success' => 'bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-500',
        'warning' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/10 dark:text-warning-500',
        'danger' => 'bg-danger-50 text-danger-600 dark:bg-danger-500/10 dark:text-danger-500',
    ];
@endphp

<x-card>
    <div class="flex items-center gap-4">
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $colors[$color] ?? $colors['primary'] }}">
            {!! $icon !!}
        </div>
        <div class="min-w-0">
            <p class="truncate text-sm font-medium text-slate-500 dark:text-slate-400">{{ $label }}</p>
            <p class="font-heading text-2xl font-semibold text-slate-900 dark:text-white">{{ $value }}</p>
        </div>
    </div>
</x-card>
