<?php

namespace Database\Factories;

use App\Models\Truck;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Truck>
 */
class TruckFactory extends Factory
{
    protected $model = Truck::class;

    public function definition(): array
    {
        return [
            'manager_id' => User::factory()->truckManager(),
            'plate_number' => strtoupper(fake()->bothify('??-####-??')),
            'capacity' => fake()->randomElement([500, 1000, 1500, 2000]),
            'is_active' => true,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
