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

        if ($currentRouteName === $menu->slug) {
        $activeClass = 'active';
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
