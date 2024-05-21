@extends('layouts.app')

@section('title', 'Your Wallet')

@section('content')
<div class="container">
    <h1>Your Wallet</h1>
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    <p>Balance in BTC: {{ $wallet->balance_btc }} BTC</p>
    <p>Equivalent in USD: ${{ number_format($usdAmount, 2) }}</p>
    {{-- Placeholder for future functionalities like adding funds or transactions --}}
</div>
@endsection
