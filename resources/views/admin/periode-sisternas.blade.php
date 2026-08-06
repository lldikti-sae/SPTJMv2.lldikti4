@extends('layouts/contentNavbarLayout')

@section('title', 'Set Periode Sisternas')

@section('content')
<style>
    .content-wrapper > div.container-p-y {
        padding-top: 0.5rem !important;
    }

    /* ===== Set Periode Sisternas Wrapper ===== */
    .sp-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 18px 22px 22px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.04);
    }

    /* Top Bar: Judul, Design Switcher & Filter Tahun */
    .sp-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 16px;
        padding-bottom: 14px;
        border-bottom: 1.5px solid #f1f5f9;
    }
    .sp-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
    }
    .sp-header-controls {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    /* Design Switcher Pill */
    .sp-design-switcher {
        display: inline-flex;
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        border-radius: 20px;
        padding: 3px;
        gap: 3px;
    }
    .sp-design-btn {
        padding: 4px 14px;
        border-radius: 16px;
        border: none;
        background: transparent;
        font-size: 0.78rem;
        font-weight: 700;
        color: #64748b;
        cursor: pointer;
        transition: all 0.2s ease;
        line-height: 1.4;
    }
    .sp-design-btn.active {
        background: #0f172a;
        color: #ffffff;
        box-shadow: 0 2px 6px rgba(15,23,42,0.2);
    }

    /* Filter Tahun Select (Desain 1 & 2) */
    .sp-filter-wrap {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .sp-filter-label {
        font-size: 0.82rem;
        font-weight: 600;
        color: #475569;
        white-space: nowrap;
    }
    .sp-select-tahun {
        border: 1.5px solid #cbd5e1;
        border-radius: 8px;
        padding: 5px 28px 5px 10px;
        font-size: 0.84rem;
        font-weight: 600;
        color: #1e293b;
        background-color: #f8fafc;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23475569' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 9px center;
        background-size: 12px;
        appearance: none;
        -webkit-appearance: none;
        outline: none;
        cursor: pointer;
        min-width: 120px;
        transition: all 0.18s ease;
    }

    /* ========================================================= */
    /* ===== DESAIN 1: Checkbox Table (2 Column Semester) ===== */
    /* ========================================================= */
    .sp-design-1-wrap {
        display: block;
    }
    .sp-bulan-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.84rem;
    }
    .sp-bulan-table thead th {
        font-size: 0.72rem;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        padding: 8px 12px;
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        text-align: left;
    }
    .sp-bulan-table tbody td {
        border: 1px solid #e2e8f0;
        padding: 0;
    }
    .sp-bulan-table tbody tr:hover td {
        background: #f8fafc;
    }
    .sp-bulan-table input[type="checkbox"] {
        display: none;
    }
    .sp-bulan-table label {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 9px 14px;
        cursor: pointer;
        user-select: none;
        color: #334155;
        font-weight: 500;
        transition: background 0.12s ease;
        width: 100%;
    }
    .sp-bulan-table label::before {
        content: '';
        flex-shrink: 0;
        width: 16px;
        height: 16px;
        border-radius: 4px;
        border: 2px solid #cbd5e1;
        background: #ffffff;
        transition: all 0.14s ease;
    }
    .sp-bulan-table input[type="checkbox"]:checked + label {
        color: #1d4ed8;
        font-weight: 600;
        background: #eff6ff;
    }
    .sp-bulan-table input[type="checkbox"]:checked + label::before {
        background: #2563eb;
        border-color: #2563eb;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%23ffffff' stroke-width='3.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='20 6 9 17 4 12'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: center;
        background-size: 10px;
    }

    /* ========================================================= */
    /* ===== DESAIN 2: Spreadsheet Select Dropdown Table ===== */
    /* ========================================================= */
    .sp-design-2-wrap {
        display: none;
    }
    .sp-table-container {
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        overflow: hidden;
    }
    .sp-table-d2 {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.84rem;
        background: #ffffff;
    }
    .sp-table-d2 thead tr {
        background: #f8fafc;
        border-bottom: 1.5px solid #cbd5e1;
    }
    .sp-table-d2 thead th {
        padding: 9px 14px;
        font-size: 0.75rem;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-right: 1px solid #cbd5e1;
        text-align: left;
    }
    .sp-table-d2 thead th:last-child {
        border-right: none;
    }
    .sp-table-d2 tbody tr {
        border-bottom: 1px solid #cbd5e1;
        transition: background 0.12s ease;
    }
    .sp-table-d2 tbody tr:last-child {
        border-bottom: none;
    }
    .sp-table-d2 tbody tr:hover {
        background: #f1f5f9;
    }
    .sp-table-d2 tbody td {
        padding: 6px 12px;
        border-right: 1px solid #cbd5e1;
        color: #1e293b;
        vertical-align: middle;
    }
    .sp-table-d2 tbody td:last-child {
        border-right: none;
    }
    .sp-td-tahun {
        font-weight: 800;
        font-size: 0.9rem;
        color: #0f172a;
        background: #f8fafc;
        text-align: center;
        vertical-align: middle;
        width: 120px;
    }
    .sp-td-bulan {
        font-weight: 600;
        color: #334155;
        width: 160px;
        text-transform: capitalize;
    }
    .sp-cell-select {
        width: 100%;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        padding: 5px 28px 5px 10px;
        font-size: 0.82rem;
        font-weight: 500;
        color: #0f172a;
        background-color: #ffffff;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 8px center;
        background-size: 11px;
        appearance: none;
        -webkit-appearance: none;
        outline: none;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .sp-cell-select.has-value {
        font-weight: 700;
        color: #1d4ed8;
        border-color: #93c5fd;
        background-color: #eff6ff;
    }

    /* ========================================================= */
    /* ===== DESAIN 3: Terpisah (Tahun, Bulan, Periode Laporan) */
    /* ========================================================= */
    .sp-design-3-wrap {
        display: none;
    }
    .sp-d3-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 16px;
    }
    @media (max-width: 900px) {
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

    /* Column 1: Tahun List */
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

    /* Column 2: Bulan List (Multiple Checkbox) */
    .sp-d3-month-list {
        display: grid;
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

    /* Column 3: Periode Laporan Select */
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

    /* ===== DESAIN 3: Table Mapping Pemetaan Pembayaran ===== */
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
        border-radius: 8px;
        overflow: hidden;
        border: 1.5px solid #e2e8f0;
    }
    .sp-d3-table {
        width: 100%;
        margin-bottom: 0;
        border-collapse: collapse;
    }
    .sp-d3-table th {
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 9px 12px;
        text-align: center;
        vertical-align: middle;
    }
    .sp-d3-table th.th-group-bayar {
        background-color: #eff6ff !important;
        color: #1e40af !important;
        border-bottom: 1.5px solid #bfdbfe;
    }
    .sp-d3-table th.th-group-pembuat {
        background-color: #f0fdf4 !important;
        color: #166534 !important;
        border-bottom: 1.5px solid #bbf7d0;
    }
    .sp-d3-table th.th-sub {
        background-color: #f8fafc !important;
        color: #475569 !important;
        border-bottom: 1.5px solid #e2e8f0;
    }
    .sp-d3-table td {
        padding: 6px 8px;
        vertical-align: middle;
        border-top: 1px solid #f1f5f9;
        background: #ffffff;
    }
    .sp-d3-input {
        border: 1.5px solid #cbd5e1;
        border-radius: 6px;
        padding: 6px 10px;
        font-size: 0.82rem;
        width: 100%;
        text-align: center;
        font-weight: 600;
        color: #1e293b;
        transition: all 0.15s ease;
    }
    .sp-d3-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        outline: none;
    }

    /* Shared Preview Box */
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

    /* Footer Actions */
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
</style>

<div class="sp-card">

    {{-- Header Bar --}}
    <div class="sp-header">
        <h1 class="sp-title">Set Periode Sisternas</h1>
        
        <div class="sp-header-controls">
            {{-- Switcher Desain 1 | Desain 2 | Desain 3 --}}
            <div class="sp-design-switcher">
                <button type="button" class="sp-design-btn active" id="btnDesain1" onclick="switchDesign(1)">Desain 1</button>
                <button type="button" class="sp-design-btn" id="btnDesain2" onclick="switchDesign(2)">Desain 2</button>
                <button type="button" class="sp-design-btn" id="btnDesain3" onclick="switchDesign(3)">Desain 3</button>
            </div>

            {{-- Filter Tahun (Desain 1 & 2) --}}
            <div class="sp-filter-wrap" id="headerFilterWrap">
                <span class="sp-filter-label">Tahun:</span>
                <select id="spTahunSelect" class="sp-select-tahun">
                    <option value="">Semua Tahun</option>
                    @foreach($tahunList as $tahun)
                        <option value="{{ $tahun }}">{{ $tahun }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- ===== DESAIN 1: Checkbox Table Layout ===== --}}
    {{-- ========================================== --}}
    <div class="sp-design-1-wrap" id="design1Content">
        @php
            $genapMonths = [
                'januari'=>'Januari','februari'=>'Februari','maret'=>'Maret',
                'april'=>'April','mei'=>'Mei','juni'=>'Juni',
            ];
            $ganjilMonths = [
                'juli'=>'Juli','agustus'=>'Agustus','september'=>'September',
                'oktober'=>'Oktober','november'=>'November','desember'=>'Desember',
            ];
            $genapKeys  = array_keys($genapMonths);
            $ganjilKeys = array_keys($ganjilMonths);
        @endphp

        <table class="sp-bulan-table">
            <thead>
                <tr>
                    <th style="width:50%;" id="d1ThGanjil">Semester Ganjil (Jul – Des)</th>
                    <th style="width:50%;" id="d1ThGenap">Semester Genap (Jan – Jun)</th>
                </tr>
            </thead>
            <tbody>
                @for($i = 0; $i < 6; $i++)
                <tr>
                    <td>
                        <input type="checkbox" name="sp_bulan_d1[]" id="d1_bulan_{{ $ganjilKeys[$i] }}" value="{{ $ganjilKeys[$i] }}">
                        <label for="d1_bulan_{{ $ganjilKeys[$i] }}">{{ $ganjilMonths[$ganjilKeys[$i]] }}</label>
                    </td>
                    <td>
                        <input type="checkbox" name="sp_bulan_d1[]" id="d1_bulan_{{ $genapKeys[$i] }}" value="{{ $genapKeys[$i] }}">
                        <label for="d1_bulan_{{ $genapKeys[$i] }}">{{ $genapMonths[$genapKeys[$i]] }}</label>
                    </td>
                </tr>
                @endfor
            </tbody>
        </table>

        {{-- Setting Pemetaan Periode Bayar & Pembayaran (Desain 1) --}}
        <div class="sp-d3-mapping-card">
            <div class="sp-d3-mapping-header">
                <div class="sp-d3-mapping-title">
                    <i class="bx bx-slider-alt text-primary" style="font-size: 1.2rem;"></i>
                    <span>Setting Pemetaan Periode Bayar & Pembayaran</span>
                </div>
                <button type="button" class="btn btn-sm rounded-pill px-3 text-white" onclick="addMappingRow('spD1MappingTbody')" style="font-size: 0.78rem; font-weight: 700; background: #2563eb; border: none; display: inline-flex; align-items: center; gap: 5px;">
                    <i class="bx bx-plus"></i> Tambah Baris
                </button>
            </div>

            <div class="sp-d3-table-wrap table-responsive text-nowrap">
                <table class="table sp-d3-table">
                    <thead>
                        <tr>
                            <th colspan="2" class="th-group-bayar">PERIODE BAYAR</th>
                            <th colspan="3" class="th-group-pembuat">PEMBAYARAN / LAPORAN</th>
                            <th width="60px" style="background-color: #f8fafc; color: #475569;">AKSI</th>
                        </tr>
                        <tr>
                            <th class="th-sub">Tahun</th>
                            <th class="th-sub">Bulan</th>
                            <th class="th-sub">Tahun</th>
                            <th class="th-sub">Bulan</th>
                            <th class="th-sub">Periode</th>
                            <th class="th-sub">-</th>
                        </tr>
                    </thead>
                    <tbody id="spD1MappingTbody">
                        <tr>
                            <td><input type="text" class="sp-d3-input" name="d1_periode_bayar_tahun[]" value="2023" placeholder="misal: 2023"></td>
                            <td><input type="text" class="sp-d3-input" name="d1_periode_bayar_bulan[]" value="maret-agustus" placeholder="misal: maret-agustus"></td>
                            <td><input type="text" class="sp-d3-input" name="d1_pembayaran_tahun[]" value="2023" placeholder="misal: 2023"></td>
                            <td><input type="text" class="sp-d3-input" name="d1_pembayaran_bulan[]" value="" placeholder="-"></td>
                            <td><input type="text" class="sp-d3-input" name="d1_pembayaran_periode[]" value="2022-1" placeholder="misal: 2022-1"></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-danger border-0 p-1" onclick="removeMappingRow(this)" title="Hapus Baris">
                                    <i class="bx bx-trash" style="font-size: 1.1rem;"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td><input type="text" class="sp-d3-input" name="d1_periode_bayar_tahun[]" value="" placeholder="misal: 2023"></td>
                            <td><input type="text" class="sp-d3-input" name="d1_periode_bayar_bulan[]" value="" placeholder="misal: maret-agustus"></td>
                            <td><input type="text" class="sp-d3-input" name="d1_pembayaran_tahun[]" value="2024" placeholder="misal: 2024"></td>
                            <td><input type="text" class="sp-d3-input" name="d1_pembayaran_bulan[]" value="" placeholder="-"></td>
                            <td><input type="text" class="sp-d3-input" name="d1_pembayaran_periode[]" value="2023-1" placeholder="misal: 2023-1"></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-danger border-0 p-1" onclick="removeMappingRow(this)" title="Hapus Baris">
                                    <i class="bx bx-trash" style="font-size: 1.1rem;"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ============================================== --}}
    {{-- ===== DESAIN 2: Spreadsheet Select Layout ===== --}}
    {{-- ============================================== --}}
    <div class="sp-design-2-wrap" id="design2Content">
        <div class="sp-table-container">
            <table class="sp-table-d2">
                <thead>
                    <tr>
                        <th style="width: 120px; text-align: center;">Tahun</th>
                        <th style="width: 160px;">Bulan</th>
                        <th>Periode Laporan</th>
                    </tr>
                </thead>
                <tbody id="spTableBodyD2">
                    {{-- Populated via JS --}}
                </tbody>
            </table>
        </div>

        {{-- Setting Pemetaan Periode Bayar & Pembayaran (Desain 2) --}}
        <div class="sp-d3-mapping-card">
            <div class="sp-d3-mapping-header">
                <div class="sp-d3-mapping-title">
                    <i class="bx bx-slider-alt text-primary" style="font-size: 1.2rem;"></i>
                    <span>Setting Pemetaan Periode Bayar & Pembayaran</span>
                </div>
                <button type="button" class="btn btn-sm rounded-pill px-3 text-white" onclick="addMappingRow('spD2MappingTbody')" style="font-size: 0.78rem; font-weight: 700; background: #2563eb; border: none; display: inline-flex; align-items: center; gap: 5px;">
                    <i class="bx bx-plus"></i> Tambah Baris
                </button>
            </div>

            <div class="sp-d3-table-wrap table-responsive text-nowrap">
                <table class="table sp-d3-table">
                    <thead>
                        <tr>
                            <th colspan="2" class="th-group-bayar">PERIODE BAYAR</th>
                            <th colspan="3" class="th-group-pembuat">PEMBAYARAN / LAPORAN</th>
                            <th width="60px" style="background-color: #f8fafc; color: #475569;">AKSI</th>
                        </tr>
                        <tr>
                            <th class="th-sub">Tahun</th>
                            <th class="th-sub">Bulan</th>
                            <th class="th-sub">Tahun</th>
                            <th class="th-sub">Bulan</th>
                            <th class="th-sub">Periode</th>
                            <th class="th-sub">-</th>
                        </tr>
                    </thead>
                    <tbody id="spD2MappingTbody">
                        <tr>
                            <td><input type="text" class="sp-d3-input" name="d2_periode_bayar_tahun[]" value="2023" placeholder="misal: 2023"></td>
                            <td><input type="text" class="sp-d3-input" name="d2_periode_bayar_bulan[]" value="maret-agustus" placeholder="misal: maret-agustus"></td>
                            <td><input type="text" class="sp-d3-input" name="d2_pembayaran_tahun[]" value="2023" placeholder="misal: 2023"></td>
                            <td><input type="text" class="sp-d3-input" name="d2_pembayaran_bulan[]" value="" placeholder="-"></td>
                            <td><input type="text" class="sp-d3-input" name="d2_pembayaran_periode[]" value="2022-1" placeholder="misal: 2022-1"></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-danger border-0 p-1" onclick="removeMappingRow(this)" title="Hapus Baris">
                                    <i class="bx bx-trash" style="font-size: 1.1rem;"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td><input type="text" class="sp-d3-input" name="d2_periode_bayar_tahun[]" value="" placeholder="misal: 2023"></td>
                            <td><input type="text" class="sp-d3-input" name="d2_periode_bayar_bulan[]" value="" placeholder="misal: maret-agustus"></td>
                            <td><input type="text" class="sp-d3-input" name="d2_pembayaran_tahun[]" value="2024" placeholder="misal: 2024"></td>
                            <td><input type="text" class="sp-d3-input" name="d2_pembayaran_bulan[]" value="" placeholder="-"></td>
                            <td><input type="text" class="sp-d3-input" name="d2_pembayaran_periode[]" value="2023-1" placeholder="misal: 2023-1"></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-danger border-0 p-1" onclick="removeMappingRow(this)" title="Hapus Baris">
                                    <i class="bx bx-trash" style="font-size: 1.1rem;"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ======================================================= --}}
    {{-- ===== DESAIN 3: Terpisah (Tahun | Bulan | Periode) ===== --}}
    {{-- ======================================================= --}}
    <div class="sp-design-3-wrap" id="design3Content">
        <div class="sp-d3-grid">
            
            {{-- Block 1: Tahun --}}
            <div class="sp-d3-block">
                <div class="sp-d3-block-header">
                    <span class="sp-d3-step-num">1</span>
                    <span class="sp-d3-block-title">Pilih Tahun</span>
                </div>
                <div class="sp-d3-year-list">
                    @foreach($tahunList as $tahun)
                        <div class="sp-d3-year-item" onclick="selectD3Year('{{ $tahun }}', this)">
                            <span>Tahun {{ $tahun }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Block 2: Periode Laporan --}}
            <div class="sp-d3-block">
                <div class="sp-d3-block-header">
                    <span class="sp-d3-step-num">2</span>
                    <span class="sp-d3-block-title">Periode Laporan</span>
                </div>
                <div class="sp-d3-periode-list" id="spD3PeriodeList">
                    @foreach($tahunList as $tahun)
                        <div class="sp-d3-periode-item" data-tahun="{{ $tahun }}">
                            <input type="radio" name="sp_periode_d3" id="d3_periode_{{ $tahun }}_1" value="{{ $tahun }}/1" onchange="onD3PeriodeChange(this)">
                            <label for="d3_periode_{{ $tahun }}_1">{{ $tahun }}/1 (Ganjil)</label>
                        </div>
                        <div class="sp-d3-periode-item" data-tahun="{{ $tahun }}">
                            <input type="radio" name="sp_periode_d3" id="d3_periode_{{ $tahun }}_2" value="{{ $tahun }}/2" onchange="onD3PeriodeChange(this)">
                            <label for="d3_periode_{{ $tahun }}_2">{{ $tahun }}/2 (Genap)</label>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Block 3: Bulan --}}
            <div class="sp-d3-block">
                <div class="sp-d3-block-header" style="justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span class="sp-d3-step-num">3</span>
                        <span class="sp-d3-block-title">Pilih Bulan</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 5px;">
                        <input type="checkbox" id="d3SelectAllBulan" onchange="toggleD3SelectAll(this)" style="cursor: pointer; width: 14px; height: 14px; accent-color: #2563eb;">
                        <label for="d3SelectAllBulan" style="font-size: 0.73rem; font-weight: 700; color: #475569; cursor: pointer; user-select: none; text-transform: uppercase; letter-spacing: 0.03em; margin: 0;">Pilih Semua</label>
                    </div>
                </div>
                <div class="sp-d3-month-list">
                    @php
                        $bulanD3 = [
                            'januari'=>'Januari','februari'=>'Februari','maret'=>'Maret',
                            'april'=>'April','mei'=>'Mei','juni'=>'Juni',
                            'juli'=>'Juli','agustus'=>'Agustus','september'=>'September',
                            'oktober'=>'Oktober','november'=>'November','desember'=>'Desember'
                        ];
                    @endphp
                    @foreach($bulanD3 as $bKey => $bName)
                        <div class="sp-d3-month-item">
                            <input type="checkbox" name="sp_bulan_d3[]" id="d3_bulan_{{ $bKey }}" value="{{ $bKey }}" onchange="updatePreview()">
                            <label for="d3_bulan_{{ $bKey }}">{{ $bName }}</label>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

        {{-- Setting Pemetaan Periode Bayar & Pembayaran (Sesuai Gambar) --}}
        <div class="sp-d3-mapping-card">
            <div class="sp-d3-mapping-header">
                <div class="sp-d3-mapping-title">
                    <span class="sp-d3-step-num" style="background: #2563eb;">4</span>
                    <span>Setting Pemetaan Periode Bayar & Pembayaran</span>
                </div>
                <button type="button" class="btn btn-sm rounded-pill px-3 text-white" onclick="addD3MappingRow()" style="font-size: 0.78rem; font-weight: 700; background: #2563eb; border: none; display: inline-flex; align-items: center; gap: 5px;">
                    <i class="bx bx-plus"></i> Tambah Baris
                </button>
            </div>

            <div class="sp-d3-table-wrap table-responsive text-nowrap">
                <table class="table sp-d3-table" id="spD3MappingTable">
                    <thead>
                        <tr>
                            <th colspan="2" class="th-group-bayar">PERIODE BAYAR</th>
                            <th colspan="3" class="th-group-pembuat">PEMBAYARAN / LAPORAN</th>
                            <th width="60px" style="background-color: #f8fafc; color: #475569;">AKSI</th>
                        </tr>
                        <tr>
                            <th class="th-sub">Tahun</th>
                            <th class="th-sub">Bulan</th>
                            <th class="th-sub">Tahun</th>
                            <th class="th-sub">Bulan</th>
                            <th class="th-sub">Periode</th>
                            <th class="th-sub">-</th>
                        </tr>
                    </thead>
                    <tbody id="spD3MappingTbody">
                        <tr>
                            <td>
                                <input type="text" class="sp-d3-input" name="d3_periode_bayar_tahun[]" value="2023" placeholder="misal: 2023">
                            </td>
                            <td>
                                <input type="text" class="sp-d3-input" name="d3_periode_bayar_bulan[]" value="maret-agustus" placeholder="misal: maret-agustus">
                            </td>
                            <td>
                                <input type="text" class="sp-d3-input" name="d3_pembayaran_tahun[]" value="2023" placeholder="misal: 2023">
                            </td>
                            <td>
                                <input type="text" class="sp-d3-input" name="d3_pembayaran_bulan[]" value="" placeholder="-">
                            </td>
                            <td>
                                <input type="text" class="sp-d3-input" name="d3_pembayaran_periode[]" value="2022-1" placeholder="misal: 2022-1">
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-danger border-0 p-1" onclick="removeD3Row(this)" title="Hapus Baris">
                                    <i class="bx bx-trash" style="font-size: 1.1rem;"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input type="text" class="sp-d3-input" name="d3_periode_bayar_tahun[]" value="" placeholder="misal: 2023">
                            </td>
                            <td>
                                <input type="text" class="sp-d3-input" name="d3_periode_bayar_bulan[]" value="" placeholder="misal: maret-agustus">
                            </td>
                            <td>
                                <input type="text" class="sp-d3-input" name="d3_pembayaran_tahun[]" value="2024" placeholder="misal: 2024">
                            </td>
                            <td>
                                <input type="text" class="sp-d3-input" name="d3_pembayaran_bulan[]" value="" placeholder="-">
                            </td>
                            <td>
                                <input type="text" class="sp-d3-input" name="d3_pembayaran_periode[]" value="2023-1" placeholder="misal: 2023-1">
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-danger border-0 p-1" onclick="removeD3Row(this)" title="Hapus Baris">
                                    <i class="bx bx-trash" style="font-size: 1.1rem;"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Shared Preview Box --}}
    <div class="sp-preview" id="spPreview">
        <i class="bx bx-check-circle sp-preview-icon"></i>
        <div>
            <div class="sp-preview-label">Periode yang akan di-set</div>
            <div class="sp-preview-tahun" id="spPreviewTahun"></div>
            <div class="sp-preview-months" id="spPreviewMonths"></div>
        </div>
    </div>

    {{-- Footer Actions --}}
    <div class="sp-footer">
        <button type="button" class="sp-btn-reset" onclick="resetForm()">
            <i class="bx bx-reset"></i> Reset
        </button>
        <button type="button" class="sp-btn-save" onclick="saveSetting()">
            <i class="bx bx-save"></i> Simpan Setting
        </button>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
let currentDesign = 1;
let selectedD3Year = null;

const yearsAvailable = @json($tahunList);
const listBulan = [
    { key: 'januari', name: 'Januari' },
    { key: 'februari', name: 'Februari' },
    { key: 'maret', name: 'Maret' },
    { key: 'april', name: 'April' },
    { key: 'mei', name: 'Mei' },
    { key: 'juni', name: 'Juni' },
    { key: 'juli', name: 'Juli' },
    { key: 'agustus', name: 'Agustus' },
    { key: 'september', name: 'September' },
    { key: 'oktober', name: 'Oktober' },
    { key: 'november', name: 'November' },
    { key: 'desember', name: 'Desember' }
];

const bulanLabels = {
    januari:'Januari', februari:'Februari', maret:'Maret',
    april:'April', mei:'Mei', juni:'Juni',
    juli:'Juli', agustus:'Agustus', september:'September',
    oktober:'Oktober', november:'November', desember:'Desember'
};

const periodeOptions = [
    { val: '2022/1', label: '2022/1 (Ganjil)' },
    { val: '2022/2', label: '2022/2 (Genap)' },
    { val: '2023/1', label: '2023/1 (Ganjil)' },
    { val: '2023/2', label: '2023/2 (Genap)' },
    { val: '2024/1', label: '2024/1 (Ganjil)' },
    { val: '2024/2', label: '2024/2 (Genap)' },
    { val: '2025/1', label: '2025/1 (Ganjil)' },
    { val: '2025/2', label: '2025/2 (Genap)' },
    { val: '2026/1', label: '2026/1 (Ganjil)' },
    { val: '2026/2', label: '2026/2 (Genap)' }
];

function switchDesign(designNum) {
    currentDesign = designNum;
    const btn1 = document.getElementById('btnDesain1');
    const btn2 = document.getElementById('btnDesain2');
    const btn3 = document.getElementById('btnDesain3');
    const content1 = document.getElementById('design1Content');
    const content2 = document.getElementById('design2Content');
    const content3 = document.getElementById('design3Content');
    const headerFilter = document.getElementById('headerFilterWrap');

    btn1.classList.remove('active');
    btn2.classList.remove('active');
    btn3.classList.remove('active');
    content1.style.display = 'none';
    content2.style.display = 'none';
    content3.style.display = 'none';

    if (designNum === 1) {
        btn1.classList.add('active');
        content1.style.display = 'block';
        headerFilter.style.display = 'flex';
    } else if (designNum === 2) {
        btn2.classList.add('active');
        content2.style.display = 'block';
        headerFilter.style.display = 'flex';
        renderTableD2();
    } else if (designNum === 3) {
        btn3.classList.add('active');
        content3.style.display = 'block';
        headerFilter.style.display = 'none'; // Desain 3 has its own Year column
    }
    updatePreview();
}

function onD3PeriodeChange(el) {
    const val = el ? el.value : '';
    const ganjilBulan = ['juli', 'agustus', 'september', 'oktober', 'november', 'desember'];
    const genapBulan = ['januari', 'februari', 'maret', 'april', 'mei', 'juni'];

    if (val.endsWith('/1')) {
        document.querySelectorAll('input[name="sp_bulan_d3[]"]').forEach(cb => {
            cb.checked = ganjilBulan.includes(cb.value);
        });
    } else if (val.endsWith('/2')) {
        document.querySelectorAll('input[name="sp_bulan_d3[]"]').forEach(cb => {
            cb.checked = genapBulan.includes(cb.value);
        });
    }

    const allCb = document.querySelectorAll('input[name="sp_bulan_d3[]"]');
    const checkedCb = document.querySelectorAll('input[name="sp_bulan_d3[]"]:checked');
    const selectAllEl = document.getElementById('d3SelectAllBulan');
    if (selectAllEl) {
        selectAllEl.checked = (allCb.length > 0 && allCb.length === checkedCb.length);
    }

    updatePreview();
}

function selectD3Year(year, el) {
    document.querySelectorAll('.sp-d3-year-item').forEach(i => i.classList.remove('active'));
    el.classList.add('active');
    selectedD3Year = year;
    renderD3PeriodeOptions(year);
    updatePreview();
}

function renderD3PeriodeOptions(selectedYear) {
    const listContainer = document.getElementById('spD3PeriodeList');
    if (!listContainer) return;

    let years = selectedYear ? [selectedYear] : yearsAvailable;
    let html = '';
    years.forEach(y => {
        html += `
            <div class="sp-d3-periode-item" data-tahun="${y}">
                <input type="radio" name="sp_periode_d3" id="d3_periode_${y}_1" value="${y}/1" onchange="onD3PeriodeChange(this)">
                <label for="d3_periode_${y}_1">${y}/1 (Ganjil)</label>
            </div>
            <div class="sp-d3-periode-item" data-tahun="${y}">
                <input type="radio" name="sp_periode_d3" id="d3_periode_${y}_2" value="${y}/2" onchange="onD3PeriodeChange(this)">
                <label for="d3_periode_${y}_2">${y}/2 (Genap)</label>
            </div>
        `;
    });
    listContainer.innerHTML = html;
}

function renderTableD2() {
    const selectedYear = document.getElementById('spTahunSelect').value;
    const yearsToRender = selectedYear ? [selectedYear] : yearsAvailable;
    const tbody = document.getElementById('spTableBodyD2');

    let html = '';
    yearsToRender.forEach(year => {
        listBulan.forEach((bulan, idx) => {
            html += `<tr>`;
            if (idx === 0) {
                html += `<td class="sp-td-tahun" rowspan="${listBulan.length}">${year}</td>`;
            }
            html += `<td class="sp-td-bulan">${bulan.name}</td>`;
            html += `<td>
                <select class="sp-cell-select" data-tahun="${year}" data-bulan="${bulan.key}" onchange="handleSelectChange(this)">
                    <option value="">-- Pilih Periode Laporan --</option>`;
            periodeOptions.forEach(opt => {
                html += `<option value="${opt.val}">${opt.label}</option>`;
            });
            html += `</select></td></tr>`;
        });
    });
    tbody.innerHTML = html;
}

function handleSelectChange(selectEl) {
    if (selectEl.value) {
        selectEl.classList.add('has-value');
    } else {
        selectEl.classList.remove('has-value');
    }
    updatePreview();
}

function updatePreview() {
    const year = document.getElementById('spTahunSelect').value;
    const preview = document.getElementById('spPreview');

    if (currentDesign === 1) {
        const checked = [...document.querySelectorAll('input[name="sp_bulan_d1[]"]:checked')].map(c => c.value);
        if (year && checked.length > 0) {
            document.getElementById('spPreviewTahun').textContent = 'Tahun ' + year;
            document.getElementById('spPreviewMonths').textContent = checked.map(b => bulanLabels[b] || b).join(' · ');
            preview.classList.add('show');
        } else {
            preview.classList.remove('show');
        }
    } else if (currentDesign === 2) {
        const selectsWithVal = [...document.querySelectorAll('.sp-cell-select.has-value')];
        if (selectsWithVal.length > 0) {
            document.getElementById('spPreviewTahun').textContent = year ? 'Tahun ' + year : 'Semua Tahun';
            document.getElementById('spPreviewMonths').textContent = selectsWithVal.length + ' bulan telah di-set periode laporannya';
            preview.classList.add('show');
        } else {
            preview.classList.remove('show');
        }
    } else if (currentDesign === 3) {
        const checkedBulan = [...document.querySelectorAll('input[name="sp_bulan_d3[]"]:checked')].map(c => c.value);
        const selectedPeriode = document.querySelector('input[name="sp_periode_d3"]:checked');
        if (selectedD3Year && checkedBulan.length > 0 && selectedPeriode) {
            document.getElementById('spPreviewTahun').textContent = 'Tahun ' + selectedD3Year + ' (' + selectedPeriode.value + ')';
            document.getElementById('spPreviewMonths').textContent = 'Bulan: ' + checkedBulan.map(b => bulanLabels[b] || b).join(' · ');
            preview.classList.add('show');
        } else {
            preview.classList.remove('show');
        }
    }
}

function toggleD3SelectAll(el) {
    document.querySelectorAll('input[name="sp_bulan_d3[]"]').forEach(cb => { cb.checked = el.checked; });
    updatePreview();
}

function updateD1Headers() {
    const year = document.getElementById('spTahunSelect').value;
    const thGanjil = document.getElementById('d1ThGanjil');
    const thGenap = document.getElementById('d1ThGenap');
    if (!thGanjil || !thGenap) return;

    if (year) {
        thGanjil.textContent = year + '/1 (Jul – Des)';
        thGenap.textContent = year + '/2 (Jan – Jun)';
    } else {
        thGanjil.textContent = 'Semester Ganjil (Jul – Des)';
        thGenap.textContent = 'Semester Genap (Jan – Jun)';
    }
}

function resetForm() {
    document.getElementById('spTahunSelect').value = '';
    document.querySelectorAll('input[name="sp_bulan_d1[]"]').forEach(cb => cb.checked = false);
    document.querySelectorAll('input[name="sp_bulan_d3[]"]').forEach(cb => cb.checked = false);
    const selectAllD3 = document.getElementById('d3SelectAllBulan');
    if (selectAllD3) selectAllD3.checked = false;
    document.querySelectorAll('input[name="sp_periode_d3"]').forEach(r => r.checked = false);
    document.querySelectorAll('.sp-d3-year-item').forEach(i => i.classList.remove('active'));
    selectedD3Year = null;
    renderD3PeriodeOptions(null);
    updateD1Headers();

    if (currentDesign === 2) renderTableD2();
    updatePreview();
}

function addMappingRow(tbodyId) {
    const tbody = document.getElementById(tbodyId);
    if (!tbody) return;
    const prefix = tbodyId.startsWith('spD1') ? 'd1' : (tbodyId.startsWith('spD2') ? 'd2' : 'd3');
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td><input type="text" class="sp-d3-input" name="${prefix}_periode_bayar_tahun[]" placeholder="misal: 2023"></td>
        <td><input type="text" class="sp-d3-input" name="${prefix}_periode_bayar_bulan[]" placeholder="misal: maret-agustus"></td>
        <td><input type="text" class="sp-d3-input" name="${prefix}_pembayaran_tahun[]" placeholder="misal: 2024"></td>
        <td><input type="text" class="sp-d3-input" name="${prefix}_pembayaran_bulan[]" placeholder="-"></td>
        <td><input type="text" class="sp-d3-input" name="${prefix}_pembayaran_periode[]" placeholder="misal: 2023-1"></td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger border-0 p-1" onclick="removeMappingRow(this)" title="Hapus Baris">
                <i class="bx bx-trash" style="font-size: 1.1rem;"></i>
            </button>
        </td>
    `;
    tbody.appendChild(tr);
}

function removeMappingRow(btn) {
    const tr = btn.closest('tr');
    if (tr) tr.remove();
}

function addD3MappingRow() {
    addMappingRow('spD3MappingTbody');
}

function removeD3Row(btn) {
    removeMappingRow(btn);
}

function saveSetting() {
    Swal.fire({
        title: 'Simpan Setting Periode?',
        text: 'Pengaturan periode sisternas akan disimpan.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0f172a',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Simpan',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Berhasil!',
                text: 'Pengaturan periode berhasil disimpan.',
                icon: 'success',
                timer: 1800,
                showConfirmButton: false
            });
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('spTahunSelect').addEventListener('change', function() {
        updateD1Headers();
        if (currentDesign === 2) renderTableD2();
        updatePreview();
    });
    document.querySelectorAll('input[name="sp_bulan_d1[]"]').forEach(el => el.addEventListener('change', updatePreview));
});
</script>
@endsection
