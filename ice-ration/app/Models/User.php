<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'mobile', 'password', 'role', 'station_id', 'manager_id', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_STATION_AGENT = 'station_agent';
    public const ROLE_TRUCK_MANAGER = 'truck_manager';
    public const ROLE_TRUCK_DRIVER = 'truck_driver';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * The station this user (agent) is assigned to.
     */
    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    /**
     * The truck manager who owns this driver account (only set for truck_driver users).
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Truck drivers created by this truck manager.
     */
    public function drivers(): HasMany
    {
        return $this->hasMany(User::class, 'manager_id')
            ->where('role', self::ROLE_TRUCK_DRIVER);
    }

    /**
     * Deliveries submitted by this user (as a truck driver / truck manager).
     */
    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class, 'manager_id');
    }

    /**
     * Daily tickets this user (as a station agent) has confirmed/claimed.
     */
    public function claimedTickets(): HasMany
    {
        return $this->hasMany(DailyTicket::class, 'claimed_by_agent_id');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isStationAgent(): bool
    {
        return $this->role === self::ROLE_STATION_AGENT;
    }

    public function isTruckManager(): bool
    {
        return $this->role === self::ROLE_TRUCK_MANAGER;
    }

    public function isTruckDriver(): bool
    {
        return $this->role === self::ROLE_TRUCK_DRIVER;
    }
}
