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


<hr class="my-3">

<div class="row g-2 mb-2">
    <!-- Jumlah Seluruh Dosen -->
    <div class="col-12 col-md-6 col-lg-4">
        <div class="sptjm-stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="sptjm-stat-title">Seluruh Dosen {{ session('tahun') }}</div>
                    <div class="sptjm-stat-value val-primary">{{ number_format($jumlahDosen, 0, ',', '.') }}</div>
                </div>
                <div class="sptjm-stat-icon-wrapper icon-bg-primary">
                    <i class="bx bx-group"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Dosen PNS Aktif -->
    <div class="col-12 col-md-6 col-lg-4">
        <div class="sptjm-stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="sptjm-stat-title">Dosen PNS Aktif</div>
                    <div class="sptjm-stat-value val-primary">{{ number_format($jumlahPnsAktif, 0, ',', '.') }}</div>
                </div>
                <div class="sptjm-stat-icon-wrapper icon-bg-primary">
                    <i class="bx bx-user-check"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Dosen PNS Tidak Aktif -->
    <div class="col-12 col-md-6 col-lg-4">
        <div class="sptjm-stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="sptjm-stat-title">Dosen PNS Tidak Aktif</div>
                    <div class="sptjm-stat-value val-danger">{{ number_format($jumlahPnsTidakAktif, 0, ',', '.') }}</div>
                </div>
                <div class="sptjm-stat-icon-wrapper icon-bg-danger">
                    <i class="bx bx-user-x"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Dosen Non-PNS Aktif -->
    <div class="col-12 col-md-6 col-lg-4">
        <div class="sptjm-stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="sptjm-stat-title">Dosen Non-PNS Aktif</div>
                    <div class="sptjm-stat-value val-primary">{{ number_format($jumlahNonPnsAktif, 0, ',', '.') }}</div>
                </div>
                <div class="sptjm-stat-icon-wrapper icon-bg-primary">
                    <i class="bx bx-user-check"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Dosen Non-PNS Tidak Aktif -->
    <div class="col-12 col-md-6 col-lg-4">
        <div class="sptjm-stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="sptjm-stat-title">Dosen Non-PNS Tidak Aktif</div>
                    <div class="sptjm-stat-value val-danger">{{ number_format($jumlahNonPnsTidakAktif, 0, ',', '.') }}</div>
                </div>
                <div class="sptjm-stat-icon-wrapper icon-bg-danger">
                    <i class="bx bx-user-x"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<hr class="my-4">

@endsection
