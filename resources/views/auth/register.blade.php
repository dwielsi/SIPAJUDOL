<x-guest-layout>
    <h2 class="mb-1 font-heading text-xl font-semibold text-slate-900 dark:text-white">Daftar Akun Admin</h2>
    <p class="mb-6 text-sm text-slate-500 dark:text-slate-400">
        Halaman ini hanya untuk membuat akun admin pertama kali saat sistem baru dipasang.
    </p>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div>
            <x-input-label for="name" value="Nama" />
            <x-text-input id="name" class="block w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="username" value="Username" />
            <x-text-input id="username" class="block w-full" type="text" name="username" :value="old('username')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('username')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="block w-full" type="email" name="email" placeholder="nama@gmail.com" :value="old('email')" required autocomplete="email" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                Email aktif ini digunakan untuk login dan fitur lupa password.
            </p>
        </div>

        <div class="mt-4">
            <x-input-label for="password" value="Password" />
            <x-password-input id="password" class="block w-full" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" value="Konfirmasi Password" />
            <x-password-input id="password_confirmation" class="block w-full" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <x-button type="submit" variant="primary" class="mt-6 w-full">
            Daftar
        </x-button>

        <p class="mt-4 text-center text-sm text-slate-500 dark:text-slate-400">
            Sudah punya akun?
            <a class="text-primary-600 hover:text-primary-700 hover:underline" href="{{ route('login') }}">Masuk</a>
        </p>
    </form>
</x-guest-layout>
