<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'variant_id',
        'product_name',
        'unit_price',
        'quantity',
        'line_total',
        'commission_rate',
        'commission_amount',
    ];

    protected $casts = [
        'unit_price' => 'integer',
        'quantity' => 'integer',
        'line_total' => 'integer',
        'commission_rate' => 'float',
        'commission_amount' => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function getFormattedUnitPriceAttribute(): string
    {
        return 'Rs ' . number_format($this->unit_price);
    }

    public function getFormattedLineTotalAttribute(): string
    {
        return 'Rs ' . number_format($this->line_total);
    }
}
