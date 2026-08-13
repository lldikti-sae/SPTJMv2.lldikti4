@extends('layouts/contentNavbarLayoutPts')

@section('title', 'SPTJM Online')

@section('content')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="md2-page-header">
    <div class="page-titles">
        <h3>Cek Data Dosen</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Data Dosen</a></li>
                <li class="breadcrumb-item active">Cek Data Dosen</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card md2-card">
    <div class="card-body px-4 pb-4 pt-4">
        <div class="table-responsive text-nowrap border rounded mb-4">
            <table class="table table-hover md2-table m-0" id="dosenTable">
                <thead style="text-align: center; background-color: #dbdee0;">
                <tr>
                    <th>No</th>
                    <th>NIDN</th>
                    <th>NUPTK</th>
                    <th>Nama Dosen</th>
                    <th>Golongan</th>
                    <th>Masa Kerja</th>
                    <th>Jabatan</th>
                    <th>BKD Genap TL
                        <br>(P = Jan - Feb)
                    </th>
                    <th>BKD Ganjil
                        <br>(P = Mar - Agu)
                    </th>
                    <th>BKD Genap BJ
                        <br>(P = Sep - Des)
                    </th>
                    <th>Status</th>
                    <th>Keterangan</th>
                    <th>Aksi</th>
                </tr>
            </thead>

        </table>
        </div>
    </div>
</div>

<script>
$(document).ready(() => {
    $('#dosenTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("pts.cek-data-dosen") }}'
        },
        columns: [{
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
            }, {
                data: 'NIDN',
                name: 'nidn',
                searchable: true
            },
            {
                data: 'NUPTK',
                name: 'nuptk',
                searchable: true
            },
            {
                data: 'Nama',
                name: 'nama'
            },
            {
                data: 'gol',
                name: 'gol'
            },
            {
                data: 'masa_kerja',
                name: 'masa_kerja'
            },
            {
                data: 'jabatan',
                name: 'jabatan'
            },
            {
                data: 'bkd_genap_tl',
                name: 'bkd_genap_tl',
                searchable: false
            },
            {
                data: 'bkd_ganjil',
                name: 'bkd_ganjil',
                searchable: false
            },
            {
                data: 'bkd_genap_bj',
                name: 'bkd_genap_bj',
                searchable: false
            },
            {
                data: 'aktif',
                name: 'aktif',
                    orderable: false,
                    searchable: false,
            },
            {
                data: 'Keterangan',
                name: 'keterangan'
            },
            {
                data: 'lihat',
                name: 'aksi',
                    orderable: false,
                    searchable: false,
            }
        ],
        order: [
            [1, 'asc']
        ],
        responsive: true,
        language: {
            paginate: {
                first: "Awal",
                last: "Akhir",
                next: "→",
                previous: "←",
            },
            zeroRecords: "Data tidak ditemukan",
            infoEmpty: "Tidak ada data tersedia",
        },
    })
})
</script>

@endsection
