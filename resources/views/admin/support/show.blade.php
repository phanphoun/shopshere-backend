@extends('admin.layouts.app')

@section('title', 'Support Conversation #' . $conversation->id)
@section('page_title', 'Conversation #' . $conversation->id)

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <div class="text-muted">{{ $conversation->customer_name ?? 'Guest' }}</div>
        <div class="text-muted small">{{ $conversation->customer_email }}</div>
    </div>
    <div class="d-flex gap-2">
        @if($conversation->status === 'open')
            <form method="POST" action="{{ route('admin.support.close', $conversation) }}" class="d-inline">
                @csrf @method('PATCH')
                <button type="submit" class="btn btn-outline-secondary btn-sm">Close</button>
            </form>
        @else
            <form method="POST" action="{{ route('admin.support.reopen', $conversation) }}" class="d-inline">
                @csrf @method('PATCH')
                <button type="submit" class="btn btn-outline-success btn-sm">Reopen</button>
            </form>
        @endif
        <a href="{{ route('admin.support.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span class="fw-semibold">Messages</span>
        <span class="badge rounded-pill bg-secondary">{{ $conversation->status }}</span>
    </div>
    <div class="card-body bg-body-tertiary">
        @forelse($conversation->messages as $message)
            <div class="card mb-3">
                <div class="card-header d-flex align-items-center justify-content-between py-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="fw-semibold small">{{ $message->sender_name ?? $message->sender_type }}</span>
                        <span class="badge rounded-pill bg-light text-secondary">{{ $message->sender_type }}</span>
                    </div>
                    <small class="text-muted">{{ $message->created_at->format('Y-m-d H:i') }}</small>
                </div>
                <div class="card-body py-3">
                    <p class="mb-0 text-wrap">{{ $message->message }}</p>
                </div>
            </div>
        @empty
            <div class="text-center text-muted py-4">No messages yet.</div>
        @endforelse
    </div>
</div>

<div class="card">
    <div class="card-header fw-semibold">Reply</div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.support.reply', $conversation) }}">
            @csrf
            <div class="mb-3">
                <label for="message" class="form-label">Message</label>
                <textarea id="message" name="message" rows="3" class="form-control" placeholder="Type your reply..." required></textarea>
            </div>
            <div class="text-end">
                <button type="submit" class="btn btn-primary">Send Reply</button>
            </div>
        </form>
    </div>
</div>
@endsection
