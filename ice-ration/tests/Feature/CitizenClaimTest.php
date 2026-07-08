<?php

namespace Tests\Feature;

use App\Models\Citizen;
use App\Models\DailyTicket;
use App\Models\Station;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CitizenClaimTest extends TestCase
{
    use RefreshDatabase;

    public function test_agent_can_claim_a_pending_ticket_once(): void
    {
        $station = Station::factory()->create(['current_stock' => 50]);
        $agent = User::factory()->stationAgent()->create(['station_id' => $station->id]);
        $citizen = Citizen::factory()->create(['preferred_station_id' => $station->id, 'daily_ration' => 3]);
        $ticket = DailyTicket::factory()->create([
            'citizen_id' => $citizen->id,
            'station_id' => $station->id,
            'allocated_blocks' => 3,
        ]);

        $response = $this->actingAs($agent)->postJson("/agent/tickets/{$ticket->id}/claim");

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $ticket->refresh();
        $this->assertSame(DailyTicket::STATUS_CLAIMED, $ticket->status);
        $this->assertNotNull($ticket->claimed_at);
        $this->assertSame($agent->id, $ticket->claimed_by_agent_id);

        $station->refresh();
        $this->assertSame(47, $station->current_stock);
    }

    public function test_claiming_the_same_ticket_twice_fails_the_second_time(): void
    {
        $station = Station::factory()->create(['current_stock' => 50]);
        $agent = User::factory()->stationAgent()->create(['station_id' => $station->id]);
        $citizen = Citizen::factory()->create(['preferred_station_id' => $station->id, 'daily_ration' => 2]);
        $ticket = DailyTicket::factory()->create([
            'citizen_id' => $citizen->id,
            'station_id' => $station->id,
            'allocated_blocks' => 2,
        ]);

        $first = $this->actingAs($agent)->postJson("/agent/tickets/{$ticket->id}/claim");
        $first->assertOk();

        $second = $this->actingAs($agent)->postJson("/agent/tickets/{$ticket->id}/claim");
        $second->assertStatus(409);
        $second->assertJson(['success' => false]);

        $station->refresh();
        $this->assertSame(48, $station->current_stock, 'Stock should only be deducted once.');
    }

    public function test_agent_cannot_claim_a_ticket_from_a_different_station(): void
    {
        $stationA = Station::factory()->create();
        $stationB = Station::factory()->create();
        $agent = User::factory()->stationAgent()->create(['station_id' => $stationA->id]);
        $citizen = Citizen::factory()->create(['preferred_station_id' => $stationB->id]);
        $ticket = DailyTicket::factory()->create([
            'citizen_id' => $citizen->id,
            'station_id' => $stationB->id,
        ]);

        $response = $this->actingAs($agent)->postJson("/agent/tickets/{$ticket->id}/claim");

        $response->assertStatus(403);
    }

    public function test_validate_endpoint_finds_citizen_by_any_identifier(): void
    {
        $station = Station::factory()->create();
        $agent = User::factory()->stationAgent()->create(['station_id' => $station->id]);
        $citizen = Citizen::factory()->create(['preferred_station_id' => $station->id]);
        DailyTicket::factory()->create([
            'citizen_id' => $citizen->id,
            'station_id' => $station->id,
        ]);

        foreach ([$citizen->national_id, $citizen->mobile, $citizen->qr_code] as $identifier) {
            $response = $this->actingAs($agent)->postJson('/agent/tickets/validate', ['identifier' => $identifier]);
            $response->assertOk();
            $response->assertJsonPath('data.status', 'approved');
            $response->assertJsonPath('data.citizen_name', $citizen->full_name);
        }
    }
}
