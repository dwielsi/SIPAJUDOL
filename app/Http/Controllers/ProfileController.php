<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class ProfileController extends Controller
{
    /**
     * Formulir profil sekarang menyatu dengan halaman Pengaturan
     * (lihat SettingController::edit()). Sistem hanya memakai satu akun,
     * jadi tidak ada fitur tambah/hapus akun.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('settings.edit')->with('status', 'profile-updated');
    }
}
