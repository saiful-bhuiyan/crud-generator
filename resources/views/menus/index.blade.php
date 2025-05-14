@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Menus</h4>
    <a href="{{ route('menus.create') }}" class="btn btn-primary mb-3">Add New Menu</a>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Title</th>
                <th>Route</th>
                <th>Icon</th>
                <th>Permission</th>
                <th>Parent</th>
                <th>Order</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($menus as $menu)
                <tr>
                    <td>{{ $menu->title }}</td>
                    <td>{{ $menu->route }}</td>
                    <td><i class="{{ $menu->icon }}"></i></td>
                    <td>{{ $menu->permission_name }}</td>
                    <td>—</td>
                    <td>{{ $menu->order }}</td>
                    <td>
                        <a href="{{ route('menus.edit', $menu) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('menus.destroy', $menu) }}" method="POST" class="d-inline-block">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this menu?')">Delete</button>
                        </form>
                    </td>
                </tr>
                @foreach ($menu->children as $child)
                    <tr>
                        <td>↳ {{ $child->title }}</td>
                        <td>{{ $child->route }}</td>
                        <td><i class="{{ $child->icon }}"></i></td>
                        <td>{{ $child->permission_name }}</td>
                        <td>{{ $menu->title }}</td>
                        <td>{{ $child->order }}</td>
                        <td>
                            <a href="{{ route('menus.edit', $child) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('menus.destroy', $child) }}" method="POST" class="d-inline-block">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this submenu?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
</div>
@endsection
