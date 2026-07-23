@extends('layouts/contentNavbarLayoutPts')

@section('title', 'SPTJM Online')

@php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\PengaturanUsulan;

//tahun
$tahun = session('tahun') ?? date('Y');
// Cari pengaturan aktif per jenis (SPTJM & TUKIN) pada hari ini
$aktifSptjm = PengaturanUsulan::where('tahun', $tahun)
->where('jenis_usulan', 'SPTJM')
->where('status', 'Aktifkan')
->whereDate('tanggal_mulai', '<=', now())
->whereDate('tanggal_selesai', '>=', now())
->get();

$aktifTukin = PengaturanUsulan::where('tahun', $tahun)
->where('jenis_usulan', 'TUKIN')
->where('status', 'Aktifkan')
->whereDate('tanggal_mulai', '<=', now())
->whereDate('tanggal_selesai', '>=', now())
->get();
if (Auth::guard('pts')->check()) {
// Login sebagai PTS
$kodePtsLogin = Auth::guard('pts')->user()->kode_pts;
$baseQuery = DB::table('s_transaksi_2')->where('kode_pt', $kodePtsLogin)
->where('Tahun_versi',$tahun);
} elseif (Auth::guard('web')->check()) {
// Login sebagai user biasa (admin/pic)
$pemegangWilayah = Auth::user()->email;
$baseQuery = DB::table('s_transaksi_2')->where('pemegang_wilayah', $pemegangWilayah)
->where('Tahun_versi',$tahun);
} else {
$baseQuery = DB::table('s_transaksi_2')->whereRaw('1 = 0'); // hasil kosong kalau belum login
}

// Jumlah total dosen
$jumlahDosen = (clone $baseQuery)->count();
// Hitung semua kategori
$jumlahPnsAktif = (clone $baseQuery)->where('jenis', 'PNS')->where('aktif', '1')->count();
$jumlahPnsTidakAktif = (clone $baseQuery)->where('jenis', 'PNS')->where(function($q) { $q->where('aktif', '!=', '1')->orWhereNull('aktif'); })->count();
$jumlahNonPnsAktif = (clone $baseQuery)->where('jenis', 'NON PNS')->where('aktif',
'1')->count();
$jumlahNonPnsTidakAktif = (clone $baseQuery)->where('jenis', 'NON PNS')->where(function($q) { $q->where('aktif', '!=', '1')->orWhereNull('aktif'); })->count();
@endphp


@section('content')

<div class="container-xxl d-flex justify-content-center mt-3">
    <div class="mb-4 w-100">
        <div class="card md2-card">
            <div class="card-body text-center py-4 px-4">
                
                    @php
                        $sptjmOpen = $aktifSptjm->isNotEmpty();
                        $tukinOpen = $aktifTukin->isNotEmpty();
                    @endphp

                    {{-- Info SPTJM --}}
                    @if ($sptjmOpen)
                        @php
                            $sptjmMulai = \Carbon\Carbon::parse($aktifSptjm->min('tanggal_mulai'))->format('d-m-Y');
                            $sptjmSelesai = \Carbon\Carbon::parse($aktifSptjm->max('tanggal_selesai'))->format('d-m-Y');
                        @endphp
                        <h5 class="fw-bold d-block mb-1 text-danger card-text-custom">
                            Usulan SPTJM dibuka dari tanggal {{ $sptjmMulai }} sampai {{ $sptjmSelesai }}
                        </h5>
                    @else
                        <h5 class="fw-bold d-block mb-1 text-danger card-text-custom">Usulan SPTJM belum dibuka.</h5>
                    @endif

                    {{-- Info TUKIN --}}
                    @if ($tukinOpen)
                        @php
                            $tukinMulai = \Carbon\Carbon::parse($aktifTukin->min('tanggal_mulai'))->format('d-m-Y');
                            $tukinSelesai = \Carbon\Carbon::parse($aktifTukin->max('tanggal_selesai'))->format('d-m-Y');
                        @endphp
                        <h5 class="fw-bold d-block mb-1 text-danger card-text-custom">
                            Usulan TUKIN dibuka dari tanggal {{ $tukinMulai }} sampai {{ $tukinSelesai }}
                        </h5>
                    @else
                        <h5 class="fw-bold d-block mb-1 text-danger card-text-custom">Usulan TUKIN belum dibuka.</h5>
                    @endif
            </div>
        </div>
    </div>
</div>


<style>
    /* Styling Card Dashboard Operator PTS Ukuran Sedang & Nyaman (Medium Scale) */
    .pts-dashboard-stat-card {
        background: #ffffff !important;
        border-radius: 14px !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.04) !important;
        padding: 1.35rem 1.5rem !important;
        margin-bottom: 0px !important;
        transition: all 0.2s ease-in-out;
    }
    .pts-dashboard-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(26, 86, 219, 0.1) !important;
        border-color: #cbd5e1 !important;
    }
    .pts-stat-icon-lg {
        width: 72px !important;
        height: 72px !important;
        border-radius: 50% !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 3.2rem !important;
        flex-shrink: 0 !important;
    }
    .pts-stat-icon-lg i {
        font-size: 3.2rem !important;
    }
    .pts-stat-val-lg {
        font-size: 1.85rem !important;
        font-weight: 750 !important;
        line-height: 1.1;
    }
    .pts-stat-title-lg {
        color: #475569 !important;
        font-size: 0.82rem !important;
        font-weight: 650 !important;
        margin-bottom: 0.3rem !important;
        text-transform: uppercase;
        letter-spacing: 0.035em;
    }
</style>

<div class="row g-4 my-2">
    <!-- Jumlah Seluruh Dosen -->
    <div class="col-12 col-md-6 col-lg-4">
        <div class="pts-dashboard-stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="pts-stat-title-lg">Seluruh Dosen {{ session('tahun') ?: date('Y') }}</div>
                    <div class="pts-stat-val-lg val-primary">{{ number_format($jumlahDosen, 0, ',', '.') }}</div>
                </div>
                <div class="pts-stat-icon-lg icon-bg-primary">
                    <i class="bx bx-group"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Dosen PNS Aktif -->
    <div class="col-12 col-md-6 col-lg-4">
        <div class="pts-dashboard-stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="pts-stat-title-lg">Dosen PNS Aktif</div>
                    <div class="pts-stat-val-lg val-primary">{{ number_format($jumlahPnsAktif, 0, ',', '.') }}</div>
                </div>
                <div class="pts-stat-icon-lg icon-bg-primary">
                    <i class="bx bx-user-check"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Dosen PNS Tidak Aktif -->
    <div class="col-12 col-md-6 col-lg-4">
        <div class="pts-dashboard-stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="pts-stat-title-lg">Dosen PNS Tidak Aktif</div>
                    <div class="pts-stat-val-lg val-danger">{{ number_format($jumlahPnsTidakAktif, 0, ',', '.') }}</div>
                </div>
                <div class="pts-stat-icon-lg icon-bg-danger">
                    <i class="bx bx-user-x"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Dosen Non-PNS Aktif -->
    <div class="col-12 col-md-6 col-lg-4">
        <div class="pts-dashboard-stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="pts-stat-title-lg">Dosen Non-PNS Aktif</div>
                    <div class="pts-stat-val-lg val-primary">{{ number_format($jumlahNonPnsAktif, 0, ',', '.') }}</div>
                </div>
                <div class="pts-stat-icon-lg icon-bg-primary">
                    <i class="bx bx-user-check"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Dosen Non-PNS Tidak Aktif -->
    <div class="col-12 col-md-6 col-lg-4">
        <div class="pts-dashboard-stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="pts-stat-title-lg">Dosen Non-PNS Tidak Aktif</div>
                    <div class="pts-stat-val-lg val-danger">{{ number_format($jumlahNonPnsTidakAktif, 0, ',', '.') }}</div>
                </div>
                <div class="pts-stat-icon-lg icon-bg-danger">
                    <i class="bx bx-user-x"></i>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
