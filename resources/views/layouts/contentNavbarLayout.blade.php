@extends('layouts/commonMaster')

@php
    /* Display elements */
    $contentNavbar = true;
    $containerNav = $containerNav ?? 'container-xxl';
    $isNavbar = $isNavbar ?? true;
    $isMenu = $isMenu ?? true;
    $isFlex = $isFlex ?? false;
    $isFooter = $isFooter ?? true;

    /* HTML Classes */
    $navbarDetached = 'navbar-detached';

    /* Content classes */
    $container = $container ?? 'container-xxl';

@endphp

@section('layoutContent')
    <div class="layout-wrapper layout-content-navbar {{ $isMenu ? '' : 'layout-without-menu' }}">
        <div class="layout-container">

            @if ($isMenu)
                @if (Auth::check() && Auth::user()->role === 'pic')
                    @include('layouts/sections/menu/verticalMenuPic')
                @else
                    @include('layouts/sections/menu/verticalMenu')
                @endif
            @endif


            <!-- Layout page -->
            <div class="layout-page">
                <!-- BEGIN: Navbar-->
                @if ($isNavbar)
                    @if (Auth::check() && Auth::user()->role === 'pic')
                        @include('layouts/sections/navbar/navbarPic')
                    @else
                        @include('layouts/sections/navbar/navbar')
                    @endif
                @endif
                <!-- END: Navbar-->


                <!-- Content wrapper -->
                <div class="content-wrapper" style="position: relative; min-height: 80vh;">
                    @if(empty(request()->query()))
                    <!-- Preloader to prevent FOUC on content area only -->
                    <style>
                        #sptjm-content-preloader {
                            position: absolute;
                            top: 0;
                            left: 0;
                            width: 100%;
                            height: 100%;
                            background: #f5f5f9; /* Match body background */
                            z-index: 9999;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            transition: opacity 0.4s ease, visibility 0.4s ease;
                            border-radius: inherit;
                        }
                        .sptjm-spinner {
                            width: 48px;
                            height: 48px;
                            border: 4px solid #e2e8f0;
                            border-bottom-color: #1a56db;
                            border-radius: 50%;
                            display: inline-block;
                            box-sizing: border-box;
                            animation: sptjm-rotation 1s linear infinite;
                        }
                        @keyframes sptjm-rotation {
                            0% { transform: rotate(0deg); }
                            100% { transform: rotate(360deg); }
                        }
                    </style>
                    <div id="sptjm-content-preloader">
                        <div style="display: flex; flex-direction: column; align-items: center; gap: 1rem;">
                            <span class="sptjm-spinner"></span>
                            <h6 style="color: #64748b; font-weight: 600; letter-spacing: 0.05em; font-family: 'Public Sans', sans-serif; margin: 0;">Memuat Data...</h6>
                        </div>
                    </div>
                    <script>
                        window.addEventListener('load', function() {
                            var preloader = document.getElementById('sptjm-content-preloader');
                            if(preloader) {
                                preloader.style.opacity = '0';
                                preloader.style.visibility = 'hidden';
                                setTimeout(function() {
                                    preloader.remove();
                                }, 400);
                            }
                        });
                    </script>
                    @endif

                    <!-- Content -->
                    @if ($isFlex)
                        <div class="{{ $container }} d-flex align-items-stretch flex-grow-1 p-0">
                        @else
                            <div class="{{ $container }} flex-grow-1 container-p-y">
                    @endif

                    @yield('content')

                </div>
                <!-- / Content -->

                <!-- Footer -->

                <!-- / Footer -->
                <div class="content-backdrop fade"></div>
            </div>
            <!--/ Content wrapper -->
        </div>
        <!-- / Layout page -->
    </div>

    @if ($isMenu)
        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>
    @endif
    <!-- Drag Target Area To SlideIn Menu On Small Screens -->
    <div class="drag-target"></div>
    </div>
    <!-- / Layout wrapper -->
@endsection
