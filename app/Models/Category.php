<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'is_active',
        'commission_rate',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'commission_rate' => 'float',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
