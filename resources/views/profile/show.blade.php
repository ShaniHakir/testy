@extends('layouts.app')

@section('title', 'Profile')

@section('content')
    <h2>Profile</h2>
    <p><strong>Username:</strong> {{ $user->username }}</p>
    <p><strong>About:</strong> {{ $user->about }}</p>
@endsection
