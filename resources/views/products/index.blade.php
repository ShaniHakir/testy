@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Products</h1>
    <a href="{{ route('products.create') }}" class="btn btn-primary mb-3">Add New Product</a>
    
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="row">
        @foreach($products as $product)
            <div class="col-md-4 mb-4">
                <div class="card">
                    @if($product->images->where('is_default', true)->first())
                        <img src="{{ Storage::url($product->images->where('is_default', true)->first()->path) }}" class="card-img-top" alt="{{ $product->name }}">
                    @endif
                    <div class="card-body">
                        <h5 class="card-title">{{ $product->name }}</h5>
                        <p class="card-text">${{ number_format($product->price, 2) }}</p>
                        <p class="card-text">{{ $product->category->name }}</p>
                        <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-info">View</a>
                        @if(Auth::user()->can('update', $product))
                            <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-primary">Edit</a>
                        @endif
                        @if(Auth::user()->can('delete', $product))
                            <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{ $products->links() }}
</div>
@endsection