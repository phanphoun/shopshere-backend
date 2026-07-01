@extends('admin.layouts.app')

@section('title', 'Edit Product')
@section('page_title', 'Edit Product')

@section('content')
<div class="card p-4">
    <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
        @csrf @method('PUT')

        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label">Name <span class="text-danger">*</span></label>
                <input type="text" name="name" value="{{ old('name', $product->name) }}" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Category <span class="text-danger">*</span></label>
                <select name="category_id" class="form-select" required>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Slug</label>
                <input type="text" name="slug" value="{{ old('slug', $product->slug) }}" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">SKU</label>
                <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" class="form-control">
            </div>

            <div class="col-12">
                <label class="form-label">Description <span class="text-danger">*</span></label>
                <textarea name="description" class="form-control" rows="5" required>{{ old('description', $product->description) }}</textarea>
            </div>

            <div class="col-md-4">
                <label class="form-label">Price <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $product->price) }}" class="form-control" required>
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Discount Price</label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" min="0" name="discount_price" value="{{ old('discount_price', $product->discount_price) }}" class="form-control">
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Stock Quantity <span class="text-danger">*</span></label>
                <input type="number" min="0" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}" class="form-control" required>
            </div>

            <div class="col-md-6">
                <div class="form-check form-switch mt-4">
                    <input type="hidden" name="featured" value="0">
                    <input type="checkbox" name="featured" value="1" class="form-check-input" id="featured" {{ $product->featured ? 'checked' : '' }}>
                    <label class="form-check-label" for="featured">Featured</label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-check form-switch mt-4">
                    <input type="hidden" name="status" value="0">
                    <input type="checkbox" name="status" value="1" class="form-check-input" id="status" {{ $product->status ? 'checked' : '' }}>
                    <label class="form-check-label" for="status">Active</label>
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label">Main Image</label>
                @if ($product->image)
                    <div class="mb-2">
                        <img src="{{ asset('storage/'.$product->image) }}" style="max-height: 100px; border-radius: 6px">
                    </div>
                @endif
                <input type="file" name="image" accept="image/*" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">Add Gallery Images</label>
                <input type="file" name="gallery[]" accept="image/*" multiple class="form-control">
            </div>
        </div>

        @if ($product->images->count())
            <hr class="my-4">
            <h5>Existing Gallery Images</h5>
            <div class="row g-2">
                @foreach ($product->images as $image)
                    <div class="col-auto position-relative">
                        <img src="{{ asset('storage/'.$image->image) }}" style="height: 100px; border-radius: 6px">
                        <form method="POST" action="{{ route('admin.product-images.destroy', $image) }}"
                              class="position-absolute top-0 end-0 m-1"
                              onsubmit="return confirm('Delete this image?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger">
                                <i class="bi bi-x"></i>
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg"></i> Update Product
            </button>
            <a href="{{ route('admin.products.index') }}" class="btn btn-light">Cancel</a>
        </div>
    </form>
</div>
@endsection
