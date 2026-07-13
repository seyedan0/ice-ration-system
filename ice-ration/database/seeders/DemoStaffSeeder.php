<?php

namespace Database\Seeders;

use App\Models\Station;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoStaffSeeder extends Seeder
{
    /**
     * Seed demo station agents and truck drivers for local testing.
     */
    public function run(): void
    {
        $stations = Station::query()->orderBy('id')->get();

        foreach ($stations as $index => $station) {
            User::query()->updateOrCreate(
                ['mobile' => '0921000' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT)],
                [
                    'name' => 'Agent ' . $station->name,
                    'password' => Hash::make('password'),
                    'role' => User::ROLE_STATION_AGENT,
                    'station_id' => $station->id,
                    'is_active' => true,
                ]
            );
        }

        for ($i = 1; $i <= 2; $i++) {
            User::query()->updateOrCreate(
                ['mobile' => '0931000' . str_pad((string) $i, 3, '0', STR_PAD_LEFT)],
                [
                    'name' => 'Driver ' . $i,
                    'password' => Hash::make('password'),
                    'role' => User::ROLE_TRUCK_MANAGER,
                    'station_id' => null,
                    'is_active' => true,
                ]
            );
        }
    }
}
