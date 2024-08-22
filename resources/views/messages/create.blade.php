@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Send a Message</h1>
    <form action="{{ route('messages.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="username" class="form-label">User:</label>
            <input type="text" class="form-control" id="username" name="username" placeholder="Enter username" required>
        </div>
        <div class="mb-3">
            <label for="content" class="form-label">Message:</label>
            <textarea class="form-control" id="content" name="content" rows="4" placeholder="Type your message here" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Send</button>
    </form>
</div>
@endsection