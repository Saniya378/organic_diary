<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'deal_no',
        'buyer_id',
        'seller_id',
        'product_id',
        'product_variant_id',
        'address_id',
        'title',
        'duration_type',
        'total_days',
        'delivered_days',
        'quantity_per_day',
        'unit_price',
        'discount_percent',
        'total_amount',
        'delivery_slot',
        'start_date',
        'end_date',
        'status',
        'payment_method',
        'payment_status',
        'special_instructions',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_days' => 'integer',
        'delivered_days' => 'integer',
        'quantity_per_day' => 'integer',
        'unit_price' => 'integer',
        'discount_percent' => 'integer',
        'total_amount' => 'integer',
    ];

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(DealDelivery::class)->orderBy('scheduled_date');
    }

    public function getProgressPercentAttribute(): int
    {
        if ($this->total_days <= 0) return 0;
        return (int) min(100, round(($this->delivered_days / $this->total_days) * 100));
    }

    public function getRemainingDaysAttribute(): int
    {
        return max(0, $this->total_days - $this->delivered_days);
    }

    public function getDurationLabelAttribute(): string
    {
        return match ($this->duration_type) {
            '3_days' => '3 Days Deal',
            '1_week' => '1 Week Deal (7 Days)',
            '1_month' => '1 Month Deal (30 Days)',
            default => "{$this->total_days} Days Deal",
        };
    }

    public function getSlotLabelAttribute(): string
    {
        return $this->delivery_slot === 'evening'
            ? 'Evening (5:00 PM - 7:00 PM)'
            : 'Morning (6:00 AM - 8:00 AM)';
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'active' => 'badge bg-success',
            'paused' => 'badge bg-warning text-dark',
            'completed' => 'badge bg-primary',
            'cancelled' => 'badge bg-secondary',
            default => 'badge bg-light text-dark',
        };
    }

    /**
     * Generate next sequential deal number (e.g. DEAL-2026-0001)
     */
    public static function generateDealNo(): string
    {
        $year = date('Y');
        $count = static::whereYear('created_at', $year)->count() + 1;
        return sprintf('DEAL-%s-%04d', $year, $count);
    }
}
