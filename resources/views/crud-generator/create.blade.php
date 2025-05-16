@extends('admin.layouts.master')

@section('body')
<div class="content">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">Create New CRUD Module</h2>
                    <a href="{{ route('crud-generator.index') }}" class="btn btn-secondary btn-sm">Back to List</a>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form action="{{ route('crud-generator.generate') }}" method="POST">
                        @csrf
                        <div class="form-group mb-3">
                            <label for="table_name">Table Name</label>
                            <input type="text" name="table_name" id="table_name" class="form-control" placeholder="e.g. products" required>
                        </div>

                        <h5>Columns</h5>
                        <div id="columns">
                            <div class="row mb-2 column-group align-items-center">
                                <div class="col-md-3">
                                    <input type="text" name="columns[0][name]" class="form-control" placeholder="Column Name" required>
                                </div>
                                <div class="col-md-2">
                                    <select name="columns[0][type]" class="form-control">
                                        <option value="string">string</option>
                                        <option value="integer">integer</option>
                                        <option value="bigInteger">bigInteger</option>
                                        <option value="unsignedBigInteger">unsignedBigInteger</option>
                                        <option value="double">double</option>
                                        <option value="text">text</option>
                                        <option value="boolean">boolean</option>
                                        <option value="date">date</option>
                                        <option value="datetime">datetime</option>
                                        <option value="image">image</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" name="columns[0][foreign_table]" class="form-control" placeholder="Foreign Table (optional)">
                                </div>
                                <div class="col-md-2">
                                    <input type="text" name="columns[0][foreign_column]" class="form-control" placeholder="Foreign Column (optional)">
                                </div>
                                <div class="col-md-1 text-center">
                                    <input type="checkbox" name="columns[0][required]" value="1" id="required_0">
                                    <label for="required_0" class="mb-0">Required</label>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-danger btn-sm remove-column">X</button>
                                </div>
                            </div>
                        </div>

                        <button type="button" class="btn btn-sm btn-secondary mb-3" onclick="addColumn()">+ Add Column</button>
                        <br>
                        <button type="submit" class="btn btn-success">Generate Module</button>
                    </form>
                </div> <!-- end card-body -->
            </div> <!-- end card -->
        </div>
    </div>
</div>

<script>
    let columnIndex = 1;

    function addColumn() {
        const columnsDiv = document.getElementById('columns');
        const row = document.createElement('div');
        row.classList.add('row', 'mb-2', 'column-group', 'align-items-center');
        row.innerHTML = `
            <div class="col-md-3">
                <input type="text" name="columns[${columnIndex}][name]" class="form-control" placeholder="Column Name" required>
            </div>
            <div class="col-md-2">
                <select name="columns[${columnIndex}][type]" class="form-control">
                    <option value="string">string</option>
                    <option value="integer">integer</option>
                    <option value="bigInteger">bigInteger</option>
                    <option value="unsignedBigInteger">unsignedBigInteger</option>
                    <option value="double">double</option>
                    <option value="text">text</option>
                    <option value="boolean">boolean</option>
                    <option value="date">date</option>
                    <option value="datetime">datetime</option>
                    <option value="image">image</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="text" name="columns[${columnIndex}][foreign_table]" class="form-control" placeholder="Foreign Table (optional)">
            </div>
            <div class="col-md-2">
                <input type="text" name="columns[${columnIndex}][foreign_column]" class="form-control" placeholder="Foreign Column (optional)">
            </div>
            <div class="col-md-1 text-center">
                <input type="checkbox" name="columns[${columnIndex}][required]" value="1" id="required_${columnIndex}">
                <label for="required_${columnIndex}" class="mb-0">Required</label>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-danger btn-sm remove-column">X</button>
            </div>
        `;
        columnsDiv.appendChild(row);
        columnIndex++;
    }

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-column')) {
            e.target.closest('.column-group').remove();
        }
    });
</script>
@endsection
