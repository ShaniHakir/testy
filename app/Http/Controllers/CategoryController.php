<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function show($id)
    {
        $category = Category::findOrFail($id);
        $products = Product::where('category_id', $id)->latest()->paginate(12);
        $categories = Category::whereNull('parent_id')->with('children')->get();

        return view('categories.show', compact('category', 'products', 'categories'));
    }
}