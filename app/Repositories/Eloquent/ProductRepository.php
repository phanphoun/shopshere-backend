<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository implements ProductRepositoryInterface
{
    public function findById(int $id): ?Product
    {
        return Product::with(['category', 'images'])
            ->withAvg('approvedReviews', 'rating')
            ->withCount('approvedReviews')
            ->withSum('orderItems', 'quantity')
            ->find($id);
    }

    public function findBySlug(string $slug): ?Product
    {
        return Product::with(['category', 'images'])
            ->withAvg('approvedReviews', 'rating')
            ->withCount('approvedReviews')
            ->withSum('orderItems', 'quantity')
            ->where('slug', $slug)
            ->first();
    }

    public function findBySku(string $sku): ?Product
    {
        return Product::where('sku', $sku)->first();
    }

    public function create(array $data): Product
    {
        return Product::create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);
        return $product->fresh(['category', 'images']);
    }

    public function delete(Product $product): bool
    {
        return $product->delete();
    }

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Product::with(['category', 'images'])
            ->withAvg('approvedReviews', 'rating')
            ->withCount('approvedReviews');

        $this->applyFilters($query, $filters);
        $this->applySorting($query, $filters['sort'] ?? 'latest');

        return $query->paginate($perPage);
    }

    public function getFeatured(int $limit = 10): Collection
    {
        return Product::with(['category', 'images'])
            ->withAvg('approvedReviews', 'rating')
            ->featured()
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function getLatest(int $limit = 10): Collection
    {
        return Product::with(['category', 'images'])
            ->withAvg('approvedReviews', 'rating')
            ->active()
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function getBestSellers(int $limit = 10): Collection
    {
        return Product::with(['category', 'images'])
            ->withAvg('approvedReviews', 'rating')
            ->withSum('orderItems as total_sold', 'quantity')
            ->active()
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->get();
    }

    public function getByCategory(string $categorySlug, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $category = \App\Models\Category::where('slug', $categorySlug)->firstOrFail();

        $query = Product::with(['category', 'images'])
            ->withAvg('approvedReviews', 'rating')
            ->withCount('approvedReviews')
            ->where('category_id', $category->id);

        $this->applyFilters($query, $filters);
        $this->applySorting($query, $filters['sort'] ?? 'latest');

        return $query->paginate($perPage);
    }

    public function getRelated(Product $product, int $limit = 8): Collection
    {
        $count = Product::active()->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)->count();

        if ($count <= $limit) {
            return Product::with(['category', 'images'])
                ->withAvg('approvedReviews', 'rating')
                ->active()
                ->where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->latest()
                ->limit($limit)
                ->get();
        }

        $ids = Product::active()->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->inRandomOrder()
            ->limit($limit)
            ->pluck('id');

        return Product::with(['category', 'images'])
            ->withAvg('approvedReviews', 'rating')
            ->whereIn('id', $ids)
            ->get();
    }

    public function decrementStock(Product $product, int $quantity): void
    {
        $product->decrement('stock_quantity', $quantity);
    }

    public function count(): int
    {
        return Product::count();
    }

    /* ------------------------------------------------------------------ */
    /*  Helpers                                                             */
    /* ------------------------------------------------------------------ */

    protected function applyFilters(Builder $query, array $filters): void
    {
        $query->active();

        if (!empty($filters['search'])) {
            $search = static::escapeLike($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['min_price'])) {
            $query->whereRaw('COALESCE(discount_price, price) >= ?', [(float) $filters['min_price']]);
        }

        if (isset($filters['max_price'])) {
            $query->whereRaw('COALESCE(discount_price, price) <= ?', [(float) $filters['max_price']]);
        }

        if (!empty($filters['featured'])) {
            $query->where('featured', true);
        }

        if (!empty($filters['in_stock'])) {
            $query->where('stock_quantity', '>', 0);
        }

        // For admin to see inactive products too
        if (array_key_exists('status', $filters)) {
            if ($filters['status'] === null) {
                return;
            }
            $query->where('status', (bool) $filters['status']);
            $query->withoutGlobalScopes();
        }
    }

    /**
     * Escape special LIKE wildcard characters (% and _) in a search string.
     */
    protected static function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }

    protected function applySorting(Builder $query, string $sort): void
    {
        switch ($sort) {
            case 'price_asc':
                $query->orderByRaw('COALESCE(discount_price, price) asc');
                break;
            case 'price_desc':
                $query->orderByRaw('COALESCE(discount_price, price) desc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'latest':
            default:
                $query->latest();
                break;
        }
    }
}
