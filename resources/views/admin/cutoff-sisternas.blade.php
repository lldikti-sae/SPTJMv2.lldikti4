@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online')

@section('content')
@php
    $tahunSession = request('tahun') ?: (session('tahun') ?: date('Y'));
    $tahunLalu = $tahunSession - 1;
    $tahunDepan = $tahunSession + 1;
@endphp
<style>
    .card {
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05) !important;
        border-radius: 12px !important;
    }
    .card-cutoff {
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05) !important;
        border-radius: 12px !important;
        background: #ffffff !important;
    }
    /* Card Style 100% Matching Reference Image Mockup (Unified Single Card) */
    .btn-select-period {
        border-radius: 10px !important;
        background: #ffffff !important;
        transition: all 0.2s ease-in-out !important;
        cursor: pointer !important;
    }
    .btn-select-period.glowing-active-card {
        border: 2px solid #22c55e !important;
        background-color: #f0fdf4 !important;
        box-shadow: 0 0 16px rgba(34, 197, 94, 0.45), 0 0 32px rgba(34, 197, 94, 0.2) !important;
        animation: neonPulse 2s infinite alternate !important;
    }
    .btn-select-period:not(.glowing-active-card) {
        border: 2px solid #e2e8f0 !important;
        background-color: #ffffff !important;
        box-shadow: none !important;
    }
    @keyframes neonPulse {
        0% {
            box-shadow: 0 0 10px rgba(34, 197, 94, 0.35), 0 0 20px rgba(34, 197, 94, 0.15);
        }
        100% {
            box-shadow: 0 0 20px rgba(34, 197, 94, 0.7), 0 0 40px rgba(34, 197, 94, 0.35);
        }
    }
    .badge-aktif-pill {
        background-color: #86efac !important;
        color: #14532d !important;
        font-weight: 800 !important;
        font-size: 0.65rem !important;
        padding: 3px 8px !important;
        border-radius: 6px !important;
        letter-spacing: 0.5px !important;
    }
    .btn-stat-memenuhi {
        background: linear-gradient(to bottom, #72c624, #449c0d) !important;
        border: 1px solid #3b8c06 !important;
        color: #ffffff !important;
        border-radius: 6px !important;
        padding: 4px 8px !important;
        font-weight: 600 !important;
        transition: all 0.2s ease !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        width: 100% !important;
        white-space: nowrap !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08) !important;
    }
    .btn-stat-memenuhi:hover {
        background: linear-gradient(to bottom, #7ddc29, #4bab0c) !important;
        transform: translateY(-1px);
        box-shadow: 0 3px 6px rgba(0, 0, 0, 0.12) !important;
    }
    .btn-stat-tm {
        background: linear-gradient(to bottom, #f14624, #c92305) !important;
        border: 1px solid #b31c02 !important;
        color: #ffffff !important;
        border-radius: 6px !important;
        padding: 4px 8px !important;
        font-weight: 600 !important;
        transition: all 0.2s ease !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        width: 100% !important;
        white-space: nowrap !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08) !important;
    }
    .btn-stat-tm:hover {
        background: linear-gradient(to bottom, #f65839, #d92707) !important;
        transform: translateY(-1px);
        box-shadow: 0 3px 6px rgba(0, 0, 0, 0.12) !important;
    }
    .btn-stat-memenuhi .stat-number,
    .btn-stat-tm .stat-number {
        font-size: 0.8rem;
        font-weight: 800;
        letter-spacing: -0.2px;
    }
    .btn-stat-memenuhi .stat-label,
    .btn-stat-tm .stat-label {
        font-size: 0.72rem;
        font-weight: 600;
        opacity: 0.95;
        text-transform: none;
        letter-spacing: 0px;
    }
    .sptjm-btn-text-action {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        font-size: 0.82rem;
        font-weight: 700;
        border-radius: 6px;
        border: none;
        transition: all 0.2s ease-in-out;
        cursor: pointer;
    }
    .sptjm-btn-text-action:active {
        transform: scale(0.97);
    }
    .sptjm-btn-text-delete {
        background-color: #fde8e8 !important;
        color: #dc3545 !important;
    }
    .sptjm-btn-text-delete:hover {
        background-color: #fcc8c8 !important;
        color: #b02a37 !important;
    }
    .sptjm-btn-text-save {
        background-color: #e8faf0 !important;
        color: #28a745 !important;
    }
    .sptjm-btn-text-save:hover {
        background-color: #c3f0d8 !important;
        color: #1e7e34 !important;
    }
    .custom-toggle-track {
        display: inline-flex !important;
        align-items: center !important;
        background-color: #dcfce7 !important;
        border: 1.5px solid #bbf7d0 !important;
        border-radius: 30px !important;
        padding: 3px !important;
        width: fit-content !important;
    }
    .btn-toggle-pill {
        font-size: 0.78rem !important;
        font-weight: 600 !important;
        padding: 5px 14px !important;
        border-radius: 25px !important;
        cursor: pointer !important;
        transition: all 0.2s ease-in-out !important;
        border: 1px solid transparent !important;
        background: transparent !important;
        color: #166534 !important;
        outline: none !important;
        user-select: none !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 4px !important;
        text-decoration: none !important;
    }
    .btn-toggle-pill.active {
        background-color: #ffffff !important;
        border-color: #bbf7d0 !important;
        color: #15803d !important;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08) !important;
    }
    .btn-toggle-pill:not(.active):hover {
        color: #14532d !important;
        opacity: 0.85 !important;
    }
    .btn-toggle-pill:active {
        transform: scale(0.97) !important;
    }

    /* Distinct visible borders for the management upload table */
    .card-cutoff table.table {
        border-collapse: collapse !important;
        border: 1px solid #cbd5e1 !important;
    }
    .card-cutoff table.table th,
    .card-cutoff table.table td {
        border: 1px solid #cbd5e1 !important;
        padding: 5px 10px !important;
    }
    .card-cutoff table.table th {
        background-color: #f8fafc !important;
        border-bottom: 2px solid #cbd5e1 !important;
    }
</style>

<script>
function updateCutoffFileName(input, targetId) {
    var fileName = input.files[0] ? input.files[0].name : "Tidak ada file dipilih";
    document.getElementById(targetId).value = fileName;
}
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="md2-page-header mb-1">
    <div class="page-titles">
        <h3 class="mb-1">Manajemen Cut Off Data Sisternas</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="#">Data Sisternas</a></li>
                <li class="breadcrumb-item active">Cut Off</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    @if(auth()->user()->role !== 'pic')
    <div class="card mb-2.5 card-cutoff" style="border-radius: 12px; background: #ffffff; border: 1px solid #e2e8f0;">
        <div class="card-body pt-2.5 pb-2.5 px-4">
            <!-- Section 1: Form Management Upload File Cut Off (Compact Layout) -->
            <div class="mb-0">
                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.95rem;">
                        <i class="bx bx-upload text-primary me-1"></i> Form Management Upload File Cut Off
                    </h6>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover md2-table mb-0" style="margin-bottom: 0 !important;">
                        <thead>
                            <tr>
                                <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; font-weight: 700 !important; width: 30%;">Pelaporan</th>
                                <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; font-weight: 700 !important; width: 25%;">Untuk Pembayaran</th>
                                <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; font-weight: 700 !important; width: 33%;">Upload Lampiran</th>
                                <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; font-weight: 700 !important; width: 12%; text-align: center;">Aksi Manajemen</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            <!-- Row 1: Ganjil Tahun Lalu -->
                            <tr>
                                <form action="{{ route('admin.cutoff-sisternas.upload') }}" method="POST"
                                    enctype="multipart/form-data" class="uploadForm">
                                    @csrf
                                    <td>
                                        <input name="table" value="p_sister_ganjil_tl" type="hidden">
                                        <span class="fw-bold" style="color: #0f2b5c; font-size: 0.85rem;">Ganjil Tahun Lalu<br><span class="text-muted" style="font-size: 0.78rem;">[Sept - Des {{ $tahunLalu }} & Jan-Feb {{ $tahunSession }}]</span></span>
                                    </td>
                                    <td class="text-dark fw-semibold" style="font-size: 0.82rem;">Maret - Agustus {{ $tahunSession }} (Berjalan)</td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input class="form-control" type="file" name="dokumen" required id="dok_p_sister_ganjil_tl" style="display:none;" onchange="updateCutoffFileName(this, 'val_p_sister_ganjil_tl')">
                                            <button class="btn btn-outline-primary" type="button" onclick="document.getElementById('dok_p_sister_ganjil_tl').click()" style="border-color: #cbd5e1; color: #4b5563; font-weight: 600; font-size: 0.78rem; padding: 4px 10px; background-color: #f3f4f6;">Pilih File</button>
                                            <input type="text" class="form-control" id="val_p_sister_ganjil_tl" value="Tidak ada file dipilih" readonly style="background: #ffffff; font-size: 0.78rem; color: #64748b; border-color: #cbd5e1;">
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        <input type="hidden" name="tahun" value="{{ $tahunSession }}">
                                        <div class="d-flex align-items-center justify-content-center">
                                            <button type="submit" class="sptjm-btn-text-action sptjm-btn-text-save" title="Simpan" style="padding: 6px 16px; font-size: 0.85rem; border-radius: 6px;">
                                                <i class="bx bx-save"></i> Simpan
                                            </button>
                                        </div>
                                    </td>
                                </form>
                            </tr>
                            <!-- Row 2: Genap (Unified with toggle pills selector) -->
                            <tr>
                                <form action="{{ route('admin.cutoff-sisternas.upload') }}" method="POST"
                                    enctype="multipart/form-data" class="uploadForm" id="genapUploadForm">
                                    @csrf
                                    <td>
                                        <input name="table" value="n_sister_genap_bj" type="hidden" id="genap_table_select">
                                        <div class="d-flex flex-column gap-1.5">
                                            <div class="d-flex align-items-center gap-2.5">
                                                <span class="fw-bold" style="color: #0f2b5c; font-size: 0.85rem;">Genap</span>
                                                <div class="custom-toggle-track">
                                                    <button type="button" class="btn-toggle-pill active" id="btn_toggle_genap_bj">Berjalan</button>
                                                    <button type="button" class="btn-toggle-pill" id="btn_toggle_genap_tl">Tahun Lalu</button>
                                                </div>
                                            </div>
                                            <div class="text-muted" id="genap_bkd_text" style="font-size: 0.78rem;">[Maret - Agustus {{ $tahunSession }}]</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark" id="genap_pembayaran_text" style="font-size: 0.82rem;">Sept-Des {{ $tahunSession }} & Jan-Feb {{ $tahunDepan }}</div>
                                    </td>
                                    <td>
                                        <!-- File input selector -->
                                        <div class="input-group input-group-sm">
                                            <input class="form-control" type="file" name="dokumen" required id="dok_genap_file" style="display:none;" onchange="updateCutoffFileName(this, 'val_genap_file')">
                                            <button class="btn btn-outline-primary" type="button" onclick="document.getElementById('dok_genap_file').click()" style="border-color: #cbd5e1; color: #4b5563; font-weight: 600; font-size: 0.78rem; padding: 4px 10px; background-color: #f3f4f6;">Pilih File</button>
                                            <input type="text" class="form-control" id="val_genap_file" value="Tidak ada file dipilih" readonly style="background: #ffffff; font-size: 0.78rem; color: #64748b; border-color: #cbd5e1;">
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        <input type="hidden" name="tahun" value="{{ $tahunSession }}">
                                        <div class="d-flex align-items-center justify-content-center">
                                            <button type="submit" class="sptjm-btn-text-action sptjm-btn-text-save" title="Simpan" style="padding: 6px 16px; font-size: 0.85rem; border-radius: 6px;">
                                                <i class="bx bx-save"></i> Simpan
                                            </button>
                                        </div>
                                    </td>
                                </form>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-2 text-end">
                    <a href="{{ Storage::url('dokumen/contoh.csv') }}" target="_blank" class="text-primary fw-semibold" style="font-size: 0.82rem; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                        <i class="bx bx-download"></i> Unduh Contoh File CSV
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="card mb-4 card-cutoff" id="cutoffDataContainer" style="border-radius: 12px; background: #ffffff; border: 1px solid #e2e8f0;">
        <div class="card-body pt-2.5 pb-4 px-4">
            <!-- Section 2: Pilih Periode Cut Off Sisternas -->
            <div class="mb-1">
                    <div class="d-flex justify-content-between align-items-center mb-2.5 pb-2 border-bottom">
                        <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.92rem;">
                            <i class="bx bx-filter-alt text-primary me-1"></i> Pilih Periode Cut Off Sisternas
                        </h6>
                        <div class="d-flex align-items-center gap-2">
                            <span style="font-size: 0.78rem; font-weight: 600; color: #475569;">Tahun:</span>
                            <select name="tahun_filter" id="tahunFilterSelect" class="form-select form-select-sm" style="width: 100px; border-color: #cbd5e1; font-weight: 600; font-size: 0.78rem; border-radius: 6px; background-color: #f8fafc;">
                                @for($y = (session('tahun') ?: date('Y')); $y >= (session('tahun') ?: date('Y')) - 3; $y--)
                                    <option value="{{ $y }}" {{ $tahunSession == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 align-items-stretch">
                        <!-- Period 1: Ganjil Tahun Lalu -->
                        <div class="col-md-4 col-sm-12 d-flex">
                            <div class="btn-select-period p-2.5 rounded-3 d-flex flex-column justify-content-between w-100 h-100 glowing-active-card" data-value="p_sister_ganjil_tl" style="cursor: pointer; transition: all 0.2s ease;">
                                <div class="w-100">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="period-title fw-bold" style="font-size: 0.82rem; color: #435971;">Ganjil Tahun Lalu</span>
                                        <span class="badge-aktif-pill">AKTIF</span>
                                    </div>
                                    <div class="period-subtitle mb-2.5" style="font-size: 0.74rem; line-height: 1.35;">
                                        <div>
                                            <i class="bx bx-calendar-event me-1" style="color: #697a8d; font-size: 0.95rem; vertical-align: -1px;"></i>
                                            <span style="color: #8592a3;">Pembayaran:</span> <span class="fw-semibold" style="color: #566a7f;">Maret - Agustus {{ $tahunSession }}</span>
                                        </div>
                                        <div style="padding-left: 17px;">
                                            <span style="color: #8592a3;">BKD:</span> <span class="fw-semibold" style="color: #566a7f;">Sept {{ $tahunLalu }} - Feb {{ $tahunSession }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex gap-1.5 align-items-center w-100 mt-auto">
                                    <div class="flex-fill">
                                        <div class="btn-stat-memenuhi">
                                            <div class="d-flex align-items-center gap-1 flex-grow-1">
                                                <i class="bx bxs-check-circle" style="font-size: 1.05rem; color: #ffffff;"></i>
                                                <span class="stat-number">{{ number_format($statGanjilTL['m'], 0, ',', '.') }}</span>
                                                <span class="stat-label">Memenuhi</span>
                                            </div>
                                            <i class="bx bx-chevron-right" style="font-size: 0.95rem; color: #ffffff; opacity: 0.85;"></i>
                                        </div>
                                    </div>
                                    <div class="flex-fill">
                                        <div class="btn-stat-tm">
                                            <div class="d-flex align-items-center gap-1 flex-grow-1">
                                                <i class="bx bxs-x-circle" style="font-size: 1.05rem; color: #ffffff;"></i>
                                                <span class="stat-number">{{ number_format($statGanjilTL['tm'], 0, ',', '.') }}</span>
                                                <span class="stat-label">TM</span>
                                            </div>
                                            <i class="bx bx-chevron-right" style="font-size: 0.95rem; color: #ffffff; opacity: 0.85;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Period 2: Genap Berjalan -->
                        <div class="col-md-4 col-sm-12 d-flex">
                            <div class="btn-select-period p-2.5 rounded-3 d-flex flex-column justify-content-between w-100 h-100" data-value="n_sister_genap_bj" style="cursor: pointer; transition: all 0.2s ease;">
                                <div class="w-100">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="period-title fw-bold" style="font-size: 0.82rem; color: #435971;">Genap Berjalan</span>
                                        <span class="badge-aktif-pill d-none">AKTIF</span>
                                    </div>
                                    <div class="period-subtitle mb-2.5" style="font-size: 0.74rem; line-height: 1.35;">
                                        <div>
                                            <i class="bx bx-calendar-event me-1" style="color: #697a8d; font-size: 0.95rem; vertical-align: -1px;"></i>
                                            <span style="color: #8592a3;">Pembayaran:</span> <span class="fw-semibold" style="color: #566a7f;">Sept {{ $tahunSession }} - Feb {{ $tahunDepan }}</span>
                                        </div>
                                        <div style="padding-left: 17px;">
                                            <span style="color: #8592a3;">BKD:</span> <span class="fw-semibold" style="color: #566a7f;">Maret - Agustus {{ $tahunSession }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex gap-1.5 align-items-center w-100 mt-auto">
                                    <div class="flex-fill">
                                        <div class="btn-stat-memenuhi">
                                            <div class="d-flex align-items-center gap-1 flex-grow-1">
                                                <i class="bx bxs-check-circle" style="font-size: 1.05rem; color: #ffffff;"></i>
                                                <span class="stat-number">{{ number_format($statGenapBJ['m'], 0, ',', '.') }}</span>
                                                <span class="stat-label">Memenuhi</span>
                                            </div>
                                            <i class="bx bx-chevron-right" style="font-size: 0.95rem; color: #ffffff; opacity: 0.85;"></i>
                                        </div>
                                    </div>
                                    <div class="flex-fill">
                                        <div class="btn-stat-tm">
                                            <div class="d-flex align-items-center gap-1 flex-grow-1">
                                                <i class="bx bxs-x-circle" style="font-size: 1.05rem; color: #ffffff;"></i>
                                                <span class="stat-number">{{ number_format($statGenapBJ['tm'], 0, ',', '.') }}</span>
                                                <span class="stat-label">TM</span>
                                            </div>
                                            <i class="bx bx-chevron-right" style="font-size: 0.95rem; color: #ffffff; opacity: 0.85;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Period 3: Genap Tahun Lalu -->
                        <div class="col-md-4 col-sm-12 d-flex">
                            <div class="btn-select-period p-2.5 rounded-3 d-flex flex-column justify-content-between w-100 h-100" data-value="o_sister_genap_tl" style="cursor: pointer; transition: all 0.2s ease;">
                                <div class="w-100">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="period-title fw-bold" style="font-size: 0.82rem; color: #435971;">Genap Tahun Lalu</span>
                                        <span class="badge-aktif-pill d-none">AKTIF</span>
                                    </div>
                                    <div class="period-subtitle mb-2.5" style="font-size: 0.74rem; line-height: 1.35;">
                                        <div>
                                            <i class="bx bx-calendar-event me-1" style="color: #697a8d; font-size: 0.95rem; vertical-align: -1px;"></i>
                                            <span style="color: #8592a3;">Pembayaran:</span> <span class="fw-semibold" style="color: #566a7f;">Sept {{ $tahunLalu }} - Feb {{ $tahunSession }}</span>
                                        </div>
                                        <div style="padding-left: 17px;">
                                            <span style="color: #8592a3;">BKD:</span> <span class="fw-semibold" style="color: #566a7f;">Maret - Agustus {{ $tahunLalu }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex gap-1.5 align-items-center w-100 mt-auto">
                                    <div class="flex-fill">
                                        <div class="btn-stat-memenuhi">
                                            <div class="d-flex align-items-center gap-1 flex-grow-1">
                                                <i class="bx bxs-check-circle" style="font-size: 1.05rem; color: #ffffff;"></i>
                                                <span class="stat-number">{{ number_format($statGenapTL['m'], 0, ',', '.') }}</span>
                                                <span class="stat-label">Memenuhi</span>
                                            </div>
                                            <i class="bx bx-chevron-right" style="font-size: 0.95rem; color: #ffffff; opacity: 0.85;"></i>
                                        </div>
                                    </div>
                                    <div class="flex-fill">
                                        <div class="btn-stat-tm">
                                            <div class="d-flex align-items-center gap-1 flex-grow-1">
                                                <i class="bx bxs-x-circle" style="font-size: 1.05rem; color: #ffffff;"></i>
                                                <span class="stat-number">{{ number_format($statGenapTL['tm'], 0, ',', '.') }}</span>
                                                <span class="stat-label">TM</span>
                                            </div>
                                            <i class="bx bx-chevron-right" style="font-size: 0.95rem; color: #ffffff; opacity: 0.85;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="mt-2 mb-3.5" style="border-top: 1.5px solid #cbd5e1 !important;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold text-dark mb-1" id="selectedPeriodTitle">Rincian Data Cut Off Dosen</h5>
                        <p class="text-muted mb-0" style="font-size: 0.82rem;" id="selectedPeriodSubtitle">Pilih salah satu periode pada tabel di atas untuk me-load data dosen</p>
                    </div>
                    <!-- Hidden select untuk tetap menjaga fungsi JavaScript DataTables -->
                    <select name="sisternas" id="sisternasSelect" class="form-select d-none">
                        <option value="">Pilih Data...</option>
                        <option value="o_sister_genap_tl" {{ request('sisternas') == 'o_sister_genap_tl' ? 'selected' : '' }}>Genap Tahun Lalu</option>
                        <option value="p_sister_ganjil_tl" {{ request('sisternas') == 'p_sister_ganjil_tl' ? 'selected' : '' }}>Ganjil Tahun Lalu</option>
                        <option value="n_sister_genap_bj" {{ request('sisternas') == 'n_sister_genap_bj' ? 'selected' : '' }}>Genap Berjalan</option>
                    </select>

                    <div class="d-flex align-items-center gap-2">
                        @if(auth()->user()->role !== 'pic')
                        <button type="button" class="btn btn-primary fw-bold" id="addDataBtn" style="background-color: #0f2b5c; border-color: #0f2b5c; display: inline-flex; align-items: center; gap: 6px; font-size: 0.85rem; padding: 8px 16px;">
                            <i class="bx bx-plus-circle"></i> Tambah Data Dosen
                        </button>
                        @endif
                        <button type="button" id="exportBackupBtn" class="btn btn-outline-primary fw-semibold" style="border-color: #cbd5e1; color: #475569; display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; font-size: 0.85rem; background-color: #ffffff;">
                            <i class="bx bx-export"></i> Export Backup ODS
                        </button>
                    </div>
                </div>
            <hr class="mt-1 mb-2.5">

            <div id="loading" style="display: none;">
                <div class="spinner"></div>
                <p>Loading...</p>
            </div>

            <div id="table-container"></div> <!-- Kontainer untuk tabel hasil -->


            <!-- Tabel Data -->
            <div class="table-responsive text-nowrap" style="overflow-x: auto;">
                            <table class="table table-hover md2-table" id="cutoffTable" style="margin-bottom: 0 !important;">
                                <thead>
                                    <tr>
                                        <th style="text-align: center;">NO</th>
                                        <th style="text-align: center;">NIDN</th>
                                        <th style="text-align: center;">NUPTK</th>
                                        <th style="text-align: center;">NO SERTIFIKAT</th>
                                        <th style="text-align: center;">NAMA DOSEN</th>
                                        <th style="text-align: center;">KODE PT</th>
                                        <th style="text-align: center;">PERGURUAN TINGGI</th>
                                        <th style="text-align: center;">PROGRAM STUDI</th>
                                        <th style="text-align: center;">KESIMPULAN BKD</th>
                                        <th style="text-align: center;">KEWAJIBAN KHUSUS</th>
                                        <th style="text-align: center;">KESIMPULAN</th>
                                        <th style="text-align: center;">KD</th>
                                        <th style="text-align: center;">KP</th>
                                        <th style="text-align: center;">POTONGAN PERIODIK</th>
                                        <th style="text-align: center;">AKSI</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

<!-- Modal Edit -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Cut Off Data Sisternas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updateForm">
                    <div class="mb-f3">
                        <label for="nidn" class="form-label">NIDN</label>
                        <input type="text" class="form-control" id="nidn" name="nidn" readonly style="background-color: #eceef1;">
                    </div>

                    <div class="mb-3">
                        <label for="nuptk" class="form-label">NUPTK</label>
                        <input type="text" class="form-control" id="nuptk" name="nuptk" readonly style="background-color: #eceef1;">
                    </div>

                    <div class="mb-3">
                        <label for="no_sertifikat" class="form-label">No Sertifikat</label>
                        <input type="text" class="form-control" id="no_sertifikat" name="no_sertifikat">
                    </div>

                    <div class="mb-3">
                        <label for="nama_dosen" class="form-label">Nama Dosen</label>
                        <input type="text" class="form-control" id="nama_dosen" name="nama_dosen">
                    </div>

                    <div class="mb-3">
                        <label for="kode_pt" class="form-label">Kode PT</label>
                        <input type="text" class="form-control" id="kode_pt" name="kode_pt">
                    </div>

                    <div class="mb-3">
                        <label for="pt" class="form-label">Perguruan Tinggi</label>
                        <input type="text" class="form-control" id="pt" name="pt">
                    </div>

                    <div class="mb-3">
                        <label for="prodi" class="form-label">Program Studi</label>
                        <input type="text" class="form-control" id="prodi" name="prodi">
                    </div>

                    <div class="mb-3">
                        <label for="kesimpulan_bkd" class="form-label">Kesimpulan BKD</label>
                        <select class="form-select" id="kesimpulan_bkd" name="kesimpulan_bkd">
                            <option value="">Pilih</option>
                            <option value="M">M</option>
                            <option value="TM">TM</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="kewajiban_khusus" class="form-label">Kewajiban Khusus</label>
                        <select class="form-select" id="kewajiban_khusus" name="kewajiban_khusus">
                            <option value="">Pilih</option>
                            <option value="Memenuhi">Memenuhi</option>
                            <option value="Tugas Belajar">Tugas Belajar</option>
                            <option value="Tidak Memenuhi">Tidak Memenuhi</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="kesimpulan" class="form-label">Kesimpulan</label>
                        <select class="form-select" id="kesimpulan" name="kesimpulan">
                            <option value="">Pilih</option>
                            <option value="Memenuhi">Memenuhi</option>
                            <option value="Tidak Memenuhi">Tidak Memenuhi</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="kd" class="form-label">KD</label>
                        <input type="number" step="0.01" class="form-control" id="kd" name="kd" min="0">
                    </div>

                    <div class="mb-3">
                        <label for="kp" class="form-label">KP</label>
                        <input type="number" step="0.01" class="form-control" id="kp" name="kp" min="0">
                    </div>

                    <div class="mb-3">
                        <label for="potongan_periodik" class="form-label">Potongan Periodik</label>
                        <input type="number" step="0.01" class="form-control" id="potongan_periodik" name="potongan_periodik" min="0">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" form="updateForm" class="btn btn-primary">Simpan</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createModalLabel">Tambah Data Sisternas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="createForm">
                    <input type="hidden" name="sisternas" id="create_sisternas">
                    <div class="mb-3">
                        <label class="form-label">Data Sisternas Terpilih</label>
                        <input type="text" id="create_sisternas_label" class="form-control" value="" disabled>
                    </div>
                    <div class="mb-3">
                        <label for="create_nidn" class="form-label">NIDN</label>
                        <input type="text" class="form-control" id="create_nidn" name="nidn" required>
                    </div>
                    <div class="mb-3">
                        <label for="create_nuptk" class="form-label">NUPTK</label>
                        <input type="text" class="form-control" id="create_nuptk" name="nuptk" required>
                    </div>
                    <div class="mb-3">
                        <label for="create_no_sertifikat" class="form-label">No Sertifikat</label>
                        <input type="text" class="form-control" id="create_no_sertifikat" name="no_sertifikat" required>
                    </div>
                    <div class="mb-3">
                        <label for="create_nama_dosen" class="form-label">Nama Dosen</label>
                        <input type="text" class="form-control" id="create_nama_dosen" name="nama_dosen" required>
                    </div>
                    <div class="mb-3">
                        <label for="create_kode_pt" class="form-label">Kode PT</label>
                        <input type="text" class="form-control" id="create_kode_pt" name="kode_pt" required>
                    </div>
                    <div class="mb-3">
                        <label for="create_pt" class="form-label">Perguruan Tinggi</label>
                        <input type="text" class="form-control" id="create_pt" name="pt" required>
                    </div>
                    <div class="mb-3">
                        <label for="create_prodi" class="form-label">Program Studi</label>
                        <input type="text" class="form-control" id="create_prodi" name="prodi" required>
                    </div>
                    <div class="mb-3">
                        <label for="create_kesimpulan_bkd" class="form-label">Kesimpulan BKD</label>
                        <select class="form-select" id="create_kesimpulan_bkd" name="kesimpulan_bkd" required>
                            <option value="">Pilih</option>
                            <option value="M">M</option>
                            <option value="TM">TM</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="create_kewajiban_khusus" class="form-label">Kewajiban Khusus</label>
                        <select class="form-select" id="create_kewajiban_khusus" name="kewajiban_khusus" required>
                            <option value="">Pilih</option>
                            <option value="Memenuhi">Memenuhi</option>
                            <option value="Tugas Belajar">Tugas Belajar</option>
                            <option value="Tidak Memenuhi">Tidak Memenuhi</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="create_kesimpulan" class="form-label">Kesimpulan</label>
                        <select class="form-select" id="create_kesimpulan" name="kesimpulan" required>
                            <option value="">Pilih</option>
                            <option value="Memenuhi">Memenuhi</option>
                            <option value="Tidak Memenuhi">Tidak Memenuhi</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="create_kd" class="form-label">KD</label>
                        <input type="number" step="0.01" class="form-control" id="create_kd" name="kd" required min="0">
                    </div>
                    <div class="mb-3">
                        <label for="create_kp" class="form-label">KP</label>
                        <input type="number" step="0.01" class="form-control" id="create_kp" name="kp" required min="0">
                    </div>
                    <div class="mb-3">
                        <label for="create_potongan_periodik" class="form-label">Potongan Periodik</label>
                        <input type="number" step="0.01" class="form-control" id="create_potongan_periodik" name="potongan_periodik" required min="0">
                    </div>

                    <button type="submit" class="btn btn-primary">Simpan</button>
                </form>
            </div>
        </div>
    </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    const uploadForm = document.querySelectorAll('.uploadForm');
    uploadForm.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Mohon Tunggu...',
                html: `
                      <div class="d-flex justify-content-center align-items-center flex-column">
                          <div class="spinner-border spinner-border-lg text-primary" role="status">
                              <span class="visually-hidden">Loading...</span>
                          </div>
                          <div class="mt-2">Sedang mengupload data</div>
                      </div>
                  `,
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false,
                backdrop: true
            });

            const formData = new FormData(form);
            fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    Swal.close();
                    if (data.headerMismatch) {
                        // Siapkan konten untuk Bootstrap modal seperti di admin/migrasi
                        const expectedBadges = (data.expectedColumns || []).map(c => `<span class=\"badge bg-label-primary text-dark me-1 mb-1\">${c}</span>`).join(' ');
                        const missingBadges = (data.missingColumns || []).map(c => `<span class=\"badge bg-label-warning text-dark me-1 mb-1\">${c}</span>`).join(' ');
                        const extraBadges = (data.extraColumns || []).map(c => `<span class=\"badge bg-label-info text-dark me-1 mb-1\">${c}</span>`).join(' ');

                        // Inject ke modal
                        const modalEl = document.getElementById('cutoffRejectedModal');
                        if (modalEl) {
                            modalEl.querySelector('.js-expected').innerHTML = expectedBadges;
                            const missEl = modalEl.querySelector('.js-missing');
                            const extraEl = modalEl.querySelector('.js-extra');
                            const countsEl = modalEl.querySelector('.js-counts');
                            if (countsEl) countsEl.textContent = `Diharapkan: ${data.expectedCount} kolom | Ditemukan: ${data.foundCount} kolom`;
                            if (missEl) missEl.innerHTML = missingBadges || '<span class=\"text-muted\">-</span>';
                            if (extraEl) extraEl.innerHTML = extraBadges || '<span class=\"text-muted\">-</span>';

                            // Tampilkan modal Bootstrap
                            if (window.bootstrap && bootstrap.Modal) {
                                const m = new bootstrap.Modal(modalEl);
                                m.show();
                            } else {
                                // Fallback
                                modalEl.classList.add('show');
                                modalEl.style.display = 'block';
                            }
                        }
                        return;
                    }

                    Swal.fire({
                        icon: data.success ? 'success' : 'error',
                        title: data.success ? 'Berhasil!' : 'Gagal!',
                        text: data.message,
                    });

                    if (data.success) {
                        console.log(data);
                        form.reset();
                        cutOffTable.ajax.reload()
                    }
                })
                .catch(() => {
                    Swal.close();
                    Swal.fire('Error!', 'Terjadi kesalahan saa  t mengupload data.',
                        'error');
                });
        });
    });

    //datatable
    const cutOffTable = $('#cutoffTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.cutoff-sisternas") }}',
            data: function(d) {
                d.sisternas = $('#sisternasSelect').val();
                d.tahun = '{{ $tahunSession }}';
            },
            ajax: null

        },
        columns: [{
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                searchable: false,
                orderable: false
            },
            {
                data: 'nidn',
                name: 'nidn',
                searchable: true
            },
            {
                data: 'nuptk',
                name: 'nuptk',
                searchable: true
            },
            {
                data: 'no_sertifikat',
                name: 'no_sertifikat'
            },
            {
                data: 'nama_dosen',
                name: 'nama_dosen'
            },
            {
                data: 'kode_pt',
                name: 'kode_pt'
            },
            {
                data: 'pt',
                name: 'pt'
            },
            {
                data: 'prodi',
                name: 'prodi'
            },
            {
                data: 'kesimpulan_bkd',
                name: 'kesimpulan_bkd'
            },
            {
                data: 'kewajiban_khusus',
                name: 'kewajiban_khusus'
            },
            {
                data: 'kesimpulan',
                name: 'kesimpulan'
            },
            {
                data: 'kd',
                name: 'kd'
            },
            {
                data: 'kp',
                name: 'kp'
            },
            {
                data: 'potongan_periodik',
                name: 'potongan periodik',
                searchable: false,
                orderable: false
            },
            {
                data: 'aksi',
                name: 'aksi',
                orderable: false,
                searchable: false
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
            zeroRecords: `<div class="text-center p-5">
                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='48' height='48' fill='%2394a3b8' viewBox='0 0 24 24'%3E%3Cpath d='M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z'/%3E%3C/svg%3E" alt="Empty" class="mb-3">
                <p class="text-muted mb-0" style="font-size: 0.9rem;">Data tidak ditemukan dalam database periode ini</p>
            </div>`,
            emptyTable: `<div class="text-center p-5">
                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='48' height='48' fill='%2394a3b8' viewBox='0 0 24 24'%3E%3Cpath d='M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z'/%3E%3C/svg%3E" alt="Empty" class="mb-3">
                <p class="text-muted mb-0" style="font-size: 0.9rem;">Data tidak ditemukan dalam database periode ini</p>
            </div>`,
            infoEmpty: "Tidak ada data tersedia",
            searchPlaceholder: "Cari data...",
            search: "Cari Data:"
        },
    });

    $('#sisternasSelect').change(() => {
        cutOffTable.ajax.reload()
    });

    // Klik pada Nav Pill Periode untuk memilih periode & me-load data dosen
    $('.btn-select-period').on('click', function() {
        const selectedVal = $(this).data('value');
        const periodTitle = $(this).find('.period-title').text().trim();
        const bkdInfo = $(this).find('.period-subtitle').text().trim();

        $('#sisternasSelect').val(selectedVal).trigger('change');

        // Update judul & deskripsi tabel bawah
        $('#selectedPeriodTitle').html('<i class="bx bx-list-check text-primary me-2"></i>Daftar Dosen: ' + periodTitle);
        $('#selectedPeriodSubtitle').text(bkdInfo);

        // Reset & toggle active state (Hanya 1 card yang menyala)
        $('.btn-select-period').removeClass('glowing-active-card active');
        $('.badge-aktif-pill').addClass('d-none');

        $(this).addClass('glowing-active-card active');
        $(this).find('.badge-aktif-pill').removeClass('d-none');

        // Scroll smooth ke tabel daftar dosen
        $('html, body').animate({
            scrollTop: $("#cutoffDataContainer").offset().top - 80
        }, 300);
    });

    // Auto-select card aktif saat pertama kali halaman dimuat
    const initialVal = $('#sisternasSelect').val();
    if (initialVal) {
        $(`.btn-select-period[data-value="${initialVal}"]`).trigger('click');
    } else {
        $('.btn-select-period.glowing-active-card').trigger('click');
    }

    // Open create modal
    $('#addDataBtn').on('click', function() {
        const selected = $('#sisternasSelect').val();
        if (!selected) {
            Swal.fire('Pilih Data', 'Silakan pilih data sisternas terlebih dahulu.', 'info');
            return;
        }
        // Reset terlebih dahulu agar nilai yang diset tidak terhapus
        $('#createForm')[0].reset();
        // Set label tampilan dan hidden value setelah reset
        $('#create_sisternas').val(selected);
        const labelText = $('#sisternasSelect option:selected').text().trim();
        $('#create_sisternas_label').val(labelText).attr('value', labelText).attr('placeholder', labelText);
        $('#createModal').modal('show');
    });

    // Jaga-jaga: saat modal akan ditampilkan, sinkronkan lagi label & hidden
    $('#createModal').on('show.bs.modal', function () {
        const selected = $('#sisternasSelect').val();
        const labelText = $('#sisternasSelect option:selected').text().trim();
        $('#create_sisternas').val(selected);
        $('#create_sisternas_label').val(labelText).attr('value', labelText).attr('placeholder', labelText);
    });

    // Submit create form
    $('#createForm').on('submit', function(e) {
        e.preventDefault();
        $('#createModal').modal('hide');
        Swal.fire({
            title: 'Mohon Tunggu...',
            html: `
          <div class="d-flex justify-content-center align-items-center flex-column">
            <div class="spinner-border spinner-border-lg text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
            <div class="mt-2">Sedang menyimpan data...</div>
          </div>
        `,
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
            backdrop: true
        });

        $.ajax({
            url: `{{ route('admin.cutoff-sisternas.create') }}`,
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                sisternas: $('#create_sisternas').val(),
                nidn: $('#create_nidn').val(),
                nuptk: $('#create_nuptk').val(),
                no_sertifikat: $('#create_no_sertifikat').val(),
                nama_dosen: $('#create_nama_dosen').val(),
                kode_pt: $('#create_kode_pt').val(),
                pt: $('#create_pt').val(),
                prodi: $('#create_prodi').val(),
                kesimpulan_bkd: $('#create_kesimpulan_bkd').val(),
                kewajiban_khusus: $('#create_kewajiban_khusus').val(),
                kesimpulan: $('#create_kesimpulan').val(),
                kd: $('#create_kd').val(),
                kp: $('#create_kp').val(),
                potongan_periodik: $('#create_potongan_periodik').val(),
            },
            success: function(res) {
                cutOffTable.ajax.reload();
                Swal.fire('Berhasil!', res.message || 'Data berhasil ditambahkan.', 'success');
            },
            error: function(xhr) {
                let msg = 'Terjadi kesalahan saat menambahkan data.';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                Swal.fire('Error!', msg, 'error');
            }
        });
    });

    // Handle clear button click
    $('.clear-data-btn').on('click', function() {
        var table = $(this).attr('data-table');

        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data akan dihapus secara permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `{{ url('/admin/cutoff-sisternas/clear') }}/${table}`,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        Swal.fire('Berhasil!', res.message, 'success');
                        cutOffTable.ajax.reload();
                    },
                    error: function(xhr) {
                        Swal.fire('Error!',
                            'Terjadi kesalahan saat menghapus data.', 'error');
                    }
                });
            }
        });
    });

    // Handle edit button click
    $('#cutoffTable').on('click', '.edit-btn', function() {
        var data = cutOffTable.row($(this).parents('tr')).data();

        // data
        $('#nidn').val(data.nidn);
        $('#nuptk').val(data.nuptk || '');
        $('#no_sertifikat').val(data.no_sertifikat || '');
        $('#nama_dosen').val(data.nama_dosen || '');
        $('#kode_pt').val(data.kode_pt || '');
        $('#pt').val(data.pt || '');
        $('#prodi').val(data.prodi || '');
        $('#kesimpulan_bkd').val(data.kesimpulan_bkd || '');
        $('#kewajiban_khusus').val(data.kewajiban_khusus || '');
        $('#kesimpulan').val(data.kesimpulan || '');
        $('#kd').val(data.kd ?? '');
        $('#kp').val(data.kp ?? '');
        $('#potongan_periodik').val(data.potongan_periodik ?? '');

        // menamppilkan modal
        $('#editModal').modal('show');
    });
    //update data
    $('#updateForm').on('submit', function(e) {
        e.preventDefault();
        $('#editModal').modal('hide');
        Swal.fire({
            title: 'Mohon Tunggu...',
            html: `
          <div class="d-flex justify-content-center align-items-center flex-column">
            <div class="spinner-border spinner-border-lg text-success" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
            <div class="mt-2">Sedang menyimpan data...</div>
          </div>
        `,
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
            backdrop: true
        });

        $.ajax({
            url: `{{ route('admin.cutoff-sisternas.update') }}`,
            type: "PUT",
            data: {
                _token: "{{ csrf_token() }}",
                nidn: $('#nidn').val(),
                sisternas: $('#sisternasSelect').val(),
                nuptk: $('#nuptk').val(),
                no_sertifikat: $('#no_sertifikat').val(),
                nama_dosen: $('#nama_dosen').val(),
                kode_pt: $('#kode_pt').val(),
                pt: $('#pt').val(),
                prodi: $('#prodi').val(),
                kesimpulan_bkd: $('#kesimpulan_bkd').val(),
                kewajiban_khusus: $('#kewajiban_khusus').val(),
                kesimpulan: $('#kesimpulan').val(),
                kd: $('#kd').val(),
                kp: $('#kp').val(),
                potongan_periodik: $('#potongan_periodik').val(),
            },
            success: function(res) {
                console.log(res);
                $('#editModal').modal('hide');
                cutOffTable.ajax.reload();
                Swal.fire('Berhasil!', 'Data berhasil diperbarui.', 'success');
            },
            error: function(xhr) {
                console.log(xhr.responseText);
                Swal.fire('Error!', 'Terjadi kesalahan saat memperbarui data.', 'error');
            }
        });
    });

    $('#exportBackupBtn').on('click', function() {
        const selected = $('#sisternasSelect').val();
        if (!selected) {
            Swal.fire('Pilih Data', 'Silakan pilih data sisternas terlebih dahulu.', 'info');
            return;
        }
        const url = `{{ route('admin.cutoff-sisternas.export') }}?table=${selected}`;
        window.open(url, '_blank');
    });

    // Toggle Genap Upload Opsi (Berjalan vs Tahun Lalu)
    $('#btn_toggle_genap_bj').on('click', function() {
        $('#btn_toggle_genap_bj').addClass('active');
        $('#btn_toggle_genap_tl').removeClass('active');
        $('#genap_table_select').val('n_sister_genap_bj');
        $('#genap_pembayaran_text').text('Sept-Des {{ $tahunSession }} & Jan-Feb {{ $tahunDepan }}');
        $('#genap_bkd_text').text('[Maret - Agustus {{ $tahunSession }}]');
        // Reset file input inside Genap form to prevent uploading to the wrong table
        $('#dok_genap_file').val('');
        $('#val_genap_file').val('Tidak ada file dipilih');
    });

    $('#btn_toggle_genap_tl').on('click', function() {
        $('#btn_toggle_genap_tl').addClass('active');
        $('#btn_toggle_genap_bj').removeClass('active');
        $('#genap_table_select').val('o_sister_genap_tl');
        $('#genap_pembayaran_text').text('Sept-Des {{ $tahunLalu }} & Jan-Feb {{ $tahunSession }}');
        $('#genap_bkd_text').text('[Maret - Agustus {{ $tahunLalu }}]');
        // Reset file input inside Genap form
        $('#dok_genap_file').val('');
        $('#val_genap_file').val('Tidak ada file dipilih');
    });

    // Reload page when Year Filter changes
    $('#tahunFilterSelect').on('change', function() {
        const val = $(this).val();
        window.location.href = `?tahun=${val}`;
    });
});
</script>
{{-- Modal penolakan header untuk cutoff-sisternas (selaras dengan admin/migrasi) --}}
<div class="modal fade" id="cutoffRejectedModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Ditolak</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Jumlah dan penamaan kolom tidak sesuai dengan tabel.</p>
                <p class="mb-2 js-counts"></p>
                <div class="alert alert-primary">
                    <strong>Kolom yang diharapkan:</strong>
                    <div class="mt-2 js-expected"></div>
                </div>
                <div class="alert alert-warning">
                    <strong>Kolom yang tidak ada (missing):</strong>
                    <div class="mt-2 js-missing"></div>
                </div>
                <div class="alert alert-info">
                    <strong>Kolom yang tidak dikenal (extra):</strong>
                    <div class="mt-2 js-extra"></div>
                </div>
                <p class="mb-1">Silakan sesuaikan file CSV Anda agar sama persis dengan kolom tabel.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection
