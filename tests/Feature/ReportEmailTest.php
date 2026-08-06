<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\Report;
use App\Models\ScanResult;
use App\Models\Setting;
use App\Models\User;
use App\Models\Website;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ReportEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_page_renders_for_kabid(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole(RoleEnum::Kabid->value);

        $response = $this->actingAs($user)->get(route('settings.edit'));

        $response->assertOk();
        $response->assertSee('Konfigurasi SMTP');
    }

    public function test_operator_cannot_access_settings(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole(RoleEnum::Operator->value);

        $response = $this->actingAs($user)->get(route('settings.edit'));

        $response->assertForbidden();
    }

    public function test_settings_update_persists_smtp_config(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole(RoleEnum::Kabid->value);

        $response = $this->actingAs($user)->put(route('settings.update'), [
            'instansi_name' => 'Diskominfo Test',
            'smtp_host' => 'smtp.mailtrap.io',
            'smtp_port' => 587,
            'smtp_username' => 'user@test.id',
            'smtp_password' => 'secret',
            'smtp_encryption' => 'tls',
        ]);

        $response->assertRedirect(route('settings.edit'));
        $this->assertDatabaseHas('settings', [
            'instansi_name' => 'Diskominfo Test',
            'smtp_host' => 'smtp.mailtrap.io',
        ]);
    }

    public function test_report_send_route_dispatches_mail_and_marks_sent(): void
    {
        Mail::fake();

        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole(RoleEnum::Kabid->value);

        Setting::create(['instansi_name' => 'Diskominfo Test']);

        $website = Website::factory()->create();
        $scanResult = ScanResult::factory()->create(['website_id' => $website->id, 'scan_date' => now()]);
        $report = Report::factory()->create(['scan_result_id' => $scanResult->id, 'report_number' => 'RPT-'.uniqid(), 'report_date' => now(), 'analyst' => 'Tester']);

        $response = $this->actingAs($user)->post(route('reports.send', $report), [
            'email' => 'instansi@example.com',
        ]);

        $response->assertRedirect(route('reports.show', $report));
        $response->assertSessionHas('success');

        Mail::assertSent(\App\Mail\ReportMail::class, function ($mail) use ($report) {
            return $mail->report->is($report);
        });

        $this->assertNotNull($report->fresh()->sent_at);
        $this->assertSame('instansi@example.com', $report->fresh()->sent_to);
    }

    public function test_report_show_renders_send_button_for_kabid(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole(RoleEnum::Kabid->value);

        $website = Website::factory()->create();
        $scanResult = ScanResult::factory()->create(['website_id' => $website->id, 'scan_date' => now()]);
        $report = Report::factory()->create(['scan_result_id' => $scanResult->id, 'report_number' => 'RPT-'.uniqid(), 'report_date' => now(), 'analyst' => 'Tester']);

        $response = $this->actingAs($user)->get(route('reports.show', $report));

        $response->assertOk();
        $response->assertSee('Kirim ke Instansi');
    }
}
