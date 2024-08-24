@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Your Cart</h2>
        @if($cartItems->isEmpty())
            <p>Your cart is empty.</p>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cartItems as $item)
                        <tr>
                            <td>{{ $item->product->name }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>${{ number_format($item->getCurrentPriceInUsd(), 2) }}</td>
                            <td>${{ number_format($item->getTotalPriceInUsd(), 2) }}</td>
                            <td>
                                <form action="{{ route('cart.remove', $item->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3"><strong>Total:</strong></td>
                        <td>${{ number_format($cart->getTotalInUsd(), 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>

            <form action="{{ route('cart.checkout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary">Proceed to Checkout</button>
            </form>
        @endif
    </div>
@endsection