@extends('layouts.app')

@section('title', 'Change Jabber')

@section('content')
    <h2>Change Jabber</h2>
    <form action="{{ route('settings.jabber.update') }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="jabber_xmpp" class="form-label">Jabber/XMPP Address</label>
            <input type="text" class="form-control" id="jabber_xmpp" name="jabber_xmpp" value="{{ old('jabber_xmpp', auth()->user()->jabber_xmpp) }}">
            @error('jabber_xmpp')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary">Update Jabber</button>
    </form>
@endsection
