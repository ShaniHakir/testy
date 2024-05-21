@extends('layouts.app')

@section('title', 'Change PIN')

@section('content')
    <h2>Change PIN</h2>
    <form action="{{ route('settings.pin.update') }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="current_pin" class="form-label">Current PIN</label>
            <input type="password" class="form-control" id="current_pin" name="current_pin" required>
            @error('current_pin')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="new_pin" class="form-label">New PIN</label>
            <input type="password" class="form-control" id="new_pin" name="new_pin" required>
            @error('new_pin')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="new_pin_confirmation" class="form-label">Confirm New PIN</label>
            <input type="password" class="form-control" id="new_pin_confirmation" name="new_pin_confirmation" required>
        </div>
        <button type="submit" class="btn btn-primary">Update PIN</button>
    </form>
@endsection
