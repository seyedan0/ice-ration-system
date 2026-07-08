<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Citizen extends Model
{
    use HasFactory;

    const CREATED_AT = 'registered_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'full_name',
        'national_id',
        'mobile',
        'qr_code',
        'daily_ration',
        'preferred_station_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'daily_ration' => 'integer',
            'registered_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Citizen $citizen) {
            if (empty($citizen->qr_code)) {
                $citizen->qr_code = (string) Str::uuid();
            }
        });
    }

    public function preferredStation(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'preferred_station_id');
    }

    public function dailyTickets(): HasMany
    {
        return $this->hasMany(DailyTicket::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Look up a citizen by any of their three permanent identifiers.
     */
    public static function findByIdentifier(string $identifier): ?self
    {
        return static::query()
            ->where('national_id', $identifier)
            ->orWhere('mobile', $identifier)
            ->orWhere('qr_code', $identifier)
            ->first();
    }

    public function todayTicket(): ?DailyTicket
    {
        return $this->dailyTickets()
            ->whereDate('ticket_date', today())
            ->first();
    }
}
