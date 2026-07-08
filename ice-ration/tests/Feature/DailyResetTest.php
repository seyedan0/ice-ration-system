<?php

namespace Tests\Feature;

use App\Models\Citizen;
use App\Models\DailyTicket;
use App\Models\Station;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DailyResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_generates_a_ticket_for_every_active_citizen(): void
    {
        $station = Station::factory()->create();
        Citizen::factory()->count(5)->create(['preferred_station_id' => $station->id, 'is_active' => true]);
        Citizen::factory()->count(2)->create(['preferred_station_id' => $station->id, 'is_active' => false]);

        $this->artisan('tickets:daily-reset')->assertSuccessful();

        $this->assertSame(5, DailyTicket::query()->whereDate('ticket_date', today())->count());
        $this->assertSame(5, DailyTicket::query()->where('status', DailyTicket::STATUS_PENDING)->count());
    }

    public function test_command_expires_unclaimed_tickets_from_previous_days_without_rollover(): void
    {
        $station = Station::factory()->create();
        $citizen = Citizen::factory()->create(['preferred_station_id' => $station->id]);

        $yesterdayTicket = DailyTicket::factory()->create([
            'citizen_id' => $citizen->id,
            'station_id' => $station->id,
            'ticket_date' => Carbon::yesterday(),
            'status' => DailyTicket::STATUS_PENDING,
        ]);

        $this->artisan('tickets:daily-reset')->assertSuccessful();

        $yesterdayTicket->refresh();
        $this->assertSame(DailyTicket::STATUS_EXPIRED, $yesterdayTicket->status);

        $todayTicket = DailyTicket::query()
            ->where('citizen_id', $citizen->id)
            ->whereDate('ticket_date', today())
            ->first();

        $this->assertNotNull($todayTicket);
        $this->assertSame(DailyTicket::STATUS_PENDING, $todayTicket->status);
    }

    public function test_command_does_not_duplicate_todays_ticket_if_already_generated(): void
    {
        $station = Station::factory()->create();
        $citizen = Citizen::factory()->create(['preferred_station_id' => $station->id]);

        DailyTicket::factory()->create([
            'citizen_id' => $citizen->id,
            'station_id' => $station->id,
            'ticket_date' => today(),
        ]);

        $this->artisan('tickets:daily-reset')->assertSuccessful();

        $this->assertSame(1, DailyTicket::query()->where('citizen_id', $citizen->id)->whereDate('ticket_date', today())->count());
    }
}
