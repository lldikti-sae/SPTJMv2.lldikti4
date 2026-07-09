@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online - Dashboard')

@section('page-style')
<style>
    /* Specific styles for Dashboard page that might not be in demo.css */
</style>
@endsection

@section('content')
<div class="row g-3 mb-3">
    <!-- Jumlah Seluruh Dosen -->
    <div class="col-12 col-md-6 col-lg-4">
        <div class="sptjm-stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="sptjm-stat-title">Jumlah Seluruh Dosen</div>
                    <div class="sptjm-stat-value val-primary">{{ number_format($totalDosen, 0, ',', '.') }}</div>
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
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="sptjm-stat-title">Dosen PNS Aktif</div>
                    <div class="sptjm-stat-value val-success">{{ number_format($jumlahDosenPNSAktif, 0, ',', '.') }}</div>
                </div>
                <div class="sptjm-stat-icon-wrapper icon-bg-success">
                    <i class="bx bx-user-check"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Dosen PNS Tidak Aktif -->
    <div class="col-12 col-md-6 col-lg-4">
        <div class="sptjm-stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="sptjm-stat-title">Dosen PNS Tidak Aktif</div>
                    <div class="sptjm-stat-value val-danger">{{ number_format($jumlahDosenPNSNon, 0, ',', '.') }}</div>
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
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="sptjm-stat-title">Perguruan Tinggi Swasta</div>
                    <div class="sptjm-stat-value val-warning">{{ number_format($ptsCount, 0, ',', '.') }}</div>
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
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="sptjm-stat-title">Dosen Non-PNS Aktif</div>
                    <div class="sptjm-stat-value val-success">{{ number_format($jumlahDosenNonPNSAktif, 0, ',', '.') }}</div>
                </div>
                <div class="sptjm-stat-icon-wrapper icon-bg-success">
                    <i class="bx bx-user-check"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Dosen Non-PNS Tidak Aktif -->
    <div class="col-12 col-md-6 col-lg-4">
        <div class="sptjm-stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="sptjm-stat-title">Dosen Non-PNS Tidak Aktif</div>
                    <div class="sptjm-stat-value val-danger">{{ number_format($jumlahDosenNonPNSNon, 0, ',', '.') }}</div>
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
            <i class="bx bx-history text-primary"></i>
            Data Dosen Pensiun Berjalan
        </h5>
        <div class="sptjm-table-actions">
            <!-- Table actions can go here (search handled by datatables usually, but we could add custom) -->
        </div>
    </div>

    <div class="table-responsive text-nowrap">
        <table class="table table-hover" id="dosenPensiunTable">
            <thead>
                <tr>
                    <th>Nidn</th>
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
        pageLength: 10,
        ajax: {
            url: '{{ route('admin.dashboard.dosen-pensiun.data') }}'
        },
        columns: [
            { 
                data: 'nidn', 
                name: 'nidn',
                render: function(data, type, row) {
                    return '<span class="fw-semibold text-primary">' + data + '</span>';
                }
            },
            { data: 'nuptk', name: 'nuptk' },
            { 
                data: 'nama', 
                name: 'nama',
                render: function(data, type, row) {
                    return '<span class="fw-bold text-dark">' + data + '</span>';
                }
            },
            { 
                data: 'pts', 
                name: 'pts',
                render: function(data, type, row) {
                    return '<span class="fw-semibold text-primary">' + data + '</span>';
                }
            },
            { data: 'tmt_pensiun', name: 'tmt_pensiun' },
            { data: 'usia', name: 'usia' },
            { 
                data: 'status', 
                name: 'status', 
                orderable: false, 
                searchable: false,
                render: function(data, type, row) {
                    // Figma design uses active pill
                    return '<span class="sptjm-badge-active">' + (data || 'AKTIF') + '</span>';
                }
            },
            { 
                data: 'aksi', 
                name: 'aksi', 
                orderable: false, 
                searchable: false
            }
        ],
        order: [[3, 'asc']],
        pagingType: 'simple_numbers'
    });
});
</script>
@endsection