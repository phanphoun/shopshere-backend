<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;
use App\Repositories\Contracts\WishlistRepositoryInterface;
use Illuminate\Validation\ValidationException;

class WishlistService
{
    public function __construct(
        protected WishlistRepositoryInterface $wishlistRepository
    ) {}

    public function getForUser(User $user)
    {
        return $this->wishlistRepository->getForUser($user->id);
    }

    public function toggle(User $user, Product $product): array
    {
        $added = $this->wishlistRepository->toggle($user->id, $product);

        return [
            'product_id' => $product->id,
            'in_wishlist' => $added,
            'message' => $added ? 'Added to wishlist.' : 'Removed from wishlist.',
        ];
    }

    public function remove(User $user, Product $product): bool
    {
        return $this->wishlistRepository->remove($user->id, $product);
    }
}
