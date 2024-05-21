@extends('layouts.app')

@section('title', 'Two-Factor Authentication')

@section('content')
    <h2>Two-Factor Authentication</h2>
    <form action="{{ route('two_factor.verify') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="verification_code" class="form-label">Enter the decrypted code</label>
            <input type="text" class="form-control" id="verification_code" name="verification_code" required>
        </div>
        <button type="submit" class="btn btn-primary">Verify</button>
    </form>
@endsection
