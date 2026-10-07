<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-force-light>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SIDEPSIL') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|poppins:600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="flex min-h-screen flex-col items-center justify-center bg-surface px-4 py-10 dark:bg-gradient-to-br dark:from-slate-900 dark:via-slate-900 dark:to-navy-900">
            <div class="mb-8 flex flex-col items-center gap-3 text-center">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-navy-900 to-navy-500 text-white shadow-lg shadow-navy-900/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-teal-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z" />
                    </svg>
                </div>
                <div>
                    <h1 class="font-heading text-lg font-semibold text-slate-900 dark:text-white">SIDEPSIL</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Sistem Informasi Deteksi &amp; Pelaporan Penyisipan Konten Ilegal</p>
                </div>
            </div>

            <div class="w-full sm:max-w-md">
                <div class="rounded-2xl border border-slate-200 bg-white px-6 py-8 shadow-card dark:border-slate-700/60 dark:bg-gradient-to-b dark:from-slate-700 dark:to-slate-900">
                    {{ $slot }}
                </div>
            </div>

            <p class="mt-8 text-xs text-slate-400 dark:text-slate-500">
                &copy; {{ now()->year }} Dinas Komunikasi dan Informatika Kabupaten Kubu Raya
            </p>
        </div>
    </body>
</html>
