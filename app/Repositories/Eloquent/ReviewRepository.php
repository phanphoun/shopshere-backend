<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Repositories\Contracts\ReviewRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ReviewRepository implements ReviewRepositoryInterface
{
    public function paginateForProduct(int $productId, int $perPage = 10): LengthAwarePaginator
    {
        return Review::with('user')
            ->where('product_id', $productId)
            ->where('approved', true)
            ->latest()
            ->paginate($perPage);
    }

    public function create(User $user, Product $product, int $rating, ?string $comment): Review
    {
        return Review::updateOrCreate(
            ['user_id' => $user->id, 'product_id' => $product->id],
            ['rating' => $rating, 'comment' => $comment, 'approved' => true]
        );
    }

    public function averageRating(int $productId): float
    {
        return (float) round(Review::where('product_id', $productId)
            ->where('approved', true)
            ->avg('rating') ?? 0, 1);
    }
}
