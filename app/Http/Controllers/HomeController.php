<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $products = Product::latest()->take(10)->get();
        $categories = Category::whereNull('parent_id')->with('children')->get();
        return view('home', compact('products', 'categories'));
    }
}