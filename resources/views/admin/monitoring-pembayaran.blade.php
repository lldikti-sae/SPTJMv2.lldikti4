@extends('layouts/contentNavbarLayout')

@section('title', 'Monitoring Pembayaran - SPTJM Online')

@section('page-style')
<style>
.md2-page-header { display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:24px; }

.md2-page-header .breadcrumb { margin:0; font-size:0.8rem; background:none; padding:0; }
.md2-page-header .breadcrumb-item a { color:#696cff; text-decoration:none; }
.md2-page-header .breadcrumb-item.active { color:#8592a3; }
.md2-page-header .breadcrumb-item+.breadcrumb-item::before { color:#8592a3; }

/* ── Status Filter Buttons ── */
.md-status-filters {
    display: flex;
    gap: 8px;
}
.md-status-btn {
    background-color: #fff;
    border: 1px solid #e2e8f0;
    color: #4a5568;
    font-weight: 500;
    font-size: 0.82rem;
    padding: 5px 18px;
    border-radius: 20px;
    transition: all 0.2s;
    cursor: pointer !important;
    position: relative;
    z-index: 10;
    pointer-events: auto !important;
}
.md-status-btn.active {
    background-color: #0b3d91;
    border-color: #0b3d91;
    color: #fff;
}
.md-status-btn:hover:not(.active) {
    background-color: #f8fafc;
    border-color: #cbd5e1;
}
</style>
@endsection

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

<div class="md2-page-header">
    <div class="page-titles">
        <h1>Monitoring Pembayaran</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Monitoring</a></li>
            <li class="breadcrumb-item active">Monitoring Pembayaran</li>
        </ol></nav>
    </div>
</div>

<div class="card" style="width: 100%; padding: 20px 10px 10px;">

  @if (session('error'))
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  @endif

  <form action="{{ route('monitoring-pembayaran.cari') }}" method="POST">
    @csrf
    <div class="row mb-3 mx-2">
      <label class="col-sm-2 col-form-label"><b style="font-size: 12px;">NIDN / NUPTK</b></label>
      <div class="col-sm-6">
        <input type="text" class="form-control" name="nidn" id="search_nidn" value="{{ old('nidn', $nidn ?? '') }}"
          placeholder="Masukkan NIDN/NUPTK" required>
      </div>
      <div class="col-sm-2">
        <button type="submit" class="btn btn-primary w-100">
          <span class="tf-icons bx bx-search"></span>&nbsp; Cari
        </button>
      </div>
    </div>

    <div class="row mb-3 mx-2">
      <label class="col-sm-2 col-form-label"><b style="font-size: 12px;">Tahun</b></label>
      <div class="col-sm-2">
        <select name="start_year" class="form-select">
          @if(!empty($years))
            @php $selStart = old('start_year', $startYear ?? $years[0]); @endphp
            @foreach($years as $y)
              <option value="{{ $y }}" {{ $selStart == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
          @else
            <option value="">-</option>
          @endif
        </select>
      </div>
      <div class="col-auto d-flex align-items-center">
        <strong style="margin:0 6px;">s/d</strong>
      </div>
      <div class="col-sm-2">
        <select name="end_year" class="form-select">
          @if(!empty($years))
            @php $selEnd = old('end_year', $endYear ?? end($years)); @endphp
            @foreach($years as $y)
              <option value="{{ $y }}" {{ $selEnd == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
          @else
            <option value="">-</option>
          @endif
        </select>
      </div>
    </div>
  </form>
  
  @if ($transaksi)
  @php
    $jenis = trim($transaksi->Jenis ?? '');
    $isPns = stripos($jenis, 'PNS') !== false && stripos($jenis, 'NON') === false;
  @endphp

  <div class="row mb-2 mx-2 mt-3">
    <label class="col-sm-2 col-form-label py-1" style="font-size:13px;font-weight:600;">NIDN - Nama</label>
    <div class="col-sm-10">
      <input id="hdr-nidn" type="text" class="form-control" readonly style="background:#eceef1;font-size:14px;font-weight:600;" value="{{ !empty($transaksi->NIDN) ? $transaksi->NIDN : ($transaksi->NUPTK ?? '-') }} - {{ $transaksi->Nama }}">
    </div>
  </div>
  <div class="row mb-2 mx-2">
    <label class="col-sm-2 col-form-label py-1" style="font-size:13px;font-weight:600;">Status Keaktifan</label>
    <div class="col-sm-10">
      <div class="form-control d-flex align-items-center" style="background:#eceef1;font-size:14px;min-height:38px;">
        <span id="hdr-status-jenis" style="font-weight:700;color:{{ $isPns ? '#0056b3' : '#dc3545' }};">{{ $isPns ? 'PNS' : 'Non PNS' }}</span>
        <span class="mx-1">-</span>
        <span id="hdr-status-aktif" style="font-weight:500;color:#4a5568;">{{ $transaksi->Aktif == 1 ? 'Aktif' : 'Tidak Aktif' }}</span>
      </div>
    </div>
  </div>
  <div class="row mb-2 mx-2">
    <label class="col-sm-2 col-form-label py-1" style="font-size:13px;font-weight:600;">Perguruan Tinggi</label>
    <div class="col-sm-10">
      <input id="hdr-pt" type="text" class="form-control" readonly style="background:#eceef1;font-size:14px;" value="{{ $transaksi->Kode_PT }} - {{ $transaksi->PTS }}">
    </div>
  </div>

  {{-- Summary Cards --}}
  @php
    $sKewajiban = $summary['totalKewajiban'] ?? 0;
    $sDibayar = $summary['totalDibayar'] ?? 0;
    $sSelisih = $summary['totalSelisih'] ?? 0;
  @endphp
  <div class="row mx-2 mb-2 mt-1 g-2">
    <div class="col-md-4">
      <div class="card shadow-none border mb-0"><div class="card-body py-1 px-2">
        <div class="d-flex align-items-center">
          <span class="avatar-initial rounded bg-label-danger me-2" style="width:28px;height:28px;display:flex;align-items:center;justify-content:center;font-size:14px;"><i class="bx bx-upload"></i></span>
          <div><span style="font-size:11px; font-weight:700; color:#1a1a2e; display:block;">Total Kewajiban (OUT)</span><strong id="sum-kewajiban" style="font-size:13px; color:#1a1a2e !important; font-weight:800 !important;">Rp {{ number_format($sKewajiban,0,',','.') }}</strong></div>
        </div>
      </div></div>
    </div>
    <div class="col-md-4">
      <div class="card shadow-none border mb-0"><div class="card-body py-1 px-2">
        <div class="d-flex align-items-center">
          <span class="avatar-initial rounded bg-label-info me-2" style="width:28px;height:28px;display:flex;align-items:center;justify-content:center;font-size:14px;"><i class="bx bx-download"></i></span>
          <div><span style="font-size:11px; font-weight:700; color:#1a1a2e; display:block;">Total Dibayar (IN)</span><strong id="sum-dibayar" style="font-size:13px; color:#1a1a2e !important; font-weight:800 !important;">Rp {{ number_format($sDibayar,0,',','.') }}</strong></div>
        </div>
      </div></div>
    </div>
    <div class="col-md-4">
      <div class="card shadow-none border mb-0"><div class="card-body py-1 px-2">
        <div class="d-flex align-items-center">
          <span id="sum-selisih-icon" class="avatar-initial rounded {{ $sSelisih == 0 ? 'bg-label-success' : 'bg-label-danger' }} me-2" style="width:28px;height:28px;display:flex;align-items:center;justify-content:center;font-size:14px;"><i class="bx bx-transfer"></i></span>
          <div><span style="font-size:11px; font-weight:700; color:#1a1a2e; display:block;">Total Selisih</span><strong id="sum-selisih" style="font-size:13px; color:#1a1a2e !important; font-weight:800 !important;" class="{{ $sSelisih == 0 ? 'text-success' : 'text-danger' }}">Rp {{ number_format($sSelisih,0,',','.') }}</strong></div>
        </div>
      </div></div>
    </div>
  </div>

  <hr class="my-2">

  <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <!-- Kiri: Filter Tahun & Tabs -->
    <div class="d-flex align-items-end gap-3">
      <div>
        <label class="form-label mb-1" style="font-size:11px;">Filter Tahun</label>
        <select name="tahun_versi" class="form-select form-select-sm">
          @foreach(['2023','2024','2025','2026'] as $y)
            <option value="{{ $y }}" {{ ($selectedYear ?? null) == $y ? 'selected' : '' }}>{{ $y }}</option>
          @endforeach
        </select>
      </div>
      
      <div class="md-status-filters">
        @if ($isPns)
        <button type="button" class="md-status-btn {{ ($jenisTunjangan ?? 'semua') == 'semua' ? 'active' : '' }}" data-jenis="semua">Semua</button>
        @endif
        <button type="button" class="md-status-btn {{ ($jenisTunjangan ?? '') == 'sptjm' ? 'active' : '' }}" data-jenis="sptjm">SPTJM</button>
        @if ($isPns)
        <button type="button" class="md-status-btn {{ ($jenisTunjangan ?? '') == 'tukin' ? 'active' : '' }}" data-jenis="tukin">TUKIN</button>
        @endif
      </div>
    </div>

    <!-- Kanan: Tombol Cetak -->
    <div class="d-flex gap-2">
      <form action="{{ route('monitoring-pembayaran.export-excel') }}" method="POST">
        @csrf
        <input type="hidden" name="nidn" value="{{ $nidn ?? '' }}">
        <input type="hidden" name="tahun_versi" id="export_tahun_versi" value="{{ $selectedYear ?? '' }}">
        <input type="hidden" name="jenis_tunjangan" id="export_jenis_tunjangan" value="{{ $jenisTunjangan ?? 'semua' }}">
        <button type="submit" class="btn btn-success btn-sm">
          <span class="tf-icons bx bx-download"></span>&nbsp; Cetak
        </button>
      </form>

      <form action="{{ route('monitoring-pembayaran.cetak-spt') }}" method="POST">
        @csrf
        <input type="hidden" name="nidn" value="{{ $nidn ?? '' }}">
        <input type="hidden" name="tahun_versi" id="cetak_spt_tahun_versi" value="{{ $selectedYear ?? '' }}">
        <button type="submit" class="btn btn-primary btn-sm">
          <span class="tf-icons bx bx-printer"></span>&nbsp; Cetak SPT
        </button>
      </form>

      <a
        href="{{ route('monitoring-pembayaran.cek-koordinat-spt-pdf', ['nidn' => $nidn ?? '', 'tahun_versi' => $selectedYear ?? '']) }}"
        target="_blank"
        rel="noopener"
        class="btn btn-outline-danger btn-sm d-none"
      >
        <span class="tf-icons bx bx-target-lock"></span>&nbsp; Cek Koordinat
      </a>
    </div>
  </div>
  
  <input type="hidden" name="start_year" id="hidden_start_year" value="{{ $startYear ?? old('start_year') }}">
  <input type="hidden" name="end_year" id="hidden_end_year" value="{{ $endYear ?? old('end_year') }}">
  <input type="hidden" name="jenis_tunjangan" id="hidden_jenis_tunjangan" value="{{ $jenisTunjangan ?? 'semua' }}">

  <style>
    .mp-tbl{font-size:12px;line-height:1.3}
    .mp-tbl th,.mp-tbl td{padding:3px 5px!important;vertical-align:middle}
    .mp-tbl.tukin-wrap th { white-space: normal !important; min-width: 70px; word-break: break-word; }
    .mp-tbl.tukin-wrap td, .mp-tbl.tukin-wrap th.col-bulan { white-space: nowrap !important; min-width: auto; word-break: normal; }
  </style>
  @php
    $hasTkgb = $isGuruBesar ?? false;
    $isSemua = ($jenisTunjangan ?? 'semua') === 'semua';
    $totalGaji = array_sum($gajiBulanan);
    $totalKotorTpd = array_sum($kotorTpd);
    $totalKotorTkgb = array_sum($kotorTkgb);
    $totalPajakTpd = array_sum($pajakTpd);
    $totalPajakTkgb = array_sum($pajakTkgb);
    $totalBersihTpd = array_sum($bersihTpd);
    $totalBersihTkgb = array_sum($bersihTkgb);
    $nomColspan = $hasTkgb ? 6 : 3;
    $statusMap = ['usulan'=>['bg-label-warning','Usulan'],'proses'=>['bg-label-info','Proses'],'kurang'=>['bg-label-danger','Kurang'],'lebih'=>['bg-label-secondary','Lebih'],'selesai'=>['bg-label-success','Selesai']];
  @endphp
  <div id="mp-table-wrapper" class="table-responsive {{ ($jenisTunjangan ?? 'semua') == 'tukin' ? '' : 'text-nowrap' }} mt-2" style="overflow:auto;padding-right:0;">
    <table class="table table-bordered table-hover table-sm mp-tbl {{ ($jenisTunjangan ?? 'semua') == 'tukin' ? 'tukin-wrap' : '' }}" id="mp-table" style="width:100%" data-has-tkgb="{{ $hasTkgb ? '1' : '0' }}">
      @if (($jenisTunjangan ?? 'semua') == 'tukin')
      <thead>
        <tr>
          <th class="text-center align-middle">Tahun</th>
          <th class="text-center align-middle col-bulan">Bulan</th>
          <th class="text-center align-middle">Kode Usulan</th>
          <th class="text-center align-middle">Jabatan / Gol-MK</th>
          <th class="text-center align-middle">Nominal TUKIN</th>
          <th class="text-center align-middle">% KD</th>
          <th class="text-center align-middle">Nominal Kinerja Dasar</th>
          <th class="text-center align-middle">% KP</th>
          <th class="text-center align-middle">Nominal Kinerja Prestasi</th>
          <th class="text-center align-middle">Nominal Bersih TPD</th>
          <th class="text-center align-middle">% PP </th>
          <th class="text-center align-middle">Potongan Periodik</th>
          <th class="text-center align-middle">Nilai Bersih TUKIN</th>
          <th class="text-center align-middle">No SP2D</th>
          <th class="text-center align-middle">Tgl SP2D</th>
          <th class="text-center align-middle">Status</th>
        </tr>
      </thead>
      <tbody>
        @php
            $totGaji = 0; $totDasar = 0; $totPrestasi = 0; $totBersihTpd = 0; $totPotongan = 0; $totNilaiBersih = 0;
        @endphp
        @foreach ($months as $index => $month)
        @php
            $st = $statusBulanan[$index] ?? null;
            $nominalTukin = $gajiBulanan[$index] ?? 0;
            
            $persenDasar = $tukinDasar[$index] ?? 0.60;
            $persenPrestasi = $tukinPrestasi[$index] ?? 0;
            $persenPotongan = $tukinPotongan[$index] ?? 0;
            
            $nominalDasar = $nominalTukin * $persenDasar;
            $nominalPrestasi = $nominalTukin * $persenPrestasi;
            $nominalBersihTpd = $bersihTpd[$index] ?? 0;
            $nominalPotongan = $nominalTukin * $persenPotongan;
            
            $nilaiBersihTukin = $nominalDasar + $nominalPrestasi - $nominalBersihTpd - $nominalPotongan;
            
            $jabatanText = ($jabatanBulanan[$index] ?? '-') . ' / ' . ($golonganBulanan[$index] ?? '-') . '-' . ($tahunBulanan[$index] ?? '-');

            $totGaji += $nominalTukin;
            $totDasar += $nominalDasar;
            $totPrestasi += $nominalPrestasi;
            $totBersihTpd += $nominalBersihTpd;
            $totPotongan += $nominalPotongan;
            $totNilaiBersih += $nilaiBersihTukin;
        @endphp
        <tr>
          <td class="text-center">{{ $selectedYear ?? '-' }}</td>
          <td class="col-bulan">{{ $month }}</td>
          <td class="text-center">{{ $kodeUsulanBulanan[$index] ?? '-' }}</td>
          <td class="text-center">{{ $jabatanText }}</td>
          <td class="text-end">{{ number_format($nominalTukin,0,',','.') }}</td>
          <td class="text-center">{{ rtrim(rtrim(number_format($persenDasar * 100, 2, ',', '.'), '0'), ',') }}%</td>
          <td class="text-end">{{ number_format($nominalDasar,0,',','.') }}</td>
          <td class="text-center">{{ rtrim(rtrim(number_format($persenPrestasi * 100, 2, ',', '.'), '0'), ',') }}%</td>
          <td class="text-end">{{ number_format($nominalPrestasi,0,',','.') }}</td>
          <td class="text-end">{{ number_format($nominalBersihTpd,0,',','.') }}</td>
          <td class="text-center">{{ rtrim(rtrim(number_format($persenPotongan * 100, 2, ',', '.'), '0'), ',') }}%</td>
          <td class="text-end">{{ number_format($nominalPotongan,0,',','.') }}</td>
          <td class="text-end">{{ number_format($nilaiBersihTukin,0,',','.') }}</td>
          <td class="text-center" style="font-size:11px;">{{ $noSp2d[$index] ?? '-' }}</td>
          <td class="text-center" style="font-size:11px;">{{ !empty($tglSp2d[$index]) && $tglSp2d[$index] !== '-' ? date('d/m/Y', strtotime($tglSp2d[$index])) : '-' }}</td>
          <td class="text-center">@if($st && isset($statusMap[$st]))<span class="badge {{ $statusMap[$st][0] }}" style="font-size:10px;">{{ $statusMap[$st][1] }}</span>@elseif($st && str_starts_with($st, 'kode:'))<span class="badge bg-label-secondary" style="font-size:10px;">{{ substr($st, 5) }}</span>@else - @endif</td>
        </tr>
        @endforeach
        <tr class="fw-bold table-light">
          <td colspan="4" class="text-center">Jumlah</td>
          <td class="text-end">{{ number_format($totGaji,0,',','.') }}</td>
          <td></td>
          <td class="text-end">{{ number_format($totDasar,0,',','.') }}</td>
          <td></td>
          <td class="text-end">{{ number_format($totPrestasi,0,',','.') }}</td>
          <td class="text-end">{{ number_format($totBersihTpd,0,',','.') }}</td>
          <td></td>
          <td class="text-end">{{ number_format($totPotongan,0,',','.') }}</td>
          <td class="text-end">{{ number_format($totNilaiBersih,0,',','.') }}</td>
          <td colspan="3"></td>
        </tr>
      @elseif (($jenisTunjangan ?? 'semua') == 'sptjm')
      <thead>
        <tr>
          <th rowspan="2" class="text-center align-middle">Tahun</th>
          <th rowspan="2" class="text-center align-middle">Bulan</th>
          <th rowspan="2" class="text-center align-middle">Kode Usulan</th>
          <th rowspan="2" class="text-center align-middle">Jabatan / Gol/MK</th>
          <th rowspan="2" class="text-center align-middle">Gaji</th>
          <th colspan="{{ $hasTkgb ? 4 : 2 }}" class="text-center align-middle">Nominal</th>
          <th rowspan="2" class="text-center align-middle">NO SP2D</th>
          <th rowspan="2" class="text-center align-middle">TGL SP2D</th>
          @if(!$isPns)
          <th rowspan="2" class="text-center align-middle">Selisih</th>
          @endif
          <th rowspan="2" class="text-center align-middle">Status</th>
        </tr>
        <tr>
          <th class="text-center align-middle">Kotor TPD</th>
          <th class="text-center align-middle">Bersih TPD</th>
          @if($hasTkgb)
          <th class="text-center align-middle tkgb-col">Kotor TKGB</th>
          <th class="text-center align-middle tkgb-col">Bersih TKGB</th>
          @endif
        </tr>
      </thead>
      <tbody>
        @foreach ($months as $index => $month)
        @php $st = $statusBulanan[$index] ?? null; @endphp
        <tr>
          <td class="text-center">{{ $selectedYear ?? '-' }}</td>
          <td>{{ $month }}</td>
          <td class="text-center">{{ $kodeUsulanBulanan[$index] ?? '-' }}</td>
          <td class="text-center">{{ $jabatanBulanan[$index] ?? '-' }} / {{ $golonganBulanan[$index] ?? '-' }} - {{ $tahunBulanan[$index] ?? '-' }}</td>
          <td class="text-end">{{ number_format($gajiBulanan[$index] ?? 0,0,',','.') }}</td>
          <td class="text-end">{{ number_format($kotorTpd[$index] ?? 0,0,',','.') }}</td>
          <td class="text-end">{{ number_format($bersihTpd[$index] ?? 0,0,',','.') }}</td>
          @if($hasTkgb)
          <td class="text-end tkgb-col">{{ number_format($kotorTkgb[$index] ?? 0,0,',','.') }}</td>
          <td class="text-end tkgb-col">{{ number_format($bersihTkgb[$index] ?? 0,0,',','.') }}</td>
          @endif
          <td class="text-center" style="font-size:11px;">{{ $noSp2d[$index] ?? '-' }}</td>
          @php
             $tglSp2dStr = $tglSp2d[$index] ?? '-';
             if ($tglSp2dStr !== '' && $tglSp2dStr !== '-') {
                 try { $tglSp2dStr = \Carbon\Carbon::parse($tglSp2dStr)->format('d/m/Y'); } catch(\Exception $e) {}
             }
          @endphp
          <td class="text-center" style="font-size:11px;">{{ $tglSp2dStr }}</td>
          @if(!$isPns)
          @php $sel = $selisihBulanan[$index] ?? 0; @endphp
          <td class="text-end fw-bold {{ $sel < 0 ? 'text-danger' : ($sel > 0 ? 'text-success' : 'text-success') }}">{{ $sel < 0 ? '-' : ($sel > 0 ? '+' : '') }}{{ number_format(abs($sel),0,',','.') }}</td>
          @endif
          <td class="text-center">@if($st && isset($statusMap[$st]))<span class="badge {{ $statusMap[$st][0] }}" style="font-size:10px;">{{ $statusMap[$st][1] }}</span>@elseif($st && str_starts_with($st, 'kode:'))<span class="badge bg-label-secondary" style="font-size:10px;">{{ substr($st, 5) }}</span>@else - @endif</td>
        </tr>
        @endforeach
        <tr class="fw-bold table-light">
          <td colspan="4" class="text-center">Jumlah</td>
          <td class="text-end">{{ number_format($totalGaji,0,',','.') }}</td>
          <td class="text-end">{{ number_format($totalKotorTpd,0,',','.') }}</td>
          <td class="text-end">{{ number_format($totalBersihTpd,0,',','.') }}</td>
          @if($hasTkgb)
          <td class="text-end tkgb-col">{{ number_format($totalKotorTkgb,0,',','.') }}</td>
          <td class="text-end tkgb-col">{{ number_format($totalBersihTkgb,0,',','.') }}</td>
          @endif
          <td colspan="{{ !$isPns ? '4' : '3' }}"></td>
        </tr>
      </tbody>
      @else
      <thead>
        <tr>
          <th class="text-center align-middle">Tahun</th>
          <th class="text-center align-middle">Bulan</th>
          <th class="text-center align-middle">Jabatan / Gol-MK</th>
          <th class="text-center align-middle">Gaji</th>
          <th class="text-center align-middle">Nominal SPTJM</th>
          @if($hasTkgb)<th class="text-center align-middle tkgb-col">Nominal TUKIN</th>@endif
          <th class="text-center align-middle">Bersih SPTJM</th>
          @if($hasTkgb)<th class="text-center align-middle tkgb-col">Bersih TUKIN</th>@endif
          <th class="text-center align-middle">Selisih</th>
          <th class="text-center align-middle">Status</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($months as $index => $month)
        @php $sel = $selisihBulanan[$index] ?? 0; $st = $statusBulanan[$index] ?? null; @endphp
        <tr>
          <td class="text-center">{{ $selectedYear ?? '-' }}</td>
          <td>{{ $month }}</td>
          <td class="text-center">{{ $jabatanBulanan[$index] ?? '-' }} / {{ $golonganBulanan[$index] ?? '-' }}-{{ $tahunBulanan[$index] ?? '-' }}</td>
          <td class="text-end">{{ number_format($gajiBulanan[$index] ?? 0,0,',','.') }}</td>
          <td class="text-end">{{ number_format($kotorTpd[$index] ?? 0,0,',','.') }}</td>
          @if(($jenisTunjangan ?? 'semua') == 'sptjm')<td class="text-end">{{ number_format($pajakTpd[$index] ?? 0,0,',','.') }}</td>@endif
          @if($hasTkgb)<td class="text-end tkgb-col">{{ number_format($kotorTkgb[$index] ?? 0,0,',','.') }}</td>@endif
          @if(($jenisTunjangan ?? 'semua') == 'sptjm' && $hasTkgb)<td class="text-end tkgb-col">{{ number_format($pajakTkgb[$index] ?? 0,0,',','.') }}</td>@endif
          <td class="text-end">{{ number_format($bersihTpd[$index] ?? 0,0,',','.') }}</td>
          @if($hasTkgb)<td class="text-end tkgb-col">{{ number_format($bersihTkgb[$index] ?? 0,0,',','.') }}</td>@endif
          <td class="text-end fw-bold {{ $sel < 0 ? 'text-danger' : ($sel > 0 ? 'text-success' : 'text-success') }}">{{ $sel < 0 ? '-' : ($sel > 0 ? '+' : '') }}{{ number_format(abs($sel),0,',','.') }}</td>
          <td class="text-center">@if($st && isset($statusMap[$st]))<span class="badge {{ $statusMap[$st][0] }}" style="font-size:10px;">{{ $statusMap[$st][1] }}</span>@elseif($st && str_starts_with($st, 'kode:'))<span class="badge bg-label-secondary" style="font-size:10px;">{{ substr($st, 5) }}</span>@else - @endif</td>
        </tr>
        @endforeach
        <tr class="fw-bold table-light">
          <td colspan="3" class="text-center">Jumlah</td>
          <td class="text-end">{{ number_format($totalGaji,0,',','.') }}</td>
          <td class="text-end">{{ number_format($totalKotorTpd,0,',','.') }}</td>
          @if($hasTkgb)<td class="text-end tkgb-col">{{ number_format($totalKotorTkgb,0,',','.') }}</td>@endif
          <td class="text-end">{{ number_format($totalBersihTpd,0,',','.') }}</td>
          @if($hasTkgb)<td class="text-end tkgb-col">{{ number_format($totalBersihTkgb,0,',','.') }}</td>@endif
          <td colspan="2"></td>
        </tr>
      </tbody>
      @endif
        @php
           $kGrossRow = $summaryOriginal['k_gross'] ?? 0;
           $kPajakRow = $summaryOriginal['k_pajak'] ?? 0;
           $kNetRow   = $summaryOriginal['k_net'] ?? 0;
           
           $lGrossRow = $summaryOriginal['l_gross'] ?? 0;
           $lPajakRow = $summaryOriginal['l_pajak'] ?? 0;
           $lNetRow   = $summaryOriginal['l_net'] ?? 0;

            $jmKotorTpd = $totalKotorTpd + $totalKotorTkgb;
            $jmPajakTpd = $totalPajakTpd + $totalPajakTkgb;
            $jmBersihTpd = $totalBersihTpd + $totalBersihTkgb;

            $taKotorTpdSptjm = $totalKotorTpd + $kGrossRow - $lGrossRow;
            $taPajakTpdSptjm = $totalPajakTpd + $kPajakRow - $lPajakRow;
            $taBersihTpdSptjm = $totalBersihTpd + $kNetRow - $lNetRow;
            
            $taKotorTpdSemua = $jmKotorTpd + $kGrossRow - $lGrossRow;
            $taBersihTpdSemua = $jmBersihTpd + $kNetRow - $lNetRow;
        @endphp
      <tfoot>

      @if (($jenisTunjangan ?? 'semua') == 'tukin')
        <tr class="fw-bold" style="background-color: #ffdcdc">
          <td colspan="4" class="text-center">Pembayaran Kekurangan</td>
          <td colspan="12"></td>
        </tr>
        <tr class="fw-bold" style="background-color: #dbeafe">
          <td colspan="4" class="text-center">Pengembalian Kelebihan</td>
          <td colspan="12"></td>
        </tr>
        <tr class="fw-bold" style="background-color: #d1fae5">
          <td colspan="4" class="text-center">Total Akhir</td>
          <td class="text-end">{{ number_format($totGaji ?? 0,0,',','.') }}</td>
          <td></td><td class="text-end">{{ number_format($totDasar ?? 0,0,',','.') }}</td>
          <td></td><td class="text-end">{{ number_format($totPrestasi ?? 0,0,',','.') }}</td>
          <td class="text-end">{{ number_format($totBersihTpd ?? 0,0,',','.') }}</td>
          <td></td><td class="text-end">{{ number_format($totPotongan ?? 0,0,',','.') }}</td>
          <td class="text-end">{{ number_format($totNilaiBersih ?? 0,0,',','.') }}</td>
          <td colspan="3"></td>
        </tr>
      @elseif (($jenisTunjangan ?? 'semua') == 'sptjm')
        <tr class="fw-bold" style="background-color: #ffdcdc">
          <td colspan="4" class="text-center">Pembayaran Kekurangan</td>
          <td></td>
          <td class="text-end">{{ number_format($kGrossRow,0,',','.') }}</td>
          <td class="text-end">{{ number_format($kNetRow,0,',','.') }}</td>
          @if($hasTkgb)
          <td class="text-end tkgb-col">0</td>
          <td class="text-end tkgb-col">0</td>
          @endif
          <td colspan="{{ !$isPns ? '4' : '3' }}"></td>
        </tr>
        <tr class="fw-bold" style="background-color: #dbeafe">
          <td colspan="4" class="text-center">Pengembalian Kelebihan</td>
          <td></td>
          <td class="text-end">{{ number_format($lGrossRow,0,',','.') }}</td>
          <td class="text-end">{{ number_format($lNetRow,0,',','.') }}</td>
          @if($hasTkgb)
          <td class="text-end tkgb-col">0</td>
          <td class="text-end tkgb-col">0</td>
          @endif
          <td colspan="{{ !$isPns ? '4' : '3' }}"></td>
        </tr>
        <tr class="fw-bold" style="background-color: #d1fae5">
          <td colspan="4" class="text-center">Total Akhir</td>
          <td class="text-end">{{ number_format($totalGaji ?? 0,0,',','.') }}</td>
          <td class="text-end">{{ number_format(($totalKotorTpd ?? 0) + $kGrossRow - $lGrossRow,0,',','.') }}</td>
          <td class="text-end">{{ number_format(($totalBersihTpd ?? 0) + $kNetRow - $lNetRow,0,',','.') }}</td>
          @if($hasTkgb)
          <td class="text-end tkgb-col">{{ number_format($totalKotorTkgb ?? 0,0,',','.') }}</td>
          <td class="text-end tkgb-col">{{ number_format($totalBersihTkgb ?? 0,0,',','.') }}</td>
          @endif
          <td colspan="{{ !$isPns ? '4' : '3' }}"></td>
        </tr>
      @else
        <tr class="fw-bold" style="background-color: #ffdcdc">
          <td colspan="3" class="text-center">Pembayaran Kekurangan</td>
          <td></td>
          <td class="text-end">{{ number_format($kGrossRow,0,',','.') }}</td>
          @if($hasTkgb)<td class="text-end tkgb-col">0</td>@endif
          <td class="text-end">{{ number_format($kNetRow,0,',','.') }}</td>
          @if($hasTkgb)<td class="text-end tkgb-col">0</td>@endif
          <td colspan="4"></td>
        </tr>
        <tr class="fw-bold" style="background-color: #dbeafe">
          <td colspan="3" class="text-center">Pengembalian Kelebihan</td>
          <td></td>
          <td class="text-end">{{ number_format($lGrossRow,0,',','.') }}</td>
          @if($hasTkgb)<td class="text-end tkgb-col">0</td>@endif
          <td class="text-end">{{ number_format($lNetRow,0,',','.') }}</td>
          @if($hasTkgb)<td class="text-end tkgb-col">0</td>@endif
          <td colspan="4"></td>
        </tr>
        <tr class="fw-bold" style="background-color: #d1fae5">
          <td colspan="3" class="text-center">Total Akhir</td>
          <td class="text-end">{{ number_format($totalGaji ?? 0,0,',','.') }}</td>
          <td class="text-end">{{ number_format(($totalKotorTpd ?? 0) + ($totalKotorTkgb ?? 0) + $kGrossRow - $lGrossRow,0,',','.') }}</td>
          @if($hasTkgb)<td class="text-end tkgb-col">0</td>@endif
          <td class="text-end">{{ number_format(($totalBersihTpd ?? 0) + ($totalBersihTkgb ?? 0) + $kNetRow - $lNetRow,0,',','.') }}</td>
          @if($hasTkgb)<td class="text-end tkgb-col">0</td>@endif
          <td colspan="4"></td>
        </tr>
      @endif
      </tfoot>
      </tbody>
    </table>
  </div>

  <hr class="mt-5 mb-4">
  <h6 class="text-start px-2 fw-bold" style="color: #566a7f;">Uraian Pembayaran</h6>
  
  <div class="table-responsive text-nowrap mb-4" style="overflow:auto; padding-right:0;">
    <table class="table table-bordered table-hover" id="tabel-riwayat" style="width:100%">
      <thead>
        <tr>
          <th class="text-center">No</th>
          <th class="text-center">Uraian Pembayaran</th>
          <th class="text-center">Bulan</th>
          <th class="text-center">Nominal</th>
          <th class="text-center">Pajak</th>
          <th class="text-center">Bersih</th>
          <th class="text-center">No SP2D</th>
          <th class="text-center">Tanggal</th>
        </tr>
      </thead>
      <tbody>
        @php
          $totalUraianBersih = 0;
          $totalUraianNominal = 0;
          $totalUraianPajak = 0;
        @endphp
        @forelse ($riwayatPembayaran ?? [] as $index => $riwayat)
        @php
          $totalUraianBersih += ($riwayat->bersih ?? 0);
          $totalUraianNominal += ($riwayat->nominal ?? 0);
          $totalUraianPajak += ($riwayat->pajak ?? 0);
        @endphp
        <tr>
          <td class="text-center">{{ $index + 1 }}</td>
          @php
             $uraianClean = $riwayat->uraian_pembayaran;
             foreach($months as $m) {
                 $uraianClean = str_ireplace(' ' . $m, '', $uraianClean);
             }
          @endphp
          <td>{{ ucfirst($uraianClean) }}</td>
          <td class="text-center">{{ $months[(int)$riwayat->bulan - 1] ?? $riwayat->bulan }}</td>
          <td class="text-end">{{ number_format($riwayat->nominal ?? 0, 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format($riwayat->pajak ?? 0, 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format($riwayat->bersih ?? 0, 0, ',', '.') }}</td>
          <td>{{ $riwayat->nomor }}</td>
          <td class="text-center">{{ $riwayat->tanggal ? \Carbon\Carbon::parse($riwayat->tanggal)->format('d-M-y') : '-' }}</td>
        </tr>
        @empty
        <tr>
          <td colspan="8" class="text-center">Tidak ada data riwayat pembayaran</td>
        </tr>
        @endforelse
        @php
           $kGross = $summaryOriginal['k_gross'] ?? 0;
           $kPajak = $summaryOriginal['k_pajak'] ?? 0;
           $kNet = $summaryOriginal['k_net'] ?? 0;
           
           $lGross = $summaryOriginal['l_gross'] ?? 0;
           $lPajak = $summaryOriginal['l_pajak'] ?? 0;
           $lNet = $summaryOriginal['l_net'] ?? 0;
           
           $nettingText = '';
           
           $totalAkhirGross = $totalKotorTpd + $totalKotorTkgb + $kGross - $lGross;
           $totalAkhirPajak = $totalPajakTpd + $totalPajakTkgb + $kPajak - $lPajak;
           $totalAkhirNet = $totalBersihTpd + $totalBersihTkgb + $kNet - $lNet;
        @endphp
        <tr class="fw-bold table-light">
          <td colspan="3" class="text-start">Total Pembayaran</td>
          <td class="text-end">{{ number_format($totalUraianNominal, 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format($totalUraianPajak, 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format($totalUraianBersih, 0, ',', '.') }}</td>
          <td colspan="2"></td>
        </tr>
        <tr class="fw-bold" style="background-color: #ffdcdc;">
          <td colspan="3" class="text-start">Pembayaran Kekurangan {!! $kGross > 0 ? $nettingText : '' !!}</td>
          <td class="text-end">{{ number_format($kGross, 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format($kPajak, 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format($kNet, 0, ',', '.') }}</td>
          <td colspan="2"></td>
        </tr>
        <tr class="fw-bold" style="background-color: #dbeafe;">
          <td colspan="3" class="text-start">Pengembalian Kelebihan {!! $lGross > 0 ? $nettingText : '' !!}</td>
          <td class="text-end">{{ number_format($lGross, 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format($lPajak, 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format($lNet, 0, ',', '.') }}</td>
          <td colspan="2"></td>
        </tr>
        <tr class="fw-bold" style="background-color: #d1fae5;">
          <td colspan="3" class="text-start">Total Akhir</td>
          <td class="text-end">{{ number_format($totalAkhirGross, 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format($totalAkhirPajak, 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format($totalAkhirNet, 0, ',', '.') }}</td>
          <td colspan="2"></td>
        </tr>
      </tbody>
    </table>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const filterSelect = document.querySelector('select[name="tahun_versi"]');
      if (!filterSelect) return;
      const token = document.querySelector('input[name="_token"]')?.value;
      const nidnInput = document.getElementById('search_nidn');
      const startYearInput = document.getElementById('hidden_start_year');
      const endYearInput = document.getElementById('hidden_end_year');
      const jenisTunjanganInput = document.getElementById('hidden_jenis_tunjangan');
      let currentJenis = jenisTunjanganInput ? jenisTunjanganInput.value : 'sptjm';
      
      const fmt = n => Number(n).toLocaleString('id-ID',{maximumFractionDigits:0});
      const fmtDec = n => Number(n).toLocaleString('id-ID',{minimumFractionDigits:2, maximumFractionDigits:2});
      const statusCfg = {usulan:['bg-label-warning','Usulan'],proses:['bg-label-info','Proses'],kurang:['bg-label-danger','Kurang'],lebih:['bg-label-secondary','Lebih'],selesai:['bg-label-success','Selesai']};

      function loadData() {
        const tahun = filterSelect.value, nidn = nidnInput?.value||'', sy = startYearInput?.value||'', ey = endYearInput?.value||'';
        const exy = document.getElementById('export_tahun_versi'); if(exy) exy.value=tahun;
        const csy = document.getElementById('cetak_spt_tahun_versi'); if(csy) csy.value=tahun;
        if(jenisTunjanganInput) jenisTunjanganInput.value = currentJenis;

        // Update active class on buttons
        document.querySelectorAll('.md-status-btn').forEach(btn => {
            btn.classList.remove('active');
            if (btn.getAttribute('data-jenis') === currentJenis) {
                btn.classList.add('active');
            }
        });

        // Toggle wrap untuk tabel khusus tukin
        const wrapper = document.getElementById('mp-table-wrapper');
        const table = document.getElementById('mp-table');
        if (wrapper) {
            if (currentJenis === 'tukin') {
                wrapper.classList.remove('text-nowrap');
                if (table) table.classList.add('tukin-wrap');
            } else {
                wrapper.classList.add('text-nowrap');
                if (table) table.classList.remove('tukin-wrap');
            }
        }

        // Tampilkan state loading agar tidak terasa "mentok"
        filterSelect.disabled = true;

        fetch("{{ route('monitoring-pembayaran.data') }}", {
          method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':token},
          body: JSON.stringify({nidn,start_year:sy,end_year:ey,tahun_versi:tahun,jenis_tunjangan:currentJenis})
        }).then(r=>r.json()).then(data=>{
          filterSelect.disabled = false;
          if(!data.success){
              alert("Gagal memuat data: " + data.message);
              console.error(data.message);
              return;
          }
          const h=data.header||{};

          // Header
          const el1=document.getElementById('hdr-nidn'); if(el1) el1.value=(h.NIDN||'')+' - '+(h.Nama||'');
          const spanJenis=document.getElementById('hdr-status-jenis');
          const spanAktif=document.getElementById('hdr-status-aktif');
          if(spanJenis && spanAktif) {
              const j=(h.Jenis||'').toUpperCase(), pns=j.indexOf('PNS')!==-1&&j.indexOf('NON')===-1;
              spanJenis.textContent = pns ? 'PNS' : 'Non PNS';
              spanJenis.style.color = pns ? '#0056b3' : '#dc3545';
              spanAktif.textContent = h.Aktif==1 ? 'Aktif' : 'Tidak Aktif';
          }
          const el3=document.getElementById('hdr-pt'); if(el3) el3.value=(h.Kode_PT||'')+' - '+(h.PTS||'');


          const sm=data.summary||{};
          const ek=document.getElementById('sum-kewajiban'); if(ek) ek.textContent='Rp '+fmt(sm.totalKewajiban||0);
          const ed=document.getElementById('sum-dibayar'); if(ed) ed.textContent='Rp '+fmt(sm.totalDibayar||0);
          const es=document.getElementById('sum-selisih');
          if(es){es.textContent='Rp '+fmt(sm.totalSelisih||0); es.className=(sm.totalSelisih||0)==0?'text-success':'text-danger';}
          const si=document.getElementById('sum-selisih-icon');
          if(si){si.className='avatar-initial rounded '+((sm.totalSelisih||0)==0?'bg-label-success':'bg-label-danger')+' me-2';}

          let hasTkgb = data.isGuruBesar || false;
          const tbl=document.getElementById('mp-table');
          if(tbl) tbl.dataset.hasTkgb=hasTkgb?'1':'0';

          const thead=tbl?.querySelector('thead');
          if(thead){
            if (currentJenis === 'tukin') {
                thead.innerHTML=`<tr><th class="text-center align-middle">Tahun</th><th class="text-center align-middle col-bulan">Bulan</th><th class="text-center align-middle">Kode Usulan</th><th class="text-center align-middle">Jabatan / Gol-MK</th><th class="text-center align-middle">Nominal TUKIN</th><th class="text-center align-middle">% KD</th><th class="text-center align-middle">Nominal Kinerja Dasar</th><th class="text-center align-middle">% KP</th><th class="text-center align-middle">Nominal Kinerja Prestasi</th><th class="text-center align-middle">Nominal Bersih TPD</th><th class="text-center align-middle">% PP </th><th class="text-center align-middle">Potongan Periodik</th><th class="text-center align-middle">Nilai Bersih TUKIN</th><th class="text-center align-middle">No SP2D</th><th class="text-center align-middle">Tgl SP2D</th><th class="text-center align-middle">Status</th></tr>`;
            } else if (currentJenis === 'sptjm') {
                thead.innerHTML=`<tr><th rowspan="2" class="text-center align-middle">Tahun</th><th rowspan="2" class="text-center align-middle">Bulan</th><th rowspan="2" class="text-center align-middle">Kode Usulan</th><th rowspan="2" class="text-center align-middle">Jabatan / Gol/MK</th><th rowspan="2" class="text-center align-middle">Gaji</th><th colspan="${hasTkgb ? 4 : 2}" class="text-center align-middle">Nominal</th><th rowspan="2" class="text-center align-middle">NO SP2D</th><th rowspan="2" class="text-center align-middle">TGL SP2D</th>${!data.isPns ? '<th rowspan="2" class="text-center align-middle">Selisih</th>' : ''}<th rowspan="2" class="text-center align-middle">Status</th></tr><tr><th class="text-center align-middle">Kotor TPD</th><th class="text-center align-middle">Bersih TPD</th>${hasTkgb?'<th class="text-center align-middle tkgb-col">Kotor TKGB</th>':''}${hasTkgb?'<th class="text-center align-middle tkgb-col">Bersih TKGB</th>':''}</tr>`;
            } else {
                thead.innerHTML=`<tr><th class="text-center">Tahun</th><th class="text-center">Bulan</th><th class="text-center">Jabatan / Gol-MK</th><th class="text-center">Gaji</th><th class="text-center">Nominal SPTJM</th>${hasTkgb?'<th class="text-center tkgb-col">Nominal TUKIN</th>':''}<th class="text-center">Bersih SPTJM</th>${hasTkgb?'<th class="text-center tkgb-col">Bersih TUKIN</th>':''}<th class="text-center">Selisih</th><th class="text-center">Status</th></tr>`;
            }
          }

          const tbody=tbl?.querySelector('tbody'); 
          if(tbody) {
            tbody.innerHTML='';
            const months=data.months||[], sb=data.selisihBulanan||[], stb=data.statusBulanan||[];
            const tkc=(v)=>hasTkgb?`<td class="text-end tkgb-col">${fmt(v)}</td>`:'';
            
            let totGaji=0, totDasar=0, totPrestasi=0, totBersihTpd=0, totPotongan=0, totNilaiBersih=0;

            if (currentJenis === 'tukin') {
                for(let i=0;i<months.length;i++){
                    const st=stb[i];
                    let stH='-'; if(st&&statusCfg[st]) stH=`<span class="badge ${statusCfg[st][0]}" style="font-size:10px">${statusCfg[st][1]}</span>`; else if(st&&st.startsWith('kode:')) stH=`<span class="badge bg-label-secondary" style="font-size:10px">${st.substring(5)}</span>`;
                    
                    const nominalTukin = data.gajiBulanan[i] ?? 0;
                    
                    const persenDasar = data.tukinDasar ? (data.tukinDasar[i] ?? 0.60) : 0.60;
                    const persenPrestasi = data.tukinPrestasi ? (data.tukinPrestasi[i] ?? 0) : 0;
                    const persenPotongan = data.tukinPotongan ? (data.tukinPotongan[i] ?? 0) : 0;
                    
                    const nominalDasar = nominalTukin * persenDasar;
                    const nominalPrestasi = nominalTukin * persenPrestasi;
                    const nominalBersihTpd = data.bersihTpd ? (data.bersihTpd[i] ?? 0) : 0;
                    const nominalPotongan = nominalTukin * persenPotongan;
                    
                    const nilaiBersihTukin = nominalDasar + nominalPrestasi - nominalBersihTpd - nominalPotongan;

                    let jb = data.jabatanBulanan[i] ?? '-';
                    let gl = data.golonganBulanan[i] ?? '-';
                    let mk = data.tahunBulanan[i] ?? '-';
                    const jabatanText = `${jb} / ${gl}-${mk}`;
                    
                    let tglMain = data.tglSp2d[i] ?? '-';
                    if (tglMain !== '' && tglMain !== '-') {
                        const dMain = new Date(tglMain);
                        if(!isNaN(dMain)) {
                            const d = String(dMain.getDate()).padStart(2, '0');
                            const m = String(dMain.getMonth() + 1).padStart(2, '0');
                            const y = dMain.getFullYear();
                            tglMain = `${d}/${m}/${y}`;
                        }
                    }

                    totGaji += nominalTukin; totDasar += nominalDasar; totPrestasi += nominalPrestasi;
                    totBersihTpd += nominalBersihTpd; totPotongan += nominalPotongan; totNilaiBersih += nilaiBersihTukin;
                    
                    const pDasarStr = String(Math.round(persenDasar * 100 * 100) / 100) + '%';
                    const pPrestasiStr = String(Math.round(persenPrestasi * 100 * 100) / 100) + '%';
                    const pPotonganStr = String(Math.round(persenPotongan * 100 * 100) / 100) + '%';

                    tbody.innerHTML += `<tr><td class="text-center">${data.selectedYear||'-'}</td><td class="col-bulan">${months[i]}</td><td class="text-center">${data.kodeUsulanBulanan[i]??'-'}</td><td class="text-center">${jabatanText}</td><td class="text-end">${fmt(nominalTukin)}</td><td class="text-center">${pDasarStr}</td><td class="text-end">${fmt(nominalDasar)}</td><td class="text-center">${pPrestasiStr}</td><td class="text-end">${fmt(nominalPrestasi)}</td><td class="text-end">${fmt(nominalBersihTpd)}</td><td class="text-center">${pPotonganStr}</td><td class="text-end">${fmt(nominalPotongan)}</td><td class="text-end">${fmt(nilaiBersihTukin)}</td><td class="text-center" style="font-size:11px">${data.noSp2d[i]??'-'}</td><td class="text-center" style="font-size:11px">${tglMain}</td><td class="text-center">${stH}</td></tr>`;
                }
                tbody.innerHTML += `<tr class="fw-bold table-light"><td colspan="4" class="text-center">Jumlah</td><td class="text-end">${fmt(totGaji)}</td><td></td><td class="text-end">${fmt(totDasar)}</td><td></td><td class="text-end">${fmt(totPrestasi)}</td><td class="text-end">${fmt(totBersihTpd)}</td><td></td><td class="text-end">${fmt(totPotongan)}</td><td class="text-end">${fmt(totNilaiBersih)}</td><td colspan="3"></td></tr>`;
              } else if (currentJenis === 'sptjm') {
                for(let i=0;i<months.length;i++){
                  const st=stb[i];
                  let stH='-'; if(st&&statusCfg[st]) stH=`<span class="badge ${statusCfg[st][0]}" style="font-size:10px">${statusCfg[st][1]}</span>`; else if(st&&st.startsWith('kode:')) stH=`<span class="badge bg-label-secondary" style="font-size:10px">${st.substring(5)}</span>`;
                  
                  let tglMain=data.tglSp2d[i]??'-';
                  if(tglMain!=='-'){ try{ const pd=new Date(tglMain); if(!isNaN(pd)) tglMain=('0'+pd.getDate()).slice(-2)+'/'+('0'+(pd.getMonth()+1)).slice(-2)+'/'+pd.getFullYear(); }catch(e){} }
                  
                  const gaji = data.gajiBulanan[i]??0;
                  const kotorTpdCol = `<td class="text-end">${fmt(data.kotorTpd[i]??0)}</td>`;
                  const bersihTpdCol = `<td class="text-end">${fmt(data.bersihTpd[i]??0)}</td>`;
                  let nomCols = kotorTpdCol + bersihTpdCol;
                  if (hasTkgb) {
                      nomCols += `<td class="text-end tkgb-col">${fmt(data.kotorTkgb[i]??0)}</td>`;
                      nomCols += `<td class="text-end tkgb-col">${fmt(data.bersihTkgb[i]??0)}</td>`;
                  }
                  
                  let selTd = '';
                  if (!data.isPns) {
                      let s = sb[i]||0;
                      let sc=s<0?'text-end text-danger fw-bold':(s>0?'text-end text-success fw-bold':'text-end text-success fw-bold');
                      let pfx=s<0?'-':(s>0?'+':'');
                      selTd = `<td class="${sc}">${pfx}${fmt(Math.abs(s))}</td>`;
                  }
                  
                  tbody.innerHTML+=`<tr><td class="text-center">${data.selectedYear||'-'}</td><td>${months[i]}</td><td class="text-center">${data.kodeUsulanBulanan[i]??'-'}</td><td class="text-center">${data.jabatanBulanan[i]??'-'} / ${data.golonganBulanan[i]??'-'} - ${data.tahunBulanan[i]??'-'}</td><td class="text-end">${fmt(gaji)}</td>${nomCols}<td class="text-center" style="font-size:11px">${data.noSp2d[i]??'-'}</td><td class="text-center" style="font-size:11px">${tglMain}</td>${selTd}<td class="text-center">${stH}</td></tr>`;
                }
    
                // Totals
                const t=data.totals||{};
                const sumCols = `<td class="text-end">${fmt(t.kotorTpd||0)}</td><td class="text-end">${fmt(t.bersihTpd||0)}</td>${hasTkgb ? `<td class="text-end tkgb-col">${fmt(t.kotorTkgb||0)}</td><td class="text-end tkgb-col">${fmt(t.bersihTkgb||0)}</td>` : ''}`;
                tbody.innerHTML+=`<tr class="fw-bold table-light"><td colspan="4" class="text-center">Jumlah</td><td class="text-end">${fmt(t.gaji||0)}</td>${sumCols}<td colspan="${!data.isPns ? '4' : '3'}"></td></tr>`;
              } else {
                for(let i=0;i<months.length;i++){
                  const gaji = data.gajiBulanan[i]??0;
                  const nomSptjm = data.kotorTpd[i]??0;
                  const nomTukin = data.kotorTkgb[i]??0;
                  
                  let s = sb[i]||0;
                  let st = stb[i];
                  
                  if (currentJenis === 'semua') {
                      s = gaji - (nomSptjm + nomTukin);
                      if (s > 0) st = 'kurang';
                      else if (s < 0) st = 'lebih';
                      else st = 'selesai';
                  }

                  let sc=s<0?'text-end text-danger fw-bold':(s>0?'text-end text-success fw-bold':'text-end text-success fw-bold'), ss='';
                  const pfx=s<0?'-':(s>0?'+':'');
                  let stH='-'; 
                  if(st&&statusCfg[st]) {
                      stH=`<span class="badge ${statusCfg[st][0]}" style="font-size:10px">${statusCfg[st][1]}</span>`; 
                  } else if(st&&st.startsWith('kode:')) {
                      stH=`<span class="badge bg-label-secondary" style="font-size:10px">${st.substring(5)}</span>`;
                  }
                  
                  let tglMain = data.tglSp2d[i] ?? '-';
                  if (tglMain !== '' && tglMain !== '-') {
                      const dMain = new Date(tglMain);
                      if(!isNaN(dMain)) {
                          const dd = String(dMain.getDate()).padStart(2, '0');
                          const mm = String(dMain.getMonth() + 1).padStart(2, '0');
                          const yy = dMain.getFullYear();
                          tglMain = `${dd}/${mm}/${yy}`;
                      }
                  }
    
                  const kotorTpdCol = `<td class="text-end">${fmt(nomSptjm)}</td>`;
                  const kotorTkgbCol = tkc(nomTukin);
                  const bersihTpdCol = `<td class="text-end">${fmt(data.bersihTpd[i]??0)}</td>`;
                  const bersihTkgbCol = tkc(data.bersihTkgb[i]??0);
                  
                  let nomCols = kotorTpdCol + kotorTkgbCol + bersihTpdCol + bersihTkgbCol;
                  
                  let jb = data.jabatanBulanan[i] ?? '-';
                  let gl = data.golonganBulanan[i] ?? '-';
                  let mk = data.tahunBulanan[i] ?? '-';
                  let jabatanGolMk = `${jb} / ${gl}-${mk}`;
    
                  tbody.innerHTML+=`<tr><td class="text-center">${data.selectedYear||'-'}</td><td>${months[i]}</td><td class="text-center">${jabatanGolMk}</td><td class="text-end">${fmt(gaji)}</td>${nomCols}<td class="${sc}">${pfx}${fmt(Math.abs(s))}</td><td class="text-center">${stH}</td></tr>`;
                }
    
                const t=data.totals||{};
                let sumCols = `<td class="text-end">${fmt(t.kotorTpd||0)}</td>${tkc(t.kotorTkgb||0)}<td class="text-end">${fmt(t.bersihTpd||0)}</td>${tkc(t.bersihTkgb||0)}`;
                tbody.innerHTML+=`<tr class="fw-bold table-light"><td colspan="3" class="text-center">Jumlah</td><td class="text-end">${fmt(t.gaji||0)}</td>${sumCols}<td colspan="2"></td></tr>`;
            }
          }
          
          const tfoot=tbl?.querySelector('tfoot');
          if(tfoot) {
            tfoot.innerHTML='';
            const sumOri = data.summaryOriginal || {};
            const valKGrRow = sumOri.k_gross || 0;
            const valKNeRow = sumOri.k_net || 0;
            const valLGrRow = sumOri.l_gross || 0;
            const valLNeRow = sumOri.l_net || 0;

            if (currentJenis === 'tukin') {
                tfoot.innerHTML+=`<tr class="fw-bold" style="background-color:#ffdcdc"><td colspan="4" class="text-center">Pembayaran Kekurangan</td><td colspan="12"></td></tr>`;
                tfoot.innerHTML+=`<tr class="fw-bold" style="background-color:#dbeafe"><td colspan="4" class="text-center">Pengembalian Kelebihan</td><td colspan="12"></td></tr>`;
                tfoot.innerHTML+=`<tr class="fw-bold" style="background-color:#d1fae5"><td colspan="4" class="text-center">Total Akhir</td><td class="text-end">${fmt(totGaji)}</td><td></td><td class="text-end">${fmt(totDasar)}</td><td></td><td class="text-end">${fmt(totPrestasi)}</td><td class="text-end">${fmt(totBersihTpd)}</td><td></td><td class="text-end">${fmt(totPotongan)}</td><td class="text-end">${fmt(totNilaiBersih)}</td><td colspan="3"></td></tr>`;
            } else if (currentJenis === 'sptjm') {
                const taKotorTpd = (tTotals.kotorTpd||0) + valKGrRow - valLGrRow;
                const taBersihTpd = (tTotals.bersihTpd||0) + valKNeRow - valLNeRow;
                tfoot.innerHTML+=`<tr class="fw-bold" style="background-color:#ffdcdc"><td colspan="4" class="text-center">Pembayaran Kekurangan</td><td></td><td class="text-end">${fmt(valKGrRow)}</td><td class="text-end">${fmt(valKNeRow)}</td>${hasTkgb?'<td class="text-end tkgb-col">0</td><td class="text-end tkgb-col">0</td>':''}<td colspan="${!data.isPns ? '4' : '3'}"></td></tr>`;
                tfoot.innerHTML+=`<tr class="fw-bold" style="background-color:#dbeafe"><td colspan="4" class="text-center">Pengembalian Kelebihan</td><td></td><td class="text-end">${fmt(valLGrRow)}</td><td class="text-end">${fmt(valLNeRow)}</td>${hasTkgb?'<td class="text-end tkgb-col">0</td><td class="text-end tkgb-col">0</td>':''}<td colspan="${!data.isPns ? '4' : '3'}"></td></tr>`;
                tfoot.innerHTML+=`<tr class="fw-bold" style="background-color:#d1fae5"><td colspan="4" class="text-center">Total Akhir</td><td class="text-end">${fmt(tTotals.gaji||0)}</td><td class="text-end">${fmt(taKotorTpd)}</td><td class="text-end">${fmt(taBersihTpd)}</td>${hasTkgb?`<td class="text-end tkgb-col">${fmt(tTotals.kotorTkgb||0)}</td><td class="text-end tkgb-col">${fmt(tTotals.bersihTkgb||0)}</td>`:''}<td colspan="${!data.isPns ? '4' : '3'}"></td></tr>`;
            } else {
                const taKotorTpd = (tTotals.kotorTpd||0) + (tTotals.kotorTkgb||0) + valKGrRow - valLGrRow;
                const taBersihTpd = (tTotals.bersihTpd||0) + (tTotals.bersihTkgb||0) + valKNeRow - valLNeRow;
                tfoot.innerHTML+=`<tr class="fw-bold" style="background-color:#ffdcdc"><td colspan="3" class="text-center">Pembayaran Kekurangan</td><td></td><td class="text-end">${fmt(valKGrRow)}</td>${tkc(0)}<td class="text-end">${fmt(valKNeRow)}</td>${tkc(0)}<td colspan="4"></td></tr>`;
                tfoot.innerHTML+=`<tr class="fw-bold" style="background-color:#dbeafe"><td colspan="3" class="text-center">Pengembalian Kelebihan</td><td></td><td class="text-end">${fmt(valLGrRow)}</td>${tkc(0)}<td class="text-end">${fmt(valLNeRow)}</td>${tkc(0)}<td colspan="4"></td></tr>`;
                tfoot.innerHTML+=`<tr class="fw-bold" style="background-color:#d1fae5"><td colspan="3" class="text-center">Total Akhir</td><td class="text-end">${fmt(tTotals.gaji||0)}</td><td class="text-end">${fmt(taKotorTpd)}</td>${tkc(0)}<td class="text-end">${fmt(taBersihTpd)}</td>${tkc(0)}<td colspan="4"></td></tr>`;
            }
          }

          // UPDATE TABEL KEDUA (URAIAN PEMBAYARAN)
          const tbodyRiwayat = document.querySelector('#tabel-riwayat tbody');
          if (tbodyRiwayat) {
            tbodyRiwayat.innerHTML = '';
            const riwayatData = data.riwayatPembayaran || [];
            
            let totalUraianBersih = 0;
            let totalUraianNominal = 0;
            let totalUraianPajak = 0;
            
            if (riwayatData.length === 0) {
              tbodyRiwayat.innerHTML = '<tr><td colspan="8" class="text-center">Tidak ada data riwayat pembayaran</td></tr>';
            } else {
              riwayatData.forEach((item, index) => {
                totalUraianBersih += parseFloat(item.bersih || 0);
                totalUraianNominal += parseFloat(item.nominal || 0);
                totalUraianPajak += parseFloat(item.pajak || 0);
                
                const tr = document.createElement('tr');
                
                let tglFormat = '-';
                if(item.tanggal) {
                   const d = new Date(item.tanggal);
                   tglFormat = d.toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'2-digit' }).replace(/ /g, '-');
                }

                tr.innerHTML = `
                  <td class="text-center">${index + 1}</td>
                  <td>${(() => {
                      let u = item.uraian_pembayaran ? item.uraian_pembayaran.charAt(0).toUpperCase() + item.uraian_pembayaran.slice(1) : '-';
                      const mn = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                      mn.forEach(m => {
                          const regex = new RegExp('\\s+' + m, 'gi');
                          u = u.replace(regex, '');
                      });
                      return u;
                  })()}</td>
                  <td class="text-center">${(() => { const mn = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember']; const b = parseInt(item.bulan); return (b >= 1 && b <= 12) ? mn[b-1] : (item.bulan || '-'); })()}</td>
                  <td class="text-end">${fmt(item.nominal || 0)}</td>
                  <td class="text-end">${fmt(item.pajak || 0)}</td>
                  <td class="text-end">${fmt(item.bersih || 0)}</td>
                  <td>${item.nomor || '-'}</td>
                  <td class="text-center">${tglFormat}</td>
                `;
                tbodyRiwayat.appendChild(tr);
              });
            }

              const sumOriU = data.summaryOriginal || {};
              const nettingText = '';

              const kGross = sumOriU.k_gross || 0;
              const kPajak = sumOriU.k_pajak || 0;
              const kNet = sumOriU.k_net || 0;
              const lGross = sumOriU.l_gross || 0;
              const lPajak = sumOriU.l_pajak || 0;
              const lNet = sumOriU.l_net || 0;

              const sumKotorTpd = (data.kotorTpd || []).reduce((a,b)=>a+parseFloat(b||0), 0);
              const sumKotorTkgb = (data.kotorTkgb || []).reduce((a,b)=>a+parseFloat(b||0), 0);
              const sumPajakTpd = (data.pajakTpd || []).reduce((a,b)=>a+parseFloat(b||0), 0);
              const sumPajakTkgb = (data.pajakTkgb || []).reduce((a,b)=>a+parseFloat(b||0), 0);
              const sumBersihTpd = (data.bersihTpd || []).reduce((a,b)=>a+parseFloat(b||0), 0);
              const sumBersihTkgb = (data.bersihTkgb || []).reduce((a,b)=>a+parseFloat(b||0), 0);

              const totalAkhirGross = sumKotorTpd + sumKotorTkgb + kGross - lGross;
              const totalAkhirPajak = sumPajakTpd + sumPajakTkgb + kPajak - lPajak;
              const totalAkhirNet = sumBersihTpd + sumBersihTkgb + kNet - lNet;

              // Row: Total Pembayaran
              const trTotal = document.createElement('tr');
              trTotal.className = 'fw-bold table-light';
              trTotal.innerHTML = `
                <td colspan="3" class="text-start">Total Pembayaran</td>
                <td class="text-end">${fmt(totalUraianNominal)}</td>
                <td class="text-end">${fmt(totalUraianPajak)}</td>
                <td class="text-end">${fmt(totalUraianBersih)}</td>
                <td colspan="2"></td>
              `;
              tbodyRiwayat.appendChild(trTotal);

              // Row: Pembayaran Kekurangan
              const trKurang = document.createElement('tr');
              trKurang.className = 'fw-bold';
              trKurang.style.backgroundColor = '#ffdcdc';
              trKurang.innerHTML = `
                <td colspan="3" class="text-start">Pembayaran Kekurangan ${kGross > 0 ? nettingText : ''}</td>
                <td class="text-end">${fmt(kGross)}</td>
                <td class="text-end">${fmt(kPajak)}</td>
                <td class="text-end">${fmt(kNet)}</td>
                <td colspan="2"></td>
              `;
              tbodyRiwayat.appendChild(trKurang);

              // Row: Pengembalian Kelebihan
              const trLebih = document.createElement('tr');
              trLebih.className = 'fw-bold';
              trLebih.style.backgroundColor = '#dbeafe';
              trLebih.innerHTML = `
                <td colspan="3" class="text-start">Pengembalian Kelebihan ${lGross > 0 ? nettingText : ''}</td>
                <td class="text-end">${fmt(lGross)}</td>
                <td class="text-end">${fmt(lPajak)}</td>
                <td class="text-end">${fmt(lNet)}</td>
                <td colspan="2"></td>
              `;
              tbodyRiwayat.appendChild(trLebih);

              // Row: Total Akhir
              const trAkhir = document.createElement('tr');
              trAkhir.className = 'fw-bold';
              trAkhir.style.backgroundColor = '#d1fae5';
              trAkhir.innerHTML = `
                <td colspan="3" class="text-start">Total Akhir</td>
                <td class="text-end">${fmt(totalAkhirGross)}</td>
                <td class="text-end">${fmt(totalAkhirPajak)}</td>
                <td class="text-end">${fmt(totalAkhirNet)}</td>
                <td colspan="2"></td>
              `;
              tbodyRiwayat.appendChild(trAkhir);
          }

        }).catch(err=>{
            filterSelect.disabled = false;
            alert("Terjadi kesalahan: " + err.message);
            console.error(err);
        });
      }

      filterSelect.addEventListener('change', loadData);

      document.querySelectorAll('.md-status-btn').forEach(btn => {
          btn.addEventListener('click', function() {
              currentJenis = this.getAttribute('data-jenis');
              // Append a hidden input to the main form so it remembers the filter if user clicks "Cari"
              let form = document.querySelector('form[action="{{ route('monitoring-pembayaran.cari') }}"]');
              if (form) {
                  let hiddenInput = form.querySelector('input[name="jenis_tunjangan"]');
                  if (!hiddenInput) {
                      hiddenInput = document.createElement('input');
                      hiddenInput.type = 'hidden';
                      hiddenInput.name = 'jenis_tunjangan';
                      form.appendChild(hiddenInput);
                  }
                  hiddenInput.value = currentJenis;
              }
              loadData();
          });
      });
      
      // Initialize form hidden input
      let mainForm = document.querySelector('form[action="{{ route('monitoring-pembayaran.cari') }}"]');
      if (mainForm && currentJenis) {
          let hiddenInput = mainForm.querySelector('input[name="jenis_tunjangan"]');
          if (!hiddenInput) {
              hiddenInput = document.createElement('input');
              hiddenInput.type = 'hidden';
              hiddenInput.name = 'jenis_tunjangan';
              mainForm.appendChild(hiddenInput);
          }
          hiddenInput.value = currentJenis;
      }
    });
  </script>
  @endif
</div>

@endsection
