@can('notifications.view')
    <div x-data="notificationBell('{{ route('notifications.unread') }}')" class="relative">
        <x-dropdown align="right" width="80">
            <x-slot name="trigger">
                <button class="relative rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9" />
                        <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                    </svg>
                    <span x-show="count > 0" x-text="count > 9 ? '9+' : count" class="absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-danger-500 px-1 text-[10px] font-semibold text-white"></span>
                </button>
            </x-slot>

            <x-slot name="content">
                <div class="max-h-80 overflow-y-auto">
                    <div class="border-b border-slate-100 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-slate-400 dark:border-slate-700">Notifikasi</div>
                    <template x-if="items.length === 0">
                        <p class="px-4 py-6 text-center text-sm text-slate-400">Tidak ada notifikasi baru</p>
                    </template>
                    <template x-for="item in items" :key="item.id">
                        <div class="flex items-start gap-2 px-4 py-3 text-sm hover:bg-slate-50 dark:hover:bg-slate-700/50">
                            <span x-text="item.icon" class="leading-none"></span>
                            <div class="min-w-0">
                                <p class="font-medium text-slate-700 dark:text-slate-200" x-text="item.title"></p>
                                <p class="truncate text-xs text-slate-400" x-text="item.message"></p>
                                <p class="mt-0.5 text-xs text-slate-400" x-text="item.created_at"></p>
                            </div>
                        </div>
                    </template>
                </div>
                <a href="{{ route('notifications.index') }}" class="block border-t border-slate-100 px-4 py-2 text-center text-xs font-semibold text-primary-600 hover:bg-slate-50 dark:border-slate-700 dark:text-primary-400 dark:hover:bg-slate-700/50">
                    Lihat semua notifikasi
                </a>
            </x-slot>
        </x-dropdown>
    </div>
@endcan
