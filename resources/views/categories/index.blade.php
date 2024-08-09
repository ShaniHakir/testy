@extends('layouts.app')

@section('title', 'Manage Categories')

@section('content')
    <h2>Manage Categories</h2>
    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary mb-3">Create New Category</a>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Slug</th>
                <th>Order</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($categories as $category)
                <tr>
                    <td>{{ $category->name }}</td>
                    <td>{{ $category->slug }}</td>
                    <td>{{ $category->order }}</td>
                    <td>
                        <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-sm btn-primary">Edit</a>
                        <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
                @foreach($category->children as $child)
                    <tr>
                        <td>-- {{ $child->name }}</td>
                        <td>{{ $child->slug }}</td>
                        <td>{{ $child->order }}</td>
                        <td>
                            <a href="{{ route('admin.categories.edit', $child) }}" class="btn btn-sm btn-primary">Edit</a>
                            <form action="{{ route('admin.categories.destroy', $child) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
@endsection