@php
$containerNav = $containerNav ?? 'container-fluid';
$navbarDetached = $navbarDetached ?? '';

@endphp
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                    <div class="d-flex align-items-center flex-grow-1" style="min-width: 0;">
                        <h4 class="mb-0 fw-bold text-dark" style="min-width: 0; letter-spacing: -0.5px;">
                            <span class="d-inline d-md-none fw-bold small text-truncate d-inline-block" style="max-width: 100%;">Dashboard Admin {{ session('tahun') }}</span>
                            <span class="d-none d-md-inline">Dashboard Admin Tahun {{ session('tahun') }}</span>
                        </h4>
                    </div>
                </div>
                <!-- /Title -->
                
                <ul class="navbar-nav flex-row align-items-center ms-auto">
                    <!-- User -->
                    <li class="nav-item navbar-dropdown dropdown-user dropdown">
                        <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                            @php
                                $userName = 'Administrator';
                                $userRole = 'Admin';
                                
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
                                        $userRole = 'Admin Utama';
                                    } elseif ($user->role === 'pic') {
                                        $userRole = 'PIC Wilayah';
                                    } else {
                                        $userRole = ucfirst(strtolower($user->role ?? 'Admin'));
                                    }
                                }
                            @endphp
                            <!-- Profil avatar -->
                            <div class="avatar avatar-online">
                                <img src="{{ asset('assets/img/avatars/user.png') }}" alt class="w-px-40 h-auto rounded-circle">
                            </div>
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
                            <li>
                                <div class="dropdown-divider"></div>
                            </li>
                            <li>
                                <div class="dropdown-divider"></div>
                            </li>
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
        const btnLogout = document.getElementById("btn-logout")
        console.log(btnLogout);
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
                    const form = document.getElementById("logout-form")
                    form.submit()
                }
            });
        })
    })
    </script>
