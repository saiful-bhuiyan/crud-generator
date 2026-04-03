<div class="header">

    <div class="header-left active">
        <a href="{{ url('admin/') }}" class="logo">
            <img src="{{ isset($general_settings['site_logo']) && $general_settings['site_logo'] 
                        ? get_uploaded_asset($general_settings['site_logo']) 
                        : static_asset('assets/img/logo.png') }}" alt="img">
        </a>
        <a href="{{ url('admin/') }}" class="logo-small">
            <img src="{{ isset($general_settings['site_mini_logo']) && $general_settings['site_mini_logo'] 
                        ? get_uploaded_asset($general_settings['site_mini_logo']) 
                        : static_asset('assets/img/logo.png') }}" alt="">
        </a>
        <a id="toggle_btn" href="javascript:void(0);">
        </a>
    </div>

    <a id="mobile_btn" class="mobile_btn" href="#sidebar">
        <span class="bar-icon">
            <span></span>
            <span></span>
            <span></span>
        </span>
    </a>

    <ul class="nav user-menu">

        <li class="nav-item">
            <div class="top-nav-search">
                <a href="javascript:void(0);" class="responsive-search">
                    <i class="fa fa-search"></i>
                </a>
                <form action="#">
                    <div class="searchinputs">
                        <input type="text" placeholder="Search Here ...">
                        <div class="search-addon">
                            <span><img src="{{ static_asset('assets/img/icons/closes.svg') }}" alt="img"></span>
                        </div>
                    </div>
                    <a class="btn" id="searchdiv"><img src="{{ static_asset('assets/img/icons/search.svg') }}" alt="img"></a>
                </form>
            </div>
        </li>

        <li class="nav-item dropdown has-arrow main-drop">
            <a href="javascript:void(0);" class="dropdown-toggle nav-link userset" data-bs-toggle="dropdown">
                <span class="user-img">
                    @if(Auth::user()->avatar)
                    <img src="{{ get_uploaded_asset(Auth::user()->avatar) }}" alt="img" id="blah">
                    @else
                    <img src="assets/img/customer/customer5.jpg" alt="img" id="blah">
                    @endif
                   
                    <span class="status online"></span></span>
            </a>
            <div class="dropdown-menu menu-drop-user">
                <div class="profilename">
                    <div class="profileset">
                        <span class="user-img">
                            @if(Auth::user()->avatar)
                            <img src="{{ get_uploaded_asset(Auth::user()->avatar) }}" alt="img" id="blah">
                            @else
                            <img src="assets/img/customer/customer5.jpg" alt="img" id="blah">
                            @endif
                            <span class="status online"></span></span>
                        <div class="profilesets">
                            <h6>{{ Auth::user()->name }}</h6>
                            <h5>{{ Auth::user()->getRoleNames()->first() ?? 'No Role' }}</h5>
                        </div>
                    </div>
                    <hr class="m-0">
                    <a class="dropdown-item" href="{{ route('admin.profile.index') }}"> <i class="me-2" data-feather="user"></i> My Profile</a>
                    @can('general-setting-index')
                    <a class="dropdown-item" href="{{ route('admin.general-settings.index') }}"><i class="me-2" data-feather="settings"></i>Settings</a>
                    @endcan
                    <a class="dropdown-item" href="{{ route('admin.clear-cache') }}"><i class="me-2" data-feather="settings"></i>Clear Cache</a>

                    <hr class="m-0">
                    <a class="dropdown-item logout pb-0" href="{{ url('admin/logout') }}"><img src="{{ static_asset('assets/img/icons/log-out.svg') }}" class="me-2" alt="img">Logout</a>
                </div>
            </div>
        </li>
    </ul>


    <div class="dropdown mobile-user-menu">
        <a href="javascript:void(0);" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="fa fa-ellipsis-v"></i></a>
        <div class="dropdown-menu dropdown-menu-right">
            <a class="dropdown-item" href="{{ route('admin.profile.index') }}">My Profile</a>
            @can('general-setting-index')
            <a class="dropdown-item" href="{{ route('admin.general-settings.index') }}">Settings</a>
            @endcan
            <a class="dropdown-item" href="{{ route('admin.clear-cache') }}">Clear Cache</a>
            <a class="dropdown-item" href="{{ url('admin/logout') }}">Logout</a>
        </div>
    </div>

</div>