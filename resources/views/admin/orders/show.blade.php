@extends('admin.layouts.app')

@section('title', 'Order Details')
@section('page_title', 'Order ' . $order->order_number)

@section('content')
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card p-4 mb-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="m-0">Order Items</h5>
                <div>
                    <span class="badge {{ $order->status_class }}">{{ ucfirst($order->status) }}</span>
                    <span class="badge {{ $order->payment_class }}">{{ ucfirst($order->payment_status) }}</span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Price</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->items as $item)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if ($item->product_image)
                                            <img src="{{ asset('storage/'.$item->product_image) }}" class="product-thumb me-2">
                                        @endif
                                        <div>
                                            <strong>{{ $item->product_name }}</strong><br>
                                            <small class="text-muted">{{ $item->product_sku }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end">${{ number_format($item->price, 2) }}</td>
                                <td class="text-end">${{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="row justify-content-end">
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr><td>Subtotal</td><td class="text-end">${{ number_format($order->subtotal, 2) }}</td></tr>
                        <tr><td>Tax</td><td class="text-end">${{ number_format($order->tax, 2) }}</td></tr>
                        <tr><td>Shipping</td><td class="text-end">${{ number_format($order->shipping_fee, 2) }}</td></tr>
                        <tr><td>Discount</td><td class="text-end">-${{ number_format($order->discount, 2) }}</td></tr>
                        <tr class="table-active"><th>Total</th><th class="text-end">${{ number_format($order->total, 2) }}</th></tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="card p-4">
            <h5 class="mb-3">Update Status</h5>
            <form method="POST" action="{{ route('admin.orders.update-status', $order) }}" class="d-flex gap-2">
                @csrf @method('PATCH')
                <select name="status" class="form-select">
                    @foreach (['pending','processing','shipped','delivered','cancelled'] as $status)
                        <option value="{{ $status }}" {{ $order->status == $status ? 'selected' : '' }}>
                            {{ ucfirst($status) }}
                        </option>
                    @endforeach
                </select>
                <button class="btn btn-primary">Update</button>
                <a href="{{ route('admin.orders.invoice', $order) }}" target="_blank" class="btn btn-outline-secondary">
                    <i class="bi bi-printer"></i> Print Invoice
                </a>
            </form>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card p-4 mb-3">
            <h5 class="mb-3">Customer</h5>
            <p class="mb-1"><strong>{{ $order->user->name ?? 'Guest' }}</strong></p>
            <p class="mb-1 text-muted">{{ $order->user->email ?? '' }}</p>
            <p class="mb-0 text-muted">{{ $order->user->phone ?? '' }}</p>
        </div>

        <div class="card p-4 mb-3">
            <h5 class="mb-3">Shipping Address</h5>
            <p class="mb-0">{{ $order->shipping_address }}</p>
            <hr>
            <p class="mb-0"><strong>Phone:</strong> {{ $order->phone }}</p>
            @if ($order->notes)
                <hr>
                <p class="mb-0"><strong>Notes:</strong> {{ $order->notes }}</p>
            @endif
        </div>

        <div class="card p-4">
            <h5 class="mb-3">Payment Info</h5>
            <p class="mb-1"><strong>Method:</strong> {{ ucfirst($order->payment_method ?? 'N/A') }}</p>
            <p class="mb-0"><strong>Status:</strong> <span class="badge {{ $order->payment_class }}">{{ ucfirst($order->payment_status) }}</span></p>
            @if (!$order->isPaid())
                <form method="POST" action="{{ route('admin.orders.mark-paid', $order) }}" class="mt-2">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm w-100">
                        <i class="bi bi-check-circle"></i> Mark as Paid
                    </button>
                </form>
            @endif
            <hr>
            <p class="mb-1"><strong>Created:</strong> {{ $order->created_at->format('M d, Y H:i') }}</p>
            @if ($order->shipped_at)
                <p class="mb-1"><strong>Shipped:</strong> {{ $order->shipped_at->format('M d, Y H:i') }}</p>
            @endif
            @if ($order->delivered_at)
                <p class="mb-0"><strong>Delivered:</strong> {{ $order->delivered_at->format('M d, Y H:i') }}</p>
            @endif
        </div>
    </div>
</div>
@endsection
