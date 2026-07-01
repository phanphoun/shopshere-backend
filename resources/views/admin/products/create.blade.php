@extends('admin.layouts.app')

@section('title', 'New Product')
@section('page_title', 'Create New Product')

@section('content')
<div class="card p-4">
    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label">Name <span class="text-danger">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Category <span class="text-danger">*</span></label>
                <select name="category_id" class="form-select" required>
                    <option value="">Select category</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Slug <small class="text-muted">(optional)</small></label>
                <input type="text" name="slug" value="{{ old('slug') }}" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">SKU <small class="text-muted">(auto-generated if empty)</small></label>
                <input type="text" name="sku" value="{{ old('sku') }}" class="form-control">
            </div>

            <div class="col-12">
                <label class="form-label">Description <span class="text-danger">*</span></label>
                <textarea name="description" class="form-control" rows="5" required>{{ old('description') }}</textarea>
            </div>

            <div class="col-md-4">
                <label class="form-label">Price <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" min="0" name="price" value="{{ old('price') }}" class="form-control" required>
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Discount Price</label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" min="0" name="discount_price" value="{{ old('discount_price') }}" class="form-control">
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Stock Quantity <span class="text-danger">*</span></label>
                <input type="number" min="0" name="stock_quantity" value="{{ old('stock_quantity', 0) }}" class="form-control" required>
            </div>

            <div class="col-md-6">
                <div class="form-check form-switch mt-4">
                    <input type="hidden" name="featured" value="0">
                    <input type="checkbox" name="featured" value="1" class="form-check-input" id="featured" {{ old('featured') ? 'checked' : '' }}>
                    <label class="form-check-label" for="featured">Featured Product</label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-check form-switch mt-4">
                    <input type="hidden" name="status" value="0">
                    <input type="checkbox" name="status" value="1" class="form-check-input" id="status" {{ old('status', '1') ? 'checked' : '' }}>
                    <label class="form-check-label" for="status">Active</label>
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label">Main Image</label>
                <input type="file" name="image" accept="image/*" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">Gallery Images (multiple)</label>
                <input type="file" name="gallery[]" accept="image/*" multiple class="form-control">
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg"></i> Create Product
            </button>
            <a href="{{ route('admin.products.index') }}" class="btn btn-light">Cancel</a>
        </div>
    </form>
</div>
@endsection
