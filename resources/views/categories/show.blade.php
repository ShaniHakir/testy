@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Left Sidebar with Category Menu -->
        <div class="col-md-3">
            <h3>Categories</h3>
            <div class="category-menu">
                <ul>
                    @foreach($categories as $cat)
                        <li>
                            @if($cat->children->isNotEmpty())
                                <input type="checkbox" id="category-{{ $cat->id }}">
                                <label for="category-{{ $cat->id }}">{{ $cat->name }}</label>
                                <ul>
                                    @foreach($cat->children as $subcategory)
                                        <li>
                                            <a href="{{ route('category.show', $subcategory->id) }}">{{ $subcategory->name }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <a href="{{ route('category.show', $cat->id) }}">{{ $cat->name }}</a>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
        
        <!-- Main Content Area -->
        <div class="col-md-9">
            <h1 class="mb-4">{{ $category->name }}</h1>
            
            <h2 class="mb-3">Products in this Category</h2>
            <div class="row">
                @foreach($products as $product)
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            @if($product->default_image)
                                <img src="{{ Storage::url($product->default_image->path) }}" class="card-img-top" alt="{{ $product->name }}">
                            @endif
                            <div class="card-body">
                                <h5 class="card-title">{{ $product->name }}</h5>
                                <p class="card-text">{{ Str::limit($product->description, 100) }}</p>
                                <p class="card-text"><strong>Price: ${{ number_format($product->price, 2) }}</strong></p>
                                <a href="{{ route('products.show', $product->id) }}" class="btn btn-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            {{ $products->links() }}
        </div>
    </div>
</div>
@endsection