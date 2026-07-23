@extends('layouts/contentNavbarLayoutPic')

@section('title', 'SPTJM Online')

@section('content')
@php
    $tahunSession = session('tahun') ?: date('Y');
    $tahunLalu = $tahunSession - 1;
    $tahunDepan = $tahunSession + 1;
@endphp

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- Page Header --}}
<div class="md-page-header">
    <div class="page-titles">
        <h3>Data Sisternas</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Data Sisternas</a></li>
                <li class="breadcrumb-item active">Lihat Data</li>
            </ol>
        </nav>
    </div>
</div>

<style>
    /* Styling Card Bersih Tanpa Garis Anomali */
    .pts-period-card {
        border: 1px solid #cbd5e1 !important;
        border-left: 1px solid #cbd5e1 !important;
        border-radius: 14px !important;
        background: #ffffff !important;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05) !important;
        transition: all 0.25s ease-in-out;
    }
    .pts-period-card:hover {
        border-color: #10b981 !important;
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(16, 185, 129, 0.18) !important;
    }
</style>

<style>
    /* Styling Card Bersih & Sangat Jelas Tanpa Garis Anomali */
    .pts-period-card {
        border: 2px solid #cbd5e1 !important;
        border-radius: 16px !important;
        background: #ffffff !important;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06) !important;
        transition: all 0.25s ease-in-out;
    }
    .pts-period-card:hover {
        border-color: #10b981 !important;
        transform: translateY(-4px);
        box-shadow: 0 12px 30px rgba(16, 185, 129, 0.2) !important;
    }
</style>

<div class="mb-4">
    <h3 class="fw-bold text-dark mb-1" style="font-size: 1.35rem;">
        <i class="bx bx-folder-open text-primary me-2" style="font-size: 1.6rem;"></i>
        Unduh Data Sisternas Per Periode Pembayaran
    </h3>
    <p class="text-muted mb-3" style="font-size: 0.95rem;">Silakan pilih dan klik tombol pada kartu periode di bawah ini untuk melihat/mengunduh dokumen laporan Sisternas.</p>
</div>

<div class="row g-4">
    <!-- Card 1: Pembayaran Maret - Agustus -->
    <div class="col-md-4 col-sm-12">
        <div class="pts-period-card card h-100 p-4 d-flex flex-column" style="min-height: 280px;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="badge bg-success px-3 py-2 fw-bold" style="font-size: 0.88rem;">PEMBAYARAN UTAMA</span>
                <span class="fw-bold text-dark" style="font-size: 1.05rem;">{{ $tahunLalu }}-1 (Ganjil)</span>
            </div>
            <h4 class="fw-bold text-dark mb-2" style="font-size: 1.25rem; color: #065f46 !important;">
                <i class="bx bx-calendar-check me-2 text-success" style="font-size: 1.4rem;"></i>Maret - Agustus {{ $tahunSession }}
            </h4>
            <p class="text-muted mb-4" style="font-size: 0.92rem; line-height: 1.6;">
                Pelaporan BKD: <br><strong class="text-dark">September {{ $tahunLalu }} - Februari {{ $tahunSession }}</strong>
            </p>
            <div class="mt-auto pt-2">
                <a href="{{ route('pic.data-sisternas-export', ['sisternas' => 'p_sister_ganjil_tl']) }}"
                    class="btn btn-success w-100 fw-bold py-3 shadow-sm d-flex align-items-center justify-content-center gap-2" style="border-radius: 12px; font-size: 0.95rem;">
                    <i class="bx bx-download fs-4"></i> UNDUH DATA SISTERNAS
                </a>
            </div>
        </div>
    </div>

    <!-- Card 2: Pembayaran September - Februari -->
    <div class="col-md-4 col-sm-12">
        <div class="pts-period-card card h-100 p-4 d-flex flex-column" style="min-height: 280px;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="badge bg-primary px-3 py-2 fw-bold" style="font-size: 0.88rem;">PERIODE BERJALAN</span>
                <span class="fw-bold text-dark" style="font-size: 1.05rem;">{{ $tahunLalu }}-2 (Genap)</span>
            </div>
            <h4 class="fw-bold text-dark mb-2" style="font-size: 1.25rem; color: #1e40af !important;">
                <i class="bx bx-calendar-check me-2 text-primary" style="font-size: 1.4rem;"></i>Sept {{ $tahunSession }} - Feb {{ $tahunDepan }}
            </h4>
            <p class="text-muted mb-4" style="font-size: 0.92rem; line-height: 1.6;">
                Pelaporan BKD: <br><strong class="text-dark">Maret - Agustus {{ $tahunSession }}</strong>
            </p>
            <div class="mt-auto pt-2">
                <a href="{{ route('pic.data-sisternas-export', ['sisternas' => 'n_sister_genap_bj']) }}"
                    class="btn btn-primary w-100 fw-bold py-3 shadow-sm d-flex align-items-center justify-content-center gap-2" style="border-radius: 12px; font-size: 0.95rem;">
                    <i class="bx bx-download fs-4"></i> UNDUH DATA SISTERNAS
                </a>
            </div>
        </div>
    </div>

    <!-- Card 3: Pembayaran Susulan Genap TL -->
    <div class="col-md-4 col-sm-12">
        <div class="pts-period-card card h-100 p-4 d-flex flex-column" style="min-height: 280px;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="badge bg-secondary px-3 py-2 fw-bold" style="font-size: 0.88rem;">TAHUN LALU</span>
                <span class="fw-bold text-dark" style="font-size: 1.05rem;">{{ $tahunLalu - 1 }}-2 (Genap TL)</span>
            </div>
            <h4 class="fw-bold text-dark mb-2" style="font-size: 1.25rem; color: #334155 !important;">
                <i class="bx bx-calendar-check me-2 text-secondary" style="font-size: 1.4rem;"></i>Sept {{ $tahunLalu }} - Feb {{ $tahunSession }}
            </h4>
            <p class="text-muted mb-4" style="font-size: 0.92rem; line-height: 1.6;">
                Pelaporan BKD: <br><strong class="text-dark">Maret - Agustus {{ $tahunLalu }}</strong>
            </p>
            <div class="mt-auto pt-2">
                <a href="{{ route('pic.data-sisternas-export', ['sisternas' => 'o_sister_genap_tl']) }}"
                    class="btn btn-secondary w-100 fw-bold py-3 shadow-sm d-flex align-items-center justify-content-center gap-2" style="border-radius: 12px; font-size: 0.95rem;">
                    <i class="bx bx-download fs-4"></i> UNDUH DATA SISTERNAS
                </a>
            </div>
        </div>
    </div>
</div>

@endsection