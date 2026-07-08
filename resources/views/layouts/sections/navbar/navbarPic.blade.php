@php
$containerNav = $containerNav ?? 'container-fluid';
$navbarDetached = $navbarDetached ?? '';
@endphp
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Navbar PIC -->
@if (isset($navbarDetached) && $navbarDetached == 'navbar-detached')
<nav class="layout-navbar {{ $containerNav }} navbar navbar-expand-xl {{ $navbarDetached }} align-items-center bg-navbar-theme"
    id="layout-navbar">
    @endif
    @if (isset($navbarDetached) && $navbarDetached == '')
    <nav class="layout-navbar navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
        <div class="{{ $containerNav }}">
            @endif

                <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
                    <a class="nav-item nav-link px-0" href="javascript:void(0);" aria-label="Toggle menu">
                        <i class="bx bx-menu bx-sm" aria-hidden="true"></i>
                    </a>
                </div>

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
                            <a class="nav-link dropdown-toggle hide-arrow sptjm-user-info-wrap pe-0" href="javascript:void(0);" data-bs-toggle="dropdown">
                                <div class="sptjm-user-text">
                                    <span class="sptjm-user-name">{{ Auth::user()->email ?? 'PIC' }}</span>
                                    <span class="sptjm-user-role">PIC Wilayah</span>
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
                                                <span class="fw-medium d-block text-dark">{{ Auth::user()->email ?? 'PIC' }}</span>
                                                <small class="text-muted">PIC Wilayah</small>
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
