@extends('admin.layouts.master')

@section('body')
<div class="content">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">Roles</h2>
                    @can('role-create')
                    <a href="{{ route('roles.create') }}" class="btn btn-primary btn-sm">Create Role</a>
                    @endcan
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    {{-- <th>Permissions</th> --}}
                                    <th width="180px">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($roles as $role)
                                    <tr>
                                        <td>{{ $role->name }}</td>
                                        {{-- <td>
                                            @foreach($role->permissions as $permission)
                                                <span class="badge bg-secondary">{{ $permission->name }}</span>
                                            @endforeach
                                        </td>
                                        --}}
                                        <td>
                                            @can('role-edit')
                                            <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-sm btn-warning me-1">Edit</a>
                                            @endcan
                                            @can('role-delete')
                                            <form action="{{ route('roles.destroy', $role->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button onclick="return confirm('Delete this role?')" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">No roles found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div> <!-- end card-body -->
            </div> <!-- end card -->
        </div>
    </div>
</div>
@endsection
