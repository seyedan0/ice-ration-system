<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class Station extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'current_stock',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'current_stock' => 'integer',
        ];
    }

    public function agents(): HasMany
    {
        return $this->hasMany(User::class)->where('role', User::ROLE_STATION_AGENT);
    }

    public function citizens(): HasMany
    {
        return $this->hasMany(Citizen::class, 'preferred_station_id');
    }

    public function dailyTickets(): HasMany
    {
        return $this->hasMany(DailyTicket::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }

    public function inventoryLogs(): HasMany
    {
        return $this->hasMany(InventoryLog::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Atomically deduct blocks from this station's stock and write an
     * inventory log entry. Throws if the resulting stock would go negative.
     *
     * Must be called from within a DB transaction that already holds a
     * row lock on this station (see DailyTicket claim flow).
     */
    public function deductStock(int $blocks, int $performedBy, ?int $referenceId = null, ?string $changeType = null): void
    {
        if ($blocks <= 0) {
            throw new RuntimeException('Blocks to deduct must be positive.');
        }

        try {
            $this->decrement('current_stock', $blocks);
        } catch (QueryException $e) {
            throw new RuntimeException('Insufficient station stock.', previous: $e);
        }

        $this->refresh();

        InventoryLog::create([
            'station_id' => $this->id,
            'change_type' => $changeType ?? InventoryLog::TYPE_RATION_OUT,
            'blocks_delta' => -$blocks,
            'stock_after' => $this->current_stock,
            'reference_id' => $referenceId,
            'performed_by' => $performedBy,
        ]);
    }

    /**
     * Atomically add blocks to this station's stock and write an
     * inventory log entry.
     */
    public function addStock(int $blocks, int $performedBy, string $changeType = null, ?int $referenceId = null): void
    {
        if ($blocks <= 0) {
            throw new RuntimeException('Blocks to add must be positive.');
        }

        $this->increment('current_stock', $blocks);
        $this->refresh();

        InventoryLog::create([
            'station_id' => $this->id,
            'change_type' => $changeType ?? InventoryLog::TYPE_DELIVERY_IN,
            'blocks_delta' => $blocks,
            'stock_after' => $this->current_stock,
            'reference_id' => $referenceId,
            'performed_by' => $performedBy,
        ]);
    }
}
