<?php

namespace App\Repositories\Contracts;

use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

interface OrderRepositoryInterface
{
    public function findById(int $id): ?Order;

    public function create(array $data): Order;

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    public function paginateForUser(User $user, int $perPage = 15): LengthAwarePaginator;

    public function getRecent(int $limit = 10): Collection;

    public function getMonthlyRevenue(int $year): array;

    public function getMonthlyOrderCounts(int $year): array;

    public function getTopProducts(int $limit = 5): SupportCollection;

    public function updateStatus(Order $order, string $status): Order;

    public function totalRevenue(): float;

    public function count(): int;

    public function countByStatus(string $status): int;
}
