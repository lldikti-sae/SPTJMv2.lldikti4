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
    cursor: pointer;
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
  
  <div class="md-status-filters mb-3 mx-2">
      <button type="button" class="md-status-btn {{ ($jenisTunjangan ?? 'semua') == 'semua' ? 'active' : '' }}" data-jenis="semua">Semua</button>
      <button type="button" class="md-status-btn {{ ($jenisTunjangan ?? '') == 'sptjm' ? 'active' : '' }}" data-jenis="sptjm">SPTJM</button>
      <button type="button" class="md-status-btn {{ ($jenisTunjangan ?? '') == 'tukin' ? 'active' : '' }}" data-jenis="tukin">TUKIN</button>
  </div>

  @if ($transaksi)
  @php
    $jenis = trim($transaksi->Jenis ?? '');
    $isPns = stripos($jenis, 'PNS') !== false && stripos($jenis, 'NON') === false;
  @endphp
  <div class="row mb-2 mx-2">
    <label class="col-sm-2 col-form-label py-1" style="font-size:13px;font-weight:600;">NIDN - Nama</label>
    <div class="col-sm-7">
      <input id="hdr-nidn" type="text" class="form-control" readonly style="background:#eceef1;font-size:14px;font-weight:600;" value="{{ !empty($transaksi->NIDN) ? $transaksi->NIDN : ($transaksi->NUPTK ?? '-') }} - {{ $transaksi->Nama }}">
    </div>
    <div class="col-sm-3 d-flex align-items-center">
      <span id="badge-jenis" class="badge {{ $isPns ? 'bg-label-primary' : 'bg-label-success' }}" style="font-size:14px;font-weight:700;padding:6px 14px;">{{ $isPns ? 'PNS' : 'Non-PNS' }}</span>
    </div>
  </div>
  <div class="row mb-2 mx-2">
    <label class="col-sm-2 col-form-label py-1" style="font-size:13px;font-weight:600;">Jabatan - Status</label>
    <div class="col-sm-10">
      <input id="hdr-jabatan" type="text" class="form-control" readonly style="background:#eceef1;font-size:14px;" value="{{ $transaksi->JabatanSelected ?? $transaksi->Jabatan12 }} - {{ $transaksi->Aktif == 1 ? 'Aktif' : 'Tidak Aktif' }}">
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

  <div class="row mb-3 mx-2 align-items-end">
    <div class="col-sm-6">
      <div class="d-flex gap-2 align-items-end">
        @csrf
        <input type="hidden" name="start_year" id="hidden_start_year" value="{{ $startYear ?? old('start_year') }}">
        <input type="hidden" name="end_year" id="hidden_end_year" value="{{ $endYear ?? old('end_year') }}">
        <input type="hidden" name="jenis_tunjangan" id="hidden_jenis_tunjangan" value="{{ $jenisTunjangan ?? 'semua' }}">

        <div>
          <label class="form-label mb-1" style="font-size:11px;">Filter Tahun</label>
          <select name="tahun_versi" class="form-select form-select-sm">
            @foreach(($yearsForNidn ?? []) as $y)
              <option value="{{ $y }}" {{ ($selectedYear ?? null) == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
          </select>
        </div>

        <form action="{{ route('monitoring-pembayaran.export-excel') }}" method="POST">
          @csrf
          <input type="hidden" name="nidn" value="{{ $nidn ?? '' }}">
          <input type="hidden" name="tahun_versi" id="export_tahun_versi" value="{{ $selectedYear ?? '' }}">
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
          class="btn btn-outline-danger d-none"
        >
          <span class="tf-icons bx bx-target-lock"></span>&nbsp; Cek Koordinat
        </a>
      </div>
    </div>
  </div>

  <style>.mp-tbl{font-size:12px;line-height:1.3}.mp-tbl th,.mp-tbl td{padding:3px 5px!important;vertical-align:middle}</style>
  @php
    $hasTkgb = collect($kotorTkgb)->merge($pajakTkgb)->merge($bersihTkgb)->sum() != 0;
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
  <div class="table-responsive text-nowrap mt-2" style="overflow:auto;padding-right:0;">
    <table class="table table-bordered table-hover table-sm mp-tbl" id="mp-table" style="width:100%" data-has-tkgb="{{ $hasTkgb ? '1' : '0' }}">
      <thead>
        <tr>
          <th rowspan="2" class="text-center">Tahun</th>
          <th rowspan="2" class="text-center">Bulan</th>
          <th rowspan="2" class="text-center">Kode Usulan</th>
          <th rowspan="2" class="text-center">Gol/MK</th>
          <th rowspan="2" class="text-center">Gaji</th>
          <th colspan="{{ $nomColspan }}" class="text-center">Nominal</th>
          <th rowspan="2" class="text-center">NO SP2D</th>
          <th rowspan="2" class="text-center">TGL SP2D</th>
          <th rowspan="2" class="text-center">Selisih</th>
          <th rowspan="2" class="text-center">Status</th>
        </tr>
        <tr>
          <th class="text-center">Kotor TPD</th>
          @if($hasTkgb)<th class="text-center tkgb-col">Kotor TKGB</th>@endif
          <th class="text-center">Pajak TPD</th>
          @if($hasTkgb)<th class="text-center tkgb-col">Pajak TKGB</th>@endif
          <th class="text-center">Bersih TPD</th>
          @if($hasTkgb)<th class="text-center tkgb-col">Bersih TKGB</th>@endif
        </tr>
      </thead>
      <tbody>
        @foreach ($months as $index => $month)
        @php $sel = $selisihBulanan[$index] ?? 0; $st = $statusBulanan[$index] ?? null; @endphp
        <tr>
          <td class="text-center">{{ $selectedYear ?? '-' }}</td>
          <td>{{ $month }}</td>
          <td class="text-center">{{ $kodeUsulanBulanan[$index] ?? '-' }}</td>
          <td class="text-center">{{ $golonganBulanan[$index] ?? '-' }} - {{ $tahunBulanan[$index] ?? '-' }}</td>
          <td class="text-end">{{ number_format($gajiBulanan[$index] ?? 0,0,',','.') }}</td>
          <td class="text-end">{{ number_format($kotorTpd[$index] ?? 0,0,',','.') }}</td>
          @if($hasTkgb)<td class="text-end tkgb-col">{{ number_format($kotorTkgb[$index] ?? 0,0,',','.') }}</td>@endif
          <td class="text-end">{{ number_format($pajakTpd[$index] ?? 0,0,',','.') }}</td>
          @if($hasTkgb)<td class="text-end tkgb-col">{{ number_format($pajakTkgb[$index] ?? 0,0,',','.') }}</td>@endif
          <td class="text-end">{{ number_format($bersihTpd[$index] ?? 0,0,',','.') }}</td>
          @if($hasTkgb)<td class="text-end tkgb-col">{{ number_format($bersihTkgb[$index] ?? 0,0,',','.') }}</td>@endif
          <td class="text-center" style="font-size:11px;">{{ $noSp2d[$index] ?? '-' }}</td>
          @php
             $tglSp2dStr = $tglSp2d[$index] ?? '-';
             if ($tglSp2dStr !== '' && $tglSp2dStr !== '-') {
                 try { $tglSp2dStr = \Carbon\Carbon::parse($tglSp2dStr)->format('d/m/Y'); } catch(\Exception $e) {}
             }
          @endphp
          <td class="text-center" style="font-size:11px;">{{ $tglSp2dStr }}</td>
          <td class="text-end fw-bold {{ $sel < 0 ? 'text-danger' : ($sel > 0 ? 'text-success' : 'text-success') }}">{{ $sel < 0 ? '-' : ($sel > 0 ? '+' : '') }}{{ number_format(abs($sel),0,',','.') }}</td>
          <td class="text-center">@if($st && isset($statusMap[$st]))<span class="badge {{ $statusMap[$st][0] }}" style="font-size:10px;">{{ $statusMap[$st][1] }}</span>@elseif($st && str_starts_with($st, 'kode:'))<span class="badge bg-label-secondary" style="font-size:10px;">{{ substr($st, 5) }}</span>@else - @endif</td>
        </tr>
        @endforeach



        <tr class="fw-bold table-light">
          <td colspan="4" class="text-center">Jumlah</td>
          <td class="text-end">{{ number_format($totalGaji,0,',','.') }}</td>
          <td class="text-end">{{ number_format($totalKotorTpd,0,',','.') }}</td>
          @if($hasTkgb)<td class="text-end tkgb-col">{{ number_format($totalKotorTkgb,0,',','.') }}</td>@endif
          <td class="text-end">{{ number_format($totalPajakTpd,0,',','.') }}</td>
          @if($hasTkgb)<td class="text-end tkgb-col">{{ number_format($totalPajakTkgb,0,',','.') }}</td>@endif
          <td class="text-end">{{ number_format($totalBersihTpd,0,',','.') }}</td>
          @if($hasTkgb)<td class="text-end tkgb-col">{{ number_format($totalBersihTkgb,0,',','.') }}</td>@endif
          <td colspan="2"></td><td colspan="2"></td>
        </tr>

        @php
           // Mengambil selisih asli TANPA dikurangi pembayaran dan tanpa netting
           $kGrossRow = $summaryOriginal['k_gross'] ?? 0;
           $kPajakRow = $summaryOriginal['k_pajak'] ?? 0;
           $kNetRow   = $summaryOriginal['k_net'] ?? 0;
           
           $lGrossRow = $summaryOriginal['l_gross'] ?? 0;
           $lPajakRow = $summaryOriginal['l_pajak'] ?? 0;
           $lNetRow   = $summaryOriginal['l_net'] ?? 0;

           // Sisa Akhir
           $kGrossM = $summaryRekap['k_gross'] ?? 0;
           $kPajakM = $summaryRekap['k_pajak'] ?? 0;
           $kNetM = $summaryRekap['k_net'] ?? 0;
           $lGrossM = $summaryRekap['l_gross'] ?? 0;
           $lPajakM = $summaryRekap['l_pajak'] ?? 0;
           $lNetM = $summaryRekap['l_net'] ?? 0;
        @endphp
        <tr class="fw-bold" style="background-color: #ffdcdc">
          <td colspan="4" class="text-center">Pembayaran Kekurangan</td>
          <td></td>
          <td class="text-end">{{ number_format($kGrossRow,0,',','.') }}</td>
          @if($hasTkgb)<td class="text-end tkgb-col">0</td>@endif
          <td class="text-end">{{ number_format($kPajakRow,0,',','.') }}</td>
          @if($hasTkgb)<td class="text-end tkgb-col">0</td>@endif
          <td class="text-end">{{ number_format($kNetRow,0,',','.') }}</td>
          @if($hasTkgb)<td class="text-end tkgb-col">0</td>@endif
          <td colspan="2"></td><td colspan="2"></td>
        </tr>
        <tr class="fw-bold" style="background-color: #dbeafe">
          <td colspan="4" class="text-center">Pengembalian Kelebihan</td>
          <td></td>
          <td class="text-end">{{ number_format($lGrossRow,0,',','.') }}</td>
          @if($hasTkgb)<td class="text-end tkgb-col">0</td>@endif
          <td class="text-end">{{ number_format($lPajakRow,0,',','.') }}</td>
          @if($hasTkgb)<td class="text-end tkgb-col">0</td>@endif
          <td class="text-end">{{ number_format($lNetRow,0,',','.') }}</td>
          @if($hasTkgb)<td class="text-end tkgb-col">0</td>@endif
          <td colspan="2"></td><td colspan="2"></td>
        </tr>

        @php
            $jmKotorTpd = $totalKotorTpd + $totalKotorTkgb;
            $jmPajakTpd = $totalPajakTpd + $totalPajakTkgb;
            $jmBersihTpd = $totalBersihTpd + $totalBersihTkgb;

            $totalAkhirKotorTpd = $jmKotorTpd + $kGrossRow - $lGrossRow;
            $totalAkhirPajakTpd = $jmPajakTpd + $kPajakRow - $lPajakRow;
            $totalAkhirBersihTpd = $jmBersihTpd + $kNetRow - $lNetRow;
        @endphp
        <tr class="fw-bold" style="background-color: #d1fae5;">
          <td colspan="4" class="text-center">Total Akhir</td>
          <td class="text-end">{{ number_format($totalGaji,0,',','.') }}</td>
          <td class="text-end">{{ number_format($totalAkhirKotorTpd,0,',','.') }}</td>
          @if($hasTkgb)<td class="text-end tkgb-col">0</td>@endif
          <td class="text-end">{{ number_format($totalAkhirPajakTpd,0,',','.') }}</td>
          @if($hasTkgb)<td class="text-end tkgb-col">0</td>@endif
          <td class="text-end">{{ number_format($totalAkhirBersihTpd,0,',','.') }}</td>
          @if($hasTkgb)<td class="text-end tkgb-col">0</td>@endif
          <td colspan="2"></td><td colspan="2"></td>
        </tr>
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
      let currentJenis = jenisTunjanganInput ? jenisTunjanganInput.value : 'semua';
      
      const fmt = n => Number(n).toLocaleString('id-ID',{maximumFractionDigits:0});
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
          const el2=document.getElementById('hdr-jabatan'); if(el2) el2.value=(h.JabatanSelected||'')+' - '+(h.Aktif==1?'Aktif':'Tidak Aktif');
          const el3=document.getElementById('hdr-pt'); if(el3) el3.value=(h.Kode_PT||'')+' - '+(h.PTS||'');

          // PNS badge
          const badge=document.getElementById('badge-jenis');
          if(badge){
            const j=(h.Jenis||'').toUpperCase(), pns=j.indexOf('PNS')!==-1&&j.indexOf('NON')===-1;
            badge.textContent=pns?'PNS':'Non-PNS';
            badge.className='badge '+(pns?'bg-label-primary':'bg-label-success');
            badge.style.cssText='font-size:13px;font-weight:700;padding:6px 14px;';
          }
          const sm=data.summary||{};
          const ek=document.getElementById('sum-kewajiban'); if(ek) ek.textContent='Rp '+fmt(sm.totalKewajiban||0);
          const ed=document.getElementById('sum-dibayar'); if(ed) ed.textContent='Rp '+fmt(sm.totalDibayar||0);
          const es=document.getElementById('sum-selisih');
          if(es){es.textContent='Rp '+fmt(sm.totalSelisih||0); es.className=(sm.totalSelisih||0)==0?'text-success':'text-danger';}
          const si=document.getElementById('sum-selisih-icon');
          if(si){si.className='avatar-initial rounded '+((sm.totalSelisih||0)==0?'bg-label-success':'bg-label-danger')+' me-2';}

          const sumTkgb=[...(data.kotorTkgb||[]),...(data.pajakTkgb||[]),...(data.bersihTkgb||[])].reduce((a,b)=>a+Number(b),0);
          const hasTkgb=sumTkgb!=0;
          const tbl=document.getElementById('mp-table');
          if(tbl) tbl.dataset.hasTkgb=hasTkgb?'1':'0';

          const thead=tbl?.querySelector('thead');
          if(thead){
            const nc=hasTkgb?6:3;
            thead.innerHTML=`<tr><th rowspan="2" class="text-center">Tahun</th><th rowspan="2" class="text-center">Bulan</th><th rowspan="2" class="text-center">Kode Usulan</th><th rowspan="2" class="text-center">Gol/MK</th><th rowspan="2" class="text-center">Gaji</th><th colspan="${nc}" class="text-center">Nominal</th><th rowspan="2" class="text-center">NO SP2D</th><th rowspan="2" class="text-center">TGL SP2D</th><th rowspan="2" class="text-center">Selisih</th><th rowspan="2" class="text-center">Status</th></tr><tr><th class="text-center">Kotor TPD</th>${hasTkgb?'<th class="text-center tkgb-col">Kotor TKGB</th>':''}<th class="text-center">Pajak TPD</th>${hasTkgb?'<th class="text-center tkgb-col">Pajak TKGB</th>':''}<th class="text-center">Bersih TPD</th>${hasTkgb?'<th class="text-center tkgb-col">Bersih TKGB</th>':''}</tr>`;
          }

          const tbody=tbl?.querySelector('tbody'); 
          if(tbody) {
            tbody.innerHTML='';
            const months=data.months||[], sb=data.selisihBulanan||[], stb=data.statusBulanan||[];
            const tkc=(v)=>hasTkgb?`<td class="text-end tkgb-col">${fmt(v)}</td>`:'';

            for(let i=0;i<months.length;i++){
              const s=sb[i]||0, st=stb[i], sc=s<0?'text-end text-danger fw-bold':(s>0?'text-end text-success fw-bold':'text-end text-success fw-bold'), ss='';
              const pfx=s<0?'-':(s>0?'+':'');
              let stH='-'; if(st&&statusCfg[st]) stH=`<span class="badge ${statusCfg[st][0]}" style="font-size:10px">${statusCfg[st][1]}</span>`; else if(st&&st.startsWith('kode:')) stH=`<span class="badge bg-label-secondary" style="font-size:10px">${st.substring(5)}</span>`;
              
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

              tbody.innerHTML+=`<tr><td class="text-center">${data.selectedYear||'-'}</td><td>${months[i]}</td><td class="text-center">${data.kodeUsulanBulanan[i]??'-'}</td><td class="text-center">${data.golonganBulanan[i]??'-'} - ${data.tahunBulanan[i]??'-'}</td><td class="text-end">${fmt(data.gajiBulanan[i]??0)}</td><td class="text-end">${fmt(data.kotorTpd[i]??0)}</td>${tkc(data.kotorTkgb[i]??0)}<td class="text-end">${fmt(data.pajakTpd[i]??0)}</td>${tkc(data.pajakTkgb[i]??0)}<td class="text-end">${fmt(data.bersihTpd[i]??0)}</td>${tkc(data.bersihTkgb[i]??0)}<td class="text-center" style="font-size:11px">${data.noSp2d[i]??'-'}</td><td class="text-center" style="font-size:11px">${tglMain}</td><td class="${sc}" style="${ss}">${pfx}${fmt(Math.abs(s))}</td><td class="text-center">${stH}</td></tr>`;
            }

            // Totals
            const t=data.totals||{};
            tbody.innerHTML+=`<tr class="fw-bold table-light"><td colspan="4" class="text-center">Jumlah</td><td class="text-end">${fmt(t.gaji||0)}</td><td class="text-end">${fmt(t.kotorTpd||0)}</td>${tkc(t.kotorTkgb||0)}<td class="text-end">${fmt(t.pajakTpd||0)}</td>${tkc(t.pajakTkgb||0)}<td class="text-end">${fmt(t.bersihTpd||0)}</td>${tkc(t.bersihTkgb||0)}<td colspan="2"></td><td colspan="2"></td></tr>`;

            // Pembayaran Kekurangan dan Pengembalian Kelebihan (Nilai Asli)
            const sumOri = data.summaryOriginal || {};
            const valKGrRow = sumOri.k_gross || 0;
            const valKPjRow = sumOri.k_pajak || 0;
            const valKNeRow = sumOri.k_net || 0;
            const valLGrRow = sumOri.l_gross || 0;
            const valLPjRow = sumOri.l_pajak || 0;
            const valLNeRow = sumOri.l_net || 0;
            
            tbody.innerHTML+=`<tr class="fw-bold" style="background-color:#ffdcdc"><td colspan="4" class="text-center">Pembayaran Kekurangan</td><td></td><td class="text-end">${fmt(valKGrRow)}</td>${tkc(0)}<td class="text-end">${fmt(valKPjRow)}</td>${tkc(0)}<td class="text-end">${fmt(valKNeRow)}</td>${tkc(0)}<td colspan="2"></td><td colspan="2"></td></tr>`;
            tbody.innerHTML+=`<tr class="fw-bold" style="background-color:#dbeafe"><td colspan="4" class="text-center">Pengembalian Kelebihan</td><td></td><td class="text-end">${fmt(valLGrRow)}</td>${tkc(0)}<td class="text-end">${fmt(valLPjRow)}</td>${tkc(0)}<td class="text-end">${fmt(valLNeRow)}</td>${tkc(0)}<td colspan="2"></td><td colspan="2"></td></tr>`;

            // Total Akhir (Sisa)
            const sumRekap = data.summaryRekap || {};
            const valKGr = sumRekap.k_gross || 0;
            const valKPj = sumRekap.k_pajak || 0;
            const valKNe = sumRekap.k_net || 0;
            const valLGr = sumRekap.l_gross || 0;
            const valLPj = sumRekap.l_pajak || 0;
            const valLNe = sumRekap.l_net || 0;

            const jmKotorTpd = (t.kotorTpd||0) + (t.kotorTkgb||0);
            const jmPajakTpd = (t.pajakTpd||0) + (t.pajakTkgb||0);
            const jmBersihTpd = (t.bersihTpd||0) + (t.bersihTkgb||0);
            
            const taKotorTpd = jmKotorTpd + valKGrRow - valLGrRow;
            const taPajakTpd = jmPajakTpd + valKPjRow - valLPjRow;
            const taBersihTpd = jmBersihTpd + valKNeRow - valLNeRow;
            tbody.innerHTML+=`<tr class="fw-bold" style="background-color:#d1fae5"><td colspan="4" class="text-center">Total Akhir</td><td class="text-end">${fmt(t.gaji||0)}</td><td class="text-end">${fmt(taKotorTpd)}</td>${tkc(0)}<td class="text-end">${fmt(taPajakTpd)}</td>${tkc(0)}<td class="text-end">${fmt(taBersihTpd)}</td>${tkc(0)}<td colspan="2"></td><td colspan="2"></td></tr>`;
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
            alert("Terjadi kesalahan jaringan atau server. Silakan muat ulang halaman.");
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
