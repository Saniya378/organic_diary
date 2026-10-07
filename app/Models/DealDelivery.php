<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DealDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'deal_id',
        'day_number',
        'scheduled_date',
        'status',
        'delivered_at',
        'notes',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'delivered_at' => 'datetime',
        'day_number' => 'integer',
    ];

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'delivered' => 'badge bg-success',
            'dispatched' => 'badge bg-info text-dark',
            'scheduled' => 'badge bg-light text-dark border',
            'skipped' => 'badge bg-secondary',
            default => 'badge bg-light text-dark',
        };
    }
}
