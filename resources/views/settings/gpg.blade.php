@extends('layouts.app')

@section('title', 'GPG Settings')

@section('content')
    <h2>GPG Key Management</h2>
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <form action="{{ route('gpg.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="gpg_key" class="form-label">GPG Public Key</label>
            <textarea class="form-control" id="gpg_key" name="gpg_key" rows="10" required>{{ old('gpg_key', $user->gpg_key) }}</textarea>
        </div>
        <button type="submit" class="btn btn-primary">Submit GPG Key</button>
    </form>

    @if ($user->gpg_key)
        <form action="{{ route('gpg.delete') }}" method="POST" style="margin-top: 20px;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">Delete GPG Key</button>
        </form>
    @endif

    @if ($user->gpg_key && $user->gpg_key_verified)
        <form action="{{ route('gpg.toggle2fa') }}" method="POST" style="margin-top: 20px;">
            @csrf
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="two_factor_auth" name="two_factor_auth" value="1" {{ $user->two_factor_auth ? 'checked' : '' }}>
                <label class="form-check-label" for="two_factor_auth">
                    Enable 2FA with GPG
                </label>
            </div>
            <button type="submit" class="btn btn-primary">Update 2FA Settings</button>
        </form>
    @endif
@endsection
