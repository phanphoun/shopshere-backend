@extends('admin.layouts.app')

@section('title', 'Edit Category')
@section('page_title', 'Edit Category')

@section('content')
<div class="card p-4">
    <form method="POST" action="{{ route('admin.categories.update', $category) }}" enctype="multipart/form-data">
        @csrf @method('PUT')

        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label">Name <span class="text-danger">*</span></label>
                <input type="text" name="name" value="{{ old('name', $category->name) }}" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="1" {{ $category->status ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ !$category->status ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="col-12">
                <label class="form-label">Slug</label>
                <input type="text" name="slug" value="{{ old('slug', $category->slug) }}" class="form-control">
            </div>

            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $category->description) }}</textarea>
            </div>

            <div class="col-12">
                <label class="form-label">Image</label>
                @if ($category->image_url)
                    <div class="mb-2">
                        <img src="{{ $category->image_url }}" style="max-height: 100px; border-radius: 6px">
                    </div>
                @endif
                <input type="file" name="image" accept="image/*" class="form-control">
                <small class="text-muted">Leave empty to keep current image</small>
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg"></i> Update Category
            </button>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-light">Cancel</a>
        </div>
    </form>
</div>
@endsection
