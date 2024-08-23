@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h1 class="mb-4">Start a New Conversation</h1>
    
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    
    <form action="{{ route('messages.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="recipient" class="form-label">Recipient Username:</label>
            <input type="text" class="form-control @error('recipient') is-invalid @enderror" id="recipient" name="recipient" value="{{ old('recipient') }}" required>
            @error('recipient')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="content" class="form-label">Message:</label>
            <textarea class="form-control @error('content') is-invalid @enderror" id="content" name="content" rows="3" required>{{ old('content') }}</textarea>
            @error('content')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary">Send Message</button>
    </form>
    
    <a href="{{ route('messages.index') }}" class="btn btn-secondary mt-3">Back to Conversations</a>
</div>
@endsection