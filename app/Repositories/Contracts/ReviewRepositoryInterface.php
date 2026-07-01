<?php

namespace App\Repositories\Contracts;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ReviewRepositoryInterface
{
    public function paginateForProduct(int $productId, int $perPage = 10): LengthAwarePaginator;

    public function create(User $user, Product $product, int $rating, ?string $comment): Review;

    public function averageRating(int $productId): float;
}
