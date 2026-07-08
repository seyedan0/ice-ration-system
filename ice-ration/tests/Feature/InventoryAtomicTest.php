<?php

namespace Tests\Feature;

use App\Models\Citizen;
use App\Models\DailyTicket;
use App\Models\Station;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryAtomicTest extends TestCase
{
    use RefreshDatabase;

    public function test_claim_fails_and_ticket_stays_pending_when_stock_is_insufficient(): void
    {
        $station = Station::factory()->create(['current_stock' => 2]);
        $agent = User::factory()->stationAgent()->create(['station_id' => $station->id]);
        $citizen = Citizen::factory()->create(['preferred_station_id' => $station->id, 'daily_ration' => 5]);
        $ticket = DailyTicket::factory()->create([
            'citizen_id' => $citizen->id,
            'station_id' => $station->id,
            'allocated_blocks' => 5,
        ]);

        $response = $this->actingAs($agent)->postJson("/agent/tickets/{$ticket->id}/claim");

        $response->assertStatus(422);

        $ticket->refresh();
        $station->refresh();

        $this->assertSame(DailyTicket::STATUS_PENDING, $ticket->status, 'Ticket must not be marked claimed if stock deduction failed.');
        $this->assertSame(2, $station->current_stock, 'Stock must remain unchanged (transaction rolled back).');
    }

    public function test_stock_never_goes_negative_across_multiple_claims(): void
    {
        $station = Station::factory()->create(['current_stock' => 5]);
        $agent = User::factory()->stationAgent()->create(['station_id' => $station->id]);

        $tickets = collect(range(1, 3))->map(function () use ($station) {
            $citizen = Citizen::factory()->create(['preferred_station_id' => $station->id, 'daily_ration' => 3]);

            return DailyTicket::factory()->create([
                'citizen_id' => $citizen->id,
                'station_id' => $station->id,
                'allocated_blocks' => 3,
            ]);
        });

        $successes = 0;

        foreach ($tickets as $ticket) {
            $response = $this->actingAs($agent)->postJson("/agent/tickets/{$ticket->id}/claim");
            if ($response->status() === 200) {
                $successes++;
            }
        }

        $station->refresh();

        $this->assertGreaterThanOrEqual(0, $station->current_stock, 'Stock must never go negative.');
        $this->assertSame(1, $successes, 'Only one 3-block claim should succeed with 5 in stock.');
        $this->assertSame(2, $station->current_stock);
    }
}
