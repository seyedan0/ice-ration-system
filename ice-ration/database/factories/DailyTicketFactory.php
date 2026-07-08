<?php

namespace Database\Factories;

use App\Models\Citizen;
use App\Models\DailyTicket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DailyTicket>
 */
class DailyTicketFactory extends Factory
{
    protected $model = DailyTicket::class;

    public function definition(): array
    {
        $citizen = Citizen::factory()->create();

        return [
            'citizen_id' => $citizen->id,
            'station_id' => $citizen->preferred_station_id,
            'ticket_date' => today(),
            'allocated_blocks' => $citizen->daily_ration,
            'status' => DailyTicket::STATUS_PENDING,
        ];
    }
}
