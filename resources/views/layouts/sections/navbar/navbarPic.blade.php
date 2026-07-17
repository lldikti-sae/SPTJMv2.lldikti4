@php
$containerNav = $containerNav ?? 'container-fluid';
$navbarDetached = $navbarDetached ?? '';

@endphp
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
/* ─── NAVBAR — Unified, symmetric, proportional ─────────── */

/* ── Navbar container ── */
#layout-navbar {
    background: #ffffff !important;
    border-bottom: 1px solid #e2e8f0 !important;
    box-shadow: 0 1px 8px rgba(0, 0, 0, 0.05) !important;
    padding: 0 20px !important;
    min-height: 58px !important;
    height: 58px !important;
}

/* ── Inner nav row ── */
#layout-navbar .navbar-nav-right {
    width: 100% !important;
    display: flex !important;
    align-items: center !important;
    height: 100% !important;
    gap: 0 !important;
}

/* ── Hamburger toggle ── */
.sptjm-nav-hamburger {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 36px !important;
    height: 36px !important;
    border-radius: 8px !important;
    background: transparent !important;
    border: none !important;
    cursor: pointer !important;
    text-decoration: none !important;
    transition: background 0.15s ease !important;
    flex-shrink: 0 !important;
    margin-right: 12px !important;
}
.sptjm-nav-hamburger:hover {
    background: rgba(15, 57, 148, 0.07) !important;
}
.sptjm-nav-hamburger i {
    font-size: 1.45rem !important;
    color: #0f3994 !important;
}

/* Toggle icon open/closed state */
html:not(.layout-menu-collapsed) .navbar-toggle-icon-open  { display: inline-block !important; }
html:not(.layout-menu-collapsed) .navbar-toggle-icon-closed { display: none !important; }
html.layout-menu-collapsed .navbar-toggle-icon-open  { display: none !important; }
html.layout-menu-collapsed .navbar-toggle-icon-closed { display: inline-block !important; }

/* Desktop (≥1200px): hide hamburger when sidebar expanded */
@media (min-width: 1200px) {
    html:not(.layout-menu-collapsed) .sptjm-nav-hamburger { display: none !important; }
    html.layout-menu-collapsed .sptjm-nav-hamburger { display: flex !important; }
}

/* ── Page title ── */
#layout-navbar .sptjm-nav-title {
    font-size: 1.0rem !important;
    font-weight: 600 !important;
    color: #1e293b !important;
    letter-spacing: -0.1px !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
}

/* ── Divider between title and user info ── */
.sptjm-nav-divider {
    width: 1px;
    height: 28px;
    background: #e2e8f0;
    flex-shrink: 0;
    margin: 0 16px;
}

/* ── User info block ── */
.sptjm-user-info-wrap {
    display: flex !important;
    align-items: center !important;
    gap: 10px !important;
    cursor: pointer !important;
    text-decoration: none !important;
    padding: 6px 0 !important;
    flex-shrink: 0 !important;
}
.sptjm-user-info-wrap .sptjm-user-text {
    text-align: right !important;
    line-height: 1 !important;
}
.sptjm-user-info-wrap .sptjm-user-name {
    display: block !important;
    font-size: 0.84rem !important;
    font-weight: 600 !important;
    color: #1e293b !important;
    line-height: 1.25 !important;
}
.sptjm-user-info-wrap .sptjm-user-role {
    display: block !important;
    font-size: 0.72rem !important;
    font-weight: 400 !important;
    color: #64748b !important;
    line-height: 1.25 !important;
}
.sptjm-user-info-wrap .sptjm-avatar {
    width: 34px !important;
    height: 34px !important;
    border-radius: 50% !important;
    object-fit: cover !important;
    border: 2px solid #e2e8f0 !important;
    flex-shrink: 0 !important;
}

/* ── Dropdown arrow hidden ── */
.dropdown-user .hide-arrow::after { display: none !important; }

/* ── Dropdown menu ── */
.dropdown-user .dropdown-menu {
    min-width: 200px !important;
    border: 1px solid #e2e8f0 !important;
    box-shadow: 0 8px 24px rgba(0,0,0,0.10) !important;
    border-radius: 10px !important;
    padding: 6px !important;
    margin-top: 6px !important;
}
.dropdown-user .dropdown-item {
    border-radius: 6px !important;
    font-size: 0.84rem !important;
    padding: 8px 12px !important;
    color: #374151 !important;
    font-weight: 400 !important;
}
.dropdown-user .dropdown-item:hover {
    background: #f1f5f9 !important;
    color: #1e293b !important;
}
</style>

<!-- Navbar PIC -->
@if (isset($navbarDetached) && $navbarDetached == 'navbar-detached')
<nav class="layout-navbar {{ $containerNav }} navbar navbar-expand-xl {{ $navbarDetached }} align-items-center bg-navbar-theme"
    id="layout-navbar">
    @endif
    @if (isset($navbarDetached) && $navbarDetached == '')
    <nav class="layout-navbar navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
        <div class="{{ $containerNav }}">
            @endif

                <!-- Hamburger — always visible, toggles sidebar on all screen sizes -->
                <a href="javascript:void(0);"
                   class="sptjm-nav-hamburger layout-menu-toggle"
                   aria-label="Toggle sidebar">
                    <i class="bx bx-chevron-left navbar-toggle-icon-open"></i>
                    <i class="bx bx-chevron-right navbar-toggle-icon-closed" style="display: none;"></i>
                </a>

                <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
                    <!-- Title -->
                    <div class="d-flex align-items-center flex-grow-1" style="min-width: 0;">
                        <span class="sptjm-nav-title d-none d-md-inline">Dashboard Operator LLDIKTI Wilayah IV Tahun {{ session('tahun') }}</span>
                        <span class="sptjm-nav-title d-inline d-md-none" style="font-size:0.85rem;">Dashboard PIC {{ session('tahun') }}</span>
                    </div>
                    <!-- /Title -->

                    <ul class="navbar-nav flex-row align-items-center ms-auto">
                        <!-- User -->
                        <li class="nav-item navbar-dropdown dropdown-user dropdown">
                            @php
                                $userName = 'PIC';
                                $userRole = 'PIC Wilayah';
                                
                                if (Auth::guard('dosen')->check()) {
                                    $userName = Auth::guard('dosen')->user()->nama ?? 'Dosen';
                                    $userRole = 'Dosen';
                                } elseif (Auth::guard('pts')->check()) {
                                    $userName = Auth::guard('pts')->user()->nama_pts ?? Auth::guard('pts')->user()->nama_pimpinan ?? 'Operator PTS';
                                    $userRole = 'Operator PTS';
                                } elseif (Auth::guard('web')->check()) {
                                    $user = Auth::guard('web')->user();
                                    // Use email as display name (what the user logs in with)
                                    // cp stores a phone number, not a display name
                                    if ($user->email === 'admin') {
                                        $userName = 'Administrator';
                                    } else {
                                        $userName = ucfirst($user->email);
                                    }
                                    if ($user->role === 'admin') {
                                        $userRole = 'SystemBoot';
                                    } elseif ($user->role === 'pic') {
                                        $userRole = 'PIC Wilayah';
                                    } else {
                                        $userRole = ucfirst(strtolower($user->role ?? 'Admin'));
                                    }
                                }
                            @endphp
                            <a class="nav-link dropdown-toggle hide-arrow sptjm-user-info-wrap pe-0" href="javascript:void(0);" data-bs-toggle="dropdown">
                                <div class="sptjm-user-text">
                                    <span class="sptjm-user-name">{{ $userName }}</span>
                                    <span class="sptjm-user-role">{{ $userRole }}</span>
                                </div>
                                <img src="{{ asset('assets/img/avatars/user.png') }}" alt="avatar" class="sptjm-avatar">
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0);">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 me-3">
                                                <div class="avatar avatar-online">
                                                    <img src="{{ asset('assets/img/avatars/user.png') }}" alt class="w-px-40 h-auto rounded-circle">
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <span class="fw-medium d-block text-dark">{{ $userName }}</span>
                                                <small class="text-muted">{{ $userRole }}</small>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                                <li><div class="dropdown-divider"></div></li>
                                <li>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="button" id="btn-logout" class="dropdown-item">
                                            <i class='bx bx-power-off me-2'></i>
                                            <span class="align-middle">Log Out</span>
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                        <!--/ User -->
                    </ul>
                </div>
            @if (!isset($navbarDetached))
        </div>
        @endif
    </nav>
    <!-- / Navbar -->
    <script>
    document.addEventListener("DOMContentLoaded", () => {
        const btnLogout = document.getElementById("btn-logout");
        if (btnLogout) {
            btnLogout.addEventListener('click', (e) => {
                Swal.fire({
                    title: "Apakah Anda Yakin?",
                    text: "Kamu akan logout dan tidak bisa kembali!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Ya, logout!",
                    cancelButtonText: "Batal!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.getElementById("logout-form");
                        form.submit();
                    }
                });
            });
        }
    });
    </script>
