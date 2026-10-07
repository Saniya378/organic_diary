<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrderGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'group_no',
        'subtotal',
        'delivery_total',
        'discount',
        'grand_total',
        'payment_method',
        'shipping_full_name',
        'shipping_phone',
        'shipping_city',
        'shipping_area',
        'shipping_address_line',
    ];

    protected $casts = [
        'subtotal' => 'integer',
        'delivery_total' => 'integer',
        'discount' => 'integer',
        'grand_total' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getFormattedGrandTotalAttribute(): string
    {
        return 'Rs ' . number_format($this->grand_total);
    }
}
