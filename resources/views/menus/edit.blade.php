@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Edit Menu</h4>
    <form action="{{ route('menus.update', $menu) }}" method="POST">
        @method('PUT')
        @include('menus.form')
    </form>
</div>
@endsection
