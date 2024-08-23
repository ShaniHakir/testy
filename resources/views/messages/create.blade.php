@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h1 class="mb-4">Start a New Conversation</h1>
    
    <form action="{{ route('messages.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="recipient" class="form-label">Recipient Username:</label>
            <input type="text" class="form-control" id="recipient" name="recipient" required>
        </div>
        <div class="mb-3">
            <label for="content" class="form-label">Message:</label>
            <textarea class="form-control" id="content" name="content" rows="3" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Send Message</button>
    </form>
    
    <a href="{{ route('messages.index') }}" class="btn btn-secondary mt-3">Back to Conversations</a>
</div>
@endsection