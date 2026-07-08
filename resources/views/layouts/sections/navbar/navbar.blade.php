@php
$containerNav = $containerNav ?? 'container-fluid';
$navbarDetached = $navbarDetached ?? '';

@endphp
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
/* ─── NAVBAR REDESIGN ─────────────────────────── */
#layout-navbar {
    background: #ffffff !important;
    border-bottom: 1px solid #e8ecf4 !important;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06) !important;
    padding: 0 24px !important;
    min-height: 64px !important;
}
#layout-navbar .navbar-nav-right {
    width: 100%;
    display: flex !important;
    align-items: center !important;
}
#layout-navbar .sptjm-nav-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #0f2b5c;
    letter-spacing: -0.3px;
    white-space: nowrap;
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

                <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0">
                    <a class="nav-item nav-link px-0" href="javascript:void(0);" aria-label="Toggle menu">
                        <i class="bx bx-menu bx-sm" aria-hidden="true"></i>
                    </a>
                </div>

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
