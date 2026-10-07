<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordUpdateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Password sekarang hanya bisa diubah lewat Manajemen Akun (oleh admin),
     * bukan self-service oleh pengguna sendiri — lihat UserManagementTest
     * untuk pengujian jalur yang sekarang dipakai.
     */
    public function test_the_self_service_password_update_route_no_longer_exists(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put('/password', [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertNotFound();
    }
}
