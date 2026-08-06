@php
    $links = [
        [
            'route' => 'dashboard',
            'label' => 'Dashboard',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9.5 12 3l9 6.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V9.5Z"/></svg>',
        ],
        [
            'route' => 'monitoring.index',
            'label' => 'Monitoring',
            'permission' => 'monitoring.view',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12h4l2-8 4 16 2-8h6"/></svg>',
        ],
        [
            'route' => 'websites.index',
            'label' => 'Master Website',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.6 3.75 5.7 3.75 9s-1.25 6.4-3.75 9c-2.5-2.6-3.75-5.7-3.75-9S9.5 5.6 12 3Z"/></svg>',
        ],
        [
            'route' => 'scan-results.create',
            'label' => 'Scan Website',
            'permission' => 'scans.create',
            'active' => ['scan-results.create'],
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>',
        ],
        [
            'route' => 'scan-results.index',
            'label' => 'Riwayat Scan',
            'permission' => 'scans.viewAny',
            'active' => ['scan-results.index', 'scan-results.show', 'scan-results.status', 'scan-results.destroy'],
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v5h5"/><path d="M3.05 13A9 9 0 1 0 6 5.3L3 8"/><path d="M12 7v5l4 2"/></svg>',
        ],
        [
            'route' => 'reports.index',
            'label' => 'Laporan',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6M9 13h6M9 17h6M9 9h1"/></svg>',
        ],
        [
            'route' => 'notifications.index',
            'label' => 'Notifikasi',
            'permission' => 'notifications.view',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>',
        ],
        [
            'route' => 'keywords.index',
            'label' => 'Database Keyword',
            'permission' => 'keywords.viewAny',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="m7 7 10 10M7 17 17 7"/></svg>',
        ],
        [
            'route' => 'activity-logs.index',
            'label' => 'Activity Log',
            'permission' => 'activity_logs.viewAny',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="9"/></svg>',
        ],
        [
            'route' => 'settings.edit',
            'label' => 'Pengaturan',
            'permission' => 'settings.manage',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1Z"/></svg>',
        ],
    ];
@endphp

<nav class="flex flex-1 flex-col gap-1 overflow-y-auto px-3 py-4">
    @foreach ($links as $link)
        @if (! isset($link['permission']) || auth()->user()->can($link['permission']))
            @php
                $activePatterns = $link['active'] ?? [explode('.', $link['route'])[0] . '*'];
                $isActive = collect($activePatterns)->contains(fn ($pattern) => request()->routeIs($pattern));
            @endphp
            <a href="{{ route($link['route']) }}"
               class="sidebar-link {{ $isActive ? 'active' : '' }}"
               :class="$store.sidebar.collapsed && 'lg:justify-center'">
                {!! $link['icon'] !!}
                <span :class="$store.sidebar.collapsed && 'lg:hidden'">{{ $link['label'] }}</span>
            </a>
        @endif
    @endforeach
</nav>
