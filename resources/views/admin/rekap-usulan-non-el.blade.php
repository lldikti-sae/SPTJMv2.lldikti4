@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online')

@section('content')

{{-- Page Header --}}
<div class="md2-page-header">
    <div class="page-titles">
        <h3>Rekapitulasi Usulan Non-Eligible</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Rekapitulasi</a></li>
                <li class="breadcrumb-item active">Usulan Non-Eligible</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card md2-card mb-4">
    <div class="card-body px-4 pb-4 pt-0">

        <!-- Filter Section -->
        <div class="pt-3 pb-3 mb-3 border-bottom">
            <form action="{{ route('admin.rekap-usulan-non-el') }}" method="GET">
                <div class="row g-3 align-items-end">
                    
                    <div class="col-md-3">
                        <label for="tipe_sptjm" class="form-label fw-bold text-dark text-uppercase mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em; color: #64748b;">Tipe SPTJM</label>
                        <select class="form-select" id="tipe_sptjm" name="tipe_sptjm" onchange="this.form.submit()" style="border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.88rem; color: #374151; height: 38px;">
                            <option value="SPTJM" {{ request('tipe_sptjm', 'SPTJM') == 'SPTJM' ? 'selected' : '' }}>SPTJM</option>
                            <option value="TUKIN" {{ request('tipe_sptjm') == 'TUKIN' ? 'selected' : '' }}>TUKIN</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="pencairan_ke" class="form-label fw-bold text-dark text-uppercase mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em; color: #64748b;">Pencairan ke-</label>
                        <select class="form-select" id="pencairan_ke" name="pencairan_ke" style="border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.88rem; color: #374151; height: 38px;">
                            <option value="Semua" {{ request('pencairan_ke') == 'Semua' ? 'selected' : '' }}>Semua</option>
                            @for ($i = 1; $i <= 20; $i++)
                                <option value="{{ $i }}" {{ request('pencairan_ke') == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="Eligible_span" class="form-label fw-bold text-dark text-uppercase mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em; color: #64748b;">Eligible Span</label>
                        <select class="form-select" id="Eligible_span" name="Eligible_span" style="border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.88rem; color: #374151; height: 38px;">
                            <option value="TIDAK" {{ request('Eligible_span') == 'TIDAK' ? 'selected' : '' }}>TIDAK</option>
                        </select>
                    </div>

                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary d-flex align-items-center gap-1" style="background-color: #0b3d91; border-color: #0b3d91; border-radius: 8px; font-weight: 600; font-size: 0.88rem; height: 38px; padding: 0 24px;">
                            <i class="bx bx-search-alt"></i> Lihat
                        </button>
                    </div>
                </div>
            </form>
        </div>

        @if ($hasFilter)
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0 fw-bold text-dark">Tabel Rekapitulasi</h6>
            </div>

            <div class="mb-4">
                <table class="table table-hover md2-table text-center" id="rekapTable" style="width:100%">
                    <thead>
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
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>><"table-responsive text-nowrap"t><"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
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
