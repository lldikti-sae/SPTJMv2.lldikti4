<!DOCTYPE html>

<html class="light-style layout-menu-fixed" data-theme="theme-default" data-assets-path="{{ asset('/assets') . '/' }}"
    data-base-url="{{ url('/') }}" data-framework="laravel" data-template="vertical-menu-laravel-template-free">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>@yield('title') </title>
    <meta name="description"
        content="{{ config('variables.templateDescription') ? config('variables.templateDescription') : '' }}" />
    <meta name="keywords"
        content="{{ config('variables.templateKeyword') ? config('variables.templateKeyword') : '' }}">

    <!-- Preload Logo and Fonts to prevent blinking/flashing on page load -->
    <link rel="preload" as="image" href="{{ asset('assets/img/favicon/logo-lldikti-4.png') }}">
    <link rel="preload" href="{{ asset('assets/vendor/fonts/boxicons/boxicons.woff2') }}" as="font" type="font/woff2" crossorigin>
    <!-- laravel CRUD token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Canonical SEO -->
    <link rel="canonical" href="{{ config('variables.productPage') ? config('variables.productPage') : '' }}">
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/icon.png') }}" />
    <!-- Include Styles -->
    @include('layouts/sections/styles')

    <!-- Global zoom: scale overall UI on desktop -->
    <style>
        @media (min-width: 768px) {
            html {
                zoom: 1.0;
            }
        }
    </style>
    <!-- Make tables responsive: allow horizontal scrolling and ensure full-width tables -->
    <style>
        /* wrapper for tables to enable horizontal scroll on small screens */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* ensure tables expand to container width when possible */
        table {
            width: 100% !important;
            table-layout: auto;
        }

        /* make DataTables wrapper allow overflow if needed */
        .dataTables_wrapper {
            width: 100%;
            overflow-x: auto;
        }

        /* small tweak: keep action columns from shrinking too much */
        table th:last-child,
        table td:last-child {
            white-space: nowrap;
        }
    </style>

    <script>
        // Disable transitions instantly to prevent sidebar accordion from blinking/jumping on page load
        document.documentElement.classList.add('sptjm-no-transition');
        window.addEventListener('load', function() {
            // Re-enable transitions once ALL scripts (including menu.js) have fully initialized
            setTimeout(function() {
                document.documentElement.classList.remove('sptjm-no-transition');
            }, 50);
        });
    </script>
    <style>
        .sptjm-no-transition .layout-menu,
        .sptjm-no-transition .layout-menu * {
            transition: none !important;
            animation: none !important;
        }
    </style>

    <!-- Sidebar brand should stay visible when menu scrolls -->
    <style>
        /* Keep the brand/header above the scrollable menu items */
        .layout-menu .app-brand {
            position: sticky;
            top: 0;
            z-index: 10;
            background: inherit;
        }

        /* Ensure shadow doesn't cover brand text */
        .layout-menu .menu-inner-shadow {
            position: sticky;
            top: 0;
            z-index: 9;
            background: inherit;
        }

        /* Menu content stays beneath the brand */
        .layout-menu .menu-inner {
            position: relative;
            z-index: 1;
        }
            .sptjm-dashboard-title {
                font-size: clamp(0.95rem, 2.2vw, 1.25rem);
                line-height: 1.2;
            }
    </style>


    <style>
        /* ─── SIDEBAR BACKGROUND ─────────────────────── */
        .bg-menu-theme {
            background-color: #ffffff !important;
            border-right: 1px solid #e2e8f0 !important;
        }

        /* ─── Sidebar Toggle Button (Chevron Left) ─── */
        .layout-menu .app-brand .layout-menu-toggle {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 28px !important;
            height: 28px !important;
            border-radius: 50% !important;
            background: #eef2ff !important;
            border: 1px solid #e2e8f0 !important;
            transition: all 0.2s ease !important;
            cursor: pointer !important;
            text-decoration: none !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05) !important;
        }
        .layout-menu .app-brand .layout-menu-toggle:hover {
            background: #1a56db !important;
            border-color: #1a56db !important;
        }
        .layout-menu .app-brand .layout-menu-toggle:hover i {
            color: #ffffff !important;
        }
        
        /* Hide logo subtitle when sidebar collapsed */
        html.layout-menu-collapsed .sptjm-logo-subtitle {
            opacity: 0 !important;
            pointer-events: none !important;
        }

        /* ─── FORCE FULL-HIDE SIDEBAR for layout-menu-fixed + collapsed ─── */
        /* Sneat excludes layout-menu-fixed from mini-collapse — we override
           to fully hide the sidebar and expand content to full width. */
        @media (min-width: 1200px) {
            html.layout-menu-collapsed.layout-menu-fixed .layout-menu {
                width: 0 !important;
                min-width: 0 !important;
                overflow: hidden !important;
                transition: width 0.3s ease !important;
            }
            html.layout-menu-collapsed.layout-menu-fixed .layout-page {
                padding-left: 0 !important;
                transition: padding-left 0.3s ease !important;
            }
            html.layout-menu-collapsed.layout-menu-fixed .layout-navbar {
                left: 0 !important;
                transition: left 0.3s ease !important;
            }
        }

        /* ─── TOP-LEVEL: Parent aktif atau terbuka (open) ─── */
        .menu-vertical .menu-inner > .menu-item.active > .menu-link,
        .menu-vertical .menu-inner > .menu-item.open > .menu-link {
            background-color: #eef2ff !important;
            color: #1a56db !important;
            font-weight: 600 !important;
            border-radius: 8px !important;
        }
        .menu-vertical .menu-inner > .menu-item.active > .menu-link i,
        .menu-vertical .menu-inner > .menu-item.open > .menu-link i {
            color: #1a56db !important;
        }

        /* Hilangkan strip/bar default bawaan template di parent */
        .bg-menu-theme .menu-inner > .menu-item.active::before {
            display: none !important;
        }

        /* ─── TOP-LEVEL: Parent biasa (non-aktif, tidak terbuka) ─── */
        .menu-vertical .menu-inner > .menu-item:not(.active):not(.open) > .menu-link {
            color: #4a5568 !important;
        }
        .menu-vertical .menu-inner > .menu-item:not(.active):not(.open) > .menu-link i {
            color: #6b7a8d !important;
        }

        /* ─── TOP-LEVEL: Hover ─── */
        .menu-vertical .menu-inner > .menu-item > .menu-link:hover {
            background-color: #f1f5f9 !important;
            border-radius: 8px !important;
        }

        /* ─── APP BRAND AREA (Logo Horizontal) ─── */
        .layout-menu .app-brand.demo {
            height: auto !important;
            min-height: 72px !important;
            padding: 12px 16px !important;
            overflow: hidden !important;
            border-bottom: 1px solid #eef0f4 !important;
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            justify-content: space-between !important;
            width: 100% !important;
            box-sizing: border-box !important;
            position: relative !important;
        }
        .layout-menu .app-brand.demo .app-brand-link {
            display: flex !important;
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 2px !important;
            overflow: hidden !important;
            flex: 1 !important;
        }

        /* ─── MENU INNER: Kurangi padding atas/bawah ─── */
        .menu-vertical .menu-inner {
            padding-top: 0.5rem !important;
            padding-bottom: 0.5rem !important;
        }
        /* Kurangi tinggi setiap menu link parent ─── */
        .menu-vertical .menu-inner > .menu-item > .menu-link {
            padding-top: 0.5rem !important;
            padding-bottom: 0.5rem !important;
        }

        /* ─── MENU ITEM ICONS (Parent Level) ─── */
        .menu-vertical .menu-inner > .menu-item > .menu-link .menu-icon {
            font-size: 1.25rem !important;
            color: #6b7a8d !important;
            margin-right: 0.6rem !important;
        }
        .menu-vertical .menu-inner > .menu-item.active > .menu-link .menu-icon,
        .menu-vertical .menu-inner > .menu-item.open > .menu-link .menu-icon {
            color: #1a56db !important;
        }

        /* ─── SUBMENU WRAPPER (Garis Vertikal Kiri) ─── */
        .menu-vertical .menu-sub {
            border-left: 1.5px solid #dbeafe !important; /* garis vertikal tipis biru/abu */
            margin-left: 2.15rem !important; /* diposisikan tepat di bawah ikon induk */
            padding-left: 0 !important;
            position: relative !important;
        }

        /* Hapus total bullet dot default */
        .menu-vertical .menu-sub > .menu-item > .menu-link::before {
            display: none !important;
            content: none !important;
        }

        /* ─── SUBMENU: Item biasa ─── */
        .menu-vertical .menu-sub > .menu-item > .menu-link {
            color: #4a5568 !important;
            font-size: 0.84rem !important;
            position: relative !important;
            padding-left: 0.9rem !important; /* jarak teks ke garis vertikal kiri */
            margin-left: 0 !important;
        }

        /* ─── SUBMENU: Item AKTIF (sesuai screenshot 2) ─── */
        .menu-vertical .menu-sub > .menu-item.active > .menu-link {
            background-color: #e8f0fe !important;
            color: #1a56db !important;
            font-weight: 700 !important;
            /* Bar vertikal biru di kanan — sesuai screenshot */
            border-right: 4px solid #1a56db !important;
            border-radius: 4px 0 0 4px !important;
            padding-left: 0.9rem !important;
        }

        /* ─── SUBMENU: Hover ─── */
        .menu-vertical .menu-sub > .menu-item > .menu-link:hover {
            background-color: #f8fafc !important;
            border-radius: 4px !important;
        }

        /* ─── SECTION HEADER LABEL (misal "MASTER DATA") ─── */
        .menu-inner > .menu-header > span {
            color: #9ca3af !important;
            font-size: 0.68rem !important;
            font-weight: 700 !important;
            letter-spacing: 0.08em !important;
            text-transform: uppercase !important;
        }

        /* ─── STATUS BADGE: AKTIF (Blue label) ─── */
        .badge.bg-label-primary {
            background-color: rgba(26, 86, 219, 0.1) !important;
            color: #1a56db !important;
            font-weight: 600 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.03em !important;
        }
    </style>

    <!-- Include Scripts for customizer, helper, analytics, config -->
    @include('layouts/sections/scriptsIncludes')
</head>

<body>


    <!-- Layout Content -->
    @yield('layoutContent')
    <!--/ Layout Content -->


    <!-- Include Scripts -->
    @include('layouts/sections/scripts')

    {{-- Page-level scripts (from @push('scripts')) --}}
    @stack('scripts')

    <!-- ─── GLOBAL CARD ENHANCEMENT ─── -->
    <style>
        /* ─── GLOBAL MASTER DATA TOOLBAR BUTTONS ─── */
        /* Override all page-level .btn-md2-tambah to pill shape matching data-dosen */
        .btn-md2-tambah {
            background-color: #0b3d91 !important;
            border: none !important;
            color: #fff !important;
            font-weight: 600 !important;
            font-size: 0.82rem !important;
            padding: 8px 20px !important;
            border-radius: 20px !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
            transition: all 0.2s !important;
            white-space: nowrap !important;
            cursor: pointer !important;
        }
        .btn-md2-tambah:hover {
            background-color: #082f73 !important;
            color: #fff !important;
            box-shadow: 0 4px 14px rgba(11, 61, 145, 0.35) !important;
        }

        /* ─── GLOBAL SPTJM ICON BUTTONS (Rounded Circle Style) ─── */
        .sptjm-icon-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            transition: box-shadow 0.15s ease, opacity 0.15s ease;
            flex-shrink: 0;
            will-change: auto;
        }
        .sptjm-icon-btn:hover {
            box-shadow: 0 3px 10px rgba(0,0,0,0.18);
            opacity: 0.88;
        }
        .sptjm-icon-btn:active { opacity: 0.7; }

        /* Edit – soft blue */
        .sptjm-btn-edit {
            background-color: #e8f0fe;
            color: #1a56db;
        }
        .sptjm-btn-edit:hover { background-color: #d0e1fd; color: #1a56db; }

        /* Active – blue */
        .sptjm-btn-active {
            background-color: #e8f0fe;
            color: #1a56db;
        }
        .sptjm-btn-active:hover { background-color: #d0e1fd; color: #1a56db; }

        /* Reset/Upload – soft green */
        .sptjm-btn-reset {
            background-color: #e8faf0;
            color: #28a745;
        }
        .sptjm-btn-reset:hover { background-color: #c3f0d8; color: #1e7e34; }

        /* Delete – soft red */
        .sptjm-btn-delete {
            background-color: #fde8e8;
            color: #dc3545;
        }
        .sptjm-btn-delete:hover { background-color: #fcc8c8; color: #b02a37; }

        /* Print – soft teal */
        .sptjm-btn-print {
            background-color: #e8f7fd;
            color: #0d9488;
        }
        .sptjm-btn-print:hover { background-color: #c4eefa; color: #0a7a6e; }

        /* View – soft purple */
        .sptjm-btn-view {
            background-color: #f0e8fe;
            color: #7c3aed;
        }
        .sptjm-btn-view:hover { background-color: #e0cefc; color: #5b21b6; }

        /* Info – soft yellow */
        .sptjm-btn-info {
            background-color: #fef9e7;
            color: #d97706;
        }
        .sptjm-btn-info:hover { background-color: #fef3c7; color: #b45309; }

        /* Wrap for spacing */
        td .sptjm-icon-btn + .sptjm-icon-btn,
        td .sptjm-icon-btn + form,
        td form + .sptjm-icon-btn,
        td form + form {
            margin-left: 4px;
        }

        /* Disable all transitions during page navigation to prevent icon ghost */
        .page-transitioning *,
        .page-transitioning *::before,
        .page-transitioning *::after {
            transition: none !important;
            animation: none !important;
        }
    </style>

    <style>
        /* ─── GLOBAL CARD ENHANCEMENT ─── */
        .card, 
        .md-card,
        .md2-card, 
        .sptjm-table-card, 
        .sptjm-stat-card {
            border: 1.5px solid #dbeafe !important;
            box-shadow: 0 10px 30px rgba(26, 86, 219, 0.15) !important;
            border-radius: 12px !important;
            background: #ffffff !important;
            margin-bottom: 24px !important;
            overflow: hidden !important;
        }
        .sptjm-stat-card {
            margin-bottom: 0px !important;
        }
        .card-header, .md2-card-inner h5, .md-card-inner h5 {
            font-weight: 700 !important;
            color: #1e293b !important;
            border-bottom: 1px solid #e2e8f0 !important;
            padding-bottom: 12px !important;
            margin-bottom: 16px !important;
        }
        .md-card-inner, .md2-card-inner {
            padding: 24px !important;
        }

        /* ─── GLOBAL TABLE GRIDLINES & BORDERS ─── */
        table.dataTable,
        .table,
        .md-table-wrap table,
        .md2-table {
            border: 1.5px solid #cbd5e1 !important;
            border-collapse: collapse !important;
            width: 100% !important;
        }
        table.dataTable thead th,
        .table thead th,
        .md-table-wrap table thead th,
        .md2-table thead th {
            border: 1.5px solid #cbd5e1 !important;
            background-color: #f1f5f9 !important;
            color: #1e293b !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            font-size: 0.76rem !important;
            letter-spacing: 0.05em !important;
            padding: 5px 10px !important;
            vertical-align: middle !important;
            line-height: 1.2 !important;
        }
        table.dataTable tbody td,
        .table tbody td,
        .md-table-wrap table tbody td,
        .md2-table tbody td {
            border: 1.5px solid #cbd5e1 !important;
            padding: 6px 12px !important;
            vertical-align: middle !important;
            color: #334155 !important;
            line-height: 1.2 !important;
        }
        
        /* ─── DATATABLES PAGINATION FOOTER WRAPPER ─── */
        .dataTables_wrapper > .row:last-child {
            background-color: #edf2f9 !important;
            padding: 12px 24px !important;
            border-top: 1.5px solid #dbeafe !important;
            margin-left: -24px !important;
            margin-right: -24px !important;
            margin-bottom: -24px !important;
            margin-top: 16px !important;
            border-bottom-left-radius: 12px !important;
            border-bottom-right-radius: 12px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            background: transparent !important;
            border: none !important;
            padding: 0 !important;
            margin: 0 !important;
            box-shadow: none !important;
            cursor: pointer !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: transparent !important;
            border: none !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
        }
    </style>

</body>

</html>
