@extends('admin.layouts.master')

@section('body')
<div class="content">
    <div class="row">
        <div class="col-sm-12">

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Menus</h4>
                    <a href="{{ route('menus.create') }}" class="btn btn-primary">Add New Menu</a>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
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
                                @forelse($menus as $menu)
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
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No menus found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
