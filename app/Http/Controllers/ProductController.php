<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


class ProductController extends Controller
{

    use AuthorizesRequests;

    public function index()
    {
        $products = Product::with('category', 'user', 'images')->paginate(10);
        $products->each(function ($product) {
            $product->default_image = $product->getDefaultImage();
        });
        return view('products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::pluck('name', 'id');
        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        \Log::info('Product store method called');
        \Log::info('Request data:', $request->all());

        $validatedData = $request->validate([
            'name' => 'required|max:255',
            'description' => 'required',
            'price' => 'required|numeric|min:0',
            'discount_quantity' => 'nullable|integer|min:1',
            'discount_price' => 'nullable|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $product = new Product($validatedData);
        $product->user_id = Auth::id();
        $product->save();
    
        if ($request->hasFile('images')) {
            \Log::info('Images found in request');
            foreach ($request->file('images') as $image) {
                \Log::info('Processing image: ' . $image->getClientOriginalName());
                $path = $image->store('product_images', 'public');
                \Log::info('Image stored at: ' . $path);
                $product->images()->create(['path' => $path]);
            }
        } else {
            \Log::info('No images found in request');
        }
    
        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function show($id)
    {
        $product = Product::findOrFail($id);
        $product->load('user', 'category', 'images');
        $product->default_image = $product->getDefaultImage();
        return view('products.show', compact('product'));
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $this->authorize('update', $product);
        $categories = Category::pluck('name', 'id');
        return view('products.edit', compact('product', 'categories'));
    }

    public function setDefaultImage(Request $request, $product_id, $image_id)
    {
        $product = Product::findOrFail($product_id);
        $image = ProductImage::findOrFail($image_id);
        $this->authorize('update', $product);

        try {
            DB::transaction(function () use ($product, $image) {
                $product->images()->update(['is_default' => false]);
                $image->update(['is_default' => true]);
            });

            return redirect()->route('products.edit', $product->id)->with('success', 'Default image updated successfully.');
        } catch (\Exception $e) {
            return redirect()->route('products.edit', $product->id)->with('error', 'Failed to update default image. Please try again.');
        }
    }

    public function deleteImage(ProductImage $productImage)
    {
        $product = $productImage->product;
        
        $this->authorize('deleteImage', $product);

        Storage::disk('public')->delete($productImage->path);
        $productImage->delete();

        if ($product->images()->count() > 0 && !$product->images()->where('is_default', true)->exists()) {
            $product->images()->first()->update(['is_default' => true]);
        }

        return back()->with('success', 'Image deleted successfully.');
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $this->authorize('update', $product);

        $validatedData = $request->validate([
            'name' => 'required|max:255',
            'description' => 'required',
            'price' => 'required|numeric|min:0',
            'discount_quantity' => 'nullable|integer|min:1',
            'discount_price' => 'nullable|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $product->update($validatedData);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('product_images', 'public');
                $product->images()->create(['path' => $path]);
            }
        }

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $this->authorize('delete', $product);
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }
}