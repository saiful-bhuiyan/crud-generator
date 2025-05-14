@extends('admin.layouts.master')

@section('body')
<div class="content">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h2>Create Role</h2>
                </div>
                <div class="card-body">

                    <form method="POST" action="{{ route('roles.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="name">Role Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <h5 class="mt-4">Assign Permissions</h5>

                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Module</th>
                                        <th>Create</th>
                                        <th>Read</th>
                                        <th>Update</th>
                                        <th>Delete</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                    $grouped = [];

                                    foreach ($permissions as $permission) {
                                    if (preg_match('/^([a-zA-Z_-]+)-(create|index|edit|delete)$/', $permission->name, $matches)) {
                                    $grouped[$matches[1]][$matches[2]] = $permission->name;
                                    }
                                    }

                                    $actionLabels = ['create' => 'Create', 'index' => 'Read', 'edit' => 'Update', 'delete' => 'Delete'];
                                    @endphp

                                    @foreach($grouped as $module => $actions)
                                    <tr>
                                        <td class="text-capitalize">{{ str_replace('_', ' ', $module) }}</td>
                                        @foreach(['create', 'index', 'edit', 'delete'] as $action)
                                        <td>
                                            @if(isset($actions[$action]))
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" name="permissions[]" value="{{ $actions[$action] }}" id="{{ $actions[$action] }}">
                                                <label class="form-check-label" for="{{ $actions[$action] }}"></label>
                                            </div>
                                            @else
                                            <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        @endforeach
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <button class="btn btn-success mt-3">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection