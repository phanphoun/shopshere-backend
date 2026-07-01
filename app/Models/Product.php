<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'sku',
        'description',
        'price',
        'discount_price',
        'stock_quantity',
        'featured',
        'status',
        'image',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'featured' => 'boolean',
        'status' => 'boolean',
    ];

    /* ------------------------------------------------------------------ */
    /*  Boot                                                                */
    /* ------------------------------------------------------------------ */

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = static::generateUniqueSlug($product->name);
            }
            if (empty($product->sku)) {
                $product->sku = static::generateSku();
            }
        });

        static::updating(function (Product $product) {
            if ($product->isDirty('name') && empty($product->slug)) {
                $product->slug = static::generateUniqueSlug($product->name, $product->id);
            }
        });
    }

    public static function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;

        $query = static::query()->where('slug', $slug);
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        while ($query->exists()) {
            $slug = $base . '-' . $i++;
            $query = static::query()->where('slug', $slug);
            if ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            }
        }

        return $slug;
    }

    public static function generateSku(): string
    {
        do {
            $sku = 'SKU-' . strtoupper(Str::random(8));
        } while (static::where('sku', $sku)->exists());

        return $sku;
    }

    /* ------------------------------------------------------------------ */
    /*  Relationships                                                       */
    /* ------------------------------------------------------------------ */

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews(): HasMany
    {
        return $this->reviews()->where('approved', true)->latest();
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /* ------------------------------------------------------------------ */
    /*  Accessors                                                           */
    /* ------------------------------------------------------------------ */

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            // Each product gets its own unique picsum image via a descriptive seed
            $seed = $this->id ?? random_int(1, 999999);
            return 'https://picsum.photos/seed/prod-' . $seed . '/400/400';
        }
        if (str_starts_with($this->image, 'http')) return $this->image;
        return asset('storage/' . $this->image);
    }

    public function getFinalPriceAttribute(): float
    {
        return (float) ($this->discount_price ?? $this->price);
    }

    public function getHasDiscountAttribute(): bool
    {
        return $this->discount_price !== null
            && (float) $this->discount_price < (float) $this->price;
    }

    public function getDiscountPercentAttribute(): int
    {
        if (!$this->has_discount) return 0;
        return (int) round((($this->price - $this->discount_price) / $this->price) * 100);
    }

    public function getInStockAttribute(): bool
    {
        return $this->stock_quantity > 0;
    }

    public function getAverageRatingAttribute(): float
    {
        if ($this->relationLoaded('approvedReviewsAvgAggregate')) {
            return (float) round($this->approved_reviews_avg_rating ?? 0, 1);
        }
        if ($this->relationLoaded('approvedReviews')) {
            return (float) round($this->approvedReviews->avg('rating') ?? 0, 1);
        }
        return (float) round($this->approvedReviews()->avg('rating') ?? 0, 1);
    }

    public function getReviewsCountAttribute(): int
    {
        // Use pre-loaded count from withCount('approvedReviews') if available
        if (array_key_exists('approved_reviews_count', $this->attributes)) {
            return (int) $this->attributes['approved_reviews_count'];
        }
        if ($this->relationLoaded('approvedReviews')) {
            return $this->approvedReviews->count();
        }
        return $this->approvedReviews()->count();
    }

    public function getTotalSoldAttribute(): int
    {
        // Use pre-loaded value from withCount or withSum if available
        if (array_key_exists('total_sold', $this->attributes)) {
            return (int) $this->attributes['total_sold'];
        }
        if (array_key_exists('order_items_sum_quantity', $this->attributes)) {
            return (int) $this->attributes['order_items_sum_quantity'];
        }
        // Avoid query when the relationship is already loaded
        if ($this->relationLoaded('orderItems')) {
            return (int) $this->orderItems->sum('quantity');
        }
        return (int) $this->orderItems()->sum('quantity');
    }

    /* ------------------------------------------------------------------ */
    /*  Scopes                                                              */
    /* ------------------------------------------------------------------ */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true)->active();
    }

    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderByDesc('created_at');
    }

    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('stock_quantity', '>', 0);
    }

    public function scopeByPriceRange(Builder $query, ?float $min = null, ?float $max = null): Builder
    {
        if ($min !== null) $query->whereRaw('COALESCE(discount_price, price) >= ?', [$min]);
        if ($max !== null) $query->whereRaw('COALESCE(discount_price, price) <= ?', [$max]);
        return $query;
    }
}
