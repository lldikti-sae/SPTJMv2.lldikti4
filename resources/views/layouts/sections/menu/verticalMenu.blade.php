<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

    <style>
        /* --- Brand area layout --- */
        .layout-menu .app-brand.demo {
            padding: 0 1.2rem !important;
            min-height: 72px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: flex-start !important;
            gap: 10px !important;
            border-bottom: 1px solid #eef0f4 !important;
        }
    </style>

    <!-- App Brand (logo only — toggle is in navbar) -->
    <div class="app-brand demo">
        <a href="{{ url('/') }}" class="app-brand-link" style="text-decoration:none; display:flex; align-items:center; gap:10px; overflow:hidden;">
            <div class="app-brand-logo" style="width:44px; height:44px; border-radius:50%; overflow:hidden; flex-shrink:0;">
                <img src="{{ asset('assets/img/favicon/logo-lldikti-4.png') }}"
                     alt="LLDIKTI 4"
                     style="height:44px; width:auto; object-fit:cover; object-position:left center; display:block;">
            </div>
            <div class="app-brand-text d-flex flex-column" style="line-height:1.2; overflow:hidden;">
                <span style="color:#0f3994; font-size:1.2rem; font-weight:800; font-family:'Public Sans',sans-serif; white-space:nowrap; letter-spacing:0.3px;">LLDIKTI<span style="color:#d97706;">4</span></span>
                <span style="color:#64748b; font-size:0.68rem; font-weight:700; font-family:'Public Sans',sans-serif; text-transform:uppercase; letter-spacing:0.8px; white-space:nowrap;">SPTJM ONLINE</span>
            </div>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-3">
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
