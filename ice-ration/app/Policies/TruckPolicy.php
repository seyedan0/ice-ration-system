<?php

namespace App\Policies;

use App\Models\Truck;
use App\Models\User;

class TruckPolicy
{
    public function owns(User $user, Truck $truck): bool
    {
        return $user->id === $truck->manager_id;
    }
}
