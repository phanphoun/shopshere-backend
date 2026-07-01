<?php

namespace App\Repositories\Contracts;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ProductRepositoryInterface
{
    public function findById(int $id): ?Product;

    public function findBySlug(string $slug): ?Product;

    public function findBySku(string $sku): ?Product;

    public function create(array $data): Product;

    public function update(Product $product, array $data): Product;

    public function delete(Product $product): bool;

    /**
     * @param  array{
     *   search?: string,
     *   category_id?: int,
     *   min_price?: float,
     *   max_price?: float,
     *   featured?: bool,
     *   in_stock?: bool,
     *   sort?: string,
     *   status?: bool
     * } $filters
     */
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    public function getFeatured(int $limit = 10): Collection;

    public function getLatest(int $limit = 10): Collection;

    public function getBestSellers(int $limit = 10): Collection;

    public function getByCategory(string $categorySlug, int $perPage = 15, array $filters = []): LengthAwarePaginator;

    public function getRelated(Product $product, int $limit = 8): Collection;

    public function decrementStock(Product $product, int $quantity): void;

    public function count(): int;
}
