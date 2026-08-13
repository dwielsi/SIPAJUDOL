<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSettingRequest;
use App\Models\Setting;
use App\Services\MailSettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(): View
    {
        Gate::authorize('manage-settings');

        return view('settings.edit', [
            'setting' => Setting::firstOrCreate(),
        ]);
    }

    public function update(StoreSettingRequest $request): RedirectResponse
    {
        $setting = Setting::firstOrCreate();

        $data = $request->safe()->except('smtp_password');

        if ($request->filled('smtp_password')) {
            $data['smtp_password'] = (string) $request->input('smtp_password');
        }

        $setting->update($data);

        return redirect()->route('settings.edit')->with('success', 'Pengaturan berhasil disimpan.');
    }

    public function testEmail(Request $request): JsonResponse
    {
        Gate::authorize('manage-settings');

        $request->validate([
            'email' => ['required', 'email'],
        ]);

        app(MailSettingsService::class)->apply();

        try {
            Mail::raw(
                "Ini adalah email uji coba konfigurasi SMTP dari {$request->user()->name} melalui SIDEPSIL.",
                function ($message) use ($request) {
                    $message->to($request->input('email'))
                        ->subject('Uji Coba Konfigurasi SMTP - SIDEPSIL');
                }
            );

            return response()->json(['success' => true, 'message' => 'Email uji coba berhasil dikirim.']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mengirim email: '.$e->getMessage()], 422);
        }
    }
}
