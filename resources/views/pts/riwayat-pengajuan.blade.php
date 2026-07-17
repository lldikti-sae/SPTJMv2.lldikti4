@extends('layouts/contentNavbarLayoutPts')

@section('title', 'SPTJM Online')

@section('content')

<div class="md2-page-header">
    <div class="page-titles">
        <h3>Riwayat Pengajuan</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Usulan</a></li>
                <li class="breadcrumb-item active">Riwayat Pengajuan</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card md2-card">
    <div class="card-body px-4 pb-4 pt-4">
        <div class="table-responsive text-nowrap border rounded mb-4">
            <!-- Table Display -->
            <table class="table table-hover md2-table m-0" id="riwayatTable">
                <thead style="background-color: #dbdee0;">
                <tr>
                    <th>No</th>
                    <th>Tahun</th>
                    <th>ID Usulan</th>
                    <th>Tanggal Usulan</th>
                    <th>Bulan</th>
                    <th>Status</th>
                    <th>Alasan Penolakan</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <!-- Data akan dimuat melalui DataTables AJAX -->
            </tbody>
        </table>
        </div>
    </div>
</div>

@section('page-script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        $('#riwayatTable').DataTable({
            processing: true,
            serverSide: true,
            paging: true,
            lengthMenu: [
                [10, 25, 50, 100, 500],
                [10, 25, 50, 100, 500]
            ],
            ajax: '{{ route('pts.riwayat-pengajuan') }}',
            columns: [{
                    data: 'no',
                    name: 'no'
                },
                {
                    data: 'tahun',
                    name: 'tahun'
                },
                {
                    data: 'id_usulan',
                    name: 'id_usulan'
                },
                {
                    data: 'tanggal_usulan',
                    name: 'tanggal_usulan'
                },
                {
                    data: 'bulan',
                    name: 'bulan'
                },
                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'alasan_penolakan',
                    name: 'alasan_penolakan'
                },
                {
                    data: 'aksi',
                    name: 'aksi',
                    orderable: false,
                    searchable: false
                }
            ],
            pagingType: 'simple_numbers',
            language: {
                paginate: {
                    first: "Awal",
                    last: "Akhir",
                    next: "â†’",
                    previous: "â†",
                },
                zeroRecords: "Data tidak ditemukan",
                infoEmpty: "Tidak ada data tersedia",
                searchPlaceholder: "Cari data...",
                search: "Cari Data:"
            },
        });
    });
</script>
@endsection

@endsection
