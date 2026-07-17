@extends('layouts/contentNavbarLayoutPic')

@section('title', 'SPTJM Online')

@section('content')

@php
$transaksi = $transaksi ?? null;
$months = [
'Januari',
'Februari',
'Maret',
'April',
'Mei',
'Juni',
'Juli',
'Agustus',
'September',
'Oktober',
'November',
'Desember',
];
@endphp

{{-- Page Header --}}
<div class="md-page-header">
    <div class="page-titles">
        <h3>Monitoring Dosen</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Monitoring</a></li>
                <li class="breadcrumb-item active">Monitoring Pembayaran Dosen</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card md-card">
  <div class="md-card-inner">

  @if (session('error'))
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  @endif

  <form action="{{ route('monitoring-dosen.cari') }}" method="POST">
    @csrf
    <div class="row mb-3 mx-2 align-items-center">
      <label class="col-sm-2 col-form-label"><b style="font-size: 12px;">NIDN / NUPTK</b></label>
      <div class="col-sm-8">
        <input type="text" class="form-control" name="nidn" value="{{ old('nidn', $nidn ?? '') }}"
          placeholder="Masukkan NIDN atau NUPTK">
        <div class="form-text">Bisa menggunakan NIDN atau NUPTK; sebagian/seluruh nilai diterima.</div>
      </div>
      <div class="col-sm-2">
        <button type="submit" class="btn btn-primary rounded-pill px-4 w-100" style="height: 38px;">
          <span class="tf-icons bx bx-search"></span>&nbsp; Cari
        </button>
      </div>
    </div>
  </form>

  @if ($transaksi)
  <div class="row mb-3 mx-2">
    <label class="col-sm-2 col-form-label">NIDN - Nama</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" readonly style="background-color: #eceef1;"
        value="{{ $transaksi->NIDN }} - {{ $transaksi->Nama }}">
    </div>
  </div>

  <div class="row mb-3 mx-2">
    <label class="col-sm-2 col-form-label">Jabatan - Status</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" readonly style="background-color: #eceef1;"
        value="{{ $transaksi->jabatan ?? '-' }} - {{ $transaksi->Aktif == 1 ? 'Aktif' : 'Tidak Aktif' }}">
    </div>
  </div>

  <div class="row mb-3 mx-2">
    <label class="col-sm-2 col-form-label">Perguruan Tinggi</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" readonly style="background-color: #eceef1;"
        value="{{ $transaksi->Kode_PT }} - {{ $transaksi->PTS }}">
    </div>
  </div>

  <hr>

  <div class="table-responsive text-nowrap mt-4 mx-4">
    <table class="table table-bordered table-hover">
      <thead>
        <tr>
          <th rowspan="2" class="text-center">Bulan</th>
          <th rowspan="2" class="text-center">Kode Usulan</th>
          <th rowspan="2" class="text-center">Pangkat Golongan</th>
          <th rowspan="2" class="text-center">Gaji</th>
          <th colspan="6" class="text-center">Nominal</th>
          <th rowspan="2" class="text-center">NO SP2D</th>
          <th rowspan="2" class="text-center">TGL SP2D</th>
        </tr>
        <tr>
          <th class="text-center">Kotor TPD</th>
          <th class="text-center">Kotor TKGB</th>
          <th class="text-center">Pajak TPD</th>
          <th class="text-center">Pajak TKGB</th>
          <th class="text-center">Bersih TPD</th>
          <th class="text-center">Bersih TKGB</th>
        </tr>
      </thead>
      <tbody>
        @php
        $totalGaji = array_sum($gajiBulanan);
        $totalKotorTpd = array_sum($kotorTpd);
        $totalKotorTkgb = array_sum($kotorTkgb);
        $totalPajakTpd = array_sum($pajakTpd);
        $totalPajakTkgb = array_sum($pajakTkgb);
        $totalBersihTpd = array_sum($bersihTpd);
        $totalBersihTkgb = array_sum($bersihTkgb);
        @endphp

        @foreach ($months as $index => $month)
        <tr>
          <td><strong>{{ $month }}</strong></td>
          <td class="text-center">{{ $kodeUsulanBulanan[$index] ?? '-' }}</td>
          <td class="text-center">{{ $golonganBulanan[$index] ?? '-' }} -
            {{ $tahunBulanan[$index] ?? '-' }}
          </td>
          <td class="text-center">{{ number_format( $gajiBulanan[$index] ?? 0, 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format($kotorTpd[$index] ?? 0, 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format($kotorTkgb[$index] ?? 0, 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format($pajakTpd[$index] ?? 0, 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format($pajakTkgb[$index] ?? 0, 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format($bersihTpd[$index] ?? 0, 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format($bersihTkgb[$index] ?? 0, 0, ',', '.') }}</td>
          <td class="text-center">{{ $noSp2d[$index] ?? '-' }}</td>
          <td class="text-center">{{ $tglSp2d[$index] ?? '-' }}</td>
        </tr>
        @endforeach

        <tr class="fw-bold">
          <td colspan="3">Jumlah</td>
          <td class="text-end">{{ number_format($totalGaji, 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format($totalKotorTpd, 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format($totalKotorTkgb, 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format($totalPajakTpd, 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format($totalPajakTkgb, 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format($totalBersihTpd, 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format($totalBersihTkgb, 0, ',', '.') }}</td>
          <td></td>
          <td></td>
        </tr>
      </tbody>
    </table>
  </div>
  @endif
  </div>
</div>

@endsection