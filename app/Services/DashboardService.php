<?php

namespace App\Services;

use App\Models\Order;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Carbon\Carbon;

class DashboardService
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepository,
        protected ProductRepositoryInterface $productRepository,
        protected CategoryRepositoryInterface $categoryRepository,
        protected UserRepositoryInterface $userRepository
    ) {}

    public function stats(): array
    {
        return [
            'total_revenue'    => round($this->orderRepository->totalRevenue(), 2),
            'total_products'   => $this->productRepository->count(),
            'total_categories' => $this->categoryRepository->count(),
            'total_orders'     => $this->orderRepository->count(),
            'total_customers'  => $this->userRepository->count(),
            'pending_orders'   => $this->orderRepository->countByStatus(Order::STATUS_PENDING),
            'processing_orders'=> $this->orderRepository->countByStatus(Order::STATUS_PROCESSING),
            'shipped_orders'   => $this->orderRepository->countByStatus(Order::STATUS_SHIPPED),
            'delivered_orders' => $this->orderRepository->countByStatus(Order::STATUS_DELIVERED),
            'cancelled_orders' => $this->orderRepository->countByStatus(Order::STATUS_CANCELLED),
        ];
    }

    public function monthlySales(int $year = null): array
    {
        $year = $year ?: Carbon::now()->year;
        $revenue = $this->orderRepository->getMonthlyRevenue($year);
        $counts  = $this->orderRepository->getMonthlyOrderCounts($year);

        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[] = [
                'month'   => Carbon::create()->month($m)->format('M'),
                'revenue' => (float) ($revenue[$m] ?? 0),
                'orders'  => (int) ($counts[$m] ?? 0),
            ];
        }

        return $months;
    }

    public function topProducts(int $limit = 5)
    {
        return $this->orderRepository->getTopProducts($limit);
    }

    public function recentOrders(int $limit = 10)
    {
        return $this->orderRepository->getRecent($limit);
    }
}
