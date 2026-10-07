<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSettingRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * Halaman ini menggabungkan profil akun pribadi (bisa diakses semua
     * pengguna yang login) dengan profil instansi (hanya untuk pengguna
     * yang punya permission terkait). Tidak digerbangi Gate di level
     * halaman supaya setiap pengguna tetap bisa mengelola profilnya
     * sendiri; bagian instansi disembunyikan di view kalau tidak punya izin.
     */
    public function edit(): View
    {
        $user = auth()->user();
        $canManageSettings = $user->can('settings.manage');

        return view('settings.edit', [
            'user' => $user,
            'canManageSettings' => $canManageSettings,
            'setting' => $canManageSettings ? Setting::firstOrCreate() : null,
        ]);
    }

    public function update(StoreSettingRequest $request): RedirectResponse
    {
        $setting = Setting::firstOrCreate();

        $setting->update($request->validated());

        return redirect()->route('settings.edit')->with('success', 'Pengaturan berhasil disimpan.');
    }
}
