<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::whereNull('parent_id')
                        ->with('children')
                        ->orderBy('order')
                        ->get();
        return view('categories.index', compact('categories'));
    }
    
    public function create()
    {
        $categories = Category::whereNull('parent_id')->pluck('name', 'id');
        return view('categories.create', compact('categories'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:categories|max:255',
            'description' => 'nullable',
            'parent_id' => 'nullable|exists:categories,id',
            'order' => 'required|integer|min:0',
        ]);
    
        Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'parent_id' => $request->parent_id,
            'order' => $request->order,
        ]);
    
        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully');
    }
    
    public function edit(Category $category)
    {
        $categories = Category::whereNull('parent_id')
                        ->where('id', '!=', $category->id)
                        ->pluck('name', 'id');
        return view('categories.edit', compact('category', 'categories'));
    }
    
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable',
            'parent_id' => 'nullable|exists:categories,id',
            'order' => 'required|integer|min:0',
        ]);
    
        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'parent_id' => $request->parent_id,
            'order' => $request->order,
        ]);
    
        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully');
    }
}