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

<!-- SPTJM Global DataTable Standardization CSS -->
<link rel="stylesheet" href="{{ asset('assets/css/sptjm-datatable.css') }}" />

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
    justify-content: flex-end !important;
}

/* Footer row: info on left, pagination on right */
.dataTables_wrapper .row:last-child {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
}
.dataTables_wrapper .row:last-child > div:first-child {
    order: 1;
}
.dataTables_wrapper .row:last-child > div:last-child {
    order: 2;
    margin-left: auto;
}

/* Reset DataTables wrapper paginate button classes to avoid nested double-borders */
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
.dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
.dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
    background: transparent !important;
    border: none !important;
}

/* Style the actual Bootstrap 5 page-link elements inside the pagination list */
.dataTables_wrapper .dataTables_paginate .pagination .page-item .page-link {
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    color: #1e3a8a !important;
    border-radius: 6px !important;
    padding: 6px 14px !important;
    margin: 0 2px !important;
    font-weight: 600;
    font-size: 0.875rem;
    min-width: 36px;
    height: 36px;
    display: inline-flex;
    justify-content: center;
    align-items: center;
    transition: all 0.2s ease;
    box-shadow: none !important;
}
.dataTables_wrapper .dataTables_paginate .pagination .page-item:not(.active):not(.disabled) .page-link:hover {
    background: #f8fafc !important;
    color: #0b3d91 !important;
    border-color: #cbd5e1 !important;
}
.dataTables_wrapper .dataTables_paginate .pagination .page-item.active .page-link {
    background: #1e3a8a !important; /* Solid dark blue */
    border: 1px solid #1e3a8a !important; /* No white spacing border or outline */
    color: #ffffff !important;
    box-shadow: none !important;
}
.dataTables_wrapper .dataTables_paginate .pagination .page-item.disabled .page-link {
    color: #94a3b8 !important;
    background: #f8fafc !important;
    border-color: #e2e8f0 !important;
    cursor: not-allowed !important;
}
/* Previous & Next specific styling */
.dataTables_wrapper .dataTables_paginate .pagination .page-item.previous .page-link,
.dataTables_wrapper .dataTables_paginate .pagination .page-item.next .page-link {
    padding: 6px 16px !important;
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
