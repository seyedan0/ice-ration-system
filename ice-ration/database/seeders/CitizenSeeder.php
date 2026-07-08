<?php

namespace Database\Seeders;

use App\Models\Citizen;
use App\Models\Station;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CitizenSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $stationIds = Station::query()->pluck('id');

        if ($stationIds->isEmpty()) {
            return;
        }

        for ($i = 1; $i <= 20; $i++) {
            $nationalId = str_pad((string) (1000000000 + $i), 10, '0', STR_PAD_LEFT);
            $mobile = '091' . str_pad((string) $i, 8, '0', STR_PAD_LEFT);

            Citizen::query()->updateOrCreate(
                ['national_id' => $nationalId],
                [
                    'full_name' => 'Citizen ' . $i,
                    'mobile' => $mobile,
                    'qr_code' => (string) Str::uuid(),
                    'daily_ration' => [1, 1, 2, 2, 3][$i % 5],
                    'preferred_station_id' => $stationIds->get($i % $stationIds->count()),
                    'is_active' => true,
                ]
            );
        }
    }
}
