<?php

namespace Tests\Feature;

use App\Models\Citizen;
use App\Models\Delivery;
use App\Models\Station;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanelSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders(): void
    {
        $this->get('/login')->assertOk()->assertSee('Sign In');
    }

    public function test_guest_is_redirected_from_protected_routes(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/login');
        $this->get('/agent/dashboard')->assertRedirect('/login');
        $this->get('/manager/dashboard')->assertRedirect('/login');
    }

    public function test_super_admin_can_view_all_admin_pages(): void
    {
        $station = Station::factory()->create();
        Citizen::factory()->create(['preferred_station_id' => $station->id]);
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)->get('/admin/dashboard')->assertOk();
        $this->actingAs($admin)->get('/admin/analytics')->assertOk()->assertSee('Analytics');
        $this->actingAs($admin)->get('/admin/analytics/export')->assertOk();
        $this->actingAs($admin)->get('/admin/stations')->assertOk();
        $this->actingAs($admin)->get('/admin/stations/create')->assertOk();
        $this->actingAs($admin)->get('/admin/users')->assertOk();
        $this->actingAs($admin)->get('/admin/users/create')->assertOk();
        $this->actingAs($admin)->get('/admin/citizens')->assertOk();
        $this->actingAs($admin)->get('/admin/citizens/create')->assertOk();
        $this->actingAs($admin)->get('/admin/inventory')->assertOk();
    }

    public function test_agent_cannot_access_admin_pages(): void
    {
        $station = Station::factory()->create();
        $agent = User::factory()->stationAgent()->create(['station_id' => $station->id]);

        $this->actingAs($agent)->get('/admin/dashboard')->assertForbidden();
    }

    public function test_station_agent_panel_pages_render(): void
    {
        $station = Station::factory()->create();
        $agent = User::factory()->stationAgent()->create(['station_id' => $station->id]);
        Delivery::factory()->create(['station_id' => $station->id]);

        $this->actingAs($agent)->get('/agent/dashboard')->assertOk();
        $this->actingAs($agent)->get('/agent/validate')->assertOk()->assertSee('Validate Citizen');
        $this->actingAs($agent)->get('/agent/deliveries')->assertOk();
    }

    public function test_truck_manager_panel_pages_render(): void
    {
        Station::factory()->create();
        $driver = User::factory()->truckManager()->create();

        $this->actingAs($driver)->get('/manager/dashboard')->assertOk()->assertSee('New Delivery');
        $this->actingAs($driver)->get('/manager/deliveries/history')->assertOk();
    }

    public function test_citizen_card_and_qr_render(): void
    {
        $station = Station::factory()->create();
        $citizen = Citizen::factory()->create(['preferred_station_id' => $station->id]);
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)->get("/admin/citizens/{$citizen->id}/card")->assertOk();
        $this->actingAs($admin)->get("/admin/citizens/{$citizen->id}/qr")->assertOk();
    }
}
