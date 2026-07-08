<?php

namespace Database\Seeders;

use App\Models\Station;
use Illuminate\Database\Seeder;

class StationSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $stations = [
            ['name' => 'Sarpol Station 1 - Downtown', 'address' => 'Main Street, District 1', 'current_stock' => 500],
            ['name' => 'Sarpol Station 2 - Riverside', 'address' => 'River Road, District 2', 'current_stock' => 350],
            ['name' => 'Sarpol Station 3 - North Gate', 'address' => 'North Gate Avenue, District 3', 'current_stock' => 420],
            ['name' => 'Sarpol Station 4 - Market', 'address' => 'Central Market, District 4', 'current_stock' => 275],
            ['name' => 'Sarpol Station 5 - East Hills', 'address' => 'East Hills Road, District 5', 'current_stock' => 300],
        ];

        foreach ($stations as $station) {
            Station::query()->updateOrCreate(
                ['name' => $station['name']],
                $station + ['is_active' => true]
            );
        }
    }
}
