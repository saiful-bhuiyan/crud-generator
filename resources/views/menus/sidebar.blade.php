@php
    $menus = \App\Models\Menu::with('children')->whereNull('parent_id')->orderBy('order')->get();
@endphp

@foreach ($menus as $menu)
    @if ($menu->permission_name == null || auth()->user()->can($menu->permission_name))
        <li class="nav-item">
            <a class="nav-link" href="{{ $menu->route ? route($menu->route) : '#' }}">
                <i class="{{ $menu->icon }}"></i> {{ $menu->title }}
            </a>

            @if ($menu->children->count())
                <ul class="nav flex-column ms-3">
                    @foreach ($menu->children as $child)
                        @if ($child->permission_name == null || auth()->user()->can($child->permission_name))
                            <li>
                                <a class="nav-link" href="{{ $child->route ? route($child->route) : '#' }}">
                                    <i class="{{ $child->icon }}"></i> {{ $child->title }}
                                </a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            @endif
        </li>
    @endif
@endforeach