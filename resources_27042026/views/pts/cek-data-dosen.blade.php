@extends('layouts/contentNavbarLayoutPts')

@section('title', 'SPTJM Online')

@section('content')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="card" style="width: 100%; padding: 10px;">
    <h5 class="card-header text-start p-2">Cek Data Dosen</h5>
    <hr>
    

    <div class="table-responsive text-nowrap">
        <table class="table table-sm table-bordered table-hover" id="dosenTable">
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
        scrollX: true,
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
