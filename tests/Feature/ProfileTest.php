<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_url_redirects_to_the_settings_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/profile');

        $response->assertRedirect('/settings');
    }

    public function test_any_authenticated_user_can_see_their_own_profile_on_the_settings_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/settings');

        $response->assertOk();
        $response->assertSee('Informasi Profil');
    }

    public function test_the_settings_page_no_longer_offers_self_service_password_change(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/settings');

        $response->assertOk();
        $response->assertDontSee('Ubah Password');
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/settings');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/settings');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_the_settings_page_no_longer_offers_self_service_account_deletion(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/settings');

        $response->assertOk();
        $response->assertDontSee('Hapus Akun');
    }

    public function test_the_self_service_delete_account_route_no_longer_exists(): void
    {
        $user = User::factory()->create();

        // DELETE /profile tidak lagi terhubung ke aksi hapus akun apa pun
        // (jatuh ke redirect umum bekas URL lama /profile), jadi akun tidak
        // pernah terhapus lewat rute ini.
        $response = $this->actingAs($user)->delete('/profile', [
            'password' => 'password',
        ]);

        $response->assertRedirect('/settings');
        $this->assertNotNull($user->fresh());
    }
}
