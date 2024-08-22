@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h1 class="mb-4">Your Messages</h1>
    <div class="mb-3">
        <h2>Unread Messages: {{ $unreadCount }}</h2>
    </div>
    @foreach($messages as $message)
        <div class="card mb-3 {{ $message->read ? 'bg-light' : 'bg-warning' }}">
            <div class="row g-0">
                <div class="col-md-3">
                    <div class="card-body">
                        <h5 class="card-title">From: {{ $message->sender->username }}</h5>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card-body">
                        <p class="card-text"><small class="text-muted">{{ $message->created_at->format('M d, Y H:i') }}</small></p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card-body">
                        <span class="badge {{ $message->read ? 'bg-secondary' : 'bg-primary' }}">
                            {{ $message->read ? 'Read' : 'Unread' }}
                        </span>
                    </div>
                </div>
                <div class="col-md-3 d-flex align-items-center justify-content-center">
                    <a href="{{ route('messages.show', $message) }}" class="btn btn-primary me-2">Read</a>
                    <form action="{{ route('messages.destroy', $message) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
    <div class="mt-4 d-flex justify-content-between">
        <a href="{{ route('messages.create') }}" class="btn btn-success">Send a new message</a>
        <div>
            <form action="{{ route('messages.markAllRead') }}" method="POST" class="d-inline me-2">
                @csrf
                <button type="submit" class="btn btn-secondary">Mark All as Read</button>
            </form>
            <form action="{{ route('messages.deleteAll') }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete all messages?')">Delete All Messages</button>
            </form>
        </div>
    </div>
</div>
@endsection