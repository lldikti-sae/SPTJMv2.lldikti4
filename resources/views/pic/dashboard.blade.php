@extends('layouts/contentNavbarLayoutPic')

@section('title', 'SPTJM Online')

@section('page-style')
<style>
    /* PIC dashboard: kartu lebih besar karena tidak ada tabel di bawah */
    .sptjm-stat-card {
        padding: 1.5rem 1.75rem !important;
    }
    .sptjm-stat-title {
        font-size: 0.8rem !important;
        margin-bottom: 0.4rem !important;
    }
    .sptjm-stat-value {
        font-size: 1.85rem !important;
        line-height: 1.15 !important;
    }
    .sptjm-stat-icon-wrapper {
        width: 80px !important;
        height: 80px !important;
        border-radius: 50% !important;
    }
    .sptjm-stat-icon-wrapper i {
        font-size: 2.8rem !important;
        line-height: 1 !important;
    }
</style>
@endsection

@section('content')

@if(isset($pendingComplainCount) && $pendingComplainCount > 0)
{{-- Floating Toast Notification --}}
<style>
    .sptjm-toast-wrap {
        position: fixed;
        bottom: 28px;
        right: 28px;
        z-index: 9999;
        animation: slideInToast 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    @keyframes slideInToast {
        from { opacity: 0; transform: translateY(24px) scale(0.96); }
        to   { opacity: 1; transform: translateY(0)   scale(1); }
    }
    .sptjm-toast {
        background: #ffffff;
        border-radius: 14px;
        box-shadow: 0 8px 32px rgba(220, 38, 38, 0.18), 0 2px 8px rgba(0,0,0,0.08);
        border-left: 4px solid #dc2626;
        padding: 14px 18px 14px 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 280px;
        max-width: 340px;
        position: relative;
    }
    .sptjm-toast-icon {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: rgba(220, 38, 38, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: #dc2626;
        font-size: 1.2rem;
    }
    .sptjm-toast-body { flex: 1; min-width: 0; }
    .sptjm-toast-title {
        font-size: 0.78rem;
        font-weight: 700;
        color: #dc2626;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 2px;
    }
    .sptjm-toast-msg {
        font-size: 0.80rem;
        color: #374151;
        line-height: 1.4;
    }
    .sptjm-toast-msg a {
        color: #1a56db;
        font-weight: 600;
        text-decoration: none;
    }
    .sptjm-toast-msg a:hover { text-decoration: underline; }
    .sptjm-toast-close {
        background: none;
        border: none;
        cursor: pointer;
        color: #94a3b8;
        font-size: 1rem;
        padding: 0;
        line-height: 1;
        flex-shrink: 0;
        transition: color 0.15s;
    }
    .sptjm-toast-close:hover { color: #374151; }
</style>
<div class="sptjm-toast-wrap" id="complainToast">
    <div class="sptjm-toast">
        <div class="sptjm-toast-icon">
            <i class="bx bx-bell-ring"></i>
        </div>
        <div class="sptjm-toast-body">
            <div class="sptjm-toast-title">Complain Masuk</div>
            <div class="sptjm-toast-msg">
                <strong>{{ $pendingComplainCount }}</strong> complain menunggu tindakan.
                <a href="{{ route('pic.complain.index') }}">Lihat sekarang →</a>
            </div>
        </div>
        <button class="sptjm-toast-close" onclick="document.getElementById('complainToast').remove()" title="Tutup">
            <i class="bx bx-x"></i>
        </button>
    </div>
</div>
@endif


<div class="row g-2 mb-2">
    {{-- Dosen PNS Aktif --}}
    <div class="col-12 col-md-6 col-lg-4">
        <div class="sptjm-stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="sptjm-stat-title">Dosen PNS Aktif</div>
                    <div class="sptjm-stat-value val-primary">{{ number_format($jumlahDosenPNSAktif, 0, ',', '.') }}</div>
                </div>
                <div class="sptjm-stat-icon-wrapper icon-bg-primary">
                    <i class="bx bx-user-check"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Dosen PNS Tidak Aktif --}}
    <div class="col-12 col-md-6 col-lg-4">
        <div class="sptjm-stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="sptjm-stat-title">Dosen PNS Tidak Aktif</div>
                    <div class="sptjm-stat-value val-danger">{{ number_format($jumlahDosenPNSTidakAktif, 0, ',', '.') }}</div>
                </div>
                <div class="sptjm-stat-icon-wrapper icon-bg-danger">
                    <i class="bx bx-user-x"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Dosen PNS (Total) --}}
    <div class="col-12 col-md-6 col-lg-4">
        <div class="sptjm-stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="sptjm-stat-title">Dosen PNS</div>
                    <div class="sptjm-stat-value val-primary">{{ number_format($jumlahDosenPNS, 0, ',', '.') }}</div>
                </div>
                <div class="sptjm-stat-icon-wrapper icon-bg-primary">
                    <i class="bx bx-group"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Dosen Non-PNS Aktif --}}
    <div class="col-12 col-md-6 col-lg-4">
        <div class="sptjm-stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="sptjm-stat-title">Dosen Non-PNS Aktif</div>
                    <div class="sptjm-stat-value val-primary">{{ number_format($jumlahDosenNonPNSAktif, 0, ',', '.') }}</div>
                </div>
                <div class="sptjm-stat-icon-wrapper icon-bg-primary">
                    <i class="bx bx-user-check"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Dosen Non-PNS Tidak Aktif --}}
    <div class="col-12 col-md-6 col-lg-4">
        <div class="sptjm-stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="sptjm-stat-title">Dosen Non-PNS Tidak Aktif</div>
                    <div class="sptjm-stat-value val-danger">{{ number_format($jumlahDosenNonPNSTidakAktif, 0, ',', '.') }}</div>
                </div>
                <div class="sptjm-stat-icon-wrapper icon-bg-danger">
                    <i class="bx bx-user-x"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Dosen Non-PNS (Total) --}}
    <div class="col-12 col-md-6 col-lg-4">
        <div class="sptjm-stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="sptjm-stat-title">Dosen Non-PNS</div>
                    <div class="sptjm-stat-value val-primary">{{ number_format($jumlahDosenNonPNS, 0, ',', '.') }}</div>
                </div>
                <div class="sptjm-stat-icon-wrapper icon-bg-primary">
                    <i class="bx bx-group"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Dosen Wilayah --}}
    <div class="col-12 col-md-6 col-lg-4">
        <div class="sptjm-stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="sptjm-stat-title">Dosen Wilayah : {{ ucfirst(Auth::user()->email) }}</div>
                    <div class="sptjm-stat-value val-primary">{{ number_format($jumlahDosen, 0, ',', '.') }}</div>
                </div>
                <div class="sptjm-stat-icon-wrapper icon-bg-primary">
                    <i class="bx bx-group"></i>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

