@php
    $menus = \App\Models\Menu::with('children')->whereNull('parent_id')->orderBy('order')->get();
    $adminUrl = fn (?string $path = null) => $path
        ? url('/admin/' . ltrim($path, '/'))
        : url('/admin');
    $isCurrent = function (?string $path): bool {
        if (!$path) {
            return false;
        }

        $normalized = trim($path, '/');

        if (!str_starts_with($normalized, 'admin')) {
            $normalized = 'admin/' . $normalized;
        }

        return request()->is($normalized) || request()->is($normalized . '/*');
    };
@endphp

<div class="sidebar sidebar--redesign" id="sidebar">
    <div class="sidebar-inner slimscroll sidebar-inner--redesign">
        <nav id="sidebar-menu" class="sidebar-menu sidebar-menu--redesign" aria-label="Admin navigation">

            <div class="sidebar-menu__head">
            </div>

            <div class="sidebar-search" role="search">
                <label class="visually-hidden" for="sidebar-search">Search menu</label>
                <span class="sidebar-search__icon" aria-hidden="true"><i class="fas fa-search"></i></span>
                <input type="search" id="sidebar-search" class="sidebar-search__input" placeholder="Search menu…" autocomplete="off" spellcheck="false">
                <button type="button" id="clear-search" class="sidebar-search__clear" hidden aria-label="Clear search">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <ul id="menu-list" class="sidebar-nav">
                <li class="menu-item {{ request()->is('admin') || request()->is('admin/') ? 'active' : '' }}">
                    <a href="{{ $adminUrl() }}">
                        <span class="sidebar-nav__icon"><img src="{{ static_asset('assets/img/icons/dashboard.svg') }}" alt=""></span>
                        <span class="sidebar-nav__text">Dashboard</span>
                    </a>
                </li>

                @foreach ($menus as $menu)
                    @if (auth()->user()->can($menu->permission_name))
                        @php
                            $isActive = $isCurrent($menu->route) || $menu->children->contains(function ($child) use ($isCurrent) {
                                return $isCurrent($child->route);
                            });
                        @endphp

                        @if ($menu->children->count())
                            <li class="submenu menu-item {{ $isActive ? 'active' : '' }}">
                                <a href="javascript:void(0);">
                                    <span class="sidebar-nav__icon"><i class="{{ $menu->icon }}"></i></span>
                                    <span class="sidebar-nav__text">{{ $menu->title }}</span>
                                    <span class="menu-arrow" aria-hidden="true"></span>
                                </a>
                                <ul>
                                    @foreach ($menu->children as $child)
                                        @if (auth()->user()->can($child->permission_name))
                                            <li class="child-item {{ $isCurrent($child->route) ? 'active' : '' }}">
                                                <a href="{{ $child->route ? $adminUrl($child->route) : '#' }}">
                                                    {{ $child->title }}
                                                </a>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </li>
                        @else
                            <li class="menu-item {{ $isActive ? 'active' : '' }}">
                                <a href="{{ $menu->route ? $adminUrl($menu->route) : '#' }}">
                                    <span class="sidebar-nav__icon"><i class="{{ $menu->icon }}"></i></span>
                                    <span class="sidebar-nav__text">{{ $menu->title }}</span>
                                </a>
                            </li>
                        @endif
                    @endif
                @endforeach
            </ul>

            <p id="sidebar-no-results" class="sidebar-empty" hidden>No items match your search.</p>
        </nav>
    </div>
</div>

@section('script')
<script>
(function () {
    var sidebar = document.getElementById('sidebar');
    var input = document.getElementById('sidebar-search');
    var clearBtn = document.getElementById('clear-search');
    var list = document.getElementById('menu-list');
    var emptyMsg = document.getElementById('sidebar-no-results');
    if (!input || !list) return;filter

    function stripHidden(el) {
        el.classList.remove('sidebar-nav__hidden');
    }

    function applyFilter() {
        var q = input.value.trim().toLowerCase();
        if (clearBtn) clearBtn.hidden = !q;

        list.querySelectorAll('.sidebar-nav__hidden').forEach(stripHidden);

        if (!q) {
            sidebar.classList.remove('sidebar-is-filtering');
            if (emptyMsg) emptyMsg.hidden = true;
            return;
        }

        sidebar.classList.add('sidebar-is-filtering');
        var anyVisible = false;
        var topItems = list.querySelectorAll(':scope > li');

        topItems.forEach(function (li) {
            if (li.classList.contains('submenu')) {
                var parentA = li.querySelector(':scope > a');
                var parentText = (parentA && parentA.textContent || '').toLowerCase().trim();
                var childLis = li.querySelectorAll(':scope > ul > li');
                var childMatched = false;

                childLis.forEach(function (cli) {
                    var t = (cli.textContent || '').toLowerCase();
                    var match = t.includes(q) || parentText.includes(q);
                    if (!match) cli.classList.add('sidebar-nav__hidden');
                    else childMatched = true;
                });

                var show = parentText.includes(q) || childMatched;
                if (!show) li.classList.add('sidebar-nav__hidden');
                else anyVisible = true;
            } else {
                var text = (li.textContent || '').toLowerCase();
                if (!text.includes(q)) li.classList.add('sidebar-nav__hidden');
                else anyVisible = true;
            }
        });

        if (emptyMsg) emptyMsg.hidden = anyVisible;
    }

    input.addEventListener('input', applyFilter);
    input.addEventListener('keyup', applyFilter);

    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            input.value = '';
            applyFilter();
            input.focus();
        });
    }
})();
</script>
@endsection
