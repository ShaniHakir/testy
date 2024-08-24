@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Your Orders</h1>
    <table class="table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Total Amount (USD)</th>
                <th>Total Amount (BTC)</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($orders as $order)
            <tr>
                <td>{{ $order->id }}</td>
                <td>${{ number_format($order->total_amount_usd, 2) }}</td>
                <td>{{ $order->total_amount_btc }}</td>
                <td>{{ $order->status }}</td>
                <td>{{ $order->created_at->format('Y-m-d H:i:s') }}</td>
                <td>
                    <a href="{{ route('orders.show', $order) }}" class="btn btn-primary">View Details</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection