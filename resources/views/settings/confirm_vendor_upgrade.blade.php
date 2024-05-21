@extends('layouts.app')

@section('title', 'Become a Vendor')

@section('content')
    <h2>Become a Vendor</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <p>The cost to upgrade to a vendor is {{ $bondPriceBtc }} BTC ({{ $bondPriceUsd }} USD).</p>
    <p>Note: This is non-refunable. Do you wish to proceed with the upgrade?</p>

    <form action="{{ route('vendor.upgrade.confirm') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-primary">Become a Vendor</button>
    </form>
@endsection
