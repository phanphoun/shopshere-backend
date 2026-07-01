<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can review a product.
     * Must have purchased the product before reviewing.
     *
     * @param  int  $productId  The product ID passed from the controller.
     */
    public function create(User $user, int $productId): bool
    {
        if ($productId <= 0) {
            return false;
        }

        return $user->orders()->whereHas('items', function ($q) use ($productId) {
            $q->where('product_id', $productId);
        })->exists();
    }

    public function update(User $user, Review $review): bool
    {
        return $user->id === $review->user_id;
    }

    public function delete(User $user, Review $review): bool
    {
        return $user->id === $review->user_id;
    }
}
