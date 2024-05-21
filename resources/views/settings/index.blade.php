@extends('layouts.app')

@section('title', 'Settings')

@section('content')
    <h2>Settings</h2>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link" href="{{ route('settings.password') }}">Password</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('settings.pin') }}">PIN Change</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('settings.jabber') }}">Jabber</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('settings.about') }}">About</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('settings.gpg') }}">GPG</a>
        </li>
    </ul>
@endsection
