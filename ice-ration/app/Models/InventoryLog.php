<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryLog extends Model
{
    use HasFactory;

    const CREATED_AT = 'logged_at';
    const UPDATED_AT = null;

    public const TYPE_DELIVERY_IN = 'delivery_in';
    public const TYPE_RATION_OUT = 'ration_out';
    public const TYPE_MANUAL_ADJUSTMENT = 'manual_adjustment';

    protected $fillable = [
        'station_id',
        'change_type',
        'blocks_delta',
        'stock_after',
        'reference_id',
        'performed_by',
    ];

    protected function casts(): array
    {
        return [
            'blocks_delta' => 'integer',
            'stock_after' => 'integer',
            'logged_at' => 'datetime',
        ];
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
