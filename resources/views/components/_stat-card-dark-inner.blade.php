<div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-navy-900 to-navy-600 p-5 text-white shadow-card sm:p-6">
    <span class="pointer-events-none absolute -right-6 -top-6 h-28 w-28 rounded-full bg-teal-500/20"></span>
    <div class="relative z-10 flex items-center gap-4">
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/10 text-teal-400">
            {!! $icon !!}
        </div>
        <div class="min-w-0">
            <p class="truncate text-sm font-medium text-slate-300">{{ $label }}</p>
            <p class="font-heading text-2xl font-semibold text-white">{{ $value }}</p>
        </div>
    </div>
</div>
