@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">{{ request('reply_to') ? 'Reply to Message' : 'Send a New Message' }}</h1>
    
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('messages.store') }}" method="POST">
        @csrf
        @if(request('reply_to'))
            <input type="hidden" name="reply_to" value="{{ request('reply_to') }}">
        @else
            <div class="mb-3">
                <label for="username" class="form-label">Recipient Username:</label>
                <input type="text" class="form-control" id="username" name="username" value="{{ old('username', $recipient) }}" required {{ $recipient ? 'readonly' : '' }}>
            </div>
        @endif
        <div class="mb-3">
            <label for="content" class="form-label">Message:</label>
            <textarea class="form-control" id="content" name="content" rows="4" required>{{ old('content') }}</textarea>
        </div>
        <button type="submit" class="btn btn-primary">Send</button>
        <a href="{{ route('messages.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection