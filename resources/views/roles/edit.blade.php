@extends('admin.layouts.master')

@section('body')
<div class="content">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h2>Edit Role</h2>
                </div>
                <div class="card-body">

                    <form method="POST" action="{{ route('admin.roles.update', $role->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name">Role Name</label>
                            <input type="text" name="name" class="form-control" value="{{ $role->name }}" required>
                        </div>

                        <h5 class="mt-4">Assign Permissions</h5>

                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="checkAll">
                                                <label class="form-check-label" for="checkAll"><strong>Module (Select All)</strong></label>
                                            </div>
                                        </th>
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
                                            if (preg_match('/^([a-zA-Z_-]+)-(create|index|update|delete)$/', $permission->name, $matches)) {
                                                $grouped[$matches[1]][$matches[2]] = $permission->name;
                                            }
                                        }

                                        $actionLabels = ['create' => 'Create', 'index' => 'Read', 'update' => 'Update', 'delete' => 'Delete'];
                                    @endphp

                                    @foreach($grouped as $module => $actions)
                                    <tr>
                                        <td class="text-capitalize">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input checkModule">
                                                <label class="form-check-label"><strong>{{ str_replace('_', ' ', $module) }}</strong></label>
                                            </div>
                                        </td>
                                        @foreach(['create', 'index', 'update', 'delete'] as $action)
                                        <td>
                                            @if(isset($actions[$action]))
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input permission-checkbox" name="permissions[]" value="{{ $actions[$action] }}"
                                                    id="{{ $actions[$action] }}"
                                                    {{ $role->hasPermissionTo($actions[$action]) ? 'checked' : '' }}>
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

                        <button class="btn btn-primary mt-3">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    function updateCheckAll() {
        let allCheckboxes = document.querySelectorAll('.permission-checkbox');
        let checkAll = document.getElementById('checkAll');
        if (allCheckboxes.length > 0) {
            checkAll.checked = Array.from(allCheckboxes).every(c => c.checked);
        }
    }

    function updateInitialState() {
        document.querySelectorAll('.checkModule').forEach(moduleCb => {
            let row = moduleCb.closest('tr');
            let rowCheckboxes = row.querySelectorAll('.permission-checkbox');
            if (rowCheckboxes.length > 0) {
                moduleCb.checked = Array.from(rowCheckboxes).every(c => c.checked);
            }
        });
        updateCheckAll();
    }

    // Call on load
    document.addEventListener('DOMContentLoaded', updateInitialState);

    document.getElementById('checkAll').addEventListener('change', function() {
        let checkboxes = document.querySelectorAll('.permission-checkbox, .checkModule');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });

    document.querySelectorAll('.checkModule').forEach(moduleCb => {
        moduleCb.addEventListener('change', function() {
            let row = this.closest('tr');
            let checkboxes = row.querySelectorAll('.permission-checkbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
            
            updateCheckAll();
        });
    });

    document.querySelectorAll('.permission-checkbox').forEach(cb => {
        cb.addEventListener('change', function() {
            let row = this.closest('tr');
            let moduleCb = row.querySelector('.checkModule');
            let rowCheckboxes = row.querySelectorAll('.permission-checkbox');
            let allChecked = Array.from(rowCheckboxes).every(c => c.checked);
            moduleCb.checked = allChecked;

            updateCheckAll();
        });
    });
</script>
@endpush
