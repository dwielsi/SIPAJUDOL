<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;

class MailSettingsService
{
    /**
     * Override Laravel's mail config at runtime with SMTP settings stored
     * in the settings table, so the mailer can be configured from the UI
     * instead of the .env file. Falls back to .env values when a setting
     * is not filled in.
     */
    public function apply(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        $setting = Setting::first();

        if (! $setting || ! $setting->smtp_host) {
            return;
        }

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => $setting->smtp_host,
            'mail.mailers.smtp.port' => $setting->smtp_port ?: 587,
            'mail.mailers.smtp.username' => $setting->smtp_username,
            'mail.mailers.smtp.password' => $setting->smtp_password,
            'mail.mailers.smtp.scheme' => $this->resolveScheme($setting->smtp_encryption),
            'mail.from.address' => $setting->smtp_username ?: config('mail.from.address'),
            'mail.from.name' => $setting->instansi_name ?: config('mail.from.name'),
        ]);
    }

    /**
     * Map the human-friendly encryption choice to a Symfony Mailer scheme.
     * "tls" (STARTTLS, e.g. port 587) is negotiated automatically with a
     * null scheme; "ssl" needs the implicit-TLS "smtps" scheme (port 465).
     */
    private function resolveScheme(?string $encryption): ?string
    {
        return match ($encryption) {
            'ssl' => 'smtps',
            default => null,
        };
    }
}
