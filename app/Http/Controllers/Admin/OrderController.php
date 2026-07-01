<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrderStatusRequest;
use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepository,
        protected OrderService $orderService
    ) {}

    public function index(Request $request): View
    {
        $filters = [
            'search'         => $request->input('search'),
            'status'         => $request->input('status'),
            'payment_status' => $request->input('payment_status'),
            'from'           => $request->input('from'),
            'to'             => $request->input('to'),
        ];
        $orders = $this->orderRepository->paginate(15, array_filter($filters));

        return view('admin.orders.index', compact('orders', 'filters'));
    }

    public function show(Order $order): View
    {
        $order->load(['items.product', 'user']);

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(OrderStatusRequest $request, Order $order): RedirectResponse
    {
        $this->orderService->updateStatus($order, $request->input('status'));

        return back()->with('success', 'Order status updated.');
    }

    public function invoice(Order $order): View
    {
        $order->load(['items.product', 'user']);

        return view('admin.orders.invoice', compact('order'));
    }
}
