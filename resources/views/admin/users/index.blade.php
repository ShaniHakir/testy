@extends('layouts.app')


@section('content')
    <h1>Manage Users</h1>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">Add New User</a>

    <table class="table mt-3">
    <thead>
        <tr>
            <th>Username</th>
            <th>Role</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($users as $user)
            <tr>
                <td>{{ $user->username }}</td>
                <td>{{ ucfirst($user->role) }}</td>
                <td>
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                        @if($user->is_banned)
                            <form action="{{ route('admin.users.unban', $user) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success">Unban</button>
                            </form>
                        @else
                            <form action="{{ route('admin.users.ban', $user) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-secondary">Ban</button>
                            </form>
                        @endif
                        </td>
            </tr>
        @endforeach
    </tbody>
</table>
    {{ $users->links() }}
@endsection