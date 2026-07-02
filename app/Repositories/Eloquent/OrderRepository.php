<?php

namespace App\Repositories\Eloquent;

use App\Models\Order;
use App\Models\User;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;

class OrderRepository implements OrderRepositoryInterface
{
    public function findById(int $id): ?Order
    {
        return Order::with(['items', 'user'])->find($id);
    }

    public function create(array $data): Order
    {
        return Order::create($data);
    }

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Order::with(['user', 'items'])->orderByDesc('id');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")
                                                       ->orWhere('email', 'like', "%{$search}%"));
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        if (!empty($filters['from'])) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }

        if (!empty($filters['to'])) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }

        return $query->paginate($perPage);
    }

    public function paginateForUser(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return Order::with('items')
            ->where('user_id', $user->id)
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function getRecent(int $limit = 10): Collection
    {
        return Order::with('user')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    public function getMonthlyRevenue(int $year): array
    {
        return Order::query()
            ->selectRaw("CAST(strftime('%m', created_at) AS INTEGER) as month, SUM(total) as revenue")
            ->whereYear('created_at', $year)
            ->where('payment_status', Order::PAYMENT_PAID)
            ->groupBy('month')
            ->pluck('revenue', 'month')
            ->toArray();
    }

    public function getMonthlyOrderCounts(int $year): array
    {
        return Order::query()
            ->selectRaw("CAST(strftime('%m', created_at) AS INTEGER) as month, COUNT(*) as count")
            ->whereYear('created_at', $year)
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();
    }

    public function getTopProducts(int $limit = 5): SupportCollection
    {
        return DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select(
                'products.id',
                'products.name',
                'products.image',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.subtotal) as total_revenue')
            )
            ->groupBy('products.id', 'products.name', 'products.image')
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->get();
    }

    public function updateStatus(Order $order, string $status): Order
    {
        $order->status = $status;

        if ($status === Order::STATUS_SHIPPED && !$order->shipped_at) {
            $order->shipped_at = now();
        }

        if ($status === Order::STATUS_DELIVERED && !$order->delivered_at) {
            $order->delivered_at = now();
        }

        $order->save();
        return $order;
    }

    public function totalRevenue(): float
    {
        return (float) Order::where('payment_status', Order::PAYMENT_PAID)->sum('total');
    }

    public function count(): int
    {
        return Order::count();
    }

    public function countByStatus(string $status): int
    {
        return Order::where('status', $status)->count();
    }
}
