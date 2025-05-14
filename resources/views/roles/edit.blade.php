@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Role</h2>
    <form action="{{ route('roles.update', $role->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Name</label>
            <input name="name" class="form-control" value="{{ $role->name }}" required>
        </div>

        <div class="mb-3">
            <label>Permissions</label>
            <div class="row">
                @foreach($permissions as $permission)
                    <div class="col-md-3">
                        <div class="form-check">
                            <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" class="form-check-input"
                                   id="perm-{{ $permission->id }}"
                                   {{ in_array($permission->name, $rolePermissions) ? 'checked' : '' }}>
                            <label class="form-check-label" for="perm-{{ $permission->id }}">{{ $permission->name }}</label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <button class="btn btn-primary">Update Role</button>
    </form>
</div>
@endsection
