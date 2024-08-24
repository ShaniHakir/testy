@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Manage Orders</h1>
    <table class="table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Total Amount (USD)</th>
                <th>Total Amount (BTC)</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
                <tr>
                    <td>{{ $order->id }}</td>
                    <td>{{ $order->user ? $order->user->name : 'N/A' }}</td>
                    <td>${{ number_format($order->total_amount_usd, 2) }}</td>
                    <td>{{ $order->total_amount_btc }} BTC</td>
                    <td>{{ $order->status }}</td>
                    <td>
                        @if($order->status == App\Models\Order::STATUS_PENDING)
                            <form action="{{ route('orders.accept', $order) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm">Accept</button>
                            </form>
                            <form action="{{ route('orders.reject', $order) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                            </form>
                        @elseif($order->status == App\Models\Order::STATUS_PROCESSING)
                            <form action="{{ route('orders.complete', $order) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm">Mark as Completed</button>
                            </form>
                        @endif
                        <a href="{{ route('orders.show', $order) }}" class="btn btn-info btn-sm">View Details</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection