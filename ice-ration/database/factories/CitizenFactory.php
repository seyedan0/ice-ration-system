<?php

namespace Database\Factories;

use App\Models\Citizen;
use App\Models\Station;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Citizen>
 */
class CitizenFactory extends Factory
{
    protected $model = Citizen::class;

    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'national_id' => fake()->unique()->numerify('##########'),
            'mobile' => fake()->unique()->numerify('09#########'),
            'qr_code' => (string) Str::uuid(),
            'daily_ration' => 1,
            'preferred_station_id' => Station::factory(),
            'is_active' => true,
        ];
    }
}
