<!-- BEGIN: Theme CSS-->
<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link
    href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
    rel="stylesheet">

<link rel="stylesheet" href="{{ asset(mix('assets/vendor/fonts/boxicons.css')) }}" />

<!-- Core CSS -->
<link rel="stylesheet" href="{{ asset(mix('assets/vendor/css/core.css')) }}" />
<link rel="stylesheet" href="{{ asset(mix('assets/vendor/css/theme-default.css')) }}" />
<link rel="stylesheet" href="{{ asset(mix('assets/css/demo.css')) }}" />
<!-- Vendors CSS -->
<link rel="stylesheet" href="{{ asset(mix('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css')) }}" />
<!-- DataTables CSS with Bootstrap 5 -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.4.0/css/fixedHeader.bootstrap5.min.css">
<!-- Vendor Styles -->
@yield('vendor-style')


<!-- Page Styles -->
@yield('page-style')

<!-- =====================================================
     GLOBAL: DataTables Premium Pagination Styling
     Berlaku di semua halaman admin
     ===================================================== -->
<style>
/* =====================================================
   GLOBAL FONT: Paksa semua tabel menggunakan Public Sans
   (sama dengan font dashboard)
   ===================================================== */
table,
table th,
table td,
table thead,
table tbody,
table tfoot,
.dataTables_wrapper,
.dataTables_wrapper table,
.dataTables_wrapper th,
.dataTables_wrapper td,
.dataTables_wrapper .dataTables_info,
.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter,
.dataTables_wrapper .dataTables_paginate {
    font-family: 'Public Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif !important;
}

/* Font size konsisten di body tabel */
table tbody td {
    font-size: 0.84rem !important;
    color: #374151 !important;
}

/* Font header tabel */
table thead th {
    font-size: 0.78rem !important;
    font-weight: 700 !important;
    letter-spacing: 0.03em !important;
}

/* Pagination wrapper */
.dataTables_wrapper .dataTables_paginate {
    display: flex !important;
    align-items: center !important;
    gap: 4px !important;
}

/* Semua tombol paginate */
.dataTables_wrapper .dataTables_paginate .paginate_button {
    border: 1.5px solid #e2e8f0 !important;
    border-radius: 8px !important;
    background: #ffffff !important;
    color: #374151 !important;
    font-size: 0.82rem !important;
    font-weight: 600 !important;
    padding: 5px 12px !important;
    min-width: 36px !important;
    text-align: center !important;
    transition: all 0.2s ease !important;
    cursor: pointer !important;
    line-height: 1.5 !important;
    margin: 0 1px !important;
}

/* Previous & Next */
.dataTables_wrapper .dataTables_paginate .paginate_button.previous,
.dataTables_wrapper .dataTables_paginate .paginate_button.next {
    color: #374151 !important;
    font-weight: 600 !important;
    background: #f8fafc !important;
    padding: 5px 16px !important;
    border-color: #e2e8f0 !important;
}

/* Hover */
.dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.disabled):not(.current) {
    background: #f1f5f9 !important;
    border-color: #cbd5e1 !important;
    color: #0f2b5c !important;
    box-shadow: none !important;
}

/* Active / current */
.dataTables_wrapper .dataTables_paginate .paginate_button.current,
.dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
    background: #0f2b5c !important;
    border-color: #0f2b5c !important;
    color: #ffffff !important;
    box-shadow: 0 2px 8px rgba(15, 43, 92, 0.25) !important;
}

/* Disabled */
.dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
.dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
    background: #f8fafc !important;
    border-color: #e2e8f0 !important;
    color: #94a3b8 !important;
    cursor: not-allowed !important;
}

/* Ellipsis */
.dataTables_wrapper .dataTables_paginate span.ellipsis {
    color: #94a3b8 !important;
    padding: 5px 6px !important;
    font-size: 0.82rem !important;
}

/* dataTables_info */
.dataTables_wrapper .dataTables_info {
    color: #64748b !important;
    font-size: 0.82rem !important;
    padding-top: 8px !important;
}

/* Search input */
.dataTables_wrapper .dataTables_filter input {
    border: 1.5px solid #e2e8f0 !important;
    border-radius: 8px !important;
    padding: 6px 12px !important;
    font-size: 0.84rem !important;
    outline: none !important;
}
.dataTables_wrapper .dataTables_filter input:focus {
    border-color: #1a56db !important;
    box-shadow: 0 0 0 3px rgba(26, 86, 219, 0.1) !important;
}

/* Length select */
.dataTables_wrapper .dataTables_length select {
    border: 1.5px solid #e2e8f0 !important;
    border-radius: 8px !important;
    padding: 4px 8px !important;
    font-size: 0.84rem !important;
}
</style>

