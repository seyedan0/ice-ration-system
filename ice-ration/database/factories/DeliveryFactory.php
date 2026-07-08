<?php

namespace Database\Factories;

use App\Models\Delivery;
use App\Models\Station;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Delivery>
 */
class DeliveryFactory extends Factory
{
    protected $model = Delivery::class;

    public function definition(): array
    {
        return [
            'station_id' => Station::factory(),
            'driver_id' => User::factory()->truckDriver(),
            'blocks_delivered' => 100,
            'status' => Delivery::STATUS_PENDING,
        ];
    }
}
