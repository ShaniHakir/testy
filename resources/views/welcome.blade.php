@extends('layouts.app')

@section('title', 'Home')

@section('content')
    <h1>Welcome to the Multivendor Marketplace</h1>
    @if (auth()->check())
        <p>Hello, {{ auth()->user()->username }}!</p>
    @else
        <p><a href="{{ route('login') }}">Login</a> or <a href="{{ route('register') }}">Register</a></p>
    @endif
@endsection
