@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Order Details</h1>
    <p>Order ID: {{ $order->id }}</p>
    <p>Status: {{ $order->status }}</p>
    <p>Total Amount: ${{ number_format($order->total_amount_usd, 2) }} USD ({{ $order->total_amount_btc }} BTC)</p>

    <h2>Order Items</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Price (USD)</th>
                <th>Price (BTC)</th>
                <th>Status</th>
                @if(auth()->user()->role === 'vendor')
                    <th>Actions</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($order->orderItems as $item)
            <tr>
                <td>{{ $item->product->name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>${{ number_format($item->price_usd, 2) }}</td>
                <td>{{ $item->price_btc }}</td>
                <td>{{ $item->status }}</td>
                @if(auth()->user()->role === 'vendor' && $item->vendor_id === auth()->id())
                    <td>
                        @if($item->status === 'pending')
                            <form action="{{ route('orders.accept', $order) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm">Accept</button>
                            </form>
                            <form action="{{ route('orders.reject', $order) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                            </form>
                        @elseif($item->status === 'processing')
                            <form action="{{ route('orders.complete', $order) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm">Mark as Completed</button>
                            </form>
                        @endif
                    </td>
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>

    <a href="{{ route('orders.index') }}" class="btn btn-secondary">Back to Orders</a>
</div>
@endsection