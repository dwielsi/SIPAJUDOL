<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h1 class="truncate font-heading text-base font-semibold text-slate-900 dark:text-white">Notifikasi</h1>
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <x-button variant="secondary" type="submit">Tandai Semua Dibaca</x-button>
            </form>
        </div>
    </x-slot>

    <x-card :padding="false">
        @if ($notifications->isEmpty())
            <x-empty-state title="Belum ada notifikasi" />
        @else
            <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach ($notifications as $notification)
                    <li class="flex items-start justify-between gap-4 px-5 py-4 {{ $notification->is_read ? '' : 'bg-primary-50/40 dark:bg-primary-500/5' }}">
                        <div class="flex items-start gap-3">
                            <span class="text-lg leading-none">{{ $notification->typeIcon() }}</span>
                            <div>
                                <p class="text-sm font-medium text-slate-800 dark:text-slate-100">{{ $notification->title }}</p>
                                <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">{{ $notification->message }}</p>
                                <p class="mt-1 text-xs text-slate-400">{{ $notification->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        @unless ($notification->is_read)
                            <form method="POST" action="{{ route('notifications.read', $notification) }}">
                                @csrf
                                <button type="submit" class="whitespace-nowrap text-xs font-semibold text-primary-600 hover:underline dark:text-primary-400">Tandai dibaca</button>
                            </form>
                        @endunless
                    </li>
                @endforeach
            </ul>
        @endif
    </x-card>

    <div class="mt-4">{{ $notifications->links() }}</div>
</x-app-layout>
