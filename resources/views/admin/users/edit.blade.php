@extends('layouts.app')

@section('content')
    <h1>Edit User</h1>
    <form action="{{ route('admin.users.update', $user) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" class="form-control" id="username" name="username" value="{{ $user->username }}" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" class="form-control" id="password" name="password">
            <small class="form-text text-muted">Leave blank to keep the current password</small>
        </div>

        <div class="form-group">
            <label for="role">Role</label>
            <select class="form-control" id="role" name="role">
                <option value="user" {{ $user->role == 'user' ? 'selected' : '' }}>User</option>
                <option value="vendor" {{ $user->role == 'vendor' ? 'selected' : '' }}>Vendor</option>
                <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Update User</button>
    </form>
@endsection