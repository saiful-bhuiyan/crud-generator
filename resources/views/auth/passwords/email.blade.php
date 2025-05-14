<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password</title>

    <link rel="shortcut icon" type="image/x-icon" href="{{ static_asset('assets/img/favicon.jpg') }}">
    <link rel="stylesheet" href="{{ static_asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ static_asset('assets/plugins/fontawesome/css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ static_asset('assets/plugins/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ static_asset('assets/css/style.css') }}">
</head>
<body class="account-page">
<div class="main-wrapper">
    <div class="account-content">
        <div class="login-wrapper">
            <div class="login-content">
                <div class="login-userset">
                    <div class="login-logo">
                        <img src="{{ static_asset('assets/img/logo.png') }}" alt="img">
                    </div>
                    <div class="login-userheading">
                        <h3>Forgot Password</h3>
                        <h4>We'll email you a reset link</h4>
                    </div>

                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf
                        <div class="form-login">
                            <label>Email</label>
                            <div class="form-addons">
                                <input type="email" name="email" value="{{ old('email') }}" required placeholder="Enter your email">
                                <img src="{{ static_asset('assets/img/icons/mail.svg') }}" alt="img">
                            </div>
                            @error('email')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-login">
                            <button type="submit" class="btn btn-login">Send Reset Link</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="login-img">
                <img src="{{ static_asset('assets/img/login.jpg') }}" alt="img">
            </div>
        </div>
    </div>
</div>
<script src="{{ static_asset('assets/js/jquery-3.6.0.min.js') }}"></script>
<script src="{{ static_asset('assets/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ static_asset('assets/js/script.js') }}"></script>
</body>
</html>
