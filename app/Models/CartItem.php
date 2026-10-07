<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'product_id',
        'variant_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function getUnitPriceAttribute(): int
    {
        return $this->variant ? $this->variant->price : $this->product->price;
    }

    public function getLineTotalAttribute(): int
    {
        return $this->unit_price * $this->quantity;
    }

    public function getFormattedLineTotalAttribute(): string
    {
        return 'Rs ' . number_format($this->line_total);
    }
}
