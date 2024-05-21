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
        
        @if(session('encryptedCode'))
            <p>Please decrypt the following code with your GPG key:</p>
            <textarea readonly class="form-control">{{ session('encryptedCode') }}</textarea>
        @else
            <p>Error: No encrypted code provided.</p>
        @endif

        <button type="submit" class="btn btn-primary">Verify</button>
    </form>
@endsection
