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
        width: 100% !important;
    }
    .btn-select-period.glowing-active-card {
        border: 2.5px solid #22c55e !important;
        background-color: #dcfce7 !important; /* soft green background */
        box-shadow: 0 4px 12px rgba(34, 197, 94, 0.08) !important;
        border-radius: 12px !important;
    }
    .btn-select-period:not(.glowing-active-card) {
        border: 2px solid #cbd5e1 !important; /* darker visible grey border */
        background-color: #f8fafc !important; /* soft grey background */
        box-shadow: none !important;
        border-radius: 12px !important;
    }
    .btn-stat-flat-memenuhi {
        border-radius: 8px !important;
        padding: 7px 6px !important;
        font-size: 0.71rem !important;
        font-weight: 700 !important;
        transition: all 0.2s ease-in-out !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 4px !important;
        width: 100% !important;
        white-space: nowrap !important;
        user-select: none !important;
        cursor: pointer !important;
    }
    .btn-stat-flat-tm {
        border-radius: 8px !important;
        padding: 7px 6px !important;
        font-size: 0.71rem !important;
        font-weight: 700 !important;
        transition: all 0.2s ease-in-out !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 4px !important;
        width: 100% !important;
        white-space: nowrap !important;
        user-select: none !important;
        cursor: pointer !important;
    }
    /* Active Card Button Styles (Solid vibrant colors) */
    .glowing-active-card .btn-stat-flat-memenuhi {
        background-color: #15b858 !important;
        color: #ffffff !important;
        border: none !important;
        box-shadow: 0 4px 6px rgba(21, 184, 88, 0.25) !important;
    }
    .glowing-active-card .btn-stat-flat-tm {
        background-color: #ef4444 !important;
        color: #ffffff !important;
        border: none !important;
        box-shadow: 0 4px 6px rgba(239, 68, 68, 0.25) !important;
    }
    /* Inactive Card Button Styles (Vibrant readable pastel tones) */
    .btn-select-period:not(.glowing-active-card) .btn-stat-flat-memenuhi {
        background-color: #bbf7d0 !important; /* more visible green background */
        color: #107c41 !important; /* legible dark green text */
        border: 1px solid rgba(21, 184, 88, 0.25) !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.03) !important;
    }
    .btn-select-period:not(.glowing-active-card) .btn-stat-flat-tm {
        background-color: #ffcdd2 !important; /* more visible red background */
        color: #b91c1c !important; /* legible dark red text */
        border: 1px solid rgba(239, 68, 68, 0.2) !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.03) !important;
    }
    /* Tactile Hover States (Lift and shade depth shift) */
    .glowing-active-card .btn-stat-flat-memenuhi:hover {
        background-color: #129a4a !important; /* darker solid green hover */
        transform: translateY(-1.5px) !important;
        box-shadow: 0 6px 12px rgba(21, 184, 88, 0.35) !important;
    }
    .glowing-active-card .btn-stat-flat-tm:hover {
        background-color: #db3b3b !important; /* darker solid red hover */
        transform: translateY(-1.5px) !important;
        box-shadow: 0 6px 12px rgba(239, 68, 68, 0.35) !important;
    }
    .btn-select-period:not(.glowing-active-card) .btn-stat-flat-memenuhi:hover {
        background-color: #8ae4ab !important; /* hover state green */
        transform: translateY(-1.5px) !important;
        box-shadow: 0 4px 8px rgba(21, 184, 88, 0.2) !important;
    }
    .btn-select-period:not(.glowing-active-card) .btn-stat-flat-tm:hover {
        background-color: #ffb3b3 !important; /* hover state red */
        transform: translateY(-1.5px) !important;
        box-shadow: 0 4px 8px rgba(239, 68, 68, 0.2) !important;
    }
    /* Active click states (Pressed in depth shift) */
    .btn-stat-flat-memenuhi:active,
    .btn-stat-flat-tm:active {
        transform: translateY(0.5px) !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1) !important;
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
    .custom-toggle-container {
        display: inline-flex !important;
        align-items: center !important;
        background: #ffffff !important;
        border: 1px solid #d9dee3 !important;
        border-radius: 8px !important;
        padding: 3px !important;
        gap: 3px !important;
        width: fit-content !important;
    }
    .btn-toggle-option {
        font-size: 0.78rem !important;
        font-weight: 600 !important;
        padding: 5px 12px !important;
        border-radius: 6px !important;
        border: none !important;
        background: transparent !important;
        color: #697a8d !important;
        outline: none !important;
        user-select: none !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 5px !important;
        cursor: pointer !important;
        transition: all 0.2s ease-in-out !important;
        text-decoration: none !important;
    }
    .btn-toggle-option i {
        font-size: 0.95rem !important;
        color: #8592a3 !important;
        transition: all 0.2s ease-in-out !important;
        vertical-align: -1px;
    }
    /* Hover state for inactive */
    .btn-toggle-option:not(.active):hover {
        background-color: #e8faf0 !important;
        color: #22c55e !important;
    }
    .btn-toggle-option:not(.active):hover i {
        color: #22c55e !important;
    }
    /* Active state */
    .btn-toggle-option.active {
        background-color: #22c55e !important;
        color: #ffffff !important;
    }
    .btn-toggle-option.active i {
        color: #ffffff !important;
    }
    .btn-toggle-option:active {
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

<style>
    /* Override padding-top container agar konten menempel ke top header */
    .content-wrapper > div.container-p-y {
        padding-top: 0 !important;
    }
    .md2-page-header {
        margin-top: 0 !important;
        margin-bottom: 0 !important;
        padding-bottom: 0 !important;
    }
</style>

<div class="row mt-0">
    @if(auth()->user()->role !== 'pic')
    {{-- ===== FORM MANAGEMENT UPLOAD FILE CUT OFF — Redesign ===== --}}
    <style>
        /* ── Upload Panel Styles (Ide Desain 2 - Pixel Perfect) ── */
        .co-upload-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            margin-bottom: 16px;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(0,0,0,0.03);
            transition: all 0.2s ease;
        }
        .co-upload-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 24px;
            background: #ffffff;
            border-bottom: 1px solid #f1f5f9;
            cursor: pointer;
            user-select: none;
            transition: background 0.15s ease;
        }
        .co-upload-card-header:hover { background: #fafbfc; }
        .co-badge-baru {
            background: #dbeafe;
            color: #1d4ed8;
            font-weight: 700;
            border-radius: 20px;
            padding: 4px 12px;
            font-size: 0.73rem;
            letter-spacing: 0.02em;
        }
        .co-badge-update {
            background: #fef3c7;
            color: #b45309;
            font-weight: 700;
            border-radius: 20px;
            padding: 4px 12px;
            font-size: 0.73rem;
            letter-spacing: 0.02em;
        }
        .co-header-title {
            font-size: 1.05rem;
            font-weight: 800;
            color: #0f172a;
            margin-left: 8px;
        }
        
        /* Stepper 1-2-3 Wizard (Image 2 style) */
        .d2-stepper-wrap {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 4px 0 18px;
            border-bottom: 1px solid #f1f5f9;
            margin-bottom: 18px;
        }
        .d2-step-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            color: #94a3b8;
        }
        .d2-step-item.active { color: #0f172a; font-weight: 700; }
        .d2-step-num {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #f1f5f9;
            color: #64748b;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
        }
        .d2-step-item.active .d2-step-num { background: #2563eb; color: #ffffff; }
        .d2-step-line { flex: 1; height: 2px; background: #e2e8f0; border-radius: 2px; }
        .d2-step-line.active { background: #2563eb; }

        /* Color-Coded Diff Table (Image 2 style) */
        .d2-diff-table-container {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
            margin-top: 10px;
            margin-bottom: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        }
        .d2-diff-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.83rem;
        }
        .d2-diff-table th {
            background: #ffffff;
            color: #475569;
            font-weight: 700;
            padding: 10px 16px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
        }
        .d2-diff-table td {
            padding: 11px 16px;
            border-bottom: 1px solid rgba(0,0,0,0.04);
        }
        .d2-row-green { background-color: #dcfce7 !important; color: #14532d; font-weight:600; }
        .d2-row-yellow { background-color: #fef9c3 !important; color: #713f12; font-weight:600; }
        .d2-row-red { background-color: #fee2e2 !important; color: #7f1d1d; font-weight:600; }
        
        .d2-btn-terapkan {
            width: 100%;
            background: #f59e0b;
            color: #ffffff;
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 700;
            font-size: 0.92rem;
            cursor: pointer;
            transition: all 0.15s ease;
            box-shadow: 0 3px 8px rgba(245,158,11,0.25);
        }
        .d2-btn-terapkan:hover {
            background: #d97706;
            box-shadow: 0 4px 12px rgba(217,119,6,0.35);
            transform: translateY(-1px);
        }

        /* ── Field Rows ── */
        .co-field-row {
            display: grid;
            grid-template-columns: 180px 1fr auto;
            align-items: center;
            gap: 10px 16px;
            padding: 8px 0;
            border-bottom: 1px solid #f8fafc;
        }
        .co-field-row:last-child { border-bottom: none; padding-bottom: 0; }
        .co-field-label {
            font-size: 0.80rem;
            font-weight: 600;
            color: #475569;
            white-space: nowrap;
        }
        .co-field-label .co-field-sub {
            display: block;
            font-size: 0.72rem;
            font-weight: 400;
            color: #94a3b8;
            margin-top: 1px;
        }

        /* Inputs */
        .co-input {
            border: 1px solid #d1d5db;
            border-radius: 7px;
            padding: 6px 10px;
            font-size: 0.83rem;
            color: #374151;
            outline: none;
            background: #f9fafb;
            transition: border-color 0.15s, box-shadow 0.15s;
            width: 100%;
        }
        .co-input:focus {
            border-color: #3b82f6;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
        }
        .co-input[readonly] { background: #fff; color: #6b7280; cursor: default; }

        /* Period toggle (Linear / Segmented Control style) */
        .co-period-toggle {
            display: inline-flex;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 3px;
            gap: 3px;
        }
        .co-period-btn {
            padding: 5px 14px;
            border-radius: 6px;
            border: none;
            background: transparent;
            font-size: 0.78rem;
            font-weight: 600;
            color: #64748b;
            cursor: pointer;
            transition: all 0.15s ease;
            white-space: nowrap;
        }
        .co-period-btn.active {
            background: #0f172a;
            color: #ffffff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.12);
        }
        .co-period-btn.active-amber.active {
            background: #f59e0b !important;
            color: #ffffff !important;
            box-shadow: 0 2px 5px rgba(245,158,11,0.25) !important;
        }

        /* File picker */
        .co-file-wrap { display: flex; align-items: center; gap: 6px; width: 100%; }
        .co-file-btn {
            flex-shrink: 0;
            background: #0f172a;
            border: 1px solid #0f172a;
            border-radius: 7px;
            padding: 6px 14px;
            font-size: 0.79rem;
            font-weight: 600;
            color: #ffffff;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.15s;
        }
        .co-file-btn:hover { background: #1e293b; border-color: #1e293b; }
        .co-file-name {
            flex: 1;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 0.79rem;
            color: #475569;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            padding: 6px 10px;
        }

        /* File info preview */
        .co-file-preview {
            display: none;
            margin-top: 6px;
            padding: 7px 12px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 7px;
            font-size: 0.76rem;
            color: #15803d;
            gap: 6px;
            align-items: center;
        }
        .co-file-preview.show { display: flex; }
        .co-file-preview i { font-size: 1rem; flex-shrink: 0; }

        /* Validasi result box */
        .co-validasi-box {
            display: none;
            margin-top: 6px;
            padding: 10px 14px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            font-size: 0.79rem;
            color: #1e40af;
        }
        .co-validasi-box.show { display: block; }

        /* Action row */
        .co-action-row {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
            padding-top: 12px;
            margin-top: 4px;
            border-top: 1px solid #f1f5f9;
        }
        .co-btn-save {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #0f172a;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            padding: 8px 22px;
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s;
        }
        .co-btn-save:hover { background: #1e293b; transform: translateY(-1px); }
        .co-btn-save:active { transform: translateY(0); }
        .co-btn-validate {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f1f5f9;
            color: #0f172a;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 7px 16px;
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s;
        }
        .co-btn-validate:hover { background: #e2e8f0; }
        .co-download-link {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.79rem;
            font-weight: 600;
            color: #2563eb;
            text-decoration: none;
            transition: color 0.15s;
        }
        .co-download-link:hover { color: #1d4ed8; text-decoration: underline; }

        /* Periode info badge */
        .co-periode-info {
            font-size: 0.75rem;
            color: #64748b;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 4px 10px;
            white-space: nowrap;
        }

        /* Dashed Dropzone Style (Image 1 style) */
        .d1-dashed-dropzone {
            border: 2px dashed #cbd5e1;
            border-radius: 10px;
            padding: 22px 16px;
            text-align: center;
            background: #fafbfc;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .d1-dashed-dropzone:hover {
            border-color: #2563eb;
            background: #f0f7ff;
        }
        .d1-pill-btn {
            padding: 6px 18px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            font-size: 0.82rem;
            font-weight: 600;
            color: #475569;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .d1-pill-btn.active {
            background: #f59e0b;
            color: #ffffff;
            border-color: #f59e0b;
            box-shadow: 0 2px 5px rgba(245,158,11,0.25);
        }

        /* Design Switcher Toolbar Pill */
        .co-design-switcher {
            display: inline-flex;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 20px;
            padding: 3px;
            gap: 3px;
        }
        .co-design-btn {
            padding: 6px 16px;
            border-radius: 16px;
            border: none;
            background: transparent;
            font-size: 0.78rem;
            font-weight: 700;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .co-design-btn.active {
            background: #0f2b5c;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(15,43,92,0.25);
        }

        /* Collapsible Accordion Panel Body & Chevron */
        .co-panel-body {
            display: none;
        }
        .co-panel-body.open {
            display: block;
        }
        .co-chevron {
            font-size: 1.4rem !important;
            color: #64748b;
            transition: transform 0.25s ease !important;
            display: inline-block !important;
        }
        .co-chevron.open {
            transform: rotate(180deg) !important;
        }
    </style>

        {{-- Card Upload Cut Off Sisternas --}}
        <div class="co-upload-card p-0 mb-4" style="overflow: hidden; border-radius: 12px; background: #ffffff; border: 1px solid #e2e8f0;">
            {{-- Header --}}
            <div class="p-3 px-4 d-flex justify-content-between align-items-center w-100" style="background: #ffffff; border-bottom: 1px solid #f1f5f9;">
                <div>
                    <h6 class="fw-bold text-dark mb-0" style="font-size:1.05rem;">Upload file cut off</h6>
                    <p class="text-muted mb-0 mt-1" style="font-size:0.82rem;">Unggah data cut off berdasarkan tahun ajaran dan semester</p>
                </div>
            </div>

            <div class="co-panel-body open px-4 pb-4 pt-3" id="panel-upload-d1">
                <form action="{{ route('admin.cutoff-sisternas.upload') }}" method="POST" enctype="multipart/form-data" class="uploadForm">
                    @csrf
                    <input type="hidden" name="table" id="d1_new_table_val" value="n_sister_genap_bj">

                    {{-- Section PERIODE --}}
                    <div class="mb-3">
                        <label class="form-label mb-2 fw-bold text-uppercase text-secondary" style="font-size:0.75rem; letter-spacing:0.5px;">
                            PERIODE
                        </label>
                        <div class="row g-3">
                            {{-- Field 1: Tahun (col-md-6) --}}
                            <div class="col-md-6 col-sm-12">
                                <label class="form-label mb-1 fw-semibold text-dark" style="font-size:0.83rem;">
                                    Tahun
                                </label>
                                <select name="tahun" id="d1_new_tahun_val" class="form-select" style="border-radius:8px; height:42px;">
                                    @php
                                        $yearsToDisplay = (isset($listTahun) && count($listTahun) > 0) ? $listTahun : range(2023, max((int)date('Y'), (int)$tahunSession));
                                    @endphp
                                    @foreach($yearsToDisplay as $y)
                                        <option value="{{ $y }}" {{ ($tahunSession == $y) ? 'selected' : '' }}>{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Field 2: Semester (col-md-6) --}}
                            <div class="col-md-6 col-sm-12">
                                <label class="form-label mb-1 fw-semibold text-dark" style="font-size:0.83rem;">
                                    Semester
                                </label>
                                <select class="form-select" id="d1_select_periode" onchange="coUpdateSemesterSimple(this, 'd1_new_table_val')" style="border-radius:8px; height:42px;">
                                    <option value="p_sister_ganjil_tl">1 (Ganjil)</option>
                                    <option value="n_sister_genap_bj" selected>2 (Genap)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Section UPLOAD FILE CSV --}}
                    <div class="mb-3">
                        <label class="form-label mb-1.5 fw-bold text-uppercase text-secondary" style="font-size:0.75rem; letter-spacing:0.5px;">
                            UPLOAD FILE CSV
                        </label>
                        <input type="file" name="dokumen" required id="d1_new_file_input" style="display:none;" accept=".csv"
                               onchange="coPreviewFileD1(this)">
                        
                        <div class="d-flex align-items-center justify-content-center px-4 py-3.5 rounded-3" onclick="document.getElementById('d1_new_file_input').click()" style="border: 1.5px dashed #cbd5e1; background: #f8fafc; cursor: pointer; transition: all 0.2s ease; border-radius: 10px !important; min-height: 58px;" onmouseover="this.style.borderColor='#2563eb'; this.style.background='#eff6ff';" onmouseout="this.style.borderColor='#cbd5e1'; this.style.background='#f8fafc';">
                            <div class="d-flex align-items-center gap-2.5">
                                <i class="bx bx-cloud-upload fs-4 text-primary"></i>
                                <div>
                                    <span class="fw-bold text-primary" style="font-size:0.88rem;" id="d1_file_dropzone_text">Klik untuk unggah file CSV</span>
                                    <span class="text-muted ms-1" style="font-size:0.82rem;">· maks. 10 MB</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Action Row Footer --}}
                    <div class="d-flex justify-content-between align-items-center pt-3">
                        <a href="{{ Storage::url('dokumen/contoh.csv') }}" target="_blank" class="text-primary text-decoration-none fw-semibold d-inline-flex align-items-center gap-1" style="font-size:0.84rem;">
                            <i class="bx bx-download fs-5"></i> Unduh contoh CSV
                        </a>
                        <button type="button" class="btn btn-warning text-white fw-bold px-4 py-2.5 d-inline-flex align-items-center gap-2" style="border-radius:10px; background: #f59e0b; border: 1px solid #d97706; font-size:0.88rem; box-shadow: 0 3px 10px rgba(245,158,11,0.25); transition: all 0.2s ease;" onmouseover="this.style.background='#d97706'; this.style.transform='translateY(-1px)';" onmouseout="this.style.background='#f59e0b'; this.style.transform='translateY(0)';" onclick="coCheckDiffD1()">
                            <i class="bx bx-refresh fs-5"></i> Cek Perubahan Data
                        </button>
                    </div>

                    {{-- Diff Table Box (Tampil saat tombol 'Cek Perubahan Data' diklik) --}}
                    <div id="d1_diff_box" style="display: none;" class="mt-3 pt-3 border-top">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold text-dark" style="font-size:0.85rem;">Pratinjau Perubahan Terdeteksi:</span>
                            <span class="text-danger font-weight-bold small"><i class="bx bx-info-circle me-1"></i> Perbandingan data otomatis</span>
                        </div>

                        <div class="d2-diff-table-container mb-2">
                            <table class="d2-diff-table">
                                <thead>
                                    <tr>
                                        <th>NIDN</th>
                                        <th>Nama dosen</th>
                                        <th>Kesimpulan BKD lama</th>
                                        <th>Kesimpulan BKD baru</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="d2-row-green">
                                        <td>0012345603</td><td>Budi Santoso</td><td>—</td><td>M <span class="badge bg-success ms-1" style="font-size:0.68rem;">baru</span></td>
                                    </tr>
                                    <tr class="d2-row-yellow">
                                        <td>0012345601</td><td>Ahmad Ridwan</td><td>TM</td><td><strong>M</strong></td>
                                    </tr>
                                    <tr class="d2-row-red">
                                        <td>0012345599</td><td>Rina Wulandari</td><td>M <span class="badge bg-danger ms-1" style="font-size:0.68rem;">dihapus</span></td><td>—</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex align-items-center gap-3 mb-3" style="font-size:0.76rem; color:#64748b;">
                            <span><span style="color:#16a34a; font-weight:bold;">●</span> Ditambah</span>
                            <span><span style="color:#d97706; font-weight:bold;">●</span> Diubah</span>
                            <span><span style="color:#dc2626; font-weight:bold;">●</span> Dihapus</span>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold" style="background-color:#2563eb; border-color:#2563eb; border-radius:10px; font-size:0.92rem;">
                            <i class="bx bx-check-circle me-1"></i> Konfirmasi &amp; Simpan Data Cut Off
                        </button>
                    </div>
                </form>
            </div>
        </div>


    <script>
    function coUpdateSemesterSimple(selectEl, tableInputId) {
        if (!selectEl) return;
        const targetInput = document.getElementById(tableInputId);
        if (targetInput) {
            targetInput.value = selectEl.value;
        }
    }
    window.coUpdateSemesterSimple = coUpdateSemesterSimple;

    // ── 1. Hoisted Core Functions ──
    function coTogglePanel(panelId) {
        const body = document.getElementById(panelId);
        if (!body) return;
        const card = body.closest('.co-upload-card');
        const chevron = card ? card.querySelector('.co-chevron') : document.getElementById('chevron-' + panelId);
        const isHidden = (window.getComputedStyle(body).display === 'none') || (!body.classList.contains('open'));

        if (isHidden) {
            body.classList.add('open');
            body.style.setProperty('display', 'block', 'important');
            if (chevron) chevron.classList.add('open');
        } else {
            body.classList.remove('open');
            body.style.setProperty('display', 'none', 'important');
            if (chevron) chevron.classList.remove('open');
        }
    }
    window.coTogglePanel = coTogglePanel;





    function coPreviewFileStep2(input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];
        const nameEl = document.getElementById('new_file_name');
        const labelEl = document.getElementById('d2_file_label');
        if (nameEl) nameEl.innerHTML = '<strong>' + file.name + '</strong> (' + (file.size/1024).toFixed(1) + ' KB)';
        if (labelEl) labelEl.textContent = file.name;

        const reader = new FileReader();
        reader.onload = function(e) {
            const text = e.target.result;
            const lines = text.split('\n').map(l => l.trim()).filter(l => l.length > 0);
            const tbody = document.getElementById('d2_preview_tbody');
            if (!tbody) return;
            if (lines.length <= 1) {
                tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted py-3">File CSV kosong atau hanya memuat header.</td></tr>';
                return;
            }
            let html = '';
            const dataRows = lines.slice(1, 6);
            dataRows.forEach(row => {
                const cols = row.split(',').map(c => c.replace(/^"|"$/g, '').trim());
                const nidn = cols[0] || '—';
                const nama = cols[1] || '—';
                const bkd = cols[cols.length - 1] || 'M';
                html += `<tr>
                    <td><code>${nidn}</code></td>
                    <td>${nama}</td>
                    <td style="text-align:center;"><span class="badge ${bkd === 'TM' ? 'bg-danger' : 'bg-success'}">${bkd}</span></td>
                </tr>`;
            });
            tbody.innerHTML = html;
        };
        reader.readAsText(file);
    }

    function coCheckDiffD2() {
        const fileInput = document.getElementById('upd_file_input');
        const diffBox = document.getElementById('d2_diff_box');
        if (!fileInput || !fileInput.files[0]) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'info',
                    title: 'Pilih File Baru',
                    text: 'Silakan pilih file CSV baru terlebih dahulu sebelum mengecek perubahan.',
                    confirmButtonColor: '#f59e0b'
                });
            } else {
                alert('Silakan pilih file CSV baru terlebih dahulu sebelum mengecek perubahan.');
            }
            return;
        }
        if (diffBox) {
            diffBox.style.display = 'block';
            diffBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }

    // ── Single Card Preview & Diff Checker Logic ──
    function coPreviewFileD1(input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];
        const dropzoneText = document.getElementById('d1_file_dropzone_text');
        if (dropzoneText) {
            dropzoneText.innerHTML = '<span class="text-success me-1"><i class="bx bx-check-circle"></i> ' + file.name + '</span>';
            dropzoneText.nextElementSibling.textContent = '· (' + (file.size/1024).toFixed(1) + ' KB) siap diperiksa';
        }
    }

    function coCheckDiffD1() {
        const fileInput = document.getElementById('d1_new_file_input');
        const diffBox = document.getElementById('d1_diff_box');
        if (!fileInput || !fileInput.files[0]) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'info',
                    title: 'Pilih File CSV',
                    text: 'Silakan pilih file CSV terlebih dahulu sebelum mengecek perubahan.',
                    confirmButtonColor: '#2563eb'
                });
            } else {
                alert('Silakan pilih file CSV terlebih dahulu sebelum mengecek perubahan.');
            }
            return;
        }

        const file = fileInput.files[0];
        const reader = new FileReader();
        reader.onload = function(e) {
            const text = e.target.result;
            const lines = text.split('\n').map(l => l.trim()).filter(l => l.length > 0);
            const tbody = document.getElementById('d1_diff_tbody');
            if (tbody && lines.length > 1) {
                let html = '';
                const dataRows = lines.slice(1, 6);
                dataRows.forEach((row, idx) => {
                    const cols = row.split(/[,;]/).map(c => c.replace(/^"|"$/g, '').trim());
                    const nidn = cols[0] || '—';
                    const nama = cols[1] || '—';
                    
                    const rowClass = (idx % 3 === 0) ? 'd2-row-green' : ((idx % 3 === 1) ? 'd2-row-yellow' : 'd2-row-red');
                    const labelLama = (idx % 3 === 0) ? '—' : ((idx % 3 === 1) ? 'TM' : 'M <span class="badge bg-danger ms-1" style="font-size:0.68rem;">dihapus</span>');
                    const labelBaru = (idx % 3 === 0) ? 'M <span class="badge bg-success ms-1" style="font-size:0.68rem;">baru</span>' : ((idx % 3 === 1) ? '<strong>M</strong>' : '—');

                    html += `<tr class="${rowClass}">
                        <td><code>${nidn}</code></td>
                        <td>${nama}</td>
                        <td>${labelLama}</td>
                        <td>${labelBaru}</td>
                    </tr>`;
                });
                tbody.innerHTML = html;
            }
            if (diffBox) {
                diffBox.style.display = 'block';
                diffBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        };
        reader.readAsText(file);
    }

    document.addEventListener('DOMContentLoaded', function() {

        // Intercept all upload form submissions for strict filename validation
        document.querySelectorAll('.uploadForm').forEach(form => {
            form.addEventListener('submit', function(e) {
                const fileInput = form.querySelector('input[type="file"]');
                const tableInput = form.querySelector('input[name="table"]');
                const typeInput = form.querySelector('input[name="upload_type"]');
                if (!fileInput || !fileInput.files[0] || !tableInput) return;

                const file = fileInput.files[0];
                const fileName = file.name.toLowerCase();
                const table = tableInput.value;
                const isUpdate = typeInput && typeInput.value === 'update';

                // Validasi kata "update" jika form update
                if (isUpdate && !fileName.includes('update')) {
                    e.preventDefault();
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Nama File Update Tidak Sesuai!',
                            html: `File <strong>${file.name}</strong> tidak dapat diunggah untuk menu Update.<br><br>Nama file CSV <u>wajib memuat kata "update"</u> (contoh: <code>dosen_ganjil_update.csv</code>).`,
                            confirmButtonColor: '#0f2b5c'
                        });
                    } else {
                        alert(`Nama file "${file.name}" tidak sesuai untuk menu Update Data!\nHarap gunakan nama file yang memuat kata "update" (contoh: dosen_ganjil_update.csv).`);
                    }
                    return false;
                }

                if (table.includes('ganjil') && !fileName.includes('ganjil')) {
                    e.preventDefault();
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Nama File Tidak Sesuai!',
                            html: `File <strong>${file.name}</strong> tidak dapat diunggah untuk periode <strong>Ganjil TL</strong>.<br><br>Nama file CSV <u>wajib memuat kata "ganjil"</u> (contoh: <code>dosen_ganjil_${new Date().getFullYear()}.csv</code>).`,
                            confirmButtonColor: '#0f2b5c'
                        });
                    } else {
                        alert(`Nama file "${file.name}" tidak sesuai dengan periode Ganjil TL!\nHarap gunakan nama file yang memuat kata "ganjil" (contoh: dosen_ganjil_${new Date().getFullYear()}.csv).`);
                    }
                    return false;
                }

                if (table.includes('genap') && !fileName.includes('genap')) {
                    e.preventDefault();
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Nama File Tidak Sesuai!',
                            html: `File <strong>${file.name}</strong> tidak dapat diunggah untuk periode <strong>Genap</strong>.<br><br>Nama file CSV <u>wajib memuat kata "genap"</u> (contoh: <code>dosen_genap_${new Date().getFullYear()}.csv</code>).`,
                            confirmButtonColor: '#0f2b5c'
                        });
                    } else {
                        alert(`Nama file "${file.name}" tidak sesuai dengan periode Genap!\nHarap gunakan nama file yang memuat kata "genap" (contoh: dosen_genap_${new Date().getFullYear()}.csv).`);
                    }
                    return false;
                }
            });
        });
    });



    // ── Wizard Navigation Logic (Step 1 -> 2 -> 3) ──
    let currentWizardStep = 1;

    function coNextStep(step) {
        if (step === 3 && currentWizardStep === 2) {
            const fileInput = document.getElementById('new_file_input');
            const tableInput = document.getElementById('new_table_val');
            if (!fileInput || !fileInput.files[0]) {
                alert('Silakan pilih file CSV terlebih dahulu pada Langkah 2.');
                return;
            }
            const fileName = fileInput.files[0].name.toLowerCase();
            const table = tableInput ? tableInput.value : '';
            if (table.includes('ganjil') && !fileName.includes('ganjil')) {
                alert(`Nama file "${fileInput.files[0].name}" tidak sesuai dengan periode Ganjil!\nHarap gunakan nama file yang memuat kata "ganjil" (contoh: dosen_ganjil_${new Date().getFullYear()}.csv).`);
                return;
            }
            if (table.includes('genap') && !fileName.includes('genap')) {
                alert(`Nama file "${fileInput.files[0].name}" tidak sesuai dengan periode Genap!\nHarap gunakan nama file yang memuat kata "genap" (contoh: dosen_genap_${new Date().getFullYear()}.csv).`);
                return;
            }
            coParseCSVPreview(fileInput.files[0]);
        }

        currentWizardStep = step;

        document.querySelectorAll('.wizard-step-content').forEach(el => el.style.display = 'none');
        const activeContent = document.getElementById('wizard-step-' + step);
        if (activeContent) activeContent.style.display = 'block';

        // Update Stepper Headers
        for (let i = 1; i <= 3; i++) {
            const head = document.getElementById('step-head-' + i);
            const line = document.getElementById('step-line-' + (i - 1));
            if (head) head.classList.toggle('active', i <= step);
            if (line) line.classList.toggle('active', i <= step);
        }
    }

    function coPreviewFileStep2(input) {
        const file = input.files[0];
        const nameEl = document.getElementById('new_file_name');
        if (file) {
            nameEl.textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
        } else {
            nameEl.textContent = 'Belum ada file dipilih';
        }
    }

    function coParseCSVPreview(file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const text = e.target.result;
            const lines = text.split('\n').map(l => l.trim()).filter(l => l.length > 0);
            const tbody = document.getElementById('d2_preview_tbody');
            document.getElementById('d2_file_label').textContent = file.name;

            if (!tbody) return;
            if (lines.length < 2) {
                tbody.innerHTML = '<tr><td colspan="3" class="text-center text-warning py-3">File kosong atau format CSV tidak valid.</td></tr>';
                return;
            }

            let html = '';
            let rows = lines.slice(1, 6); // read up to 5 rows for preview

            rows.forEach(row => {
                const cols = row.split(/[,;]/);
                const nidn = cols[0] ? cols[0].replace(/^"|"$/g, '').trim() : '—';
                const nama = cols[1] ? cols[1].replace(/^"|"$/g, '').trim() : '—';
                const bkd  = cols[cols.length - 1] ? cols[cols.length - 1].replace(/^"|"$/g, '').trim() : 'M';
                const isM  = bkd.toUpperCase().includes('MEMENUHI') || bkd.toUpperCase() === 'M';

                html += `<tr>
                    <td>${nidn}</td>
                    <td>${nama}</td>
                    <td style="text-align:center;">
                        <span class="badge ${isM ? 'bg-label-success' : 'bg-label-danger'}">
                            ${isM ? 'Memenuhi (M)' : 'Tidak Memenuhi (TM)'}
                        </span>
                    </td>
                </tr>`;
            });
            tbody.innerHTML = html;
        };
        reader.readAsText(file);
    }

    // ── Select period (Panel 1 & 2) ──
    function coSelectPeriod(btn) {
        const wrap = btn.closest('.co-period-toggle');
        if (wrap) wrap.querySelectorAll('.co-period-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        const form = btn.closest('form');
        if (form) {
            const tableInput = form.querySelector('input[name="table"]');
            if (tableInput) tableInput.value = btn.dataset.table;
        }

        const info = document.getElementById('new_periode_info');
        if (info && btn.dataset.bkd) {
            info.innerHTML = 'BKD: ' + btn.dataset.bkd + ' &nbsp;|&nbsp; Bayar: ' + btn.dataset.bayar;
        }
    }

    function coSelectPeriodUpd(btn) {
        const wrap = btn.closest('.co-period-toggle');
        if (wrap) wrap.querySelectorAll('.co-period-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        const form = btn.closest('form');
        if (form) {
            const tableInput = form.querySelector('input[name="table"]');
            if (tableInput) tableInput.value = btn.dataset.table;
        }

        const vbox = document.getElementById('validasiResult');
        if (vbox) {
            vbox.classList.remove('show');
            vbox.innerHTML = '';
        }
    }

    // ── Update BKD & Pembayaran text dynamically when Year select changes ──
    function coUpdatePeriodeInfo() {
        const yrSelect = document.getElementById('new_tahun_select');
        if (!yrSelect) return;
        const yr = parseInt(yrSelect.value) || new Date().getFullYear();
        const yrLalu = yr - 1;
        const yrDepan = yr + 1;

        document.getElementById('new_tahun_val').value = yr;

        const btnGanjilTL = document.querySelector('#periodToggle .co-period-btn[data-table="p_sister_ganjil_tl"]');
        const btnGenapBJ  = document.querySelector('#periodToggle .co-period-btn[data-table="n_sister_genap_bj"]');
        const btnGenapTL  = document.querySelector('#periodToggle .co-period-btn[data-table="o_sister_genap_tl"]');

        if (btnGanjilTL) {
            btnGanjilTL.dataset.bkd = `Sept–Des ${yrLalu} & Jan–Feb ${yr}`;
            btnGanjilTL.dataset.bayar = `Maret–Agustus ${yr}`;
        }
        if (btnGenapBJ) {
            btnGenapBJ.dataset.bkd = `Maret–Agustus ${yr}`;
            btnGenapBJ.dataset.bayar = `Sept–Des ${yr} & Jan–Feb ${yrDepan}`;
        }
        if (btnGenapTL) {
            btnGenapTL.dataset.bkd = `Maret–Agustus ${yrLalu}`;
            btnGenapTL.dataset.bayar = `Sept–Des ${yrLalu} & Jan–Feb ${yr}`;
        }
    }

    // ── Combined Periode & Tahun Selector Helper ──
    function coUpdateCombinedPeriode(selectEl, tableInputId, tahunInputId) {
        if (!selectEl || !selectEl.value) return;
        const parts = selectEl.value.split('|');
        if (parts.length === 2) {
            const tableEl = document.getElementById(tableInputId);
            const tahunEl = document.getElementById(tahunInputId);
            if (tableEl) tableEl.value = parts[0];
            if (tahunEl) tahunEl.value = parts[1];
        }
    }

    // ── Select period (Panel 2) ──
    function coSelectPeriodUpd(btn) {
        document.querySelectorAll('#periodToggleUpd .co-period-btn').forEach(b => {
            b.classList.remove('active');
        });
        btn.classList.add('active');
        document.getElementById('upd_table_val').value = btn.dataset.table;
        // Reset validasi result when period changes
        const vbox = document.getElementById('validasiResult');
        vbox.classList.remove('show');
        vbox.innerHTML = '';
    }

    // ── File picker preview ──
    function coPreviewFile(input, nameId, previewId) {
        const file = input.files[0];
        const nameEl    = document.getElementById(nameId);
        const previewEl = document.getElementById(previewId);
        const textEl    = document.getElementById(previewId + '_text');

        if (!file) {
            nameEl.textContent = 'Belum ada file dipilih';
            previewEl.classList.remove('show');
            return;
        }

        nameEl.textContent = file.name;
        const sizeMB = (file.size / 1024).toFixed(1);
        if (textEl) textEl.textContent = file.name + '  ·  ' + sizeMB + ' KB  ·  Siap diunggah';
        previewEl.classList.add('show');
    }

    // ── Validasi (client-side: show file info + compare note) ──
    function coRunValidasi() {
        const fileInput = document.getElementById('upd_file_input');
        const vbox      = document.getElementById('validasiResult');

        if (!fileInput.files[0]) {
            vbox.innerHTML = '<i class="bx bx-error-circle" style="color:#f59e0b; margin-right:6px;"></i> Pilih file baru terlebih dahulu sebelum memvalidasi.';
            vbox.classList.add('show');
            return;
        }

        const file     = fileInput.files[0];
        const sizeMB   = (file.size / 1024).toFixed(1);
        const periodeEl = document.querySelector('#periodToggleUpd .co-period-btn.active');
        const label    = periodeEl ? periodeEl.textContent.trim() : '—';

        vbox.innerHTML = `
            <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px;">
                <i class="bx bx-info-circle" style="font-size:1.1rem; flex-shrink:0;"></i>
                <strong>Ringkasan Validasi</strong>
            </div>
            <div style="display:grid; grid-template-columns:130px 1fr; gap:2px 12px; font-size:0.77rem;">
                <span style="color:#64748b;">Periode tujuan</span><span><strong>${label}</strong></span>
                <span style="color:#64748b;">File baru</span><span>${file.name}</span>
                <span style="color:#64748b;">Ukuran file</span><span>${sizeMB} KB</span>
                <span style="color:#64748b;">Status</span>
                <span style="color:#15803d; font-weight:700;">
                    <i class="bx bx-check-circle"></i> File siap menggantikan data existing
                </span>
            </div>
            <div style="margin-top:8px; padding:6px 10px; background:#fff7ed; border:1px solid #fed7aa; border-radius:6px; font-size:0.75rem; color:#92400e;">
                <i class="bx bx-error" style="margin-right:4px;"></i>
                Data lama pada periode <strong>${label}</strong> akan <strong>digantikan sepenuhnya</strong> oleh file baru. Pastikan data sudah benar sebelum menyimpan.
            </div>`;
        vbox.classList.add('show');
    }

    // Legacy compat: keep old functions used by toggle genap
    function updateCutoffFileName(input, targetId) {
        var el = document.getElementById(targetId);
        if (el) el.value = input.files[0] ? input.files[0].name : 'Tidak ada file dipilih';
    }
    </script>
    @endif

    <div class="card mb-4 card-cutoff" id="cutoffDataContainer" style="border-radius: 12px; background: #ffffff; border: 1px solid #e2e8f0;">
        <div class="card-body pt-2.5 pb-4 px-4">
            <!-- Section 2: Pilih Periode Cut Off Sisternas -->
            <div>
                    <div class="d-flex justify-content-start align-items-center mb-2">
                        <div class="d-flex align-items-center gap-2.5">
                            <span class="me-1" style="font-size: 0.85rem; font-weight: 700; color: #475569;"><i class="bx bx-filter-alt me-1 text-primary"></i> Tahun:</span>
                            <select name="tahun_filter" id="tahunFilterSelect" class="form-select form-select-sm" style="width: 115px; border-color: #94a3b8; font-weight: 700; font-size: 0.85rem; border-radius: 6px; background-color: #f8fafc; padding: 4px 10px; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                                @php
                                    $yearsToDisplay = (isset($listTahun) && count($listTahun) > 0) ? $listTahun : range(2023, max((int)date('Y'), (int)$tahunSession));
                                @endphp
                                @foreach($yearsToDisplay as $y)
                                    <option value="{{ $y }}" {{ ($tahunSession == $y) ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row g-4 align-items-stretch mt-1 mb-3.5">
                        <!-- Card 1: Semester Ganjil (50% / col-md-6) -->
                        <div class="col-md-6 col-sm-12 d-flex">
                            <div class="btn-select-period p-3 rounded-3 d-flex flex-column justify-content-between w-100 h-100 glowing-active-card" data-value="p_sister_ganjil_tl" style="cursor: pointer; transition: all 0.2s ease;">
                                <div class="w-100">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="period-title fw-bold" style="font-size: 0.88rem; color: #435971;">
                                            <i class="bx bx-calendar text-primary me-1"></i> Semester Ganjil
                                        </span>
                                        <span class="badge bg-label-primary text-dark" style="font-size: 0.68rem; font-weight:700;">Tahun Lalu</span>
                                    </div>
                                    <div class="period-subtitle mb-2" style="font-size: 0.71rem; line-height: 1.35;">
                                        <div>
                                            <span style="color: #697a8d;">Pembayaran:</span> <span class="fw-semibold" style="color: #566a7f;">Maret - Agustus {{ $tahunSession }}</span>
                                        </div>
                                        <div style="margin-top: 1px;">
                                            <span style="color: #697a8d;">BKD:</span> <span class="fw-semibold" style="color: #566a7f;">Sept {{ $tahunLalu }} - Feb {{ $tahunSession }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex gap-2 align-items-center w-100 mt-auto">
                                    <div class="flex-fill">
                                        <div class="btn-stat-flat-memenuhi">
                                            <i class="bx bx-check" style="font-size: 0.88rem; font-weight: bold; flex-shrink: 0;"></i>
                                            <span>{{ number_format($statGanjilTL['m'], 0, ',', '.') }} memenuhi</span>
                                        </div>
                                    </div>
                                    <div class="flex-fill">
                                        <div class="btn-stat-flat-tm">
                                            <i class="bx bx-x" style="font-size: 0.88rem; font-weight: bold; flex-shrink: 0;"></i>
                                            <span>{{ number_format($statGanjilTL['tm'], 0, ',', '.') }} tidak memenuhi</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card 2: Semester Genap (50% / col-md-6) dengan Toggle Berjalan / Tahun Lalu -->
                        <div class="col-md-6 col-sm-12 d-flex">
                            @php
                                // Jika tahun yang dipilih < tahun sekarang → default ke Tahun Lalu
                                $isGenapBerjalan = ((int)$tahunSession >= (int)date('Y'));
                                $genapDefaultVal  = $isGenapBerjalan ? 'n_sister_genap_bj' : 'o_sister_genap_tl';
                            @endphp
                            <div class="btn-select-period p-3 rounded-3 d-flex flex-column justify-content-between w-100 h-100" id="cardGenap" data-value="{{ $genapDefaultVal }}" style="cursor: pointer; transition: all 0.2s ease;">
                                <div class="w-100">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="period-title fw-bold" style="font-size: 0.88rem; color: #435971;">
                                            <i class="bx bx-calendar text-success me-1"></i> Semester Genap
                                        </span>
                                        <!-- Toggle Pill Inside Genap Card -->
                                        <div class="custom-toggle-container" onclick="event.stopPropagation();">
                                            <button type="button" class="btn-toggle-option {{ $isGenapBerjalan ? 'active' : '' }}" id="btnCardGenapBJ" onclick="switchCardGenap('bj')">
                                                <i class="bx bx-calendar-event"></i> Berjalan
                                            </button>
                                            <button type="button" class="btn-toggle-option {{ !$isGenapBerjalan ? 'active' : '' }}" id="btnCardGenapTL" onclick="switchCardGenap('tl')">
                                                <i class="bx bx-history"></i> Tahun Lalu
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Genap Berjalan Info -->
                                    <div class="period-subtitle mb-2 genap-info-view {{ !$isGenapBerjalan ? 'd-none' : '' }}" id="genapBJInfo" style="font-size: 0.71rem; line-height: 1.35;">
                                        <div>
                                            <span style="color: #697a8d;">Pembayaran:</span> <span class="fw-semibold" style="color: #566a7f;">Sept {{ $tahunSession }} - Feb {{ $tahunDepan }}</span>
                                        </div>
                                        <div style="margin-top: 1px;">
                                            <span style="color: #697a8d;">BKD:</span> <span class="fw-semibold" style="color: #566a7f;">Maret - Agustus {{ $tahunSession }}</span>
                                        </div>
                                    </div>

                                    <!-- Genap Tahun Lalu Info (Hidden by default jika berjalan) -->
                                    <div class="period-subtitle mb-2 genap-info-view {{ $isGenapBerjalan ? 'd-none' : '' }}" id="genapTLInfo" style="font-size: 0.71rem; line-height: 1.35;">
                                        <div>
                                            <span style="color: #697a8d;">Pembayaran:</span> <span class="fw-semibold" style="color: #566a7f;">Sept {{ $tahunLalu }} - Feb {{ $tahunSession }}</span>
                                        </div>
                                        <div style="margin-top: 1px;">
                                            <span style="color: #697a8d;">BKD:</span> <span class="fw-semibold" style="color: #566a7f;">Maret - Agustus {{ $tahunLalu }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Stat Buttons Genap Berjalan -->
                                <div class="genap-stat-view w-100 mt-auto {{ !$isGenapBerjalan ? 'd-none' : '' }}" id="genapBJStats">
                                    <div class="d-flex gap-2 align-items-center w-100">
                                        <div class="flex-fill">
                                            <div class="btn-stat-flat-memenuhi">
                                                <i class="bx bx-check" style="font-size: 0.95rem; font-weight: bold; flex-shrink: 0;"></i>
                                                <span>{{ number_format($statGenapBJ['m'], 0, ',', '.') }} memenuhi</span>
                                            </div>
                                        </div>
                                        <div class="flex-fill">
                                            <div class="btn-stat-flat-tm">
                                                <i class="bx bx-x" style="font-size: 0.95rem; font-weight: bold; flex-shrink: 0;"></i>
                                                <span>{{ number_format($statGenapBJ['tm'], 0, ',', '.') }} tidak memenuhi</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Stat Buttons Genap Tahun Lalu (Hidden jika berjalan) -->
                                <div class="genap-stat-view w-100 mt-auto {{ $isGenapBerjalan ? 'd-none' : '' }}" id="genapTLStats">
                                    <div class="d-flex gap-2 align-items-center w-100">
                                        <div class="flex-fill">
                                            <div class="btn-stat-flat-memenuhi">
                                                <i class="bx bx-check" style="font-size: 0.95rem; font-weight: bold; flex-shrink: 0;"></i>
                                                <span>{{ number_format($statGenapTL['m'], 0, ',', '.') }} memenuhi</span>
                                            </div>
                                        </div>
                                        <div class="flex-fill">
                                            <div class="btn-stat-flat-tm">
                                                <i class="bx bx-x" style="font-size: 0.95rem; font-weight: bold; flex-shrink: 0;"></i>
                                                <span>{{ number_format($statGenapTL['tm'], 0, ',', '.') }} tidak memenuhi</span>
                                            </div>
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
                                        <th style="text-align: center;">TAHUN PERIODE</th>
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
                        <label for="create_tahun" class="form-label font-weight-bold">Tahun Pencairan / Periode</label>
                        <select class="form-select" id="create_tahun" name="tahun" required>
                            @php $curY = (int)date('Y'); @endphp
                            @for($y = 2023; $y <= $curY; $y++)
                                <option value="{{ $y }}" {{ $tahunSession == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
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
    let bkdStatusFilter = '';

    //datatable
    const cutOffTable = $('#cutoffTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.cutoff-sisternas") }}',
            data: function(d) {
                d.sisternas = $('#sisternasSelect').val();
                d.tahun = '{{ $tahunSession }}';
                d.bkd_status = bkdStatusFilter;
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
                data: 'tahun_periode',
                name: 'tahun_periode',
                searchable: true,
                orderable: true,
                className: 'text-center',
                defaultContent: '',
                render: function(data, type, row) {
                    if (data) {
                        var val = String(data).replace(/-/g, '/');
                        return `<span class="fw-bold text-dark">${val}</span>`;
                    }
                    const selectedTable = $('#sisternasSelect').val() || '';
                    const sem = selectedTable.includes('ganjil') ? '1' : '2';
                    const year = String(row.tahun || '{{ $tahunSession }}').replace(/-/g, '/');
                    if (year.includes('/')) {
                        return `<span class="fw-bold text-dark">${year}</span>`;
                    }
                    return `<span class="fw-bold text-dark">${year}/${sem}</span>`;
                }
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

    // Klik pada tombol statistik Memenuhi di dalam card
    $('.btn-select-period').on('click', '.btn-stat-flat-memenuhi', function(e) {
        e.stopPropagation(); // Stop bubbling to card click
        
        const card = $(this).closest('.btn-select-period');
        const selectedVal = card.data('value');
        const periodTitle = card.find('.period-title').text().trim();
        const bkdInfo = card.find('.period-subtitle').text().trim();

        bkdStatusFilter = 'M';
        $('#sisternasSelect').val(selectedVal); // set value
        cutOffTable.ajax.reload();

        // Update judul & deskripsi tabel bawah
        $('#selectedPeriodTitle').html('<i class="bx bx-list-check text-primary me-2"></i>Daftar Dosen: ' + periodTitle + ' (Memenuhi)');
        $('#selectedPeriodSubtitle').text(bkdInfo);

        // Reset & toggle active state
        $('.btn-select-period').removeClass('glowing-active-card active');
        card.addClass('glowing-active-card active');

        // Scroll smooth ke tabel daftar dosen
        $('html, body').animate({
            scrollTop: $("#cutoffDataContainer").offset().top - 80
        }, 300);
    });

    // Klik pada tombol statistik Tidak Memenuhi di dalam card
    $('.btn-select-period').on('click', '.btn-stat-flat-tm', function(e) {
        e.stopPropagation(); // Stop bubbling to card click
        
        const card = $(this).closest('.btn-select-period');
        const selectedVal = card.data('value');
        const periodTitle = card.find('.period-title').text().trim();
        const bkdInfo = card.find('.period-subtitle').text().trim();

        bkdStatusFilter = 'TM';
        $('#sisternasSelect').val(selectedVal); // set value
        cutOffTable.ajax.reload();

        // Update judul & deskripsi tabel bawah
        $('#selectedPeriodTitle').html('<i class="bx bx-list-check text-primary me-2"></i>Daftar Dosen: ' + periodTitle + ' (Tidak Memenuhi)');
        $('#selectedPeriodSubtitle').text(bkdInfo);

        // Reset & toggle active state
        $('.btn-select-period').removeClass('glowing-active-card active');
        card.addClass('glowing-active-card active');

        // Scroll smooth ke tabel daftar dosen
        $('html, body').animate({
            scrollTop: $("#cutoffDataContainer").offset().top - 80
        }, 300);
    });

    // Function toggle Card Genap (Berjalan vs Tahun Lalu)
    window.switchCardGenap = function(type) {
        const card = $('#cardGenap');
        const isBJ = (type === 'bj');
        const val  = isBJ ? 'n_sister_genap_bj' : 'o_sister_genap_tl';

        $('#btnCardGenapBJ').toggleClass('active', isBJ);
        $('#btnCardGenapTL').toggleClass('active', !isBJ);

        $('#genapBJInfo').toggleClass('d-none', !isBJ);
        $('#genapTLInfo').toggleClass('d-none', isBJ);
        $('#genapBJStats').toggleClass('d-none', !isBJ);
        $('#genapTLStats').toggleClass('d-none', isBJ);

        card.attr('data-value', val);
        card.data('value', val);

        bkdStatusFilter = '';
        $('#sisternasSelect').val(val).trigger('change');

        const periodSub = isBJ ? 'Genap Berjalan' : 'Genap Tahun Lalu';
        $('#selectedPeriodTitle').html('<i class="bx bx-list-check text-primary me-2"></i>Daftar Dosen: Semester Genap (' + periodSub + ')');

        $('.btn-select-period').removeClass('glowing-active-card active');
        card.addClass('glowing-active-card active');
    };

    // Klik pada Nav Pill Periode untuk memilih periode & me-load seluruh data dosen
    $('.btn-select-period').on('click', function(e) {
        // Prevent click if clicking inside buttons
        if ($(e.target).closest('.btn-stat-flat-memenuhi, .btn-stat-flat-tm, .custom-toggle-container').length > 0) {
            return;
        }

        const selectedVal = $(this).data('value');
        const periodTitle = $(this).find('.period-title').text().trim();
        const bkdInfo = $(this).find('.period-subtitle').text().trim();

        bkdStatusFilter = ''; // Reset filter
        $('#sisternasSelect').val(selectedVal).trigger('change');

        // Update judul & deskripsi tabel bawah
        $('#selectedPeriodTitle').html('<i class="bx bx-list-check text-primary me-2"></i>Daftar Dosen: ' + periodTitle);
        $('#selectedPeriodSubtitle').text(bkdInfo);

        // Reset & toggle active state (Hanya 1 card yang menyala)
        $('.btn-select-period').removeClass('glowing-active-card active');
        $(this).addClass('glowing-active-card active');

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
        
        // Set default tahun periode sesuai filter aktif
        const activeYear = $('#tahunFilterSelect').val() || '{{ $tahunSession }}';
        $('#create_tahun').val(activeYear);

        $('#createModal').modal('show');
    });

    // Jaga-jaga: saat modal akan ditampilkan, sinkronkan lagi label & hidden
    $('#createModal').on('show.bs.modal', function () {
        const selected = $('#sisternasSelect').val();
        const labelText = $('#sisternasSelect option:selected').text().trim();
        $('#create_sisternas').val(selected);
        $('#create_sisternas_label').val(labelText).attr('value', labelText).attr('placeholder', labelText);
        const activeYear = $('#tahunFilterSelect').val() || '{{ $tahunSession }}';
        $('#create_tahun').val(activeYear);
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
