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

    <!-- Prevent sidebar transition flicker between page navigations -->
    <script>
        (function () {
            try {
                document.documentElement.classList.add('sptjm-no-menu-transition');
            } catch (e) {
                // ignore
            }
        })();
    </script>
    <style>
        html.sptjm-no-menu-transition .layout-menu,
        html.sptjm-no-menu-transition .layout-menu * {
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
            min-height: 64px !important;
            padding: 10px 16px !important;
            overflow: hidden !important;
            border-bottom: 1px solid #eef0f4 !important;
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            justify-content: flex-start !important;
            width: 100% !important;
            box-sizing: border-box !important;
            position: relative !important;
        }
        .layout-menu .app-brand.demo .app-brand-link {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            gap: 10px !important;
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

        /* ─── STATUS BADGE: AKTIF (Green label) ─── */
        .badge.bg-label-primary {
            background-color: rgba(40, 199, 111, 0.12) !important;
            color: #28c76f !important;
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

    <!-- ─── GLOBAL TABLE COMPACT & BORDER OVERRIDE ─── -->
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
        .card-header, .md2-card-inner h5, .md-card-inner h5 {
            font-weight: 700 !important;
            color: #1e293b !important;
            border-bottom: 1px solid #e2e8f0 !important;
            padding-bottom: 12px !important;
            margin-bottom: 16px !important;
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
            padding: 10px 12px !important;
            vertical-align: middle !important;
        }
        table.dataTable tbody td,
        .table tbody td,
        .md-table-wrap table tbody td,
        .md2-table tbody td {
            border: 1.5px solid #cbd5e1 !important;
            padding: 8px 12px !important;
            vertical-align: middle !important;
            color: #334155 !important;
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
            border: 1px solid #cbd5e1 !important;
            background: #ffffff !important;
            color: #334155 !important;
            border-radius: 6px !important;
            padding: 6px 12px !important;
            margin: 0 2px !important;
            cursor: pointer !important;
            transition: all 0.2s ease !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #eef2ff !important;
            border-color: #1a56db !important;
            color: #1a56db !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            border-color: #0f2b5c !important;
            background: #0f2b5c !important;
            color: #ffffff !important;
        }
    </style>
</body>

</html>
