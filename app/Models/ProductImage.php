<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'path',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getUrlAttribute(): string
    {
        if (empty($this->path)) {
            return asset('images/products/fresh_milk.jpg');
        }

        if (file_exists(public_path('storage/' . $this->path))) {
            return asset('storage/' . $this->path);
        }

        if (file_exists(public_path('images/' . $this->path))) {
            return asset('images/' . $this->path);
        }

        if (file_exists(public_path($this->path))) {
            return asset($this->path);
        }

        return asset('storage/' . $this->path);
    }
}
