@php
    $menus = \App\Models\Menu::with('children')->whereNull('parent_id')->orderBy('order')->get();
@endphp

<div class="sidebar" id="sidebar">
    <div class="sidebar-inner slimscroll">
        <div id="sidebar-menu" class="sidebar-menu">
            <ul>
                <li class="{{ request()->is('admin/') ? 'active' : '' }}">
                    <a href="{{ url(env('APP_URL','http://127.0.0.1:8000')) }}/admin">
                        <img src="{{ static_asset('assets/img/icons/dashboard.svg') }}" alt="img">
                        <span> Dashboard</span>
                    </a>
                </li>
                
                @foreach ($menus as $menu)
                @if(auth()->user()->can($menu->permission_name))
                    @php
                        $isActive = request()->is(trim($menu->route, '/')) || $menu->children->contains(function($child) {
                            return request()->is(trim($child->route, '/'));
                        });
                    @endphp

                    @if ($menu->children->count())
                        <li class="submenu {{ $isActive ? 'active' : '' }}">
                            <a href="{{ $menu->route ? url(env('APP_URL','http://127.0.0.1:8000')).'/admin/'.$menu->route : '#' }}">
                                <i class="{{ $menu->icon }}"></i> <span> {{ $menu->title }} </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <ul>
                                @foreach ($menu->children as $child)
                                    @if(auth()->user()->can($child->permission_name))
                                    <li class="{{ request()->is(trim($child->route, '/')) ? 'active' : '' }}">
                                        <a href="{{ $child->route ? url(env('APP_URL','http://127.0.0.1:8000')).'/admin/'.$child->route : '#' }}">
                                            {{ $child->title }}
                                        </a>
                                    </li>
                                    @endif
                                @endforeach
                            </ul>
                        </li>
                    @else
                        <li class="{{ $isActive ? 'active' : '' }}">
                            <a href="{{ $menu->route ? url(env('APP_URL','http://127.0.0.1:8000')).'/admin/'.$menu->route : '#' }}">
                                <i class="{{ $menu->icon }}"></i> <span> {{ $menu->title }} </span>
                            </a>
                        </li>
                    @endif
                @endif
                @endforeach
            </ul>
        </div>
    </div>
</div>
