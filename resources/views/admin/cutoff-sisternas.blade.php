@extends('layouts/contentNavbarLayout')

@section('title', 'Set Periode Sisternas - SPTJM Online')

@section('content')
@php
    $tahunSession = request('tahun') ?: (session('tahun') ?: date('Y'));
    $tahunLalu = $tahunSession - 1;
    $tahunDepan = $tahunSession + 1;
    $tahunListD3 = (isset($listTahun) && count($listTahun) > 0) ? $listTahun : range(2023, (int)date('Y'));
@endphp
<style>
    /* ===== DESAIN 3: Terpisah (Tahun | Periode | Bulan) ===== */
    .sp-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px 24px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        margin-bottom: 24px;
    }
    .sp-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 1.5px solid #f1f5f9;
        flex-wrap: wrap;
        gap: 12px;
    }
    .sp-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .sp-d3-grid {
        display: grid;
        grid-template-columns: 1fr 1.35fr;
        gap: 16px;
    }
    @media (max-width: 992px) {
        .sp-d3-grid { grid-template-columns: 1fr; }
    }
    .sp-d3-block {
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        padding: 16px;
        display: flex;
        flex-direction: column;
    }
    .sp-d3-block-header {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 1px solid #e2e8f0;
    }
    .sp-d3-step-num {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: #0f172a;
        color: #ffffff;
        font-size: 0.75rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .sp-d3-block-title {
        font-size: 0.85rem;
        font-weight: 700;
        color: #0f172a;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .sp-d3-year-list {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .sp-d3-year-item {
        padding: 9px 14px;
        border-radius: 7px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        font-size: 0.86rem;
        font-weight: 700;
        color: #334155;
        cursor: pointer;
        transition: all 0.15s ease;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .sp-d3-year-item:hover {
        border-color: #3b82f6;
        color: #2563eb;
        background: #eff6ff;
    }
    .sp-d3-year-item.active {
        background: #0f172a;
        border-color: #0f172a;
        color: #ffffff;
    }
    .sp-d3-periode-list {
        display: flex;
        flex-direction: column;
        gap: 6px;
        max-height: 280px;
        overflow-y: auto;
    }
    .sp-d3-periode-item input[type="radio"] { display: none; }
    .sp-d3-periode-item label {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 12px;
        border-radius: 7px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        font-size: 0.82rem;
        font-weight: 600;
        color: #334155;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .sp-d3-periode-item input[type="radio"]:checked + label {
        background: #1d4ed8;
        border-color: #1d4ed8;
        color: #ffffff;
    }
    .sp-d3-month-list {
        display: grid;
        grid-auto-flow: column;
        grid-template-rows: repeat(6, auto);
        grid-template-columns: 1fr 1fr;
        gap: 6px;
        max-height: 280px;
        overflow-y: auto;
    }
    .sp-d3-month-item input[type="checkbox"] { display: none; }
    .sp-d3-month-item label {
        display: flex;
        align-items: center;
        gap: 7px;
        padding: 7px 10px;
        border-radius: 6px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        font-size: 0.8rem;
        font-weight: 500;
        color: #334155;
        cursor: pointer;
        transition: all 0.15s ease;
        user-select: none;
    }
    .sp-d3-month-item label::before {
        content: '';
        width: 14px;
        height: 14px;
        border-radius: 3px;
        border: 1.5px solid #94a3b8;
        background: #fff;
        flex-shrink: 0;
    }
    .sp-d3-month-item input[type="checkbox"]:checked + label {
        background: #eff6ff;
        border-color: #3b82f6;
        color: #1d4ed8;
        font-weight: 700;
    }
    .sp-d3-month-item input[type="checkbox"]:checked + label::before {
        background: #2563eb;
        border-color: #2563eb;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='9' height='9' viewBox='0 0 24 24' fill='none' stroke='%23ffffff' stroke-width='3.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='20 6 9 17 4 12'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: center;
        background-size: 9px;
    }
    .sp-d3-mapping-card {
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        margin-top: 20px;
        padding: 18px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
    }
    .sp-d3-mapping-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 14px;
        padding-bottom: 10px;
        border-bottom: 1.5px solid #f1f5f9;
        flex-wrap: wrap;
        gap: 10px;
    }
    .sp-d3-mapping-title {
        font-size: 0.9rem;
        font-weight: 700;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 8px;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .sp-d3-table-wrap {
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #cbd5e1;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }
    .sp-d3-table {
        width: 100%;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }
    .sp-d3-table th {
        font-size: 0.76rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 10px 14px;
        text-align: center !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #cbd5e1;
        border-right: 1px solid #e2e8f0;
    }
    .sp-d3-table th:last-child {
        border-right: none;
    }
    .sp-d3-table th.th-group-bayar {
        background-color: #eff6ff !important;
        color: #1d4ed8 !important;
        border-bottom: 1.5px solid #bfdbfe;
        border-right: 1.5px solid #cbd5e1;
        text-align: center !important;
        vertical-align: middle !important;
        font-size: 0.78rem;
    }
    .sp-d3-table th.th-group-pembuat {
        background-color: #f0fdf4 !important;
        color: #15803d !important;
        border-bottom: 1.5px solid #bbf7d0;
        text-align: center !important;
        vertical-align: middle !important;
        font-size: 0.78rem;
    }
    .sp-d3-table th.th-sub {
        background-color: #f8fafc !important;
        color: #475569 !important;
        border-bottom: 1px solid #cbd5e1;
        font-size: 0.74rem;
        padding: 8px 12px;
        text-align: center !important;
        vertical-align: middle !important;
    }
    .sp-d3-table td {
        padding: 8px 10px;
        vertical-align: middle;
        border-top: 1px solid #e2e8f0;
        border-right: 1px solid #f1f5f9;
        background: #ffffff;
    }
    .sp-d3-table td:last-child {
        border-right: none;
    }
    .sp-d3-input {
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 7px 12px;
        font-size: 0.85rem;
        height: 38px;
        width: 100%;
        text-align: center;
        font-weight: 600;
        color: #1e293b;
        background-color: #ffffff;
        box-shadow: inset 0 1px 2px rgba(0,0,0,0.02);
        transition: all 0.15s ease;
    }
    .sp-d3-input[readonly] {
        cursor: default;
        user-select: none;
    }
    .sp-d3-input:focus {
        border-color: #cbd5e1;
        box-shadow: none;
        outline: none;
        background-color: #ffffff;
    }
    .sp-preview {
        display: none;
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 9px;
        padding: 10px 14px;
        margin-top: 14px;
        align-items: flex-start;
        gap: 9px;
    }
    .sp-preview.show { display: flex; }
    .sp-preview-icon { font-size: 1.1rem; color: #16a34a; flex-shrink: 0; margin-top: 2px; }
    .sp-preview-label {
        font-size: 0.70rem;
        font-weight: 700;
        color: #16a34a;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-bottom: 2px;
    }
    .sp-preview-tahun { font-size: 0.86rem; font-weight: 700; color: #14532d; margin-bottom: 1px; }
    .sp-preview-months { font-size: 0.80rem; color: #15803d; line-height: 1.4; }
    .sp-footer {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
        padding-top: 14px;
        margin-top: 14px;
        border-top: 1.5px solid #f1f5f9;
    }
    .sp-btn-reset {
        padding: 7px 16px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        font-size: 0.82rem;
        font-weight: 600;
        color: #475569;
        cursor: pointer;
        transition: all 0.15s ease;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .sp-btn-reset:hover { background: #f1f5f9; color: #0f172a; }
    .sp-btn-save {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 20px;
        border-radius: 8px;
        border: none;
        background: #0f172a;
        color: #ffffff;
        font-size: 0.82rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.15s ease;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.15);
    }
    .sp-btn-save:hover { background: #1e293b; transform: translateY(-1px); }
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
    /* Card Style Semester Ganjil & Genap (Pure Color Fill - No Borders) */
    .btn-select-period {
        border-radius: 12px !important;
        background: #ffffff !important;
        border: none !important;
        transition: all 0.25s ease-in-out !important;
        cursor: pointer !important;
        width: 100% !important;
    }
    
    /* Semester Ganjil Active State (Pure Blue Background Fill) */
    .btn-select-period.glowing-active-card {
        border: none !important;
        background-color: #dbeafe !important;
        box-shadow: 0 6px 20px rgba(59, 130, 246, 0.22) !important;
        border-radius: 12px !important;
    }
    .btn-select-period.glowing-active-card .period-title {
        color: #1e3a8a !important;
    }
    .btn-select-period.glowing-active-card .period-title i {
        color: #2563eb !important;
    }
    .btn-select-period.glowing-active-card .badge.bg-label-primary {
        background-color: #2563eb !important;
        color: #ffffff !important;
    }

    /* Semester Genap Active State (Pure Green Background Fill) */
    #cardGenap.glowing-active-card {
        border: none !important;
        background-color: #dcfce7 !important;
        box-shadow: 0 6px 20px rgba(22, 163, 74, 0.22) !important;
        border-radius: 12px !important;
    }
    #cardGenap.glowing-active-card .period-title {
        color: #14532d !important;
    }
    #cardGenap.glowing-active-card .period-title i {
        color: #16a34a !important;
    }

    /* Inactive Card State (Plain White & Neutral Gray - No Borders) */
    .btn-select-period:not(.glowing-active-card) {
        border: none !important;
        background-color: #f8fafc !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04) !important;
        border-radius: 12px !important;
    }
    .btn-select-period:not(.glowing-active-card):hover {
        background-color: #f1f5f9 !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08) !important;
    }
    .btn-select-period:not(.glowing-active-card) .period-title {
        color: #64748b !important;
    }
    .btn-select-period:not(.glowing-active-card) .period-title i {
        color: #94a3b8 !important;
    }
    .btn-select-period:not(.glowing-active-card) .badge {
        background-color: #e2e8f0 !important;
        color: #475569 !important;
    }
    
    .btn-stat-flat-memenuhi {
        border-radius: 6px !important;
        padding: 6px 10px !important;
        font-size: 0.74rem !important;
        font-weight: 700 !important;
        border: none !important;
        transition: all 0.15s ease !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 5px !important;
        width: 100% !important;
        white-space: nowrap !important;
        user-select: none !important;
        cursor: pointer !important;
    }
    .btn-stat-flat-tm {
        border-radius: 6px !important;
        padding: 6px 10px !important;
        font-size: 0.74rem !important;
        font-weight: 700 !important;
        border: none !important;
        transition: all 0.15s ease !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 5px !important;
        width: 100% !important;
        white-space: nowrap !important;
        user-select: none !important;
        cursor: pointer !important;
    }

    /* Standard Active Card Stat Buttons */
    .glowing-active-card .btn-stat-flat-memenuhi {
        background-color: #ffffff !important;
        color: #15803d !important;
        border: none !important;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05) !important;
    }
    .glowing-active-card .btn-stat-flat-tm {
        background-color: #ffffff !important;
        color: #b91c1c !important;
        border: none !important;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05) !important;
    }

    /* Inactive Card Stat Buttons */
    .btn-select-period:not(.glowing-active-card) .btn-stat-flat-memenuhi,
    .btn-select-period:not(.glowing-active-card) .btn-stat-flat-tm {
        background-color: #e2e8f0 !important;
        color: #64748b !important;
        border: none !important;
    }
    .btn-select-period:not(.glowing-active-card) .btn-stat-flat-memenuhi:hover,
    .btn-select-period:not(.glowing-active-card) .btn-stat-flat-tm:hover {
        background-color: #cbd5e1 !important;
        color: #334155 !important;
    }

    /* Stat Filter Active States */
    .btn-stat-flat-memenuhi.active-stat-filter {
        background-color: #16a34a !important;
        color: #ffffff !important;
        border: none !important;
        opacity: 1 !important;
        box-shadow: 0 2px 8px rgba(22, 163, 74, 0.35) !important;
    }
    .btn-stat-flat-tm.active-stat-filter {
        background-color: #dc2626 !important;
        color: #ffffff !important;
        border: none !important;
        opacity: 1 !important;
        box-shadow: 0 2px 8px rgba(220, 38, 38, 0.35) !important;
    }
    .btn-select-period.has-stat-filter .btn-stat-flat-memenuhi:not(.active-stat-filter),
    .btn-select-period.has-stat-filter .btn-stat-flat-tm:not(.active-stat-filter) {
        opacity: 0.7 !important;
    }

    /* Tactile Hover States */
    .glowing-active-card .btn-stat-flat-memenuhi:not(.active-stat-filter):hover {
        background-color: #dcfce7 !important;
    }
    .glowing-active-card .btn-stat-flat-tm:not(.active-stat-filter):hover {
        background-color: #fee2e2 !important;
    }
    .btn-stat-flat-memenuhi:active,
    .btn-stat-flat-tm:active {
        transform: translateY(0) !important;
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


        {{-- ======================================================== --}}
        {{-- ===== PANEL SET PERIODE SISTERNAS (DESAIN 3 FIX) ===== --}}
        {{-- ======================================================== --}}
        <div class="sp-card mb-4">
            <div class="sp-header">
                <h5 class="sp-title">
                    <i class="bx bx-cog text-primary" style="font-size: 1.25rem;"></i>
                    SET PERIODE SISTERNAS
                </h5>
            </div>

            <form action="{{ route('admin.cutoff-sisternas.upload') }}" method="POST" enctype="multipart/form-data" class="uploadForm" id="spFormD3">
                @csrf
                <input type="hidden" name="table" id="d1_new_table_val" value="n_sister_genap_bj">

                <div class="sp-d3-grid">
                    {{-- Step 1: Dropdown Pemilihan, Pilih Tahun & Periode Laporan --}}
                    <div class="sp-d3-block">
                        <div class="sp-d3-block-header">
                            <span class="sp-d3-step-num">1</span>
                            <span class="sp-d3-block-title">Periode Pelaporan</span>
                        </div>
                        <div class="row g-2 mb-3 mt-1">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary mb-1" style="font-size:0.75rem;" for="spJenisUsulan">PEMILIHAN USULAN</label>
                                <select id="spJenisUsulan" class="form-select" name="jenis_usulan" onchange="updatePreviewD3()" required style="border-radius:7px; font-size:0.85rem; font-weight:600; border-color: #cbd5e1; background-color: #ffffff; color: #1e293b;">
                                    <option value="" disabled>-- PILIH --</option>
                                    <option value="SPTJM" selected>SPTJM</option>
                                    <option value="TUKIN">TUKIN</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary mb-1" style="font-size:0.75rem;">TAHUN LAPORAN</label>
                                <select name="tahun" id="d1_new_tahun_val" class="form-select" onchange="onD3TahunChange(this.value)" style="border-radius:7px; font-size:0.85rem; font-weight:700; border-color: #cbd5e1; background-color: #ffffff; color: #1e293b;">
                                    @foreach($tahunListD3 as $y)
                                        <option value="{{ $y }}" {{ ($tahunSession == $y) ? 'selected' : '' }}>Tahun {{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-1">
                            <label class="form-label fw-bold text-secondary mb-1" style="font-size:0.75rem;">PERIODE LAPORAN</label>
                            <div class="d-flex flex-row gap-2 mt-1">
                                <div class="sp-d3-month-item flex-grow-1">
                                    <input type="checkbox" name="sp_periode_d3_cb[]" id="d3_periode_cb_1" value="p_sister_ganjil_tl" onchange="onD3PeriodeCheckboxChange(this)">
                                    <label for="d3_periode_cb_1" style="font-weight: 600; justify-content: center; padding: 9px 12px;">1 (Ganjil)</label>
                                </div>
                                <div class="sp-d3-month-item flex-grow-1">
                                    <input type="checkbox" name="sp_periode_d3_cb[]" id="d3_periode_cb_2" value="n_sister_genap_bj" checked onchange="onD3PeriodeCheckboxChange(this)">
                                    <label for="d3_periode_cb_2" style="font-weight: 600; justify-content: center; padding: 9px 12px;">2 (Genap)</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Step 2: Periode Pembayaran --}}
                    <div class="sp-d3-block">
                        <div class="sp-d3-block-header" style="justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span class="sp-d3-step-num">2</span>
                                <span class="sp-d3-block-title">Periode Pembayaran</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 6px; background: #ffffff; padding: 4px 10px; border-radius: 7px; border: 1px solid #cbd5e1; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                                <input type="checkbox" id="d3SelectAllBulan" onchange="toggleD3SelectAll(this)" style="cursor: pointer; width: 15px; height: 15px; accent-color: #2563eb; margin: 0;">
                                <label for="d3SelectAllBulan" style="font-size: 0.75rem; font-weight: 700; color: #334155; cursor: pointer; user-select: none; margin: 0; white-space: nowrap;">Pilih Semua Bulan</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="d3_tahun_pembayaran_select" class="form-label fw-bold text-secondary mb-1" style="font-size:0.75rem;">
                                TAHUN PEMBAYARAN
                            </label>
                            <select name="tahun_pembayaran" id="d3_tahun_pembayaran_select" class="form-select" onchange="updatePreviewD3()" style="border-radius:7px; font-size:0.85rem; font-weight:700; border-color: #cbd5e1; background-color: #ffffff; color: #1e293b;">
                                @foreach($tahunListD3 as $y)
                                    <option value="{{ $y }}" {{ ($tahunSession + 1 == $y) ? 'selected' : '' }}>Tahun {{ $y }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="form-label fw-bold text-secondary mb-1" style="font-size:0.75rem;">BULAN PEMBAYARAN</label>
                            <div class="sp-d3-month-list">
                                @php
                                    $bulanD3Map = [
                                        'januari'=>'Januari','februari'=>'Februari','maret'=>'Maret',
                                        'april'=>'April','mei'=>'Mei','juni'=>'Juni',
                                        'juli'=>'Juli','agustus'=>'Agustus','september'=>'September',
                                        'oktober'=>'Oktober','november'=>'November','desember'=>'Desember'
                                    ];
                                @endphp
                                @foreach($bulanD3Map as $bKey => $bName)
                                    <div class="sp-d3-month-item">
                                        <input type="checkbox" name="sp_bulan_d3[]" id="d3_bulan_{{ $bKey }}" value="{{ $bKey }}" onchange="updatePreviewD3()">
                                        <label for="d3_bulan_{{ $bKey }}">{{ $bName }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Upload CSV File Section (Step 3) --}}
                <div class="sp-d3-mapping-card mt-3">
                    <div class="sp-d3-mapping-header">
                        <div class="sp-d3-mapping-title">
                            <span class="sp-d3-step-num" style="background: #0f172a;">3</span>
                            <span>Upload File Cut Off Sisternas (CSV)</span>
                        </div>
                        <a href="{{ route('admin.cutoff-sisternas.template') }}" class="text-primary text-decoration-none fw-semibold d-inline-flex align-items-center gap-1" style="font-size:0.82rem;">
                            <i class="bx bx-download fs-5"></i> Unduh contoh CSV
                        </a>
                    </div>

                    <input type="file" name="dokumen" required id="d1_new_file_input" style="display:none;" accept=".csv" onchange="coPreviewFileD1(this)">
                    <div class="position-relative d-flex align-items-center justify-content-center px-4 py-3.5 rounded-3" onclick="document.getElementById('d1_new_file_input').click()" style="border: 1.5px dashed #cbd5e1; background: #f8fafc; cursor: pointer; transition: all 0.2s ease; border-radius: 10px !important; min-height: 58px;" onmouseover="this.style.borderColor='#2563eb'; this.style.background='#eff6ff';" onmouseout="this.style.borderColor='#cbd5e1'; this.style.background='#f8fafc';">
                        <div class="d-flex align-items-center gap-2.5">
                            <i class="bx bx-cloud-upload fs-4 text-primary"></i>
                            <div>
                                <span class="fw-bold text-primary" style="font-size:0.88rem;" id="d1_file_dropzone_text">Klik untuk unggah file CSV</span>
                                <span class="text-muted ms-1" style="font-size:0.82rem;" id="d1_file_subtext">· maks. 10 MB</span>
                            </div>
                        </div>
                        <button type="button" id="d1_remove_file_btn" class="btn btn-sm d-none position-absolute" onclick="event.stopPropagation(); coResetFileD1();" title="Batal upload / Hapus file" style="right: 16px; width: 28px; height: 28px; border-radius: 50%; padding: 0; display: inline-flex; align-items: center; justify-content: center; background-color: #fee2e2; color: #dc2626; border: 1px solid #fecdd3; transition: all 0.2s ease;">
                            <i class="bx bx-x fs-4"></i>
                        </button>
                    </div>
                     {{-- Shared Preview Box --}}
                <div class="sp-preview mt-3" id="spPreview">
                    <i class="bx bx-check-circle sp-preview-icon"></i>
                    <div>
                        <div class="sp-preview-label">Periode yang akan di-set</div>
                        <div class="sp-preview-tahun" id="spPreviewTahun"></div>
                        <div class="sp-preview-months" id="spPreviewMonths"></div>
                    </div>
                </div>

                {{-- Footer Actions (Konfirmasi & Cek Perubahan di Atas) --}}
                <div class="sp-footer">
                    <button type="button" class="sp-btn-reset" onclick="resetFormD3()">
                        <i class="bx bx-reset"></i> Reset
                    </button>
                    <button type="button" id="d1_check_diff_btn" class="sp-btn-save" onclick="coCheckDiffD1()">
                        <i class="bx bx-search-alt-2"></i> Konfirmasi &amp; Cek Perubahan Data
                    </button>
                </div>             </div>

                {{-- Setting Pemetaan Periode Bayar & Pembayaran (Step 4 - Ditaruh Di Bawah Tombol Reset & Simpan) --}}
                <div class="sp-d3-mapping-card mt-4">
                    <div class="sp-d3-mapping-header">
                        <div class="sp-d3-mapping-title">
                            <span class="sp-d3-step-num" style="background: #2563eb;">4</span>
                            <span>Daftar Periode Pelaporan & Pembayaran</span>
                        </div>
                    </div>

                    <div class="sp-d3-table-wrap table-responsive text-nowrap">
                        <table class="table sp-d3-table" id="spD3MappingTable">
                            <thead>
                                <tr>
                                    <th colspan="3" class="th-group-bayar text-center align-middle" style="text-align: center !important; vertical-align: middle !important;">PERIODE LAPORAN</th>
                                    <th colspan="2" class="th-group-pembuat text-center align-middle" style="text-align: center !important; vertical-align: middle !important;">PERIODE PEMBAYARAN</th>
                                    <th rowspan="2" width="60px" class="text-center align-middle" style="background-color: #f8fafc; color: #475569; text-align: center !important; vertical-align: middle !important;">AKSI</th>
                                </tr>
                                <tr>
                                    <th class="th-sub text-center" style="text-align: center;">Usulan</th>
                                    <th class="th-sub text-center" style="text-align: center;">Tahun</th>
                                    <th class="th-sub text-center" style="text-align: center;">Periode Laporan</th>
                                    <th class="th-sub text-center" style="text-align: center;">Tahun</th>
                                    <th class="th-sub text-center" style="text-align: center;">Periode Pembayaran</th>
                                </tr>
                            </thead>
                            <tbody id="spD3MappingTbody">
                                {{-- Kosong saat awal, terisi otomatis setelah data tersimpan ke database --}}
                                <tr>
                                    <td colspan="6" class="text-center text-muted" style="padding: 20px; font-size: 0.84rem; font-style: italic;">
                                        <i class="bx bx-info-circle me-1"></i> Belum ada data. Upload file CSV dan simpan untuk mengisi tabel ini.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Diff Table Box --}}
                <div id="d1_diff_box" style="display: none;" class="mt-3 pt-3 border-top">
                    <div class="d-flex justify-content-between align-items-center mb-2.5">
                        <span class="fw-bold text-dark" style="font-size:0.88rem;"><i class="bx bx-list-check text-primary me-1"></i> Pratinjau Perubahan Data:</span>
                        <span class="badge bg-label-info text-dark" style="font-size:0.73rem; font-weight:600;"><i class="bx bx-info-circle me-1"></i> Centang kolom Aksi (kanan) jika data ingin dihapus</span>
                    </div>


                    <div class="table-responsive rounded-3 border mb-3" style="box-shadow: 0 1px 4px rgba(0,0,0,0.04); border-color: #e2e8f0 !important;">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.84rem;">
                            <thead style="background-color: #f8fafc; border-bottom: 1.5px solid #e2e8f0;">
                                <tr>
                                    <th style="padding: 11px 16px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 0.72rem; letter-spacing: 0.05em;">NIDN</th>
                                    <th style="padding: 11px 16px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 0.72rem; letter-spacing: 0.05em;">NUPTK</th>
                                    <th style="padding: 11px 16px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 0.72rem; letter-spacing: 0.05em;">Nama Dosen</th>
                                    <th style="padding: 11px 16px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 0.72rem; letter-spacing: 0.05em; text-align: center;">Kesimpulan BKD Lama</th>
                                    <th style="padding: 11px 16px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 0.72rem; letter-spacing: 0.05em; text-align: center;">Kesimpulan BKD Baru</th>
                                    <th style="padding: 11px 16px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 0.72rem; letter-spacing: 0.05em; text-align: center; width: 130px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="d1_diff_tbody">
                            </tbody>
                        </table>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold" style="background-color:#2563eb; border-color:#2563eb; border-radius:10px; font-size:0.92rem;">
                        <i class="bx bx-save me-1"></i> Simpan Setting &amp; Data Cut Off
                    </button>
                </div>

            </form>
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

    // Helper format NUPTK agar bersih jika terlanjur ter-expand oleh Excel
    function formatNuptk(val) {
        if (!val || val === '-' || val === '—') return '—';
        let s = String(val).trim();
        
        // Format scientific notation dari Excel (misal: 4.43E+15, 4,43E+15, 4.43e+18)
        if (/[eE]/.test(s)) {
            try {
                const num = Number(s.replace(',', '.'));
                if (!isNaN(num)) {
                    s = BigInt(Math.round(num)).toString();
                }
            } catch(e) {}
        }

        // NUPTK standar di Indonesia adalah 16 digit.
        // Jika terlanjur ter-expand Excel menjadi 19+ digit dengan trailing zero (misal 4430000000000000000)
        if (s.length > 16 && /^\d+0{3,}$/.test(s)) {
            s = s.substring(0, 16);
        }

        return s;
    }

    // ── Helper fungsi periksa validitas tahun & periode nama file ──
    function checkFileValidity(fileName, selectedYear, selectedPeriod) {
        if (!fileName) return { valid: true };
        const fileLower = fileName.toLowerCase();

        // 1. CEK TAHUN (2024 vs 2023, atau 24-1 vs 23-1)
        if (selectedYear) {
            const year4 = String(selectedYear); // misal "2024"
            const year2 = year4.length === 4 ? year4.substring(2) : year4; // misal "24"

            const match4 = fileLower.match(/20\d{2}/g);
            if (match4 && match4.length > 0) {
                const hasMatching4 = match4.includes(year4);
                if (!hasMatching4) {
                    return { valid: false, title: 'Tahun File Tidak Sesuai!', reason: `Nama file <strong>${fileName}</strong> terdeteksi mengandung tahun <strong>${match4[0]}</strong>, sedangkan tahun yang dipilih adalah <strong>${year4}</strong>.` };
                }
            } else {
                const match2 = fileLower.match(/(?:^|[^0-9])([2-9][0-9])(?:[-_\s\.\,]|$)/g);
                if (match2 && match2.length > 0) {
                    let found2DigitYears = [];
                    for (let m of match2) {
                        const num = m.replace(/[^0-9]/g, '');
                        if (num.length === 2 && parseInt(num) >= 20 && parseInt(num) <= 35) {
                            found2DigitYears.push(num);
                        }
                    }
                    if (found2DigitYears.length > 0) {
                        const hasMatching2 = found2DigitYears.includes(year2);
                        if (!hasMatching2) {
                            return { valid: false, title: 'Tahun File Tidak Sesuai!', reason: `Nama file <strong>${fileName}</strong> terdeteksi mengandung tahun <strong>20${found2DigitYears[0]}</strong>, sedangkan tahun yang dipilih adalah <strong>${year4}</strong>.` };
                        }
                    }
                }
            }
        }

        // 2. CEK PERIODE (Ganjil / 1 vs Genap / 2)
        if (selectedPeriod) {
            const periodStr = String(selectedPeriod).toLowerCase();
            const isSelectedGanjil = periodStr.includes('ganjil') || periodStr === '1' || periodStr.includes('p_sister');
            const isSelectedGenap = periodStr.includes('genap') || periodStr === '2' || periodStr.includes('n_sister') || periodStr.includes('o_sister');

            const hasGanjilMark = fileLower.includes('ganjil') || /[-_\s]1(?:[-_\s\.]|$)/.test(fileLower) || fileLower.includes('24-1') || fileLower.includes('23-1') || fileLower.includes('25-1');
            const hasGenapMark  = fileLower.includes('genap') || /[-_\s]2(?:[-_\s\.]|$)/.test(fileLower) || fileLower.includes('24-2') || fileLower.includes('23-2') || fileLower.includes('25-2');

            // File WAJIB mengandung kata "ganjil" atau "genap" sesuai periode yang dipilih
            if (isSelectedGenap) {
                if (!fileLower.includes('genap')) {
                    return { valid: false, title: 'Nama File Tidak Sesuai!', reason: `Nama file <strong>${fileName}</strong> tidak memuat kata <strong>"genap"</strong>.<br><br>Untuk periode <strong>Genap (Semester 2)</strong>, nama file CSV <u>wajib memuat kata "genap"</u> (contoh: <code>sister_genap_${selectedYear ? String(selectedYear).slice(-2) : '26'}-2.csv</code>).` };
                }
            } else if (isSelectedGanjil) {
                if (!fileLower.includes('ganjil')) {
                    return { valid: false, title: 'Nama File Tidak Sesuai!', reason: `Nama file <strong>${fileName}</strong> tidak memuat kata <strong>"ganjil"</strong>.<br><br>Untuk periode <strong>Ganjil (Semester 1)</strong>, nama file CSV <u>wajib memuat kata "ganjil"</u> (contoh: <code>sister_ganjil_tl ${selectedYear ? String(selectedYear).slice(-2) : '26'}-1.csv</code>).` };
                }
            }
        }

        return { valid: true };
    }

    // ── Single Card Preview & Diff Checker Logic ──
    function coPreviewFileD1(input) {
        const dropzoneText = document.getElementById('d1_file_dropzone_text');
        const dropzoneSubtext = document.getElementById('d1_file_subtext');
        const removeBtn = document.getElementById('d1_remove_file_btn');
        const checkDiffBtn = document.getElementById('d1_check_diff_btn');

        if (!input.files || !input.files[0]) {
            coResetFileD1();
            return;
        }

        const file = input.files[0];
        const fileName = file.name.toLowerCase();

        // Cek tahun & periode di nama file vs yang dipilih
        const selectedYr = document.getElementById('d1_new_tahun_val');
        const selectedYear = selectedYr ? selectedYr.value : '';
        const selectedPeriod = typeof getSelectedD3PeriodeVal === 'function' ? getSelectedD3PeriodeVal() : '';
        const fileCheck = checkFileValidity(fileName, selectedYear, selectedPeriod);

        if (!fileCheck.valid) {
            Swal.fire({
                icon: 'error',
                title: fileCheck.title,
                html: fileCheck.reason + `<br><br>Pratinjau perubahan tidak dapat ditampilkan. Harap pilih file CSV yang sesuai.`,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Ganti File'
            });
            input.value = '';
            coResetFileD1();
            return;
        }

        if (dropzoneText) {
            dropzoneText.innerHTML = '<span class="text-success me-1"><i class="bx bx-check-circle"></i> ' + file.name + '</span>';
        }
        if (dropzoneSubtext) {
            const yearMatch = fileName.match(/20\d{2}/);
            const yearInfo = yearMatch ? ` · Tahun terdeteksi: ${yearMatch[0]}` : '';
            dropzoneSubtext.textContent = '· (' + (file.size/1024).toFixed(1) + ' KB) siap diperiksa' + yearInfo;
        }
        if (removeBtn) {
            removeBtn.classList.remove('d-none');
            removeBtn.classList.add('d-inline-flex');
        }
        if (checkDiffBtn) {
            checkDiffBtn.style.display = 'inline-flex';
            checkDiffBtn.classList.remove('d-none');
        }
    }

    function coResetFileD1() {
        const fileInput = document.getElementById('d1_new_file_input');
        const dropzoneText = document.getElementById('d1_file_dropzone_text');
        const dropzoneSubtext = document.getElementById('d1_file_subtext');
        const removeBtn = document.getElementById('d1_remove_file_btn');
        const diffBox = document.getElementById('d1_diff_box');
        const checkDiffBtn = document.getElementById('d1_check_diff_btn');

        if (fileInput) fileInput.value = '';
        if (dropzoneText) dropzoneText.innerHTML = 'Klik untuk unggah file CSV';
        if (dropzoneSubtext) dropzoneSubtext.textContent = '· maks. 10 MB';
        if (removeBtn) {
            removeBtn.classList.add('d-none');
            removeBtn.classList.remove('d-inline-flex');
        }
        if (diffBox) diffBox.style.display = 'none';
    }

    function coCheckDiffD1() {
        const fileInput = document.getElementById('d1_new_file_input');
        const diffBox = document.getElementById('d1_diff_box');

        // Sembunyikan diffBox terlebih dahulu sebelum periksa
        if (diffBox) diffBox.style.display = 'none';

        if (!fileInput || !fileInput.files[0]) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'info',
                    title: 'Pilih File CSV',
                    text: 'Silakan pilih file CSV terlebih dahulu sebelum mengecek perubahan.',
                    confirmButtonColor: '#f59e0b'
                });
            } else {
                alert('Silakan pilih file CSV terlebih dahulu sebelum mengecek perubahan.');
            }
            return;
        }

        // Validasi Bulan Pembayaran (Wajib dicentang minimal 1 bulan)
        const checkedBulan = document.querySelectorAll('input[name="sp_bulan_d3[]"]:checked');
        if (checkedBulan.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Bulan Pembayaran Belum Dipilih!',
                html: 'Silakan centang setidaknya <strong>satu bulan pembayaran</strong> pada Step 2 (Periode Pembayaran) sebelum mengecek perubahan data.',
                confirmButtonColor: '#f59e0b',
                confirmButtonText: 'Pilih Bulan'
            });
            return;
        }

        // Validasi tahun & periode nama file vs yang dipilih
        const selectedYrEl = document.getElementById('d1_new_tahun_val');
        const selectedYear = selectedYrEl ? selectedYrEl.value : '';
        const selectedPeriod = typeof getSelectedD3PeriodeVal === 'function' ? getSelectedD3PeriodeVal() : '';
        const fileName = fileInput.files[0].name.toLowerCase();
        const fileCheck = checkFileValidity(fileName, selectedYear, selectedPeriod);

        if (!fileCheck.valid) {
            if (diffBox) diffBox.style.display = 'none';
            Swal.fire({
                icon: 'error',
                title: fileCheck.title,
                html: fileCheck.reason + `<br><br>Pratinjau perubahan <u>tidak dapat ditampilkan</u>. Harap pilih file CSV yang sesuai.`,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Ganti File'
            }).then(() => {
                coResetFileD1();
            });
            return;
        }

        const file = fileInput.files[0];
        const reader = new FileReader();
        reader.onload = function(e) {
            const text = e.target.result;
            // Split lines dengan dukungan Windows (CRLF \r\n), Mac (\r), dan Linux (\n)
            const lines = text.split(/\r\n|\n|\r/).map(l => l.replace(/[\uFEFF\u200B]/g, '').trim()).filter(l => l.length > 0);
            
            if (lines.length === 0) {
                if (diffBox) diffBox.style.display = 'none';
                Swal.fire({
                    icon: 'error',
                    title: 'File CSV Kosong!',
                    text: 'File CSV yang Anda pilih tidak berisi data.',
                    confirmButtonColor: '#ef4444'
                });
                return;
            }

            // Helper parser baris CSV dengan dukungan tanda kutip
            function parseCSVLine(line, del) {
                const fields = [];
                let field = '';
                let inQuotes = false;
                for (let i = 0; i < line.length; i++) {
                    const c = line[i];
                    if (c === '"' || c === "'") {
                        if (inQuotes && i + 1 < line.length && line[i + 1] === c) {
                            field += c;
                            i++;
                        } else {
                            inQuotes = !inQuotes;
                        }
                    } else if (c === del && !inQuotes) {
                        fields.push(field.replace(/^["']|["']$/g, '').trim());
                        field = '';
                    } else {
                        field += c;
                    }
                }
                fields.push(field.replace(/^["']|["']$/g, '').trim());
                return fields;
            }

            // ── VALIDASI TEMPLATE HEADER CSV FLEXIBLE & STRICT ──
            const headerLine = lines[0];
            const delimiter = (headerLine.match(/;/g) || []).length > (headerLine.match(/,/g) || []).length ? ';' : ',';
            const headers = parseCSVLine(headerLine, delimiter).map(h => {
                return h.replace(/[\uFEFF\u200B\r\n\t]/g, '')
                        .toLowerCase()
                        .replace(/\s+/g, '_');
            });

            // Mengecek keberadaan kolom NIDN / NUPTK, NAMA DOSEN, dan KESIMPULAN BKD (Wajib)
            const hasNidn = headers.some(h => h.includes('nidn') || h.includes('nuptk'));
            const hasNama = headers.some(h => h.includes('nama') || h.includes('dosen') || h.includes('sdm') || h.includes('pegawai'));
            const hasBkd  = headers.some(h => h.includes('bkd') || h.includes('kesimpulan'));

            if (!hasNidn || !hasNama || !hasBkd) {
                const missing = [];
                if (!hasNidn) missing.push('nidn / nuptk');
                if (!hasNama) missing.push('nama_dosen');
                if (!hasBkd)  missing.push('kesimpulan_bkd');

                if (diffBox) diffBox.style.display = 'none';
                Swal.fire({
                    icon: 'error',
                    title: 'Format Template CSV Tidak Sesuai!',
                    html: `File <strong>${file.name}</strong> <u>tidak dapat diproses/diperiksa</u> karena format kolom header tidak sesuai dengan template CSV Sisternas.<br><br>` +
                          `Kolom wajib yang tidak ditemukan: <code class="text-danger fw-bold">${missing.join(', ')}</code>.<br><br>` +
                          `Format header yang terbaca di file Anda:<br><code style="font-size:0.75rem;">${headers.join(', ')}</code>`,
                    confirmButtonText: 'Tutup & Perbaiki File',
                    confirmButtonColor: '#ef4444'
                });
                return;
            }

            // Cari indeks kolom secara dinamis berdasarkan header CSV
            const nidnIdx  = headers.findIndex(h => h.includes('nidn'));
            const nuptkIdx = headers.findIndex(h => h.includes('nuptk') || h.includes('n_u_p_t_k') || h.includes('nik'));
            const namaIdx  = headers.findIndex(h => h.includes('nama') || h.includes('dosen') || h.includes('sdm') || h.includes('pegawai'));
            const bkdIdx   = headers.findIndex(h => h.includes('bkd') || h.includes('kesimpulan'));

            const idxNidn  = nidnIdx !== -1 ? nidnIdx : (nuptkIdx !== -1 ? nuptkIdx : 0);
            const idxNuptk = nuptkIdx !== -1 ? nuptkIdx : nidnIdx;
            const idxNama  = namaIdx !== -1 ? namaIdx : (headers.length > 3 ? 3 : 2);
            const idxBkd   = bkdIdx !== -1 ? bkdIdx : (headers.length - 1);

            const tbody = document.getElementById('d1_diff_tbody');
            if (tbody && lines.length > 1) {
                let html = '';
                const dataRows = lines.slice(1, 10);
                dataRows.forEach((row, idx) => {
                    const cols = parseCSVLine(row, delimiter);

                    const nidnVal  = (idxNidn !== -1 && cols[idxNidn] && cols[idxNidn].trim() !== '' && cols[idxNidn].trim() !== '-') ? cols[idxNidn].trim() : '';
                    const nuptkVal = (idxNuptk !== -1 && cols[idxNuptk] && cols[idxNuptk].trim() !== '' && cols[idxNuptk].trim() !== '-') ? cols[idxNuptk].trim() : '';
                    const namaVal  = (idxNama !== -1 && cols[idxNama] && cols[idxNama].trim() !== '' && cols[idxNama].trim() !== '-') ? cols[idxNama].trim() : '';

                    const nidn  = nidnVal !== '' ? nidnVal : '—';
                    const nuptk = nuptkVal !== '' ? formatNuptk(nuptkVal) : '—';
                    const nama  = namaVal !== '' ? namaVal : '—';
                    const bkdVal = (idxBkd !== -1 && cols[idxBkd]) ? cols[idxBkd].toUpperCase() : '';

                    if (nidn === '—' && nuptk === '—' && nama === '—') return;

                    const labelLama = (idx % 2 === 0) ? 'TM' : 'M';
                    const labelBaru = bkdVal ? ((bkdVal.includes('MEMENUHI') && !bkdVal.includes('TIDAK')) || bkdVal === 'M' ? 'M' : 'TM') : 'M';
                    const isChecked = (labelLama === 'TM' && labelBaru === 'M');

                    html += `<tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 12px 16px;"><code class="fw-bold text-dark" style="font-size:0.83rem;">${nidn}</code></td>
                        <td style="padding: 12px 16px;"><code class="text-secondary" style="font-size:0.83rem;">${nuptk}</code></td>
                        <td style="padding: 12px 16px; font-weight: 600; color: #1e293b;">${nama}</td>
                        <td style="padding: 12px 16px; text-align: center; font-weight: 600; color: #64748b;">${labelLama}</td>
                        <td style="padding: 12px 16px; text-align: center; font-weight: 600; color: #0f172a;">${labelBaru}</td>
                        <td style="padding: 12px 16px; text-align: center;">
                            <input type="checkbox" name="delete_nidn[]" value="${nidn}" class="form-check-input" ${isChecked ? 'checked' : ''} style="width: 19px; height: 19px; cursor: pointer;" title="Centang untuk menghapus data ini">
                        </td>
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

        // Intercept all upload form submissions via AJAX with SweetAlert Loading & Auto-Reload
        document.querySelectorAll('.uploadForm').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const fileInput = form.querySelector('input[type="file"]');
                const tableInput = form.querySelector('input[name="table"]');
                const typeInput = form.querySelector('input[name="upload_type"]');
                if (!fileInput || !fileInput.files[0] || !tableInput) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Pilih File', 'Silakan pilih file CSV terlebih dahulu.', 'info');
                    } else {
                        alert('Silakan pilih file CSV terlebih dahulu.');
                    }
                    return false;
                }

                // Validasi Bulan Pembayaran (Wajib centang minimal 1 bulan)
                const checkedBulanSubmit = document.querySelectorAll('input[name="sp_bulan_d3[]"]:checked');
                if (checkedBulanSubmit.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Bulan Pembayaran Belum Dipilih!',
                        html: 'Silakan centang setidaknya <strong>satu bulan pembayaran</strong> pada Step 2 (Periode Pembayaran) sebelum mengunggah data.',
                        confirmButtonColor: '#f59e0b',
                        confirmButtonText: 'Pilih Bulan'
                    });
                    return false;
                }

                const file = fileInput.files[0];
                const fileName = file.name.toLowerCase();
                const table = tableInput.value;
                const isUpdate = typeInput && typeInput.value === 'update';

                // ── Validasi Tahun & Periode Nama File vs yang Dipilih ──
                const tahunSelectEl = form.querySelector('select[name="tahun"], #d1_new_tahun_val');
                const selectedYear = tahunSelectEl ? tahunSelectEl.value : '';
                const selectedPeriod = typeof getSelectedD3PeriodeVal === 'function' ? getSelectedD3PeriodeVal() : table;
                const fileCheck = checkFileValidity(fileName, selectedYear, selectedPeriod);
                if (!fileCheck.valid) {
                    Swal.fire({
                        icon: 'error',
                        title: fileCheck.title,
                        html: fileCheck.reason + `<br><br>Harap pilih file CSV yang sesuai.`,
                        confirmButtonColor: '#ef4444'
                    });
                    return false;
                }

                // Validasi kata "update" jika form update
                if (isUpdate && !fileName.includes('update')) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Nama File Update Tidak Sesuai!',
                            html: `File <strong>${file.name}</strong> tidak dapat diunggah untuk menu Update.<br><br>Nama file CSV <u>wajib memuat kata "update"</u> (contoh: <code>dosen_ganjil_update.csv</code>).`,
                            confirmButtonColor: '#ef4444'
                        });
                    }
                    return false;
                }

                if (table.includes('ganjil') && !fileName.includes('ganjil')) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Nama File Tidak Sesuai!',
                            html: `File <strong>${file.name}</strong> tidak dapat diunggah untuk periode <strong>Ganjil</strong>.<br><br>Nama file CSV <u>wajib memuat kata "ganjil"</u> (contoh: <code>dosen_ganjil_${new Date().getFullYear()}.csv</code>).`,
                            confirmButtonColor: '#ef4444'
                        });
                    }
                    return false;
                }

                if (table.includes('genap') && !fileName.includes('genap')) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Nama File Tidak Sesuai!',
                            html: `File <strong>${file.name}</strong> tidak dapat diunggah untuk periode <strong>Genap</strong>.<br><br>Nama file CSV <u>wajib memuat kata "genap"</u> (contoh: <code>dosen_genap_${new Date().getFullYear()}.csv</code>).`,
                            confirmButtonColor: '#ef4444'
                        });
                    }
                    return false;
                }

                // Tampilkan Loading Dialog
                Swal.fire({
                    title: 'Sedang Menyimpan Data Cut Off...',
                    html: `
                        <div class="d-flex flex-column align-items-center py-2">
                            <div class="spinner-border text-primary mb-3" role="status"></div>
                            <span class="text-muted small">Sistem sedang memproses & menyimpan data CSV ke database...</span>
                        </div>
                    `,
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    allowEscapeKey: false
                });

                const formData = new FormData(form);

                $.ajax({
                    url: form.action,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil Disimpan!',
                            text: res.message || 'Data CSV berhasil disimpan ke database.',
                            confirmButtonColor: '#2563eb'
                        }).then(() => {
                            // Isi tabel Step 4 setelah data berhasil tersimpan ke DB
                            if (typeof syncD3MappingTable === 'function') {
                                syncD3MappingTable();
                            }
                            if (typeof cutOffTable !== 'undefined') {
                                cutOffTable.ajax.reload();
                            }
                            if (typeof coResetFileD1 === 'function') {
                                coResetFileD1();
                            }
                        });
                    },
                    error: function(xhr) {
                        let msg = 'Terjadi kesalahan saat mengunggah data CSV.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Menyimpan Data!',
                            html: msg,
                            confirmButtonColor: '#ef4444'
                        });
                    }
                });
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

<!-- Modal Dashboard Cut Off Sisternas -->
<div class="modal fade" id="modalDashboardCutoff" tabindex="-1" aria-labelledby="modalDashboardCutoffLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 95vw; width: 95vw; margin: 1rem auto;">
        <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 10px 35px rgba(0,0,0,0.18);">
            <div class="modal-header px-4 py-3" style="background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; border-top-left-radius: 16px; border-top-right-radius: 16px;">
                <div class="d-flex align-items-center gap-2">
                    <i class="bx bx-tachometer text-primary fs-4"></i>
                    <h5 class="modal-title fw-bold text-dark mb-0" id="modalDashboardCutoffLabel" style="font-size: 1.05rem;">
                        Dashboard Data Cut Off Sisternas
                    </h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" style="background: #ffffff;">
                <div class="card card-cutoff border-0 shadow-none mb-0" id="cutoffDataContainer">
                    <div class="card-body p-0">
                        <!-- Section 2: Pilih Periode Cut Off Sisternas -->
                        <div>
                            <!-- Hidden Filter Tahun -->
                            <div class="d-none">
                                <select name="tahun_filter" id="tahunFilterSelect" class="form-select form-select-sm">
                                    @php
                                        $yearsToDisplay = (isset($listTahun) && count($listTahun) > 0) ? $listTahun : range(2023, max((int)date('Y'), (int)$tahunSession));
                                    @endphp
                                    @foreach($yearsToDisplay as $y)
                                        <option value="{{ $y }}" {{ ($tahunSession == $y) ? 'selected' : '' }}>{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="row g-4 align-items-stretch mt-1 mb-3.5">
                                <!-- Card 1: Semester Ganjil (50% / col-md-6) -->
                                <div class="col-md-6 col-sm-12 d-flex">
                                    <div class="btn-select-period p-3 rounded-3 d-flex flex-column justify-content-between w-100 h-100 glowing-active-card" data-value="p_sister_ganjil_tl" style="cursor: pointer; transition: all 0.2s ease;">
                                        <div class="w-100">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="period-title fw-bold" style="font-size: 0.88rem; color: #435971;">
                                                    <i class="bx bx-calendar text-primary me-1"></i> {{ $tahunSession }}/1
                                                </span>
                                            </div>
                                            <div class="period-subtitle mb-2" style="font-size: 0.71rem; line-height: 1.35;">
                                                <div>
                                                    <span style="color: #697a8d;">Pembayaran:</span> <span class="fw-semibold" style="color: #566a7f;">Maret - Agustus</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2 align-items-center w-100 mt-auto">
                                            <div class="flex-fill">
                                                <div class="btn-stat-flat-memenuhi" title="Klik untuk menyaring data Memenuhi">
                                                    <i class="bx bx-check" style="font-size: 0.88rem; font-weight: bold; flex-shrink: 0;"></i>
                                                    <span>{{ number_format($statGanjilTL['m'], 0, ',', '.') }} memenuhi</span>
                                                </div>
                                            </div>
                                            <div class="flex-fill">
                                                <div class="btn-stat-flat-tm" title="Klik untuk menyaring data Tidak Memenuhi">
                                                    <i class="bx bx-x" style="font-size: 0.88rem; font-weight: bold; flex-shrink: 0;"></i>
                                                    <span>{{ number_format($statGanjilTL['tm'], 0, ',', '.') }} tidak memenuhi</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Card 2: Semester Genap (50% / col-md-6) -->
                                <div class="col-md-6 col-sm-12 d-flex">
                                    @php
                                        $isGenapBerjalan = ((int)$tahunSession >= (int)date('Y'));
                                        $genapDefaultVal = $isGenapBerjalan ? 'n_sister_genap_bj' : 'o_sister_genap_tl';
                                        $statGenap       = $isGenapBerjalan ? $statGenapBJ : $statGenapTL;
                                    @endphp
                                    <div class="btn-select-period p-3 rounded-3 d-flex flex-column justify-content-between w-100 h-100" id="cardGenap" data-value="{{ $genapDefaultVal }}" style="cursor: pointer; transition: all 0.2s ease;">
                                        <div class="w-100">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="period-title fw-bold" style="font-size: 0.88rem; color: #435971;">
                                                    <i class="bx bx-calendar text-primary me-1"></i> {{ $tahunSession }}/2
                                                </span>
                                            </div>
                                            <div class="period-subtitle mb-2" style="font-size: 0.71rem; line-height: 1.35;">
                                                <div>
                                                    <span style="color: #697a8d;">Pembayaran:</span> <span class="fw-semibold" style="color: #566a7f;">September - Februari</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2 align-items-center w-100 mt-auto">
                                            <div class="flex-fill">
                                                <div class="btn-stat-flat-memenuhi" title="Klik untuk menyaring data Memenuhi">
                                                    <i class="bx bx-check" style="font-size: 0.88rem; font-weight: bold; flex-shrink: 0;"></i>
                                                    <span>{{ number_format($statGenap['m'], 0, ',', '.') }} memenuhi</span>
                                                </div>
                                            </div>
                                            <div class="flex-fill">
                                                <div class="btn-stat-flat-tm" title="Klik untuk menyaring data Tidak Memenuhi">
                                                    <i class="bx bx-x" style="font-size: 0.88rem; font-weight: bold; flex-shrink: 0;"></i>
                                                    <span>{{ number_format($statGenap['tm'], 0, ',', '.') }} tidak memenuhi</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Dropdown hidden untuk DataTables -->
                        <div class="d-none">
                            <select name="sisternas" id="sisternasSelect">
                                <option value="p_sister_ganjil_tl" selected>p_sister_ganjil_tl</option>
                                <option value="n_sister_genap_bj">n_sister_genap_bj</option>
                                <option value="o_sister_genap_tl">o_sister_genap_tl</option>
                            </select>
                        </div>

                        <!-- Single Header + Subtitle ringkas -->
                        <div class="d-flex flex-wrap justify-content-between align-items-center border-top pt-3 pb-2.5 mb-3" style="border-color: #e2e8f0 !important;">
                            <div>
                                <h6 class="mb-0 text-dark fw-bold" id="selectedPeriodTitle" style="font-size: 0.95rem;">
                                    <i class="bx bx-list-check text-primary me-2"></i>Daftar Dosen: {{ $tahunSession }}/1 (Memenuhi)
                                </h6>
                                <small class="text-muted" id="selectedPeriodSubtitle" style="font-size: 0.76rem;">Pembayaran: Maret - Agustus</small>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" id="addDataBtn" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1.5 px-3 py-1.5 fw-bold" onclick="openCreateModal()" style="border-radius: 7px; background: #0f172a; border-color: #0f172a; font-size: 0.82rem;">
                                    <i class="bx bx-plus-circle fs-5"></i> Tambah Data Dosen
                                </button>

                                <a href="{{ route('admin.cutoff-sisternas.export', ['sisternas' => 'p_sister_ganjil_tl']) }}" id="btnExportBackupODS" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1.5 px-3 py-1.5 fw-semibold" style="border-radius: 7px; font-size: 0.82rem;">
                                    <i class="bx bx-export fs-5"></i> Export Backup ODS
                                </a>
                            </div>
                        </div>

                        <div id="loading" style="display: none;">
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
            <div class="modal-footer px-4 py-2.5" style="background: #f8fafc; border-top: 1px solid #e2e8f0; border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
                <button type="button" class="btn btn-secondary px-4 py-2 fw-semibold" data-bs-dismiss="modal" style="border-radius: 8px;">Tutup</button>
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
                    <input type="hidden" name="tahun" id="create_tahun" value="{{ $tahunSession }}">
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Tahun Pelaporan</label>
                        <input type="text" id="create_tahun_label" class="form-control" value="{{ $tahunSession }}" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Semester Pelaporan</label>
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
    let bkdStatusFilter = '';

    //datatable
    const cutOffTable = $('#cutoffTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.cutoff-sisternas") }}',
            data: function(d) {
                d.sisternas = $('#sisternasSelect').val();
                d.tahun = $('#tahunFilterSelect').val() || '{{ $tahunSession }}';
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
                className: 'text-center align-middle',
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

    cutOffTable.on('xhr.dt', function (e, settings, json, xhr) {
        if (json && json.stat_ganjil_tl) {
            const formatNum = n => new Intl.NumberFormat('id-ID').format(n || 0);
            const yr = json.tahun_query || $('#tahunFilterSelect').val() || '{{ $tahunSession }}';

            // Card 1 (Ganjil)
            const ganjilM = formatNum(json.stat_ganjil_tl.m);
            const ganjilTM = formatNum(json.stat_ganjil_tl.tm);

            $('.btn-select-period[data-value="p_sister_ganjil_tl"] .period-title').html('<i class="bx bx-calendar text-primary me-1"></i> ' + yr + '/1');
            $('.btn-select-period[data-value="p_sister_ganjil_tl"] .btn-stat-flat-memenuhi span').text(ganjilM + ' memenuhi');
            $('.btn-select-period[data-value="p_sister_ganjil_tl"] .btn-stat-flat-tm span').text(ganjilTM + ' tidak memenuhi');

            // Card 2 (Genap)
            const curYr = new Date().getFullYear();
            const isGenapBerjalan = (parseInt(yr) >= curYr);
            const statGenap = isGenapBerjalan ? json.stat_genap_bj : json.stat_genap_tl;
            const genapM = formatNum(statGenap ? statGenap.m : 0);
            const genapTM = formatNum(statGenap ? statGenap.tm : 0);

            $('#cardGenap .period-title').html('<i class="bx bx-calendar text-primary me-1"></i> ' + yr + '/2');
            $('#cardGenap .btn-stat-flat-memenuhi span').text(genapM + ' memenuhi');
            $('#cardGenap .btn-stat-flat-tm span').text(genapTM + ' tidak memenuhi');
        }
    });

    $('#sisternasSelect').change(() => {
        cutOffTable.ajax.reload()
    });

    // Klik pada tombol statistik Memenuhi di dalam card
    $('.btn-select-period').on('click', '.btn-stat-flat-memenuhi', function(e) {
        e.stopPropagation(); // Stop bubbling to card click
        
        const card = $(this).closest('.btn-select-period');
        
        // Terapkan perubahan UI secara instan (0ms response)
        $('.btn-select-period').removeClass('has-stat-filter glowing-active-card active');
        $('.btn-stat-flat-memenuhi, .btn-stat-flat-tm').removeClass('active-stat-filter');
        
        card.addClass('has-stat-filter glowing-active-card active');
        $(this).addClass('active-stat-filter');

        const selectedVal = card.data('value');
        const periodTitle = card.find('.period-title').text().trim();
        const bkdInfo = card.find('.period-subtitle:not(.d-none)').text().replace(/\s+/g, ' ').trim();

        // Update judul & deskripsi tabel bawah
        $('#selectedPeriodTitle').html('<i class="bx bx-list-check text-primary me-2"></i>Daftar Dosen: ' + periodTitle + ' (Memenuhi)');
        $('#selectedPeriodSubtitle').text(bkdInfo);

        bkdStatusFilter = 'M';
        $('#sisternasSelect').val(selectedVal); // set value
        cutOffTable.ajax.reload();

        // Scroll smooth ke tabel daftar dosen
        $('html, body').animate({
            scrollTop: $("#cutoffDataContainer").offset().top - 80
        }, 300);
    });

    // Klik pada tombol statistik Tidak Memenuhi di dalam card
    $('.btn-select-period').on('click', '.btn-stat-flat-tm', function(e) {
        e.stopPropagation(); // Stop bubbling to card click
        
        const card = $(this).closest('.btn-select-period');
        
        // Terapkan perubahan UI secara instan (0ms response)
        $('.btn-select-period').removeClass('has-stat-filter glowing-active-card active');
        $('.btn-stat-flat-memenuhi, .btn-stat-flat-tm').removeClass('active-stat-filter');
        
        card.addClass('has-stat-filter glowing-active-card active');
        $(this).addClass('active-stat-filter');

        const selectedVal = card.data('value');
        const periodTitle = card.find('.period-title').text().trim();
        const bkdInfo = card.find('.period-subtitle:not(.d-none)').text().replace(/\s+/g, ' ').trim();

        // Update judul & deskripsi tabel bawah
        $('#selectedPeriodTitle').html('<i class="bx bx-list-check text-primary me-2"></i>Daftar Dosen: ' + periodTitle + ' (Tidak Memenuhi)');
        $('#selectedPeriodSubtitle').text(bkdInfo);

        bkdStatusFilter = 'TM';
        $('#sisternasSelect').val(selectedVal); // set value
        cutOffTable.ajax.reload();

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

        // Secara default langsung aktifkan filter "Memenuhi"
        const memenuhiBtn = card.find('.genap-stat-view:not(.d-none) .btn-stat-flat-memenuhi');
        if (memenuhiBtn.length > 0) {
            memenuhiBtn.trigger('click');
        }
    };

    // Klik pada Nav Pill Periode untuk memilih periode & langsung aktif ke filter Memenuhi secara default
    $('.btn-select-period').on('click', function(e) {
        // Prevent click if clicking inside buttons
        if ($(e.target).closest('.btn-stat-flat-memenuhi, .btn-stat-flat-tm, .custom-toggle-container').length > 0) {
            return;
        }

        // Cari tombol Memenuhi yang terlihat pada card ini lalu trigger click
        const memenuhiBtn = $(this).find('.genap-stat-view:not(.d-none) .btn-stat-flat-memenuhi, .btn-stat-flat-memenuhi').filter(':visible').first();
        if (memenuhiBtn.length > 0) {
            memenuhiBtn.trigger('click');
        }
    });



    // Auto-select card aktif saat pertama kali halaman dimuat
    const initialVal = $('#sisternasSelect').val();
    if (initialVal) {
        $(`.btn-select-period[data-value="${initialVal}"]`).trigger('click');
    } else {
        $('.btn-select-period.glowing-active-card').trigger('click');
    }

    // Open create modal
    function openCreateModal() {
        const selected = $('#sisternasSelect').val() || (typeof getSelectedD3PeriodeVal === 'function' ? getSelectedD3PeriodeVal() : '');
        if (!selected) {
            Swal.fire('Pilih Data', 'Silakan pilih data sisternas terlebih dahulu.', 'info');
            return;
        }
        if ($('#createForm').length) {
            $('#createForm')[0].reset();
        }
        $('#create_sisternas').val(selected);
        const semesterLabel = (selected === 'p_sister_ganjil_tl') ? 'Semester 1 (Ganjil)' : 'Semester 2 (Genap)';
        $('#create_sisternas_label').val(semesterLabel).attr('value', semesterLabel).attr('placeholder', semesterLabel);
        
        const activeYear = $('#tahunFilterSelect').val() || $('#d1_new_tahun_val').val() || '{{ $tahunSession }}';
        $('#create_tahun').val(activeYear);
        $('#create_tahun_label').val(activeYear).attr('value', activeYear);

        $('#createModal').modal('show');
    }
    window.openCreateModal = openCreateModal;

    $('#addDataBtn').on('click', function(e) {
        e.preventDefault();
        openCreateModal();
    });

    // Jaga-jaga: saat modal akan ditampilkan, sinkronkan lagi label & hidden
    $('#createModal').on('show.bs.modal', function () {
        const selected = $('#sisternasSelect').val();
        const semesterLabel = (selected === 'p_sister_ganjil_tl') ? 'Semester 1 (Ganjil)' : 'Semester 2 (Genap)';
        $('#create_sisternas').val(selected);
        $('#create_sisternas_label').val(semesterLabel).attr('value', semesterLabel).attr('placeholder', semesterLabel);
        const activeYear = $('#tahunFilterSelect').val() || $('#d1_new_tahun_val').val() || '{{ $tahunSession }}';
        $('#create_tahun').val(activeYear);
        $('#create_tahun_label').val(activeYear).attr('value', activeYear);
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
                tahun: $('#create_tahun').val(),
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
                const activeYear = $('#tahunFilterSelect').val() || '{{ $tahunSession }}';
                $.ajax({
                    url: `{{ url('/admin/cutoff-sisternas/clear') }}/${table}`,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}',
                        tahun: activeYear
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
        const activeYear = $('#tahunFilterSelect').val() || '{{ $tahunSession }}';
        const url = `{{ route('admin.cutoff-sisternas.export') }}?table=${selected}&tahun=${activeYear}`;
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

    // Auto sync mapping table on initial page load
    const periodeCb = document.querySelector('input[name="sp_periode_d3_cb[]"]:checked');
    if (periodeCb) {
        onD3PeriodeCheckboxChange(periodeCb);
    }

    // Reload DataTables via AJAX when Year Filter changes (no full page refresh)
    $('#tahunFilterSelect').on('change', function() {
        if (typeof cutOffTable !== 'undefined' && cutOffTable) {
            cutOffTable.ajax.reload();
        }
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
<script>
const bulanLabelsD3 = {
    januari:'Januari', februari:'Februari', maret:'Maret',
    april:'April', mei:'Mei', juni:'Juni',
    juli:'Juli', agustus:'Agustus', september:'September',
    oktober:'Oktober', november:'November', desember:'Desember'
};

function onD3TahunChange(yr) {
    const hiddenTahun = document.getElementById('d1_new_tahun_val');
    if (hiddenTahun) hiddenTahun.value = yr;
    const filterTahunSelect = document.getElementById('tahunFilterSelect');
    if (filterTahunSelect) {
        filterTahunSelect.value = yr;
        $(filterTahunSelect).trigger('change');
    }

    // Auto-set Tahun Pembayaran = Tahun Laporan + 1 (admin bisa override manual)
    const bayarYearSelect = document.getElementById('d3_tahun_pembayaran_select');
    if (bayarYearSelect) {
        const targetBayarYear = (parseInt(yr) + 1).toString();
        const optExists = [...bayarYearSelect.options].some(o => o.value === targetBayarYear);
        if (optExists) {
            bayarYearSelect.value = targetBayarYear;
        }
    }

    if (typeof coResetFileD1 === 'function') {
        coResetFileD1();
    }
    updatePreviewD3();
}

function getSelectedD3PeriodeVal() {
    const cb = document.querySelector('input[name="sp_periode_d3_cb[]"]:checked');
    if (cb) return cb.value;
    const sel = document.getElementById('spPeriodeLaporanSelect');
    if (sel && sel.value) return sel.value;
    return 'n_sister_genap_bj';
}

function onD3PeriodeCheckboxChange(el) {
    if (el && el.checked) {
        document.querySelectorAll('input[name="sp_periode_d3_cb[]"]').forEach(cb => {
            if (cb !== el) cb.checked = false;
        });
    }

    if (typeof coResetFileD1 === 'function') {
        coResetFileD1();
    }

    const val = getSelectedD3PeriodeVal();
    const tableValInput = document.getElementById('d1_new_table_val');
    if (tableValInput) {
        tableValInput.value = val;
    }

    updatePreviewD3();
}

function onBayarTahunInput(el) {
    const tr = el.closest('tr');
    if (!tr) return;
    const inpBayarBulan = tr.querySelector('input[name="d3_periode_bayar_bulan[]"]');
    const checkedBulan = [...document.querySelectorAll('input[name="sp_bulan_d3[]"]:checked')].map(c => c.value);
    
    if (checkedBulan.length > 0) {
        let monthRange = '';
        if (checkedBulan.length === 6) {
            monthRange = (bulanLabelsD3[checkedBulan[0]] || checkedBulan[0]) + ' - ' + (bulanLabelsD3[checkedBulan[checkedBulan.length - 1]] || checkedBulan[checkedBulan.length - 1]);
        } else {
            monthRange = checkedBulan.map(b => bulanLabelsD3[b] || b).join(', ');
        }
        const yr = el.value ? el.value.trim() : '';
        if (inpBayarBulan) inpBayarBulan.value = yr ? (monthRange + ' ' + yr) : monthRange;
    }
}

function syncD3MappingTable() {
    const selectedUsulan = document.getElementById('spJenisUsulan') ? document.getElementById('spJenisUsulan').value : 'SPTJM';
    
    // Ambil Tahun Laporan dari Step 1
    const tahunLaporanSelect = document.getElementById('d1_new_tahun_val');
    if (!tahunLaporanSelect || !tahunLaporanSelect.value) return;
    const tahunLaporan = parseInt(tahunLaporanSelect.value);

    // Ambil Tahun Pembayaran dari Step 2 (bisa di-override manual oleh admin)
    const bayarYearSelect = document.getElementById('d3_tahun_pembayaran_select');
    const Y = bayarYearSelect && bayarYearSelect.value ? parseInt(bayarYearSelect.value) : (tahunLaporan + 1);

    // Cek periode mana yang dicentang di Step 1
    const cbGanjil = document.getElementById('d3_periode_cb_1');
    const cbGenap = document.getElementById('d3_periode_cb_2');
    const isGanjilChecked = cbGanjil && cbGanjil.checked;
    const isGenapChecked = cbGenap && cbGenap.checked;

    // ═══ Rumus Dinamis (berlaku selamanya untuk tahun berapapun) ═══
    // Ganjil: Laporan Sep-Des (tahunLaporan) & Jan-Feb (tahunLaporan+1) → Bayar Mar-Ags Y
    // Genap:  Laporan Mar-Ags (tahunLaporan+1) → Bayar Sep-Des Y & Jan-Feb (Y+1)
    //         ↑ Jan & Feb masuk tahun Y+1 (beda tahun dari Sep-Des)
    const mappingRows = [];

    // Ambil bulan yang benar-benar dicentang di Step 2
    const checkedBulan = [...document.querySelectorAll('input[name="sp_bulan_d3[]"]:checked')].map(c => c.value);

    if (isGanjilChecked) {
        // Ganjil: semua bulan pembayaran berada di tahun Y (Mar-Ags)
        let bayarBulanText = 'Maret - Agustus ' + Y;
        if (checkedBulan.length > 0) {
            bayarBulanText = checkedBulan.map(b => (bulanLabelsD3[b] || b) + ' ' + Y).join(', ');
        }
        mappingRows.push({
            usulan: selectedUsulan,
            lapTahun: tahunLaporan.toString(),
            lapPeriode: tahunLaporan + '/1',
            bayarTahun: Y.toString(),
            bayarBulan: bayarBulanText
        });
    }

    if (isGenapChecked) {
        // Genap: Sep-Des masuk tahun Y, Jan-Feb masuk tahun Y+1
        const bulanTahunY = ['september', 'oktober', 'november', 'desember'];
        const bulanTahunY1 = ['januari', 'februari'];

        let groupY = [];
        let groupY1 = [];

        if (checkedBulan.length > 0) {
            checkedBulan.forEach(b => {
                if (bulanTahunY.includes(b)) {
                    groupY.push((bulanLabelsD3[b] || b) + ' ' + Y);
                } else if (bulanTahunY1.includes(b)) {
                    groupY1.push((bulanLabelsD3[b] || b) + ' ' + (Y + 1));
                } else {
                    // Bulan lain (Mar-Ags) → ikut tahun Y
                    groupY.push((bulanLabelsD3[b] || b) + ' ' + Y);
                }
            });
        } else {
            groupY = ['September - Desember ' + Y];
            groupY1 = ['Januari - Februari ' + (Y + 1)];
        }

        let bayarBulanText = '';
        if (groupY.length > 0 && groupY1.length > 0) {
            bayarBulanText = groupY.join(', ') + ' & ' + groupY1.join(', ');
        } else if (groupY.length > 0) {
            bayarBulanText = groupY.join(', ');
        } else if (groupY1.length > 0) {
            bayarBulanText = groupY1.join(', ');
        }

        // Tentukan bayarTahun berdasarkan bulan yang dicentang
        let bayarTahunText = Y.toString();
        if (groupY.length > 0 && groupY1.length > 0) {
            bayarTahunText = Y + ' - ' + (Y + 1);
        } else if (groupY1.length > 0 && groupY.length === 0) {
            bayarTahunText = (Y + 1).toString();
        }

        mappingRows.push({
            usulan: selectedUsulan,
            lapTahun: tahunLaporan.toString(),
            lapPeriode: tahunLaporan + '/2',
            bayarTahun: bayarTahunText,
            bayarBulan: bayarBulanText
        });
    }

    // Jika tidak ada periode yang dicentang, tidak tampilkan apa-apa
    if (mappingRows.length === 0) return;

    const tbody = document.getElementById('spD3MappingTbody');
    if (!tbody) return;

    // Bersihkan baris lama, lalu isi sesuai periode yang dipilih
    tbody.innerHTML = '';

    mappingRows.forEach(row => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><input type="text" class="sp-d3-input" name="d3_usulan[]" value="${row.usulan}" readonly></td>
            <td><input type="text" class="sp-d3-input" name="d3_pembayaran_tahun[]" value="${row.lapTahun}" readonly></td>
            <td><input type="text" class="sp-d3-input" name="d3_pembayaran_periode[]" value="${row.lapPeriode}" readonly></td>
            <td><input type="text" class="sp-d3-input" name="d3_periode_bayar_tahun[]" value="${row.bayarTahun}" readonly></td>
            <td><input type="text" class="sp-d3-input" name="d3_periode_bayar_bulan[]" value="${row.bayarBulan}" readonly></td>
            <td class="text-center align-middle" style="text-align: center !important; vertical-align: middle !important;">
                <div class="d-flex align-items-center justify-content-center gap-1">
                    <button type="button" class="btn btn-sm btn-outline-info border-0 p-0 d-inline-flex align-items-center justify-content-center" onclick="viewD3MappingRow(this)" title="View Detail Dashboard" style="width: 30px; height: 30px; border-radius: 6px;">
                        <i class="bx bx-show fs-5" style="display: inline-flex; align-items: center; justify-content: center; line-height: 1;"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger border-0 p-0 d-inline-flex align-items-center justify-content-center" onclick="removeD3MappingRow(this)" title="Hapus Baris" style="width: 30px; height: 30px; border-radius: 6px;">
                        <i class="bx bx-trash fs-5" style="display: inline-flex; align-items: center; justify-content: center; line-height: 1;"></i>
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function updatePreviewD3() {
    const preview = document.getElementById('spPreview');
    const yearSelect = document.getElementById('d1_new_tahun_val');
    const selectedYr = yearSelect ? yearSelect.value : '';
    const checkedBulan = [...document.querySelectorAll('input[name="sp_bulan_d3[]"]:checked')].map(c => c.value);
    const selectedPeriode = getSelectedD3PeriodeVal();
    const periodeLabel = selectedPeriode.includes('ganjil') || selectedPeriode === '1' || selectedPeriode === 'p_sister_ganjil_tl' ? '1 (Ganjil)' : '2 (Genap)';
    // Catatan: tabel Step 4 TIDAK di-sync otomatis di sini.
    // Tabel hanya terisi setelah data berhasil disimpan ke database (AJAX success).

    if (preview) {
        const tbody = document.getElementById('spD3MappingTbody');
        const firstRow = tbody ? tbody.querySelector('tr') : null;
        const bayarYrInp = firstRow ? firstRow.querySelector('input[name="d3_periode_bayar_tahun[]"]') : null;
        const bayarYrText = bayarYrInp ? bayarYrInp.value : '';

        if (selectedYr && checkedBulan.length > 0 && selectedPeriode) {
            document.getElementById('spPreviewTahun').textContent = 'Laporan Tahun ' + selectedYr + ' (' + periodeLabel + ')' + (bayarYrText ? (' → Pembayaran Tahun ' + bayarYrText) : '');
            document.getElementById('spPreviewMonths').textContent = 'Rincian Pembayaran: ' + checkedBulan.map(b => bulanLabelsD3[b] || b).join(' · ');
            preview.classList.add('show');
        } else {
            preview.classList.remove('show');
        }
    }
}

function toggleD3SelectAll(el) {
    document.querySelectorAll('input[name="sp_bulan_d3[]"]').forEach(cb => { cb.checked = el.checked; });
    updatePreviewD3();
}

function resetFormD3() {
    // 1. Uncheck semua bulan pembayaran
    document.querySelectorAll('input[name="sp_bulan_d3[]"]').forEach(cb => cb.checked = false);
    
    // 2. Uncheck 'Pilih Semua Bulan'
    const selectAllD3 = document.getElementById('d3SelectAllBulan');
    if (selectAllD3) selectAllD3.checked = false;

    // 3. Reset Pilihan Periode Laporan ke default (2 Genap)
    const cbGanjil = document.getElementById('d3_periode_cb_1');
    const cbGenap = document.getElementById('d3_periode_cb_2');
    if (cbGanjil) cbGanjil.checked = false;
    if (cbGenap) cbGenap.checked = true;

    const tableValInput = document.getElementById('d1_new_table_val');
    if (tableValInput) tableValInput.value = 'n_sister_genap_bj';

    // 4. Reset Upload File CSV & Pratinjau Perubahan
    if (typeof coResetFileD1 === 'function') {
        coResetFileD1();
    }
    
    // 5. Update Pratinjau & Tabel Pemetaan
    updatePreviewD3();
}

function saveSettingD3() {
    const diffBox = document.getElementById('d1_diff_box');
    const form = document.getElementById('spFormD3');
    
    if (diffBox && window.getComputedStyle(diffBox).display !== 'none') {
        if (form) {
            $(form).trigger('submit');
        }
    } else {
        coCheckDiffD1();
    }
}

function addD3MappingRow() {
    const tbody = document.getElementById('spD3MappingTbody');
    if (!tbody) return;
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td><input type="text" class="sp-d3-input" name="d3_usulan[]" value="" readonly></td>
        <td><input type="text" class="sp-d3-input" name="d3_pembayaran_tahun[]" value="" readonly></td>
        <td><input type="text" class="sp-d3-input" name="d3_pembayaran_periode[]" value="" readonly></td>
        <td><input type="text" class="sp-d3-input" name="d3_periode_bayar_tahun[]" value="" readonly></td>
        <td><input type="text" class="sp-d3-input" name="d3_periode_bayar_bulan[]" value="" readonly></td>
        <td class="text-center align-middle" style="text-align: center !important; vertical-align: middle !important;">
            <div class="d-flex align-items-center justify-content-center gap-1">
                <button type="button" class="btn btn-sm btn-outline-info border-0 p-0 d-inline-flex align-items-center justify-content-center" onclick="viewD3MappingRow(this)" title="View Detail Dashboard" style="width: 30px; height: 30px; border-radius: 6px;">
                    <i class="bx bx-show fs-5" style="display: inline-flex; align-items: center; justify-content: center; line-height: 1;"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger border-0 p-0 d-inline-flex align-items-center justify-content-center" onclick="removeD3MappingRow(this)" title="Hapus Baris" style="width: 30px; height: 30px; border-radius: 6px;">
                    <i class="bx bx-trash fs-5" style="display: inline-flex; align-items: center; justify-content: center; line-height: 1;"></i>
                </button>
            </div>
        </td>
    `;
    tbody.appendChild(tr);
}

function removeD3MappingRow(btn) {
    const tr = btn.closest('tr');
    if (!tr) return;

    const inpUsulan = tr.querySelector('input[name="d3_usulan[]"]');
    const inpTahun = tr.querySelector('input[name="d3_pembayaran_tahun[]"]');
    const inpPeriode = tr.querySelector('input[name="d3_pembayaran_periode[]"]');
    
    const usulanVal = inpUsulan && inpUsulan.value ? inpUsulan.value.trim() : 'SPTJM';
    const tahunVal = inpTahun && inpTahun.value ? inpTahun.value.trim() : (document.getElementById('d1_new_tahun_val') ? document.getElementById('d1_new_tahun_val').value : '{{ date('Y') }}');
    const periodeText = inpPeriode && inpPeriode.value ? inpPeriode.value.trim() : (tahunVal + '/2');
    
    const selectedTable = getSelectedD3PeriodeVal();

    Swal.fire({
        title: 'Hapus Data Periode Ini?',
        html: `Apakah Anda yakin ingin menghapus seluruh data cut off sisternas untuk periode <strong>${periodeText} (${usulanVal})</strong>?<br><br><span class="text-danger small"><i class="bx bx-error me-1"></i>Data yang dihapus dari database tidak dapat dikembalikan.</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Hapus Data',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Sedang Menghapus Data...',
                html: '<div class="d-flex justify-content-center my-3"><div class="spinner-border text-danger" role="status"></div></div><span class="text-muted small">Sistem sedang menghapus data dari database...</span>',
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false
            });

            $.ajax({
                url: `/admin/cutoff-sisternas/clear/${selectedTable}?tahun=${tahunVal}`,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil Dihapus!',
                        text: res.message || 'Data periode berhasil dihapus dari database.',
                        confirmButtonColor: '#2563eb'
                    }).then(() => {
                        tr.querySelectorAll('input').forEach(inp => inp.value = '');
                        if (typeof coResetFileD1 === 'function') {
                            coResetFileD1();
                        }
                        if (typeof cutOffTable !== 'undefined' && cutOffTable) {
                            cutOffTable.ajax.reload();
                        }
                    });
                },
                error: function(xhr) {
                    let msg = 'Gagal menghapus data dari database.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Hapus Data!',
                        html: msg,
                        confirmButtonColor: '#dc2626'
                    });
                }
            });
        }
    });
}

function viewD3MappingRow(btn) {
    const tr = btn.closest('tr');
    let selectedYr = '';

    if (tr) {
        const inpTahun = tr.querySelector('input[name="d3_periode_bayar_tahun[]"]');
        if (inpTahun && inpTahun.value) {
            selectedYr = inpTahun.value.trim();
        }
    }

    if (!selectedYr) {
        const mainYearSelect = document.getElementById('d1_new_tahun_val');
        if (mainYearSelect && mainYearSelect.value) {
            selectedYr = mainYearSelect.value.trim();
        }
    }

    // Sync selected table type from Step 2
    const selectedPeriodeVal = getSelectedD3PeriodeVal();
    const sisternasSelect = document.getElementById('sisternasSelect');
    if (sisternasSelect && selectedPeriodeVal) {
        sisternasSelect.value = selectedPeriodeVal;
    }

    const tahunSelect = document.getElementById('tahunFilterSelect');
    if (selectedYr && tahunSelect) {
        let optionToSelect = [...tahunSelect.options].find(o => o.value == selectedYr);
        if (!optionToSelect) {
            optionToSelect = new Option(selectedYr, selectedYr, true, true);
            tahunSelect.add(optionToSelect);
        }
        tahunSelect.value = selectedYr;
        tahunSelect.dispatchEvent(new Event('change'));
    }

    $('#modalDashboardCutoff').modal('show');
}

$('#modalDashboardCutoff').on('shown.bs.modal', function () {
    if (typeof cutOffTable !== 'undefined' && cutOffTable) {
        cutOffTable.columns.adjust();
        cutOffTable.ajax.reload();
    }
});

function editD3MappingRow(btn) {
    const tr = btn.closest('tr');
    if (!tr) return;
    const inputs = tr.querySelectorAll('input');
    const isReadOnly = inputs[0] ? inputs[0].hasAttribute('readonly') : false;
    
    inputs.forEach(inp => {
        if (isReadOnly) {
            inp.removeAttribute('readonly');
            inp.style.backgroundColor = '#ffffff';
            inp.style.borderColor = '#3b82f6';
        } else {
            inp.setAttribute('readonly', 'readonly');
            inp.style.backgroundColor = '#f8fafc';
            inp.style.borderColor = '#cbd5e1';
        }
    });

    if (isReadOnly) {
        if (inputs[0]) {
            inputs[0].focus();
            inputs[0].select();
        }
        Swal.fire({
            icon: 'info',
            title: 'Mode Edit Manual Aktif',
            text: 'Kolom kini dapat diubah secara manual.',
            timer: 1400,
            showConfirmButton: false
        });
    } else {
        Swal.fire({
            icon: 'success',
            title: 'Mode Otomatis Aktif',
            text: 'Kolom kembali terkunci (otomatis diisi dari setting di atas).',
            timer: 1400,
            showConfirmButton: false
        });
    }
}

function removeD3Row(btn) {
    const tr = btn.closest('tr');
    if (!tr) return;
    Swal.fire({
        title: 'Hapus Baris Pemetaan?',
        text: 'Baris pemetaan ini akan dihapus dari tabel.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            tr.remove();
        }
    });
}

function saveSettingD3() {
    const yearSelect = document.getElementById('d1_new_tahun_val');
    const selectedYr = yearSelect ? yearSelect.value : '';
    const selectedPeriode = document.querySelector('input[name="sp_periode_d3"]:checked');
    const checkedBulan = [...document.querySelectorAll('input[name="sp_bulan_d3[]"]:checked')].map(c => c.value);
    const fileInput = document.getElementById('d1_new_file_input');

    if (!selectedYr || !selectedPeriode || checkedBulan.length === 0) {
        Swal.fire({
            title: 'Peringatan',
            text: 'Harap lengkapi Periode Pelaporan dan Periode Pembayaran terlebih dahulu.',
            icon: 'warning',
            confirmButtonColor: '#2563eb'
        });
        return;
    }

    if (!fileInput || !fileInput.files[0]) {
        Swal.fire({
            title: 'Upload File CSV Terlebih Dahulu',
            text: 'Silakan unggah file CSV Cut Off Sisternas pada Langkah 4 terlebih dahulu sebelum menyimpan.',
            icon: 'info',
            confirmButtonColor: '#2563eb'
        });
        return;
    }

    // Set / update nilai ke Setting Pemetaan Periode Bayar & Pembayaran
    syncD3MappingTable();

    // Jalankan cek pratinjau perubahan data & tampilkan konfirmasi simpan
    coCheckDiffD1();
}
</script>
@endsection
