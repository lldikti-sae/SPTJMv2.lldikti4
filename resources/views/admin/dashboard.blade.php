@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online - Dashboard')

@section('page-style')
<style>
    /* Specific styles for Dashboard page - smaller cards */
    .sptjm-stat-card {
        padding: 1rem 1.25rem !important;
    }
    .sptjm-stat-title {
        font-size: 0.75rem !important;
        margin-bottom: 0.15rem !important;
    }
    .sptjm-stat-value {
        font-size: 1.35rem !important;
    }
    .sptjm-stat-icon-wrapper {
        width: 42px !important;
        height: 42px !important;
    }
    .sptjm-stat-icon-wrapper i {
        font-size: 1.3rem !important;
    }
</style>
@endsection

@section('content')
@php
    $tahun = session('tahun') ?? date('Y');
    $aktifValues = ['1', 'YA', 'Ya', 'ya', 'Y'];
    
    $realTotalDosen = DB::table('s_transaksi_2')->where('tahun_versi', $tahun)->count();
    
    $realJumlahDosenPNSAktif = DB::table('s_transaksi_2')
        ->whereRaw('TRIM(UPPER(jenis)) = ?', ['PNS'])
        ->where('aktif', '1')
        ->where('tahun_versi', $tahun)
        ->count();
        
    $realJumlahDosenPNSNon = DB::table('s_transaksi_2')
        ->whereRaw('TRIM(UPPER(jenis)) = ?', ['PNS'])
        ->where(function($q) {
            $q->where('aktif', '!=', '1')->orWhereNull('aktif');
        })
        ->where('tahun_versi', $tahun)
        ->count();
        
    $realJumlahDosenNonPNSAktif = DB::table('s_transaksi_2')
        ->where(function($q) {
            $q->whereRaw('TRIM(UPPER(jenis)) != ?', ['PNS'])->orWhereNull('jenis');
        })
        ->where('aktif', '1')
        ->where('tahun_versi', $tahun)
        ->count();
        
    $realJumlahDosenNonPNSNon = DB::table('s_transaksi_2')
        ->where(function($q) {
            $q->whereRaw('TRIM(UPPER(jenis)) != ?', ['PNS'])->orWhereNull('jenis');
        })
        ->where(function($q) {
            $q->where('aktif', '!=', '1')->orWhereNull('aktif');
        })
        ->where('tahun_versi', $tahun)
        ->count();
        
    $realPtsCount = \App\Models\Pts::where('aktif', '1')->count();
@endphp

<div class="row g-2 mb-2">
    <!-- Jumlah Seluruh Dosen -->
    <div class="col-12 col-md-6 col-lg-4">
        <div class="sptjm-stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="sptjm-stat-title">Jumlah Seluruh Dosen</div>
                    <div class="sptjm-stat-value val-primary">{{ number_format($realTotalDosen, 0, ',', '.') }}</div>
                </div>
                <div class="sptjm-stat-icon-wrapper icon-bg-primary">
                    <i class="bx bx-group"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Dosen PNS Aktif -->
    <div class="col-12 col-md-6 col-lg-4">
        <div class="sptjm-stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="sptjm-stat-title">Dosen PNS Aktif</div>
                    <div class="sptjm-stat-value val-primary">{{ number_format($realJumlahDosenPNSAktif, 0, ',', '.') }}</div>
                </div>
                <div class="sptjm-stat-icon-wrapper icon-bg-primary">
                    <i class="bx bx-user-check"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Dosen PNS Tidak Aktif -->
    <div class="col-12 col-md-6 col-lg-4">
        <div class="sptjm-stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="sptjm-stat-title">Dosen PNS Tidak Aktif</div>
                    <div class="sptjm-stat-value val-danger">{{ number_format($realJumlahDosenPNSNon, 0, ',', '.') }}</div>
                </div>
                <div class="sptjm-stat-icon-wrapper icon-bg-danger">
                    <i class="bx bx-user-x"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Perguruan Tinggi Swasta -->
    <div class="col-12 col-md-6 col-lg-4">
        <div class="sptjm-stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="sptjm-stat-title">Perguruan Tinggi Swasta</div>
                    <div class="sptjm-stat-value val-warning">{{ number_format($realPtsCount, 0, ',', '.') }}</div>
                </div>
                <div class="sptjm-stat-icon-wrapper icon-bg-warning">
                    <i class="bx bxs-graduation"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Dosen Non-PNS Aktif -->
    <div class="col-12 col-md-6 col-lg-4">
        <div class="sptjm-stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="sptjm-stat-title">Dosen Non-PNS Aktif</div>
                    <div class="sptjm-stat-value val-primary">{{ number_format($realJumlahDosenNonPNSAktif, 0, ',', '.') }}</div>
                </div>
                <div class="sptjm-stat-icon-wrapper icon-bg-primary">
                    <i class="bx bx-user-check"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Dosen Non-PNS Tidak Aktif -->
    <div class="col-12 col-md-6 col-lg-4">
        <div class="sptjm-stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="sptjm-stat-title">Dosen Non-PNS Tidak Aktif</div>
                    <div class="sptjm-stat-value val-danger">{{ number_format($realJumlahDosenNonPNSNon, 0, ',', '.') }}</div>
                </div>
                <div class="sptjm-stat-icon-wrapper icon-bg-danger">
                    <i class="bx bx-user-x"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="sptjm-table-card">
    <div class="sptjm-table-header">
        <h5 class="sptjm-table-title">
            Data Dosen Pensiun Berjalan
        </h5>
        <div class="sptjm-table-actions">
            <!-- Table actions can go here (search handled by datatables usually, but we could add custom) -->
        </div>
    </div>

    <div class="md-table-wrap px-4 pb-4 pt-2">
        <table class="table table-hover md2-table" id="dosenPensiunTable" style="width: 100%;">
            <thead>
                <tr>
                    <th>NIDN</th>
                    <th>NUPTK</th>
                    <th>Nama Dosen</th>
                    <th>Nama PTS</th>
                    <th>TMT Pensiun</th>
                    <th>Usia</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            {{-- body akan diisi oleh DataTables via AJAX --}}
        </table>
    </div>
</div>

@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    $('#dosenPensiunTable').DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        ajax: {
            url: '{{ route('admin.dashboard.dosen-pensiun.data') }}'
        },
        columns: [
            { data: 'nidn', name: 'nidn', className: 'text-start' },
            { data: 'nuptk', name: 'nuptk', className: 'text-start' },
            { 
                data: 'nama', 
                name: 'nama',
                className: 'text-start'
            },
            { 
                data: 'pts', 
                name: 'pts',
                className: 'text-start'
            },
            { data: 'tmt_pensiun', name: 'tmt_pensiun', className: 'text-start' },
            { data: 'usia', name: 'usia', className: 'text-start' },
            { 
                data: 'status', 
                name: 'status', 
                orderable: false, 
                searchable: false,
                className: 'text-start',
                render: function(data, type, row) {
                    // Figma design uses active pill
                    // Strip HTML if present to prevent nesting
                    var text = (data || 'AKTIF').toString().replace(/<[^>]*>/g, '');
                    return '<span class="badge bg-label-success border border-success">' + text + '</span>';
                }
            },
            { 
                data: 'aksi', 
                name: 'aksi', 
                orderable: false, 
                searchable: false,
                className: 'text-start'
            }
        ],
        order: [[3, 'asc']],
        pagingType: 'simple_numbers',
        dom: "<'md-toolbar'<'entries-wrap'l><'search-wrap'f>><'table-responsive text-nowrap't><'row dt-bottom-row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        language: {
            paginate: {
                first: "Awal",
                last: "Akhir",
                next: "→",
                previous: "←",
            },
            zeroRecords: "Data tidak ditemukan",
            infoEmpty: "Tidak ada data tersedia",
            info: "Menampilkan _START_–_END_ dari _TOTAL_ entri",
            search: "Filter Data:",
            searchPlaceholder: "Cari data...",
            lengthMenu: "Show _MENU_ entries"
        }
    });
});
</script>
@endsection