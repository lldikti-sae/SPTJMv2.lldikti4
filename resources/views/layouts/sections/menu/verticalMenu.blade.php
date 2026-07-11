<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

    <style>
        /* --- Brand area layout --- */
        .layout-menu .app-brand.demo {
    padding: 12px 1.2rem 22px !important;
    min-height: 90px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: flex-start !important;
    gap: 10px !important;
    border-bottom: 1px solid #eef0f4 !important;
    margin-bottom: 12px !important;
}
    </style>

    <!-- App Brand -->
    <div class="app-brand demo">
        <a href="{{ url('/') }}" class="app-brand-link" style="text-decoration:none;">
            <img src="{{ asset('assets/img/favicon/logo-lldikti-4.png') }}"
                 alt="LLDIKTI 4"
                 style="height: 38px; width: auto; display: block; max-width: 100%; object-fit: contain;">
           <span class="sptjm-logo-subtitle"
           style="color:#64748b;
           font-size:1rem;
           font-weight:700;
           letter-spacing:2px;
           text-transform:uppercase;
           text-align:center;
           display:block;
           margin-top:2px;
           margin-left:45px ;">
    SPTJM ONLINE
</span>
        </a>
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block">
            <i class="bx bx-chevron-left align-middle" style="font-size: 1.4rem !important; color: #1a56db !important;"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner pt-5 pb-3">
        @foreach ($menuData[0]->menu as $menu)
            {{-- adding active and open class if child is active --}}

            {{-- menu headers --}}
            @if (isset($menu->menuHeader))
                <li class="menu-header small text-uppercase">
                    <span class="menu-header-text">{{ __($menu->menuHeader) }}</span>
                </li>
            @else
                {{-- active menu method --}}
                @php
                    $activeClass = null;
                    $currentRouteName = Route::currentRouteName();

                    // Cek apakah ada submenu yang aktif berdasarkan URL request atau route name
                    $hasActiveSubmenu = false;
                    if (isset($menu->submenu)) {
                        foreach ($menu->submenu as $sub) {
                            $isSubUrlActive = isset($sub->url) && (request()->is(trim($sub->url, '/')) || request()->is(trim($sub->url, '/') . '/*'));
                            if ($currentRouteName === ($sub->slug ?? '') || $isSubUrlActive) {
                                $hasActiveSubmenu = true;
                                break;
                            }
                            // Dukungan bersarang (nested submenu) jika ada
                            if (isset($sub->submenu)) {
                                foreach ($sub->submenu as $nestedSub) {
                                    $isNestedUrlActive = isset($nestedSub->url) && (request()->is(trim($nestedSub->url, '/')) || request()->is(trim($nestedSub->url, '/') . '/*'));
                                    if ($currentRouteName === ($nestedSub->slug ?? '') || $isNestedUrlActive) {
                                        $hasActiveSubmenu = true;
                                        break 2;
                                    }
                                }
                            }
                        }
                    }

                    $isParentUrlActive = isset($menu->url) && (request()->is(trim($menu->url, '/')) || request()->is(trim($menu->url, '/') . '/*'));

                    if ($currentRouteName === $menu->slug || $isParentUrlActive) {
                        $activeClass = 'active';
                    } elseif ($hasActiveSubmenu) {
                        $activeClass = 'active open';
                    } elseif (isset($menu->submenu)) {
                        if (gettype($menu->slug) === 'array') {
                            foreach ($menu->slug as $slug) {
                                if (str_contains($currentRouteName, $slug) and strpos($currentRouteName, $slug) === 0) {
                                    $activeClass = 'active open';
                                }
                            }
                        } else {
                            if (
                                str_contains($currentRouteName, $menu->slug) and
                                strpos($currentRouteName, $menu->slug) === 0
                            ) {
                                $activeClass = 'active open';
                            }
                        }
                    }
                @endphp

                {{-- main menu --}}
                <li class="menu-item {{ $activeClass }}">
                    <a href="{{ isset($menu->url) ? url($menu->url) : 'javascript:void(0);' }}"
                        class="{{ isset($menu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}"
                        @if (isset($menu->target) and !empty($menu->target)) target="_blank" @endif>
                        @isset($menu->icon)
                            <i class="{{ $menu->icon }}"></i>
                        @endisset
                        <div>{{ isset($menu->name) ? __($menu->name) : '' }}</div>
                        @isset($menu->badge)
                            <div class="badge bg-{{ $menu->badge[0] }} rounded-pill ms-auto">{{ $menu->badge[1] }}</div>
                        @endisset
                    </a>

                    {{-- submenu --}}
                    @isset($menu->submenu)
                        @include('layouts.sections.menu.submenu', ['menu' => $menu->submenu])
                    @endisset
                </li>
            @endif
        @endforeach
    </ul>

</aside>
