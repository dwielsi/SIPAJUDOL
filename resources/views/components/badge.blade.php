@props(['color' => 'slate'])

@php
    $colors = [
        'success' => 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-500',
        'warning' => 'bg-warning-50 text-warning-700 dark:bg-warning-500/10 dark:text-warning-500',
        'danger' => 'bg-danger-50 text-danger-700 dark:bg-danger-500/10 dark:text-danger-500',
        'primary' => 'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-400',
        'slate' => 'bg-slate-100 text-slate-600 dark:bg-slate-700/50 dark:text-slate-300',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium ' . ($colors[$color] ?? $colors['slate'])]) }}>
    {{ $slot }}
</span>
