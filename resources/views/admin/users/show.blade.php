@extends('admin.layouts.app')

@section('title', 'User Details')
@section('page_title', $user->name)

@section('content')
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card p-4 text-center">
            <div class="position-relative d-inline-block mb-4">
                <div class="rounded-circle border border-2 border-light shadow-sm overflow-hidden d-inline-flex align-items-center justify-content-center" width="160" height="160" style="width:160px;height:160px;background:#f1f5f9;">
                    @if ($user->avatar)
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" width="160" height="160" style="width:160px;height:160px;object-fit:cover;">
                    @else
                        <span class="text-white fw-bold" style="font-size:3rem;">{{ strtoupper(collect(explode(' ', $user->name))->take(2)->map(fn($n)=>Str::substr($n,0,1))->join('')) }}</span>
                    @endif
                </div>
            </div>

            <h4 class="mb-1">{{ $user->name }}</h4>
            <p class="text-muted">{{ $user->email }}</p>

            <div class="d-flex justify-content-center gap-2 my-3">
                @if ($user->role === 'admin')
                    <span class="badge bg-primary">Admin</span>
                @else
                    <span class="badge bg-secondary">Customer</span>
                @endif

                @if ($user->status === 'active')
                    <span class="badge bg-success">Active</span>
                @elseif ($user->status === 'banned')
                    <span class="badge bg-danger">Banned</span>
                @else
                    <span class="badge bg-warning text-dark">Inactive</span>
                @endif
            </div>

            <div class="d-flex justify-content-center gap-2 mt-3">
                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary w-100">
                    <i class="bi bi-pencil-square me-1"></i> Edit User
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card p-4 mb-3">
            <h5 class="mb-3">Recent Orders ({{ $user->orders->count() }})</h5>

            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Date</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($user->orders->take(10) as $order)
                        <tr>
                            <td>
                                <a href="{{ route('admin.orders.show', $order) }}" class="text-decoration-none">
                                    {{ $order->order_number }}
                                </a>
                            </td>
                            <td>${{ number_format($order->total, 2) }}</td>
                            <td><span class="badge {{ $order->status_class }}">{{ ucfirst($order->status) }}</span></td>
                            <td><span class="badge {{ $order->payment_class }}">{{ ucfirst($order->payment_status) }}</span></td>
                            <td>{{ $order->created_at->format('M d, Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">No orders yet</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card p-4">
            <h5 class="mb-3">Reviews ({{ $user->reviews->count() }})</h5>

            @forelse ($user->reviews->take(5) as $review)
                <div class="border-bottom pb-3 mb-3">
                    <div class="d-flex justify-content-between">
                        <strong>{{ $review->product->name ?? 'Product' }}</strong>
                        <span>{{ $review->stars }}</span>
                    </div>
                    <p class="mb-1 text-muted small">{{ $review->created_at->format('M d, Y') }}</p>
                    <p class="mb-0">{{ $review->comment }}</p>
                </div>
            @empty
                <p class="text-center text-muted">No reviews yet</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
