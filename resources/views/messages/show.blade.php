@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h2 class="mb-0">Message from {{ $message->sender->username }}</h2>
            <small>Sent: {{ $message->created_at->format('M d, Y H:i') }}</small>
            <span class="badge bg-light text-dark float-end">
                {{ $message->read ? 'Read' : 'Unread' }}
            </span>
        </div>
        <div class="card-body">
            <p class="card-text">{!! nl2br(e($message->content)) !!}</p>
        </div>
        <div class="card-footer">
            <a href="{{ route('messages.index') }}" class="btn btn-secondary">Back to messages</a>
            <form action="{{ route('messages.destroy', $message) }}" method="POST" class="d-inline float-end">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this message?')">Delete</button>
            </form>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header bg-secondary text-white">
            <h3 class="mb-0">Reply</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('messages.store') }}" method="POST">
                @csrf
                <input type="hidden" name="reply_to" value="{{ $message->id }}">
                <div class="mb-3">
                    <label for="content" class="form-label">Your Reply:</label>
                    <textarea class="form-control" id="content" name="content" rows="4" placeholder="Type your reply here" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Send Reply</button>
            </form>
        </div>
    </div>
</div>
@endsection