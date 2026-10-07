<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title . ' - ' : '' }}{{ config('app.name', 'SIDEPSIL') }}</title>

        @include('partials.theme-init')

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|poppins:600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        {{ $head ?? '' }}
    </head>
    <body class="font-sans antialiased">
        <div x-data="{ mobileOpen: false }" class="flex min-h-screen flex-col gap-4 bg-surface p-3 dark:bg-gradient-to-br dark:from-slate-900 dark:via-slate-900 dark:to-navy-900 sm:gap-6 sm:p-4 lg:flex-row lg:p-6">

            <!-- Mobile overlay -->
            <div x-show="mobileOpen"
                 x-transition:enter="transition-opacity ease-linear duration-200"
                 x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-150"
                 x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-40 bg-slate-900/50 lg:hidden" style="display: none;"
                 @click="mobileOpen = false"></div>

            <!-- Sidebar -->
            <aside
                x-cloak
                :class="[mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0', $store.sidebar.collapsed ? 'lg:w-20' : 'lg:w-64']"
                class="sidebar-cloak fixed inset-y-0 left-0 z-50 flex w-64 transform flex-col rounded-r-3xl bg-gradient-to-b from-navy-900 via-navy-900 to-navy-500 text-slate-100 shadow-2xl shadow-navy-900/30 transition-all duration-200 ease-in-out lg:sticky lg:inset-y-auto lg:left-auto lg:top-6 lg:z-auto lg:h-[calc(100vh-3rem)] lg:rounded-3xl">

                <div class="flex h-16 shrink-0 items-center gap-3 px-5">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-teal-500 text-white shadow-lg shadow-teal-500/30">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z" />
                        </svg>
                    </div>
                    <span class="font-heading text-base font-semibold text-white" :class="$store.sidebar.collapsed && 'lg:hidden'">SIDEPSIL</span>
                </div>

                @include('layouts.partials.sidebar-nav')

                <div class="p-3">
                    <button @click="$store.sidebar.toggle()" class="hidden w-full items-center justify-center rounded-xl py-2 text-slate-400 hover:bg-white/5 hover:text-white lg:flex">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform" :class="$store.sidebar.collapsed && 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 19l-7-7 7-7M20 19l-7-7 7-7" />
                        </svg>
                    </button>
                </div>
            </aside>

            <!-- Main column -->
            <div class="flex min-w-0 flex-1 flex-col gap-4 sm:gap-6">
                <!-- Topbar -->
                <header class="sticky top-3 z-30 flex min-h-16 shrink-0 flex-wrap items-center gap-3 rounded-2xl bg-white px-4 py-2 shadow-card transform-gpu will-change-transform isolate sm:top-6 sm:gap-4 sm:px-5 dark:bg-gradient-to-b dark:from-slate-700 dark:to-slate-900">
                    <button @click="mobileOpen = true" class="text-slate-500 hover:text-slate-700 lg:hidden dark:text-slate-400 dark:hover:text-slate-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <div class="min-w-0 flex-1">
                        @isset($header)
                            {{ $header }}
                        @else
                            <h1 class="truncate font-heading text-base font-semibold text-slate-900 dark:text-white">{{ $title ?? 'Dashboard' }}</h1>
                        @endisset
                    </div>

                    @if (request()->routeIs(['websites.*', 'scan-results.*']))
                        <div class="hidden shrink-0 md:flex" x-show="$store.websiteTab.current === 'monitoring'">
                            {{ $headerAction ?? '' }}
                        </div>
                    @endif

                    <button @click="$store.theme.toggle()" class="shrink-0 rounded-xl p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200">
                        <svg x-show="!$store.theme.dark" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/></svg>
                        <svg x-show="$store.theme.dark" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"/></svg>
                    </button>

                    @include('layouts.partials.notification-bell')

                    <x-dropdown align="right" width="56">
                        <x-slot name="trigger">
                            <button class="flex shrink-0 items-center gap-2 rounded-xl py-1.5 pl-1.5 pr-2 hover:bg-slate-100 dark:hover:bg-slate-800">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-teal-500 text-sm font-semibold text-white">
                                    {{ Str::upper(Str::substr(auth()->user()->name, 0, 1)) }}
                                </span>
                                <span class="hidden text-left sm:block">
                                    <span class="block text-sm font-medium leading-tight text-slate-700 dark:text-slate-200">{{ auth()->user()->name }}</span>
                                    <span class="block text-xs leading-tight text-slate-400">{{ auth()->user()->getRoleNames()->first() ? \App\Enums\RoleEnum::from(auth()->user()->getRoleNames()->first())->label() : '' }}</span>
                                </span>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('settings.edit')">Pengaturan</x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();">
                                    Keluar
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>

                    @isset($headerAction)
                        <div class="basis-full md:hidden" x-show="$store.websiteTab.current === 'monitoring'">
                            {{ $headerAction }}
                        </div>
                    @endisset
                </header>

                <main class="flex-1">
                    {{ $slot }}
                </main>
            </div>
        </div>

        @include('partials.flash-toasts')
    </body>
</html>
