<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyTicket extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    public const STATUS_PENDING = 'pending';
    public const STATUS_CLAIMED = 'claimed';
    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'citizen_id',
        'station_id',
        'ticket_date',
        'allocated_blocks',
        'status',
        'claimed_at',
        'claimed_by_agent_id',
    ];

    protected function casts(): array
    {
        return [
            'ticket_date' => 'date',
            'allocated_blocks' => 'integer',
            'claimed_at' => 'datetime',
        ];
    }

    public function citizen(): BelongsTo
    {
        return $this->belongsTo(Citizen::class);
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    public function claimedByAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimed_by_agent_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeForToday($query)
    {
        return $query->whereDate('ticket_date', today());
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isClaimed(): bool
    {
        return $this->status === self::STATUS_CLAIMED;
    }
}
