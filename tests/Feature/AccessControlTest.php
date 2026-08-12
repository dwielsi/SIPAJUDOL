<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use App\Models\Website;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    private function kabid(): User
    {
        $user = User::factory()->create();
        $user->assignRole(RoleEnum::Kabid->value);

        return $user;
    }

    public function test_kabid_can_view_keyword_management(): void
    {
        $this->actingAs($this->kabid())->get('/keywords')->assertOk();
    }

    public function test_kabid_can_view_activity_logs(): void
    {
        $this->actingAs($this->kabid())->get('/activity-logs')->assertOk();
    }

    public function test_kabid_can_view_monitoring(): void
    {
        $this->actingAs($this->kabid())->get('/monitoring')->assertOk();
    }

    public function test_kabid_can_view_notifications(): void
    {
        $this->actingAs($this->kabid())->get('/notifications')->assertOk();
    }

    public function test_kabid_can_trigger_a_scan(): void
    {
        Queue::fake();

        $website = Website::factory()->create();

        $response = $this->actingAs($this->kabid())
            ->post('/scan-results', ['website_id' => $website->id]);

        $response->assertRedirect();
        $this->assertDatabaseHas('scan_results', ['website_id' => $website->id, 'scan_state' => 'queued']);
    }

    public function test_guest_is_redirected_from_monitoring(): void
    {
        $this->get('/monitoring')->assertRedirect('/login');
    }
}
