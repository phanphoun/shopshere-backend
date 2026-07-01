<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Repositories\Contracts\ReviewRepositoryInterface;
use Illuminate\Validation\ValidationException;

class ReviewService
{
    public function __construct(
        protected ReviewRepositoryInterface $reviewRepository
    ) {}

    /**
     * Create or update a review. A user can only review a product once.
     */
    public function review(User $user, Product $product, int $rating, ?string $comment): Review
    {
        if ($rating < 1 || $rating > 5) {
            throw ValidationException::withMessages([
                'rating' => 'Rating must be between 1 and 5.',
            ]);
        }

        return $this->reviewRepository->create($user, $product, $rating, $comment);
    }

    public function listForProduct(int $productId, int $perPage = 10)
    {
        return $this->reviewRepository->paginateForProduct($productId, $perPage);
    }
}
