<x-guest-layout>
    <h2 class="mb-1 font-heading text-xl font-semibold text-slate-900 dark:text-white">Lupa Password</h2>
    <p class="mb-6 text-sm text-slate-500 dark:text-slate-400">
        Masukkan username Anda dan kami akan mengirimkan tautan reset password ke email yang terdaftar pada akun tersebut.
    </p>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div>
            <x-input-label for="username" value="Username" />
            <x-text-input id="username" class="block w-full" type="text" name="username" :value="old('username')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('username')" class="mt-2" />
        </div>

        <x-button type="submit" variant="primary" class="mt-6 w-full">
            Kirim Tautan Reset Password
        </x-button>
    </form>
</x-guest-layout>
