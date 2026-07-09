@extends('layouts/contentNavbarLayoutPts')

@section('title', 'SPTJM Online')

@section('content')

<div class="card" style="width: 100%; padding: 10px;">
    <h5 class="card-header text-start p-2">Tabel Riwayat Pengajuan</h5>
    <hr>
    <div class="table-responsive text-nowrap">
        <!-- Table Display -->
        <table class="table table-sm table-hover" id="riwayatTable">
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

@section('page-script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        $('#riwayatTable').DataTable({
            processing: true,
            serverSide: true,
            paging: true,
            pageLength: 10,
            lengthMenu: [
                [10, 25, 50, 100],
                [10, 25, 50, 100]
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
                    next: "→",
                    previous: "←",
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
