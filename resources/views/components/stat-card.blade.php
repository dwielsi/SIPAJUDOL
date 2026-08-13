@props(['label', 'value', 'color' => 'primary', 'icon' => null, 'href' => null])

@php
    $colors = [
        'primary' => 'bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400',
        'success' => 'bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-500',
        'warning' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/10 dark:text-warning-500',
        'danger' => 'bg-danger-50 text-danger-600 dark:bg-danger-500/10 dark:text-danger-500',
        'purple' => 'bg-purple-50 text-purple-600 dark:bg-purple-500/10 dark:text-purple-400',
        'cyan' => 'bg-cyan-50 text-cyan-600 dark:bg-cyan-500/10 dark:text-cyan-400',
    ];

    $sweepColors = [
        'primary' => 'bg-primary-600',
        'success' => 'bg-success-600',
        'warning' => 'bg-warning-500',
        'danger' => 'bg-danger-600',
        'purple' => 'bg-purple-600',
        'cyan' => 'bg-cyan-600',
    ];

    $borderHoverColors = [
        'primary' => 'group-hover:border-primary-600',
        'success' => 'group-hover:border-success-600',
        'warning' => 'group-hover:border-warning-500',
        'danger' => 'group-hover:border-danger-600',
        'purple' => 'group-hover:border-purple-600',
        'cyan' => 'group-hover:border-cyan-600',
    ];
@endphp

@if ($href)
    <a href="{{ $href }}" class="group block rounded-2xl transition hover:-translate-y-0.5 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
        <x-card class="relative overflow-hidden transition-colors {{ $borderHoverColors[$color] ?? $borderHoverColors['primary'] }}">
            <span class="pointer-events-none absolute inset-0 -translate-x-full {{ $sweepColors[$color] ?? $sweepColors['primary'] }} transition-transform duration-300 ease-out group-hover:translate-x-0"></span>
            <div class="relative z-10 flex items-center gap-4">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl transition-colors {{ $colors[$color] ?? $colors['primary'] }} group-hover:bg-white/15 group-hover:text-white">
                    {!! $icon !!}
                </div>
                <div class="min-w-0">
                    <p class="truncate text-sm font-medium text-slate-500 transition-colors group-hover:text-white/80 dark:text-slate-400">{{ $label }}</p>
                    <p class="font-heading text-2xl font-semibold text-slate-900 transition-colors group-hover:text-white dark:text-white">{{ $value }}</p>
                </div>
            </div>
        </x-card>
    </a>
@else
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
@endif
