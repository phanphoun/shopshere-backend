@extends('admin.layouts.app')

@section('title', 'Edit Profile')
@section('page_title', 'Edit Profile')

@section('content')
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card p-4 text-center">
            <div class="position-relative d-inline-block mb-3">
                @if ($user->avatar)
                    <img src="{{ $user->avatar_url }}" alt="" width="120" height="120" class="rounded-circle border border-2 border-light shadow-sm" style="object-fit: cover;">
                @else
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold mx-auto" width="120" height="120"
                         style="width:120px;height:120px;background:linear-gradient(145deg,#0ea5e9,#2563eb);font-size:2.4rem;">
                        {{ strtoupper(collect(explode(' ', $user->name))->take(2)->map(fn($n)=>Str::substr($n,0,1))->join('')) }}
                    </div>
                @endif
            </div>

            <h4 class="mb-1">{{ $user->name }}</h4>
            <p class="text-muted small">{{ $user->email }}</p>

            <div class="d-flex justify-content-center gap-2">
                <span class="badge bg-primary">Admin</span>
                <span class="badge bg-success">Active</span>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card p-4">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small text-uppercase ls-wide">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control" required>
                        @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-muted small text-uppercase ls-wide">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" required>
                        @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-muted small text-uppercase ls-wide">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="form-control">
                        @error('phone') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-muted small text-uppercase ls-wide">Address</label>
                        <input type="text" name="address" value="{{ old('address', $user->address) }}" class="form-control">
                        @error('address') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-muted small text-uppercase ls-wide">New Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current">
                        <small class="text-muted">Minimum 8 characters.</small>
                        @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-muted small text-uppercase ls-wide">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-muted small text-uppercase ls-wide">Avatar</label>
                        <input type="file" name="avatar" accept="image/*" class="form-control">
                        @error('avatar') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Save Changes
                    </button>
                    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-light border">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
