@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard Overview')

@section('content')
    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="icon bg-success-subtle text-success">
                    <i class="bi bi-currency-dollar"></i>
                </div>
                <div>
                    <p class="label">Total Revenue</p>
                    <p class="value">${{ number_format($stats['total_revenue'], 2) }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="icon bg-primary-subtle text-primary">
                    <i class="bi bi-cart-check"></i>
                </div>
                <div>
                    <p class="label">Total Orders</p>
                    <p class="value">{{ number_format($stats['total_orders']) }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="icon bg-info-subtle text-info">
                    <i class="bi bi-box-seam"></i>
                </div>
                <div>
                    <p class="label">Total Products</p>
                    <p class="value">{{ number_format($stats['total_products']) }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="icon bg-warning-subtle text-warning">
                    <i class="bi bi-people"></i>
                </div>
                <div>
                    <p class="label">Total Customers</p>
                    <p class="value">{{ number_format($stats['total_customers']) }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl">
            <div class="stat-card">
                <div class="icon bg-secondary-subtle text-secondary">
                    <i class="bi bi-tags"></i>
                </div>
                <div>
                    <p class="label">Categories</p>
                    <p class="value">{{ $stats['total_categories'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl">
            <div class="stat-card">
                <div class="icon bg-warning-subtle text-warning">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div>
                    <p class="label">Pending</p>
                    <p class="value">{{ $stats['pending_orders'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl">
            <div class="stat-card">
                <div class="icon bg-info-subtle text-info">
                    <i class="bi bi-arrow-repeat"></i>
                </div>
                <div>
                    <p class="label">Processing</p>
                    <p class="value">{{ $stats['processing_orders'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl">
            <div class="stat-card">
                <div class="icon bg-primary-subtle text-primary">
                    <i class="bi bi-truck"></i>
                </div>
                <div>
                    <p class="label">Shipped</p>
                    <p class="value">{{ $stats['shipped_orders'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl">
            <div class="stat-card">
                <div class="icon bg-success-subtle text-success">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div>
                    <p class="label">Delivered</p>
                    <p class="value">{{ $stats['delivered_orders'] }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="card p-4">
                <h5 class="mb-3">Monthly Sales ({{ now()->year }})</h5>
                <canvas id="salesChart" height="100"></canvas>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card p-4">
                <h5 class="mb-3">Orders Overview</h5>
                <canvas id="ordersChart" height="200"></canvas>
            </div>
        </div>
    </div>

    {{-- Top Products & Recent Orders --}}
    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card p-4">
                <h5 class="mb-3">Top Selling Products</h5>
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="text-end">Sold</th>
                            <th class="text-end">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($topProducts as $product)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $product->image ? asset('storage/'.$product->image) : 'https://via.placeholder.com/40' }}"
                                             alt="" class="product-thumb me-2">
                                        <span>{{ Str::limit($product->name, 30) }}</span>
                                    </div>
                                </td>
                                <td class="text-end">{{ $product->total_sold }}</td>
                                <td class="text-end">${{ number_format($product->total_revenue, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted">No data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="m-0">Recent Orders</h5>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentOrders as $order)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.orders.show', $order) }}" class="text-decoration-none">
                                        {{ $order->order_number }}
                                    </a>
                                </td>
                                <td>{{ $order->user->name ?? 'Guest' }}</td>
                                <td>${{ number_format($order->total, 2) }}</td>
                                <td>{!! $order->status_badge !!}</td>
                                <td>{{ $order->created_at->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">No orders yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Monthly Sales Chart
    const salesCtx = document.getElementById('salesChart');
    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: @json(collect($monthlySales)->pluck('month')),
            datasets: [{
                label: 'Revenue ($)',
                data: @json(collect($monthlySales)->pluck('revenue')),
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Orders Overview
    const ordersCtx = document.getElementById('ordersChart');
    new Chart(ordersCtx, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'],
            datasets: [{
                data: [
                    {{ $stats['pending_orders'] }},
                    {{ $stats['processing_orders'] }},
                    {{ $stats['shipped_orders'] }},
                    {{ $stats['delivered_orders'] }},
                    {{ $stats['cancelled_orders'] }}
                ],
                backgroundColor: ['#f59e0b', '#06b6d4', '#3b82f6', '#10b981', '#ef4444'],
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
</script>
@endpush
