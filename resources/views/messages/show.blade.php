@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h1 class="mb-4">Conversation with {{ $otherUser->username }}</h1>
    <div class="card">
        <div class="card-body">
            <div class="messages-container">
                @foreach($messages as $message)
                    <div class="message mb-3 p-3 {{ $message->sender_id === Auth::id() ? 'text-right bg-light' : 'text-left bg-white' }}" style="border: 1px solid #e0e0e0; border-radius: 10px;">
                        <strong>{{ $message->sender->username }}:</strong>
                        <p>{!! nl2br(e($message->content)) !!}</p>
                        <small class="text-muted">{{ $message->created_at->format('M d, Y H:i') }}</small>
                        @if($message->sender_id === Auth::id())
                            <form action="{{ route('messages.destroy', $message) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger ml-2" onclick="return confirm('Are you sure you want to delete this message?')">Delete</button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-body">
            <form action="{{ route('messages.store') }}" method="POST">
                @csrf
                <input type="hidden" name="recipient" value="{{ $otherUser->username }}">
                <div class="form-group">
                    <label for="content">New Message:</label>
                    <textarea class="form-control" id="content" name="content" rows="3" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary mt-2">Send Message</button>
            </form>
        </div>
    </div>

    <div class="mt-3">
        <a href="{{ route('messages.index') }}" class="btn btn-secondary">Back to Conversations</a>
        <form action="{{ route('messages.deleteAll') }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger ml-2" onclick="return confirm('Are you sure you want to delete all messages?')">Delete All Messages</button>
        </form>
    </div>
</div>
@endsection