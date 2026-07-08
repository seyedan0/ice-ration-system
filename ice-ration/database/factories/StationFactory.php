<?php

namespace Database\Factories;

use App\Models\Station;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Station>
 */
class StationFactory extends Factory
{
    protected $model = Station::class;

    public function definition(): array
    {
        return [
            'name' => 'Station ' . fake()->unique()->city(),
            'address' => fake()->address(),
            'current_stock' => 100,
            'is_active' => true,
        ];
    }
}
