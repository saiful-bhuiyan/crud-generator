@extends('admin.layouts.master')

@section('body')
<div class="content">
    <div class="row">
        <div class="col-sm-12">

            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Create Menu</h4>
                </div>

                <div class="card-body">
                    <form action="{{ route('menus.store') }}" method="POST">
                        @include('menus.form')
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
