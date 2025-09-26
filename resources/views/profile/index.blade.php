@extends('admin.layouts.master')
@section('body')
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Profile</h4>
            <h6>User Profile</h6>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.profile.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="profile-set">
                    <div class="profile-head">
                    </div>
                    <div class="profile-top">
                        <div class="profile-content">
                            <div class="profile-contentimg">
                                @if(Auth::user()->avatar)
                                <img src="{{ Auth::user()->avatar }}" alt="img" id="blah">
                                @else
                                <img src="assets/img/customer/customer5.jpg" alt="img" id="blah">
                                @endif
                                <div class="profileupload">
                                    <input type="file" id="avatar" name="avatar">
                                    <a href="javascript:void(0);"><img src="{{ static_asset('assets/img/icons/edit-set.svg') }}" alt="img"></a>
                                </div>
                                @error('avatar')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="profile-contentname">
                                <h2>{{ Auth::user()->name }} {{ Auth::user()->last_name }}</h2>
                                <h4>Updates Your Photo and Personal Details.</h4>
                            </div>
                        </div>
                        <!-- <div class="ms-auto">
                            <a href="javascript:void(0);" class="btn btn-submit me-2">Save</a>
                            <a href="javascript:void(0);" class="btn btn-cancel">Cancel</a>
                        </div> -->
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6 col-sm-12">
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" name="name" value="{{ old('name', Auth::user()->name) }}" placeholder="William">
                            @error('name')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-lg-6 col-sm-12">
                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" name="last_name" value="{{ old('last_name', Auth::user()->last_name) }}" placeholder="Castilo">
                            @error('last_name')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-lg-6 col-sm-12">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="text" name="email" value="{{ old('email', Auth::user()->email) }}" placeholder="william@example.com">
                            @error('email')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-lg-6 col-sm-12">
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="text" name="phone" value="{{ old('phone', Auth::user()->phone) }}" placeholder="+1452 876 5432">
                            @error('phone')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-lg-6 col-sm-12">
                        <div class="form-group">
                            <label>Password</label>
                            <div class="pass-group">
                                <input type="password" name="password" class=" pass-input">
                                <span class="fas toggle-password fa-eye-slash"></span>
                            </div>
                            @error('password')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-submit me-2">Submit</button>
                        <a href="javascript:void(0);" class="btn btn-cancel">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection