<x-guest-layout>
    <h2 class="mb-1 font-heading text-xl font-semibold text-slate-900 dark:text-white">Lupa Password</h2>
    <p class="mb-6 text-sm text-slate-500 dark:text-slate-400">
        Masukkan alamat email Anda dan kami akan mengirimkan tautan untuk mengatur ulang password.
    </p>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="block w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <x-button type="submit" variant="primary" class="mt-6 w-full">
            Kirim Tautan Reset Password
        </x-button>
    </form>
</x-guest-layout>
