@extends('layouts.app')

@section('title', 'Verify GPG Key')

@section('content')
    <h2>Verify GPG Key</h2>
    <p>Please decrypt the following message and submit the verification code:</p>
    <textarea readonly class="form-control" rows="5">{{ $encryptedData }}</textarea>
    <form action="{{ route('gpg.checkVerification') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="verification_code" class="form-label">Verification Code</label>
            <input type="text" class="form-control" id="verification_code" name="verification_code" required>
        </div>
        <button type="submit" class="btn btn-primary">Verify</button>
    </form>
@endsection
