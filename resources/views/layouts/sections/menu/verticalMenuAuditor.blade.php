<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

  <!-- App Brand -->
  <div class="app-brand demo">
    <a href="{{ url('/') }}" class="app-brand-link" style="text-decoration:none;">
        <img src="{{ asset('assets/img/favicon/logo-lldikti-4.png') }}"
             alt="LLDIKTI 4"
             style="height: 38px; width: auto; display: block; max-width: 100%; object-fit: contain;">
        <span class="sptjm-logo-subtitle" style="color:#64748b; font-size:0.6rem; font-weight:700; font-family:'Public Sans',sans-serif; text-transform:uppercase; letter-spacing:1.2px; margin-left: 45px; margin-top: -4px;">SPTJM ONLINE</span>
    </a>
    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
        <i class="bx bx-chevron-left" style="font-size: 1.9rem !important; color: #1a56db !important;"></i>
    </a>
  </div>

  <div class="menu-inner-shadow"></div>

  <ul class="menu-inner py-3">
    @foreach ($menuData[0]->menu as $menu)
      @if (isset($menu->menuHeader))
        <li class="menu-header small text-uppercase">
          <span class="menu-header-text">{{ __($menu->menuHeader) }}</span>
        </li>
      @else
        @php
                    $activeClass = null;

                    // 1. Check if the parent menu itself is active based on URL
                    $isParentUrlActive = false;
                    if (isset($menu->url)) {
                        $menuUrl = trim($menu->url, '/');
                        if ($menuUrl === '') {
                            $isParentUrlActive = request()->is('/');
                        } else {
                            $isParentUrlActive = request()->is($menuUrl) || request()->is($menuUrl . '/*');
                        }
                    }

                    // 2. Check if any submenu is active based on URL
                    $hasActiveSubmenu = false;
                    if (isset($menu->submenu)) {
                        foreach ($menu->submenu as $sub) {
                            $subUrl = trim($sub->url ?? '', '/');
                            if ($subUrl === '') {
                                if (request()->is('/')) { $hasActiveSubmenu = true; break; }
                            } else {
                                if (request()->is($subUrl) || request()->is($subUrl . '/*')) {
                                    $hasActiveSubmenu = true;
                                    break;
                                }
                            }
                            
                            // Nested submenu support
                            if (isset($sub->submenu)) {
                                foreach ($sub->submenu as $nestedSub) {
                                    $nestedSubUrl = trim($nestedSub->url ?? '', '/');
                                    if ($nestedSubUrl === '') {
                                        if (request()->is('/')) { $hasActiveSubmenu = true; break 2; }
                                    } else {
                                        if (request()->is($nestedSubUrl) || request()->is($nestedSubUrl . '/*')) {
                                            $hasActiveSubmenu = true;
                                            break 2;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    // 3. Determine active class
                    if ($hasActiveSubmenu) {
                        $activeClass = 'active open';
                    } elseif ($isParentUrlActive) {
                        $activeClass = 'active';
                    }
        @endphp

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

          @isset($menu->submenu)
            @include('layouts.sections.menu.submenu', ['menu' => $menu->submenu])
          @endisset
        </li>
      @endif
    @endforeach
  </ul>

</aside>
