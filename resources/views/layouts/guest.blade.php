<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SIPAJUDOL') }}</title>

        @include('partials.theme-init')

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|poppins:600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="flex min-h-screen flex-col items-center justify-center bg-surface px-4 py-10 dark:bg-slate-900">
            <div class="mb-8 flex flex-col items-center gap-3 text-center">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-primary-600 text-white shadow-card">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z" />
                    </svg>
                </div>
                <div>
                    <h1 class="font-heading text-lg font-semibold text-slate-900 dark:text-white">SIPAJUDOL</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Sistem Informasi Pemantauan &amp; Pelaporan Website Judol</p>
                </div>
            </div>

            <div class="w-full sm:max-w-md">
                <div class="rounded-2xl border border-slate-200 bg-white px-6 py-8 shadow-card dark:border-slate-800 dark:bg-slate-800/60">
                    {{ $slot }}
                </div>
            </div>

            <p class="mt-8 text-xs text-slate-400 dark:text-slate-500">
                &copy; {{ now()->year }} Dinas Komunikasi dan Informatika Kabupaten Kubu Raya
            </p>
        </div>
    </body>
</html>
