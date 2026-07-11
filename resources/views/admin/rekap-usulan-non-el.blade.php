@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online')

@section('content')

<style>
    .card-non-el {
        border: 1.5px solid #fee2e2 !important;
        box-shadow: 0 10px 30px rgba(220, 38, 38, 0.1) !important;
        border-radius: 12px !important;
        background: #ffffff !important;
    }
    
    .md2-table th {
        border-bottom: 2px solid #e2e8f0 !important;
        padding: 12px 16px !important;
        text-transform: uppercase;
    }

    .md2-table td {
        padding: 12px 16px !important;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.85rem;
    }
</style>

<div class="content-wrapper">

    <!-- Filter Form -->
    <div class="card card-non-el mb-4">
        <div class="card-body p-4">
            <h6 class="fw-bold text-dark mb-3" style="color: #7f1d1d !important; display: inline-flex; align-items: center; gap: 8px;">
                <i class="bx bx-filter-alt" style="color: #dc2626;"></i> Parameter Filter Data
            </h6>
            <form action="{{ route('admin.rekap-usulan-non-el') }}" method="GET">
                <div class="row g-3 align-items-end">
                    
                    <div class="col-md-3">
                        <label for="tipe_sptjm" class="form-label fw-bold text-dark text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.05em;">Tipe SPTJM</label>
                        <select class="form-select" id="tipe_sptjm" name="tipe_sptjm" onchange="this.form.submit()" style="border-color: #cbd5e1;">
                            <option value="SPTJM" {{ request('tipe_sptjm', 'SPTJM') == 'SPTJM' ? 'selected' : '' }}>SPTJM</option>
                            <option value="TUKIN" {{ request('tipe_sptjm') == 'TUKIN' ? 'selected' : '' }}>TUKIN</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="pencairan_ke" class="form-label fw-bold text-dark text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.05em;">Pencairan ke-</label>
                        <select class="form-select" id="pencairan_ke" name="pencairan_ke" style="border-color: #cbd5e1;">
                            <option value="Semua" {{ request('pencairan_ke') == 'Semua' ? 'selected' : '' }}>Semua</option>
                            @for ($i = 1; $i <= 20; $i++)
                                <option value="{{ $i }}" {{ request('pencairan_ke') == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="Eligible_span" class="form-label fw-bold text-dark text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.05em;">Eligible Span</label>
                        <select class="form-select" id="Eligible_span" name="Eligible_span" style="border-color: #cbd5e1;">
                            <option value="TIDAK" {{ request('Eligible_span') == 'TIDAK' ? 'selected' : '' }}>TIDAK</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <button type="submit" class="btn btn-danger w-100 fw-bold" style="background-color: #dc2626; border-color: #dc2626; border-radius: 6px; padding: 7px 0; font-size: 0.875rem;">Lihat</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if ($hasFilter)
        <div class="card card-non-el mb-4">
            <div class="card-header d-flex justify-content-between align-items-center p-4">
                <h6 class="mb-0 fw-bold text-dark" style="color: #7f1d1d !important;">Tabel Rekapitulasi</h6>
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
