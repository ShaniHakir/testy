<!-- resources/views/settings/index.blade.php -->

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
            <a class="nav-link" href="{{ route('settings.gpg') }}">GPG</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('settings.jabber') }}">Jabber</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('settings.about') }}">About</a>
        </li>
        @if ($isVendorOrAdmin)
            <li class="nav-item">
                <a class="nav-link" href="{{ route('products.index') }}">Manage Products</a>
            </li>
        @endif
        @if (auth()->user()->role !== 'vendor')
            <li class="nav-item">
                <a class="nav-link" href="{{ route('vendor.upgrade') }}">Upgrade to Vendor</a>
            </li>
        @endif
    </ul>
@endsection
