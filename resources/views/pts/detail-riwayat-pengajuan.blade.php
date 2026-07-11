@extends('layouts/contentNavbarLayoutPts')

@section('title', 'SPTJM Online')

@section('content')
<div class="card w-100 p-3">
    <h5 class="card-header text-start p-2">Tabel Riwayat Pengajuan</h5>
    <hr>

    <div class="card-body">
        {{-- Informasi Usulan --}}
        <div class="mb-4">
            <div class="row mb-2">
                <div class="col-md-2 fw-bold">Nomor Usulan</div>
                <div class="col-md-10">: {{ $pengajuan->id_usulan ?? '-' }}</div>

            </div>
            <div class="row mb-3">
                <div class="col-md-2 fw-bold">Tanggal Usulan</div>
                <div class="col-md-10">: {{ $pengajuan->tanggal_usulan ?? '-' }}</div>
            </div>
        </div>

        {{-- Tabel Dosen (PNS) --}}
        <h6 class="mb-2">Daftar Nama Dosen (PNS) :</h6>
        <div class="table-responsive mb-4">
            <table class="table table-sm table-hover table-bordered">
                <thead style="background-color: #dbdee0;">
                    <tr>
                        <th>No</th>
                        <th>NIDN/NUPTK</th>
                        <th>Nama Dosen</th>
                        <th>SP2D</th>
                        <th>Tanggal Usulan</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($usulanPns ?? collect()) as $index => $dosen)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $dosen->identifier ?? ($dosen->nidn ?? $dosen->nuptk ?? '-') }}</td>
                        <td>{{ $dosen->nama }}</td>
                        <td>{{ $dosen->no_sp2d_1 ?? "-" }}</td>
                        <td>{{ $pengajuan->tanggal_usulan ?? '-' }}</td>
                        <td>{{ $pengajuan->status ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">Tidak ada dosen PNS yang diusulkan untuk bulan ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Tabel Dosen (NON PNS) --}}
        <h6 class="mb-2">Daftar Nama Dosen (NON PNS) :</h6>
        <div class="table-responsive mb-4">
            <table class="table table-sm table-hover table-bordered">
                <thead style="background-color: #dbdee0;">
                    <tr>
                        <th>No</th>
                        <th>NIDN/NUPTK</th>
                        <th>Nama Dosen</th>
                        <th>SP2D</th>
                        <th>Tanggal Usulan</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($usulanNonPns ?? collect()) as $index => $dosen)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $dosen->identifier ?? ($dosen->nidn ?? $dosen->nuptk ?? '-') }}</td>
                        <td>{{ $dosen->nama }}</td>
                        <td>{{ $dosen->no_sp2d_1 ?? "-" }}</td>
                        <td>{{ $pengajuan->tanggal_usulan ?? '-' }}</td>
                        <td>{{ $pengajuan->status ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">Tidak ada dosen NON PNS yang diusulkan untuk bulan ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>


    </div>
</div>
@endsection

@section('page-script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        $('#detailRiwayatTable').DataTable({
            processing: true,
            serverSide: true,
            paging: true,
            lengthMenu: [
                [25, 50, 100, 500],
                [25, 50, 100, 500]
            ],
            ajax: '{{ route('pts.detail-riwayat-pengajuan', ['no' => $pengajuan->no ?? request()->route("no")]) }}',
            columns: [
                {
                    data: null,
                    name: 'rownum',
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    },
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'nidn',
                    name: 'nidn'
                },
                {
                    data: 'nuptk',
                    name: 'nuptk'
                },
                {
                    data: 'nama',
                    name: 'nama'
                },
                {
                    data: 'no_sp2d_1',
                    name: 'no_sp2d_1',
                    render: function(data, type, row) {
                        return data ? data : '-';
                    }
                },
                {
                    data: 'tanggal_usulan',
                    name: 'tanggal_usulan',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'status',
                    name: 'status',
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
