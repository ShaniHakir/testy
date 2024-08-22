@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ $product->name }}</h1>
    
    <div class="row">
        <div class="col-md-6">
            @if($product->images->count() > 0)
                <div id="productCarousel" class="carousel slide" data-ride="carousel">
                    <div class="carousel-inner">
                        @foreach($product->images->sortByDesc('is_default') as $index => $image)
                            <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                                <img src="{{ Storage::url($image->path) }}" class="d-block w-100" alt="Product Image">
                            </div>
                        @endforeach
                    </div>
                    <a class="carousel-control-prev" href="#productCarousel" role="button" data-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="sr-only">Previous</span>
                    </a>
                    <a class="carousel-control-next" href="#productCarousel" role="button" data-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="sr-only">Next</span>
                    </a>
                </div>
            @else
                <p>No images available for this product.</p>
            @endif
        </div>
        
        <div class="col-md-6">
            <p><strong>Price:</strong> ${{ number_format($product->price, 2) }}</p>
            <p><strong>Category:</strong> {{ $product->category->name }}</p>
            
            @if($product->user)
                <p><strong>Vendor:</strong> {{ $product->user->username }}</p>
            @else
                <p><strong>Vendor:</strong> Not assigned</p>
            @endif
            
            @if($product->discount_quantity && $product->discount_price)
                <p><strong>Discount:</strong> Buy {{ $product->discount_quantity }} or more for ${{ number_format($product->discount_price, 2) }} each</p>
            @endif
            
            <p><strong>Description:</strong></p>
            <p>{{ $product->description }}</p>
            
            <div class="mt-4">
                @if(Auth::check() && Auth::id() !== $product->user_id)
                    <a href="{{ route('messages.create', ['recipient' => $product->user->username]) }}" class="btn btn-primary">Message Vendor</a>
                @endif

                @if(Auth::user()->can('update', $product))
                    <a href="{{ route('products.edit', $product) }}" class="btn btn-secondary">Edit Product</a>
                @endif

                @if(Auth::user()->can('delete', $product))
                    <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this product?')">Delete Product</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection