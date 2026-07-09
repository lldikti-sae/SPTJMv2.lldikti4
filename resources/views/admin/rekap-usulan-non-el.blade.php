@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online')

@section('page-style')
<!-- Select2 CSS CDN -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .card-non-el {
        border: 1.5px solid #dbeafe !important;
        box-shadow: 0 10px 30px rgba(26, 86, 219, 0.15) !important;
        border-radius: 12px !important;
        background: #ffffff !important;
    }
    /* Modern Select2 Styling to match Sneat template */
    .select2-container--default .select2-selection--single {
        border: 1px solid #cbd5e1 !important;
        border-radius: 0.375rem !important;
        height: 38px !important;
        display: flex !important;
        align-items: center !important;
        background-color: #fff !important;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out !important;
        width: 100% !important;
    }
    /* Focus State */
    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #696cff !important;
        box-shadow: 0 0 0.25rem 0.05rem rgba(105, 108, 255, 0.25) !important;
        outline: 0 !important;
    }
    /* Text and Arrow spacing */
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #566a7f !important;
        font-size: 0.9375rem !important;
        padding-left: 12px !important;
        padding-right: 30px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
        right: 10px !important;
        width: 20px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow b {
        border-color: #8592a3 transparent transparent transparent !important;
        border-width: 5px 4px 0 4px !important;
    }
    .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
        border-color: transparent transparent #8592a3 transparent !important;
        border-width: 0 4px 5px 4px !important;
    }
    /* Dropdown container */
    .select2-dropdown {
        border: 1px solid #d9dee3 !important;
        border-radius: 0.375rem !important;
        box-shadow: 0 0.25rem 1rem rgba(161, 172, 184, 0.45) !important;
        z-index: 1060 !important;
    }
    /* Search input */
    .select2-container--default .select2-search--dropdown {
        padding: 8px 12px !important;
    }
    .select2-container--default .select2-search--dropdown .select2-search__field {
        border: 1px solid #d9dee3 !important;
        border-radius: 0.375rem !important;
        padding: 6px 10px !important;
        outline: none !important;
    }
    .select2-container--default .select2-search--dropdown .select2-search__field:focus {
        border-color: #696cff !important;
    }
    /* Options styling */
    .select2-container--default .select2-results__option {
        padding: 8px 12px !important;
        font-size: 0.9375rem !important;
        color: #566a7f !important;
        border-radius: 0.25rem !important;
        margin: 2px 4px !important;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #696cff !important;
        color: #fff !important;
    }
    .select2-container--default .select2-results__option[aria-selected=true] {
        background-color: #e7e7ff !important;
        color: #696cff !important;
        font-weight: 500 !important;
    }
    .select2-results__options {
        max-height: 400px !important;
    }
</style>
@endsection

@section('content')

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
                <div class="row g-3">
                    <!-- Tipe SPTJM -->
                    <div class="col-md-6 mb-2">
                        <label for="tipe_sptjm" class="form-label fw-bold text-dark text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.05em;">Pilih Tipe SPTJM</label>
                        <select class="select2 form-select" id="tipe_sptjm" name="tipe_sptjm" onchange="this.form.submit()">
                            <option value="SPTJM" {{ request('tipe_sptjm', 'SPTJM') == 'SPTJM' ? 'selected' : '' }}>SPTJM</option>
                            <option value="TUKIN" {{ request('tipe_sptjm') == 'TUKIN' ? 'selected' : '' }}>TUKIN</option>
                        </select>
                    </div>

                    <!-- Pencairan -->
                    <div class="col-md-6 mb-2">
                        <label for="pencairan_ke" class="form-label fw-bold text-dark text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.05em;">Pencairan ke-</label>
                        <select class="select2 form-select" id="pencairan_ke" name="pencairan_ke">
                            <option value="Semua" {{ request('pencairan_ke') == 'Semua' ? 'selected' : '' }}>Semua</option>
                            @for ($i = 1; $i <= 20; $i++)
                                <option value="{{ $i }}" {{ request('pencairan_ke') == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <!-- Hidden Eligible Span (TIDAK) -->
                    <input type="hidden" name="Eligible_span" value="TIDAK">
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary px-5 py-2 fw-bold" style="background-color: #0f2b5c; border-color: #0f2b5c; border-radius: 6px; font-size: 0.875rem;">Lihat</button>
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

@push('scripts')
<!-- Select2 JS CDN -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
  $(document).ready(function() {
    if (typeof $ !== 'undefined' && $.fn.select2) {
      $('.select2').select2({
        width: '100%'
      });
    }
  });
</script>
@endpush
