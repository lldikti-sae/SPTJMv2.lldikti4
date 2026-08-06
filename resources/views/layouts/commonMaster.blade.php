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
            /* FIX OVERLAP: overflow harus visible agar parent .menu-item bisa menghitung
               tingginya secara otomatis saat submenu nested dibuka. overflow:hidden
               yang lama memblokir reflow dan menyebabkan sibling tumpang-tindih. */
            overflow: visible !important;
        }

        /* ─── SAFETY NET: Pastikan .menu-item yang sudah terbuka selalu auto-height ─── */
        /* Ketika menu.js selesai animasi, ia menghapus height inline via clearItemStyle().
           Rule ini menjadi fallback agar tidak ada pixel-height yang tersisa
           pada elemen yang sudah open tapi TIDAK sedang dianimasikan. */
        .menu-vertical .menu-item.open:not(.menu-item-animating) {
            height: auto !important;
            overflow: visible !important;
        }

        /* FIX: Hentikan animasi transition pada menu-icon yang menyebabkan icon bocor ke submenu */
        .menu-vertical .menu-inner > .menu-item > .menu-link .menu-icon,
        .menu-vertical .menu-sub .menu-icon {
            transition: none !important;
            position: static !important;
        }

        /* FIX: Sembunyikan icon yang ada di dalam submenu (submenu tidak punya icon) */
        .menu-vertical .menu-sub > .menu-item > .menu-link .menu-icon {
            display: none !important;
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

        /* ─── STATUS BADGE GLOBAL STANDARDIZATION ─── */
        /* Aktif = Biru */
        .badge.bg-label-primary,
        .badge-jenis,
        .badge-pns,
        .badge-nonpns {
            background-color: rgba(26, 86, 219, 0.1) !important;
            color: #1a56db !important;
            font-weight: 600 !important;
            font-size: 0.73rem !important;
            padding: 3px 10px !important;
            border-radius: 20px !important;
            border: 1px solid rgba(26,86,219,0.18) !important;
            display: inline-block !important;
        }
        /* Tidak Aktif / Non / Merah */
        .badge.bg-label-danger,
        .badge-nonaktif,
        .badge-tidak-aktif,
        .badge-tidak,
        .vdd-badge-nonaktif,
        .vdd-badge-tidak {
            background-color: rgba(220, 38, 38, 0.1) !important;
            color: #dc2626 !important;
            font-weight: 600 !important;
            font-size: 0.73rem !important;
            padding: 3px 10px !important;
            border-radius: 20px !important;
            border: 1px solid rgba(220,38,38,0.18) !important;
            display: inline-block !important;
        }
        /* Sukses = Hijau */
        .badge.bg-label-success,
        .badge-aktif,
        .vdd-badge-aktif {
            background-color: rgba(5, 150, 105, 0.1) !important;
            color: #059669 !important;
            font-weight: 600 !important;
            font-size: 0.73rem !important;
            padding: 3px 10px !important;
            border-radius: 20px !important;
            border: 1px solid rgba(5,150,105,0.18) !important;
            display: inline-block !important;
        }
        /* Warning = Kuning */
        .badge.bg-label-warning {
            background-color: rgba(217, 119, 6, 0.1) !important;
            color: #d97706 !important;
            font-weight: 600 !important;
            font-size: 0.73rem !important;
            padding: 3px 10px !important;
            border-radius: 20px !important;
            border: 1px solid rgba(217,119,6,0.18) !important;
            display: inline-block !important;
        }
        /* Info = Abu-abu */
        .badge.bg-label-secondary,
        .badge.bg-label-info {
            background-color: rgba(100, 116, 139, 0.1) !important;
            color: #64748b !important;
            font-weight: 600 !important;
            font-size: 0.73rem !important;
            padding: 3px 10px !important;
            border-radius: 20px !important;
            border: 1px solid rgba(100,116,139,0.18) !important;
            display: inline-block !important;
        }

        /* ─── LINK LIHAT DOKUMEN ─── */
        a:has(> .bx-file),
        a.link-lihat-dokumen {
            color: #1a56db !important;
            font-weight: 500 !important;
            text-decoration: none !important;
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
        }
        a:has(> .bx-file):hover,
        a.link-lihat-dokumen:hover {
            text-decoration: underline !important;
        }

        /* ─── TOMBOL SINKRONISASI (ORANGE) ─── */
        .btn-sinkron-md,
        button[class*="sinkron"],
        .btn-sptjm-sinkron {
            background-color: #d97706 !important;
            color: #fff !important;
            border: none !important;
        }

        /* ─── HIDE CKEDITOR SECURITY NOTIFICATION ─── */
        /* CKEditor 4.22.1 shows a red security warning bar that bleeds into view */
        .cke_notification_warning {
            display: none !important;
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

    <!-- Stylesheets consolidated into sptjm-custom.css -->

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.jQuery) {
            window.jQuery(document).on('draw.dt', function (e, settings) {
                var api = new window.jQuery.fn.dataTable.Api(settings);
                api.cells().every(function () {
                    var cell = this.node();
                    if (cell) {
                        var $cell = window.jQuery(cell);
                        // If cell has no child elements, it's just raw text
                        if ($cell.children().length === 0) {
                            var text = $cell.text().trim();
                            var upper = text.toUpperCase();
                            if (upper === 'PNS' || upper === 'NON PNS' || upper === 'NON-PNS') {
                                $cell.html('<span class="badge bg-label-primary">' + text + '</span>');
                                $cell.attr('style', function(i, s) { return (s || '') + ' text-align: center !important;'; });
                            } else if (upper === 'AKTIF') {
                                $cell.html('<span class="badge bg-label-success border border-success">Aktif</span>');
                            } else if (upper === 'TIDAK AKTIF') {
                                $cell.html('<span class="badge bg-label-danger">Tidak Aktif</span>');
                            }
                        } else if ($cell.children('.badge').length > 0) {
                            // If it already has a badge, ensure it has the correct colors based on text
                            var badge = $cell.find('.badge');
                            var text = badge.text().trim();
                            var upper = text.toUpperCase();
                            
                            if (upper === 'PNS' || upper === 'NON PNS' || upper === 'NON-PNS') {
                                badge.removeClass('bg-label-danger bg-label-success bg-label-warning bg-label-info border border-success badge-aktif badge-nonaktif');
                                badge.addClass('bg-label-primary');
                                $cell.attr('style', function(i, s) { return (s || '') + ' text-align: center !important;'; });
                            } else if (upper === 'AKTIF') {
                                badge.removeClass('bg-label-primary bg-label-danger bg-label-warning bg-label-info badge-nonaktif');
                                badge.addClass('bg-label-success border border-success');
                            } else if (upper === 'TIDAK AKTIF') {
                                badge.removeClass('bg-label-primary bg-label-success bg-label-warning bg-label-info border border-success badge-aktif');
                                badge.addClass('bg-label-danger');
                            }
                        }
                    }
                });
            });
        }
    });
    </script>
</body>

</html>
