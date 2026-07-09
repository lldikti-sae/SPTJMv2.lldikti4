@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online')

@section('content')
    <div class="row">
        <div class="col-12">
            <!-- Filter Form -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Rekapitulasi Berjalan Non Eligible</h5>
                    <hr>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.rekap-usulan-non-el') }}" method="GET">
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label for="tipe_sptjm" class="form-label fw-semibold">Pilih Tipe SPTJM</label>
                                <select class="form-select" id="tipe_sptjm" name="tipe_sptjm" onchange="this.form.submit()">
                                    <option value="SPTJM" {{ request('tipe_sptjm', 'SPTJM') == 'SPTJM' ? 'selected' : '' }}>SPTJM</option>
                                    <option value="TUKIN" {{ request('tipe_sptjm') == 'TUKIN' ? 'selected' : '' }}>TUKIN</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label for="pencairan_ke" class="form-label fw-semibold">Pencairan ke-</label>
                                <select class="form-select" id="pencairan_ke" name="pencairan_ke">
                                    <option value="Semua" {{ request('pencairan_ke') == 'Semua' ? 'selected' : '' }}>Semua
                                    </option>
                                    @for ($i = 1; $i <= 20; $i++)
                                        <option value="{{ $i }}" {{ request('pencairan_ke') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label for="Eligible_span" class="form-label fw-semibold">Eligible Span</label>
                                <select class="form-select" id="Eligible_span" name="Eligible_span">
                                    <option value="TIDAK" {{ request('Eligible_span') == 'TIDAK' ? 'selected' : '' }}>TIDAK
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">Lihat</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <hr>

            @if ($hasFilter)
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Tabel Rekapitulasi</h6>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive text-nowrap">
                            <table class="table table-sm table-bordered text-center table-hover" id="rekapTable">
                                <thead style="background-color: #dbdee0;">
                                    <tr>
                                        <th>NIDN</th>
                                        <th>NUPTK</th>
                                        <th>No Peserta</th>
                                        <th>Nama</th>
                                        <th>Jabatan</th>
                                        <th>Golongan</th>
                                        <th>Masa Kerja</th>
                                        <th>Status Pegawai</th>
                                        <th>Bank</th>
                                        <th>Eligible</th>
                                        @foreach ($namaBulan ?? ['Bulan'] as $bln)
                                            <th>{{ $bln }}</th>
                                        @endforeach
                                        <th>No Rekening</th>
                                        <th>NPWP</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Data dimuat via DataTables AJAX --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                {{-- <div class="alert alert-info mt-3">
                    Silakan pilih terlebih dahulu untuk menampilkan data rekapitulasi.
                </div> --}}
            @endif
        </div>
    </div>

    <script>
        $(document).ready(function() {
            const ajaxUrl = "{{ route('admin.rekap-usulan-non-el.data') }}";
            const tipeSptjm = "{{ request('tipe_sptjm', 'SPTJM') }}";
            const pencairanKe = "{{ request('pencairan_ke', 'Semua') }}";
            const eligibleSpan = "{{ request('Eligible_span', 'TIDAK') }}";

            $('#rekapTable').DataTable({
                processing: true,
                serverSide: true,
                paging: true,
                pageLength: 100,
                lengthMenu: [[50, 100, 200, 500], [50, 100, 200, 500]],
                scrollX: true,
                ajax: {
                    url: ajaxUrl,
                    data: {
                        tipe_sptjm: tipeSptjm,
                        pencairan_ke: pencairanKe,
                        Eligible_span: eligibleSpan,
                    }
                },
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
