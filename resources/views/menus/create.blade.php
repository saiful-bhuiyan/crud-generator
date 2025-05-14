@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Create Menu</h4>
    <form action="{{ route('menus.store') }}" method="POST">
        @include('menus.form')
    </form>
</div>
@endsection
