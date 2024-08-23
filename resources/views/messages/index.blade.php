@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h1 class="mb-4">Your Conversations</h1>
    <div class="mb-3">
        <h2>Unread Messages: {{ $unreadCount }}</h2>
    </div>
    @foreach($conversations as $conversation)
        @php
            $otherUser = $conversation->getOtherUser(Auth::id());
            $lastMessage = $conversation->messages()->latest()->first();
        @endphp
        <div class="card mb-3 {{ $lastMessage && !$lastMessage->read && $lastMessage->sender_id !== Auth::id() ? 'bg-light' : '' }}">
            <div class="card-body">
                <h5 class="card-title">{{ $otherUser->name }}</h5>
                <p class="card-text">
                    <small class="text-muted">Last message: {{ $lastMessage ? $lastMessage->created_at->format('M d, Y H:i') : 'No messages yet' }}</small>
                </p>
                @if($lastMessage && !$lastMessage->read && $lastMessage->sender_id !== Auth::id())
                    <span class="badge bg-primary">Unread</span>
                @endif
                <div class="float-end">
                    <a href="{{ route('messages.show', $conversation) }}" class="btn btn-primary">View Conversation</a>
                    <form action="{{ route('conversations.destroy', $conversation) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this conversation?')">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
    <div class="mt-4">
        <a href="{{ route('messages.create') }}" class="btn btn-success">Start a new conversation</a>
        <form action="{{ route('conversations.deleteAll') }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete all conversations?')">Delete All Conversations</button>
        </form>
    </div>
</div>
@endsection