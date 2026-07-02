@extends('admin.layouts.app')

@section('title', 'Support Conversations')
@section('page_title', 'Support Conversations')

@section('content')
<div class="space-y-4">
    <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex gap-2">
            <a href="{{ route('admin.support.index') }}" class="btn {{ !request('status') ? 'btn-primary' : 'btn-outline-primary' }}">Open</a>
            <a href="{{ route('admin.support.index', ['status' => 'closed']) }}" class="btn {{ request('status') === 'closed' ? 'btn-primary' : 'btn-outline-primary' }}">Closed</a>
        </div>
    </div>

    <div class="card p-0">
        <div class="table-responsive">
            <table class="table table-hover m-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Source</th>
                        <th>Status</th>
                        <th>Last Message</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($conversations as $conversation)
                        @php $lastMessage = $conversation->messages()->latest()->first(); @endphp
                        <tr>
                            <td class="font-monospace text-muted">#{{ $conversation->id }}</td>
                            <td>
                                <div class="fw-semibold text-body">{{ $conversation->customer_name ?? 'Guest' }}</div>
                                <small class="text-muted">{{ $conversation->customer_email }}</small>
                            </td>
                            <td class="text-capitalize">{{ $conversation->source }}</td>
                            <td>
                                <span class="badge rounded-pill {{ $conversation->status === 'open' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                    {{ ucfirst($conversation->status) }}
                                </span>
                            </td>
                            <td class="text-muted">{{ $lastMessage?->created_at?->diffForHumans() }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.support.show', $conversation) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No support conversations found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-body-tertiary">
            {{ $conversations->links() }}
        </div>
    </div>
</div>
@endsection
