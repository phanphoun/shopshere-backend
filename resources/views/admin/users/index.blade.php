@extends('admin.layouts.app')

@section('title', 'Users')
@section('page_title', 'Users')

@section('content')
<div class="card">
    <div class="filter-bar">
        <form method="GET" class="d-flex gap-2 flex-wrap align-items-center">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" style="min-width: 260px" placeholder="Search by name, email, phone...">
            <select name="role" class="form-select" style="max-width: 180px">
                <option value="">All Roles</option>
                <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="customer" {{ request('role') == 'customer' ? 'selected' : '' }}>Customer</option>
            </select>
            <select name="status" class="form-select" style="max-width: 180px">
                <option value="">All Status</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                <option value="banned" {{ request('status') == 'banned' ? 'selected' : '' }}>Banned</option>
            </select>
            <button class="btn btn-primary"><i class="bi bi-search"></i> Search</button>
        </form>
    </div>

    <div class="table-wrapper">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Avatar</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td class="text-muted">{{ $user->id }}</td>
                        <td>
                            @if ($user->avatar_url)
                                <img src="{{ $user->avatar_url }}" class="rounded-circle" width="40" height="40" alt="">
                            @else
                                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold d-inline-flex"
                                     style="width:40px;height:40px;background:linear-gradient(145deg,#0ea5e9,#2563eb);font-size:0.9rem;">
                                    {{ strtoupper(collect(explode(' ', $user->name))->take(2)->map(fn($n)=>Str::substr($n,0,1))->join('')) }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $user->name }}</div>
                            <div class="text-muted small d-none d-md-inline">{{ Str::limit($user->address ?? '', 32) }}</div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->phone ?? '—' }}</td>
                        <td>
                            @if ($user->role === 'admin')
                                <span class="badge text-bg-primary-subtle text-primary border">Admin</span>
                            @else
                                <span class="badge text-bg-secondary-subtle text-secondary border">Customer</span>
                            @endif
                        </td>
                        <td>
                            @if ($user->status === 'active')
                                <span class="badge text-bg-success-subtle text-success border">Active</span>
                            @elseif ($user->status === 'banned')
                                <span class="badge text-bg-danger-subtle text-danger border">Banned</span>
                            @else
                                <span class="badge text-bg-warning-subtle text-dark border">Inactive</span>
                            @endif
                        </td>
                        <td class="text-muted">{{ $user->created_at->format('M d, Y') }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-light border" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-light border" title="Edit">
                                <i class="bi bi-pencil-square"></i>
                            </a>

                            <button type="button"
                                    class="btn btn-sm btn-light border text-danger"
                                    data-bs-toggle="modal"
                                    data-bs-target="#deleteUserModal{{ $user->id }}"
                                    title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-5">
                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                            No users found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-4 py-3 d-flex justify-content-between align-items-center">
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Add User
        </a>

        {{ $users->links() }}
    </div>
</div>

@foreach ($users as $user)
    <div class="modal fade" id="deleteUserModal{{ $user->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Delete User</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="m-0">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body">
                        Are you sure you want to delete <strong>{{ $user->name }}</strong>?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light border btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
@endsection
