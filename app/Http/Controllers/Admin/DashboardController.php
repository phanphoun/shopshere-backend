<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    /**
     * Admin dashboard.
     */
    public function index(): View
    {
        $stats           = $this->dashboardService->stats();
        $monthlySales    = $this->dashboardService->monthlySales();
        $topProducts     = $this->dashboardService->topProducts(5);
        $recentOrders    = $this->dashboardService->recentOrders(10);

        return view('admin.dashboard.index', compact(
            'stats',
            'monthlySales',
            'topProducts',
            'recentOrders'
        ));
    }
}
