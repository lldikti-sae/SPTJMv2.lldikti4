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
                  font-size:1.15rem;
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
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="bx bx-chevron-left" style="font-size: 1.9rem !important; color: #1a56db !important;"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    @php
        // Tentukan apakah SPTJM / TUKIN sedang dibuka oleh Admin (berdasarkan Pengaturan Usulan)
        $tahunMenu = session('tahun') ?? date('Y');
        $nowDate = now();
        $isSptjmOpen = \Illuminate\Support\Facades\DB::table('m_pengaturan_usulan')
            ->where('jenis_usulan', 'SPTJM')
            ->where('tahun', $tahunMenu)
            ->where('status', 'Aktifkan')
            ->whereDate('tanggal_mulai', '<=', $nowDate)
            ->whereDate('tanggal_selesai', '>=', $nowDate)
            ->exists();
        $isTukinOpen = \Illuminate\Support\Facades\DB::table('m_pengaturan_usulan')
            ->where('jenis_usulan', 'TUKIN')
            ->where('tahun', $tahunMenu)
            ->where('status', 'Aktifkan')
            ->whereDate('tanggal_mulai', '<=', $nowDate)
            ->whereDate('tanggal_selesai', '>=', $nowDate)
            ->exists();
    @endphp

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

        {{-- main menu --}}
        <li class="menu-item {{ $activeClass }}">
            <a href="{{ isset($menu->url) ? url($menu->url) : 'javascript:void(0);' }}"
                class="{{ isset($menu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}" @if (isset($menu->target)
                and !empty($menu->target)) target="_blank" @endif>
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
            @include('layouts.sections.menu.submenuPts', [
                'menu' => $menu->submenu,
                'isSptjmOpen' => $isSptjmOpen,
                'isTukinOpen' => $isTukinOpen,
                'parentName' => $menu->name,
            ])
            @endisset
        </li>
        @endif
        @endforeach
    </ul>

</aside>

<!-- Modal: Info Usulan Belum Dibuka -->
<div class="modal fade" id="menuLockedModal" tabindex="-1" aria-labelledby="menuLockedLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="menuLockedLabel">Informasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="menuLockedMessage">
                Usulan belum dibuka oleh Admin.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
    </div>

<script>
    document.addEventListener('click', function (e) {
        const link = e.target.closest('a[data-locked]');
        if (!link) return;
        e.preventDefault();
        const type = link.getAttribute('data-locked');
        let msg = 'Usulan belum dibuka oleh Admin.';
        if (type === 'sptjm') {
            msg = 'Tunjangan Profesi belum dibuka oleh Admin pada periode saat ini.';
        } else if (type === 'tukin') {
            msg = 'Tunjangan Kinerja belum dibuka oleh Admin pada periode saat ini.';
        }

        // Use SptjmAlert (defined in assets/js/sptjm-alert.js) for consistent UI
        try {
            if (window.SptjmAlert && typeof window.SptjmAlert.warning === 'function') {
                window.SptjmAlert.warning('Informasi', msg);
                return;
            }
        } catch (err) {
            // fallthrough to fallback
        }

        // Fallback: show existing bootstrap modal if SptjmAlert isn't available
        const msgEl = document.getElementById('menuLockedMessage');
        if (msgEl) msgEl.textContent = msg;
        const modal = new bootstrap.Modal(document.getElementById('menuLockedModal'));
        modal.show();
    });
</script>
