@extends('layouts.app')

@section('title', 'Change About')

@section('content')
    <h2>Change About</h2>
    <form action="{{ route('settings.about.update') }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="about" class="form-label">About</label>
            <textarea class="form-control" id="about" name="about" rows="4">{{ old('about', auth()->user()->about) }}</textarea>
            @error('about')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary">Update About</button>
    </form>
@endsection
