<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

    <!-- ! Hide app brand if navbar-full -->
    <div class="app-brand demo" style="height: auto; min-height: 80px; padding: 12px 16px; display: flex; flex-direction: row; align-items: center; justify-content: flex-start; gap: 10px; border-bottom: 1px solid #eef0f4; width: 100%; box-sizing: border-box; overflow: hidden; position: relative;">
        <a href="{{ url('/') }}" class="app-brand-link d-flex align-items-center text-decoration-none" style="gap: 10px; overflow: hidden;">
            {{-- Crop hanya bagian logo bulat dari gambar yang landscape --}}
            <div style="width: 46px; height: 46px; overflow: hidden; flex-shrink: 0; border-radius: 50%;">
                <img src="{{ asset('assets/img/favicon/logo-lldikti-4.png') }}"
                     alt="LLDIKTI 4"
                     style="height: 46px; width: auto; object-fit: cover; object-position: left center; display: block;">
            </div>
            <div class="d-flex flex-column" style="line-height: 1.15; overflow: hidden;">
                <span style="color: #0f3994; font-size: 1.1rem; font-weight: 800; letter-spacing: 0.3px; font-family: 'Public Sans', sans-serif; white-space: nowrap;">LLDIKTI<span style="color:#d97706;">4</span></span>
                <span style="color: #64748b; font-size: 0.65rem; font-weight: 600; letter-spacing: 0.8px; font-family: 'Public Sans', sans-serif; text-transform: uppercase; white-space: nowrap;">SPTJM ONLINE</span>
            </div>
        </a>
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large d-block d-xl-none" style="position:absolute; right:8px; top:50%; transform:translateY(-50%);">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
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
