@php
$containerNav = $containerNav ?? 'container-fluid';
$navbarDetached = $navbarDetached ?? '';

@endphp
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
/* ─── NAVBAR REDESIGN ─────────────────────────── */
/* ─── Navbar hamburger toggle — always visible ─── */
.sptjm-nav-hamburger {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 40px !important;
    height: 40px !important;
    border-radius: 8px !important;
    background: transparent !important;
    border: none !important;
    cursor: pointer !important;
    text-decoration: none !important;
    transition: background 0.18s ease !important;
    flex-shrink: 0 !important;
    margin-right: 8px !important;
}
.sptjm-nav-hamburger:hover {
    background: rgba(15, 57, 148, 0.07) !important;
}
.sptjm-nav-hamburger i {
    font-size: 1.55rem !important;
    color: #0f3994 !important;
}
html:not(.layout-menu-collapsed) .navbar-toggle-icon-open {
    display: inline-block !important;
}
html:not(.layout-menu-collapsed) .navbar-toggle-icon-closed {
    display: none !important;
}
html.layout-menu-collapsed .navbar-toggle-icon-open {
    display: none !important;
}
html.layout-menu-collapsed .navbar-toggle-icon-closed {
    display: inline-block !important;
}

/* On desktop (1200px and up): hide navbar toggle when sidebar is expanded */
@media (min-width: 1200px) {
    html:not(.layout-menu-collapsed) .sptjm-nav-hamburger {
        display: none !important;
    }
    html.layout-menu-collapsed .sptjm-nav-hamburger {
        display: flex !important;
    }
}

#layout-navbar {
    background: #ffffff !important;
    border-bottom: 1px solid #e8ecf4 !important;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06) !important;
    padding: 0 16px !important;
    min-height: 64px !important;
}
#layout-navbar .navbar-nav-right {
    width: 100%;
    display: flex !important;
    align-items: center !important;
}
#layout-navbar .sptjm-nav-title {
    font-size: 1.12rem !important;
    font-weight: 700 !important;
    color: #0f2b5c !important;
    letter-spacing: -0.15px !important;
    white-space: nowrap !important;
}
/* User info inline display (name + role stacked, then avatar) */
.sptjm-user-info-wrap {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    text-decoration: none;
}
.sptjm-user-info-wrap .sptjm-user-text {
    text-align: right;
}
.sptjm-user-info-wrap .sptjm-user-name {
    display: block;
    font-size: 0.88rem;
    font-weight: 700;
    color: #1e293b;
    line-height: 1.2;
}
.sptjm-user-info-wrap .sptjm-user-role {
    display: block;
    font-size: 0.75rem;
    color: #64748b;
    line-height: 1.2;
}
.sptjm-user-info-wrap .sptjm-avatar {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e2e8f0;
}
/* Dropdown arrow hidden on custom link */
.dropdown-user .hide-arrow::after { display: none !important; }
</style>

<!-- Navbar -->
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
                        <span class="sptjm-nav-title d-none d-md-inline">Dashboard Admin Tahun {{ session('tahun') }}</span>
                        <span class="sptjm-nav-title d-inline d-md-none" style="font-size:0.9rem;">Dashboard Admin {{ session('tahun') }}</span>
                    </div>
                    <!-- /Title -->

                    <ul class="navbar-nav flex-row align-items-center ms-auto">
                        <!-- User -->
                        <li class="nav-item navbar-dropdown dropdown-user dropdown">
                            @php
                                $userName = 'Administrator';
                                $userRole = 'SystemBoot';
                                
                                if (Auth::guard('dosen')->check()) {
                                    $userName = Auth::guard('dosen')->user()->nama ?? 'Dosen';
                                    $userRole = 'Dosen';
                                } elseif (Auth::guard('pts')->check()) {
                                    $userName = Auth::guard('pts')->user()->nama_pts ?? Auth::guard('pts')->user()->nama_pimpinan ?? 'Operator PTS';
                                    $userRole = 'Operator PTS';
                                } elseif (Auth::guard('web')->check()) {
                                    $user = Auth::guard('web')->user();
                                    $userName = $user->cp;
                                    if (!$userName) {
                                        $userName = ($user->email === 'admin') ? 'Administrator' : ucfirst($user->email);
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
