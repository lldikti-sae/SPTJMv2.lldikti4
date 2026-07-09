@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online')

@section('content')
<<<<<<< HEAD
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
=======
<style>
    .card-non-el {
        border: 1.5px solid #dbeafe !important;
        box-shadow: 0 10px 30px rgba(26, 86, 219, 0.15) !important;
        border-radius: 12px !important;
        background: #ffffff !important;
    }
</style>
>>>>>>> feature/ui-admin-SPTJM

<div class="content-wrapper">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb" style="font-size: 0.85rem; padding: 0; background: transparent;">
            <li class="breadcrumb-item"><a href="#" style="color: #64748b;">Proses Pembayaran</a></li>
            <li class="breadcrumb-item"><a href="#" style="color: #64748b;">Rekapitulasi Usulan</a></li>
            <li class="breadcrumb-item active fw-bold" aria-current="page" style="color: #1a56db;">Non Eligible</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1" style="color: #0f2b5c !important; font-size: 1.5rem;">Rekapitulasi Berjalan Non Eligible</h4>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="card card-non-el mb-4">
        <div class="card-body p-4">
            <h6 class="fw-bold text-dark mb-3" style="color: #0f2b5c !important; display: inline-flex; align-items: center; gap: 8px;">
                <i class="bx bx-filter-alt" style="color: #d97706;"></i> Parameter Filter Data
            </h6>
            <form action="{{ route('admin.rekap-usulan-non-el') }}" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label for="pencairan_ke" class="form-label fw-bold text-dark text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.05em;">Pencairan ke-</label>
                        <select class="form-select" id="pencairan_ke" name="pencairan_ke" style="border-color: #cbd5e1;">
                            <option value="Semua" {{ request('pencairan_ke') == 'Semua' ? 'selected' : '' }}>Semua</option>
                            @for ($i = 1; $i <= 20; $i++)
                                <option value="{{ $i }}" {{ request('pencairan_ke') == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-md-5">
                        <label for="Eligible_span" class="form-label fw-bold text-dark text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.05em;">Eligible Span</label>
                        <select class="form-select" id="Eligible_span" name="Eligible_span" style="border-color: #cbd5e1;">
                            <option value="TIDAK" {{ request('Eligible_span') == 'TIDAK' ? 'selected' : '' }}>TIDAK</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100 fw-bold" style="background-color: #0f2b5c; border-color: #0f2b5c; border-radius: 6px; padding: 7px 0; font-size: 0.875rem;">Lihat</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if ($hasFilter)
        <div class="card card-non-el mb-4">
            <div class="card-header d-flex justify-content-between align-items-center p-4">
                <h6 class="mb-0 fw-bold text-dark" style="color: #0f2b5c !important;">Tabel Rekapitulasi</h6>
            </div>

            <div class="card-body px-4 pb-4 pt-0">
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover md2-table text-center" id="rekapTable" style="width:100%">
                        <thead>
                            <tr>
                                <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">NIDN</th>
                                <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">NUPTK</th>
                                <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">No Peserta</th>
                                <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">Nama</th>
                                <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">Jabatan</th>
                                <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">Golongan</th>
                                <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">Masa Kerja</th>
                                <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">Status Pegawai</th>
                                <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">Bank</th>
                                <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">Eligible</th>
                                @foreach ($namaBulan ?? ['Bulan'] as $bln)
                                    <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">{{ $bln }}</th>
                                @endforeach
                                <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">No Rekening</th>
                                <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">NPWP</th>
                                <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Data dimuat via DataTables AJAX --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
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
