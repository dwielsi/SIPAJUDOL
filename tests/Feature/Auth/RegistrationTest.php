<?php

namespace Tests\Feature\Auth;

use App\Enums\RoleEnum;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered_when_no_admin_exists(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_first_admin_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Kepala Bidang',
            'username' => 'kabid',
            'email' => 'kabid@sipajudol.test',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard'));

        $user = User::where('username', 'kabid')->firstOrFail();
        $this->assertTrue($user->hasRole(RoleEnum::Kabid->value));
    }

    public function test_email_is_required_to_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Kepala Bidang',
            'username' => 'kabid',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['username' => 'kabid']);
    }

    public function test_registration_screen_is_blocked_once_an_admin_exists(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole(RoleEnum::Kabid->value);

        $response = $this->get('/register');

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status', 'Akun admin sudah ada. Silakan login.');
    }

    public function test_registration_submission_is_blocked_once_an_admin_exists(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole(RoleEnum::Kabid->value);

        $response = $this->post('/register', [
            'name' => 'Orang Lain',
            'username' => 'oranglain',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['username' => 'oranglain']);
    }
}
