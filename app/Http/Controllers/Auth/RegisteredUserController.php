<?php

namespace App\Http\Controllers\Auth;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    /**
     * Halaman "Daftar Akun" hanya untuk membuat akun admin pertama kali saat
     * sistem baru diinstal. Begitu sudah ada akun admin (role Kabid),
     * pendaftaran ditutup dan pengguna diarahkan untuk login.
     */
    public function create(): View|RedirectResponse
    {
        if (User::adminAccountExists()) {
            return redirect()->route('login')
                ->with('status', 'Akun admin sudah ada. Silakan login.');
        }

        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        // Dicek ulang di sini untuk mencegah kondisi balapan (race condition)
        // jika dua orang membuka form pendaftaran secara bersamaan.
        if (User::adminAccountExists()) {
            return redirect()->route('login')
                ->with('status', 'Akun admin sudah ada. Silakan login.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:'.User::class],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Jaga-jaga apabila seeder role & permission belum pernah dijalankan
        // saat instalasi awal, supaya role "kabid" tetap tersedia untuk
        // diberikan ke admin pertama ini.
        if (Role::where('name', RoleEnum::Kabid->value)->where('guard_name', 'web')->doesntExist()) {
            (new RolePermissionSeeder)->run();
        }

        $user->assignRole(RoleEnum::Kabid->value);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('dashboard');
    }
}
