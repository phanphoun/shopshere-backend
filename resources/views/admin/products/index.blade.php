@extends('admin.layouts.app')

@section('title', 'Products')
@section('page_title', 'Products')

@section('content')
<div class="card">
    <div class="filter-bar">
        <form method="GET" class="d-flex gap-2 flex-wrap align-items-center">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" style="min-width: 220px" placeholder="Search products...">
            <select name="category_id" class="form-select" style="max-width: 200px">
                <option value="">All Categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            <select name="status" class="form-select" style="max-width: 150px">
                <option value="">All Status</option>
                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
            </select>
            <button class="btn btn-primary"><i class="bi bi-search"></i></button>
            <a href="{{ route('admin.products.create') }}" class="btn btn-success ms-auto">
                <i class="bi bi-plus-lg"></i> New Product
            </a>
        </form>
    </div>

    <div class="table-wrapper">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Featured</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr>
                        <td class="text-muted">{{ $product->id }}</td>
                        <td>
                            @if ($product->image)
                                <img src="{{ asset('storage/'.$product->image) }}" class="product-thumb" alt="">
                            @else
                                <div class="product-thumb bg-light d-flex align-items-center justify-content-center text-muted">
                                    <i class="bi bi-image"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold">{{ Str::limit($product->name, 40) }}</div>
                            <small class="text-muted">{{ $product->sku }}</small>
                        </td>
                        <td>
                            <span class="badge text-bg-info-subtle text-info border">{{ $product->category->name ?? 'N/A' }}</span>
                        </td>
                        <td>
                            @if ($product->discount_price)
                                <span class="text-decoration-line-through text-muted">${{ number_format($product->price, 2) }}</span>
                                <div class="fw-semibold text-danger">${{ number_format($product->discount_price, 2) }}</div>
                            @else
                                <span class="fw-semibold">${{ number_format($product->price, 2) }}</span>
                            @endif
                        </td>
                        <td>
                            @if ($product->stock_quantity > 0)
                                <span class="badge text-bg-success-subtle text-success border">{{ $product->stock_quantity }}</span>
                            @else
                                <span class="badge text-bg-danger-subtle text-danger border">Out of stock</span>
                            @endif
                        </td>
                        <td>
                            @if ($product->featured)
                                <span class="text-warning"><i class="bi bi-star-fill"></i></span>
                            @else
                                <span class="text-muted"><i class="bi bi-star"></i></span>
                            @endif
                        </td>
                        <td>
                            @if ($product->status)
                                <span class="badge text-bg-success-subtle text-success border">Active</span>
                            @else
                                <span class="badge text-bg-secondary-subtle text-secondary border">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-light border">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="d-inline"
                                  onsubmit="return confirm('Delete this product?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-light border text-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-5">
                            <i class="bi bi-box-seam fs-2 d-block mb-2"></i>
                            No products found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-4 py-3">
        {{ $products->links() }}
    </div>
</div>
@endsection
