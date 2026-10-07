<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * Akun pada aplikasi ini diidentifikasi lewat username (bukan email), dan
     * tidak semua akun memiliki email yang valid terisi. Jadi pengguna diminta
     * memasukkan username miliknya, lalu sistem mencari email yang terdaftar
     * pada akun tersebut untuk dikirimi tautan reset password.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'username' => ['required', 'string'],
        ]);

        $user = User::where('username', $request->string('username'))->first();

        if (! $user) {
            return back()->withInput()
                ->withErrors(['username' => 'Kami tidak dapat menemukan akun dengan username tersebut.']);
        }

        if (! filter_var((string) $user->email, FILTER_VALIDATE_EMAIL)) {
            return back()->withInput()
                ->withErrors(['username' => 'Akun ini belum memiliki alamat email yang valid. Silakan hubungi administrator untuk mengatur ulang password.']);
        }

        // Kirim tautan reset ke email yang sudah terdaftar pada akun tersebut,
        // bukan ke email yang diketik pengguna, agar tidak bisa disalahgunakan
        // untuk mengirim tautan reset ke sembarang alamat email.
        $status = Password::sendResetLink(['email' => $user->email]);

        return $status == Password::RESET_LINK_SENT
                    ? back()->with('status', 'Kami telah mengirimkan tautan reset password ke email yang terdaftar untuk akun '.$user->username.' ('.$this->maskEmail($user->email).').')
                    : back()->withInput()
                        ->withErrors(['username' => __($status)]);
    }

    /**
     * Sembunyikan sebagian alamat email agar tidak terekspos penuh di layar,
     * misalnya "operator@sipajudol.test" menjadi "op***@sipajudol.test".
     */
    private function maskEmail(string $email): string
    {
        [$local, $domain] = explode('@', $email, 2);

        $visible = mb_substr($local, 0, min(2, mb_strlen($local)));

        return $visible.str_repeat('*', max(mb_strlen($local) - mb_strlen($visible), 3)).'@'.$domain;
    }
}
