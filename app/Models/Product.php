<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'seller_id',
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'unit',
        'stock',
        'batch_no',
        'produced_on',
        'is_organic',
        'deals_enabled',
        'status',
        'rejection_reason',
    ];

    protected $casts = [
        'price' => 'integer',
        'stock' => 'integer',
        'is_organic' => 'boolean',
        'deals_enabled' => 'boolean',
        'produced_on' => 'date',
    ];

    /**
     * Scope only approved products from active & approved sellers.
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('products.status', 'approved')
            ->whereHas('seller', function ($q) {
                $q->where('status', 'active')
                  ->whereHas('sellerProfile', function ($sp) {
                      $sp->where('status', 'approved');
                  });
            });
    }

    /**
     * Scope products that have recurring deals enabled by seller.
     */
    public function scopeDealsEnabled(Builder $query): Builder
    {
        return $query->where('products.deals_enabled', true);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'Rs ' . number_format($this->price);
    }

    public function getIsApprovedAttribute(): bool
    {
        return $this->status === 'approved';
    }

    public function getAverageRatingAttribute(): float
    {
        return round((float) ($this->reviews()->avg('rating') ?? 0), 1);
    }

    public function getReviewCountAttribute(): int
    {
        return (int) $this->reviews()->count();
    }

    public function getPrimaryImageAttribute(): ?string
    {
        return $this->images->first()?->path;
    }

    /**
     * Check if product stock is below threshold and dispatch alert notification to seller.
     */
    public function checkAndDispatchLowStockAlert(): void
    {
        $threshold = (int) Setting::get('low_stock_threshold', 10);
        $freshStock = (int) $this->stock;

        if ($freshStock <= $threshold) {
            $sellerId = $this->seller_id;
            $title = $freshStock <= 0 ? "🚨 Sold Out: {$this->name}" : "⚠️ Low Stock Alert: {$this->name}";
            $message = $freshStock <= 0 
                ? "Your organic dairy item '{$this->name}' is now completely SOLD OUT. Update inventory or harvest fresh batches."
                : "Only {$freshStock} unit(s) remaining for '{$this->name}' (Low stock threshold: {$threshold}). Please harvest or update inventory.";

            // Prevent spamming unread duplicate notifications within 6 hours
            $alreadyNotified = Notification::where('user_id', $sellerId)
                ->where('type', 'low_stock')
                ->where('title', $title)
                ->where('is_read', false)
                ->where('created_at', '>=', now()->subHours(6))
                ->exists();

            if (!$alreadyNotified) {
                Notification::create([
                    'user_id' => $sellerId,
                    'title' => $title,
                    'message' => $message,
                    'type' => 'low_stock',
                    'action_url' => route('seller.inventory.index'),
                    'is_read' => false,
                ]);
            }
        }
    }
}
