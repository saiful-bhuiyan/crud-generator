@extends('layouts.app')
@section('content')
<div class="container">
    <h2>Create Role</h2>
    <form method="POST" action="{{ route('roles.store') }}">
        @csrf
        <div class="mb-3">
            <label for="name">Role Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <h5>Assign Permissions</h5>
        <div class="mb-3">
            @foreach($permissions as $permission)
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->name }}">
                    <label class="form-check-label">{{ $permission->name }}</label>
                </div>
            @endforeach
        </div>
        <button class="btn btn-success">Save</button>
    </form>
</div>
@endsection
