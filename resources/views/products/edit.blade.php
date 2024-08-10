@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Product</h1>
    
    <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $product->name) }}" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" required>{{ old('description', $product->description) }}</textarea>
            @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="form-group">
            <label for="price">Price</label>
            <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', $product->price) }}" required>
            @error('price')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="form-group">
            <label for="discount_quantity">Discount Quantity</label>
            <input type="number" class="form-control @error('discount_quantity') is-invalid @enderror" id="discount_quantity" name="discount_quantity" value="{{ old('discount_quantity', $product->discount_quantity) }}">
            @error('discount_quantity')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="form-group">
            <label for="discount_price">Discount Price</label>
            <input type="number" step="0.01" class="form-control @error('discount_price') is-invalid @enderror" id="discount_price" name="discount_price" value="{{ old('discount_price', $product->discount_price) }}">
            @error('discount_price')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="form-group">
            <label for="category_id">Category</label>
            <select class="form-control @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                <option value="">Select a category</option>
                @foreach($categories as $id => $name)
                    <option value="{{ $id }}" {{ old('category_id', $product->category_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
            @error('category_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="form-group">
            <label for="images">Add More Images</label>
            <input type="file" class="form-control-file @error('images') is-invalid @enderror" id="images" name="images[]" multiple accept="image/*">
            @error('images')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        <button type="submit" class="btn btn-primary">Update Product</button>
        </form>

    @if($product->images->count() > 0)
    <div class="mt-4">
        <h2>Product Images</h2>
        <div class="row">
            @foreach($product->images as $image)
                <div class="col-md-3 mb-3">
                    <img src="{{ Storage::url($image->path) }}" alt="Product Image" class="img-thumbnail">
                    <div class="mt-2">
                        @can('update', $product)
                            <form action="{{ route('products.images.setDefault', ['product' => $product->id, 'image' => $image->id]) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-primary" {{ $image->is_default ? 'disabled' : '' }}>
                                    {{ $image->is_default ? 'Default' : 'Set as Default' }}
                                </button>
                            </form>
                        @endcan
                        @can('deleteImage', $product)
                            <form action="{{ route('products.images.delete', $image->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this image?')">Delete</button>
                            </form>
                        @endcan
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection