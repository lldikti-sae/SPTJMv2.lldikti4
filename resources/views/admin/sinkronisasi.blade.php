@extends((auth()->check() && method_exists(auth()->user(), 'isPIC') && auth()->user()->isPIC()) ? 'layouts/contentNavbarLayoutPic' : 'layouts/contentNavbarLayout')

@section('title', 'Sinkronisasi Data - SPTJM Online')

@section('page-style')
<style>
.md2-page-header { display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:24px; }

.md2-page-header .breadcrumb { margin:0; font-size:0.8rem; background:none; padding:0; }
.md2-page-header .breadcrumb-item a { color:#696cff; text-decoration:none; }
.md2-page-header .breadcrumb-item.active { color:#8592a3; }
.md2-page-header .breadcrumb-item+.breadcrumb-item::before { color:#8592a3; }

/* Modern tab styling as pill buttons */
.nav-tabs.custom-tabs {
  border-bottom: none !important;
  display: flex !important;
  gap: 10px !important;
  margin-bottom: 6px !important;
  padding: 0 !important;
}
.nav-tabs.custom-tabs .nav-item {
  margin-bottom: 0 !important;
}
.nav-tabs.custom-tabs .nav-link {
  background-color: #fff !important;
  border: 1.5px solid #e2e8f0 !important;
  color: #4a5568 !important;
  font-weight: 600 !important;
  font-size: 0.82rem !important;
  padding: 8px 24px !important;
  border-radius: 24px !important;
  transition: all 0.2s ease !important;
  cursor: pointer !important;
}
.nav-tabs.custom-tabs .nav-link:hover:not(.active) {
  background-color: #f8fafc !important;
  border-color: #cbd5e1 !important;
  color: #1a56db !important;
}
.nav-tabs.custom-tabs .nav-link.active {
  background-color: #0b3d91 !important; /* Dark blue matching Figma / data-dosen */
  border-color: #0b3d91 !important;
  color: #fff !important;
  font-weight: 700 !important;
  box-shadow: 0 4px 10px rgba(11, 61, 145, 0.25) !important;
}

/* Modern buttons styling */
.btn-sptjm-primary, .btn-sptjm-warning, .btn-sptjm-success {
  border: none;
  color: #fff !important;
  font-weight: 600;
  font-size: 0.82rem;
  padding: 8px 18px;
  border-radius: 20px;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: all 0.2s;
  cursor: pointer;
}
.btn-sptjm-primary {
  background: #1a56db;
}
.btn-sptjm-primary:hover {
  background: #1648c0;
  box-shadow: 0 4px 12px rgba(26,86,219,0.35);
}
.btn-sptjm-warning {
  background: #ffab00;
}
.btn-sptjm-warning:hover {
  background: #e09600;
  box-shadow: 0 4px 12px rgba(255,171,0,0.35);
}
.btn-sptjm-success {
  background: #28c76f;
}
.btn-sptjm-success:hover {
  background: #20a65b;
  box-shadow: 0 4px 12px rgba(40,199,111,0.35);
}
.btn-sptjm-success:disabled {
  background: #a8ebd0;
  cursor: not-allowed;
  box-shadow: none;
}

/* Action buttons in tables styling */
.btn-sptjm-action {
  padding: 5px 12px;
  font-size: 0.75rem;
  font-weight: 600;
  border-radius: 4px;
  border: none;
  cursor: pointer;
  transition: all 0.15s ease;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}
.btn-sptjm-action-success {
  background-color: #e8faf0;
  color: #28c76f !important;
}
.btn-sptjm-action-success:hover {
  background-color: #c3f0d8;
  color: #1e7e34 !important;
}
.btn-sptjm-action-primary {
  background-color: #e8f0fe;
  color: #1a56db !important;
}
.btn-sptjm-action-primary:hover {
  background-color: #d0e1fd;
  color: #1a56db !important;
}

/* Modern form controls styling */
.form-control-sptjm {
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  padding: 8px 14px;
  font-size: 0.84rem;
  color: #2d3748;
  outline: none;
  transition: border-color 0.2s;
  background: #f8fafc;
  width: 100%;
}
.form-control-sptjm:focus {
  border-color: #1a56db;
  background-color: #fff;
}
.form-select-sptjm {
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  padding: 8px 28px 8px 12px;
  font-size: 0.84rem;
  color: #2d3748;
  outline: none;
  transition: border-color 0.2s;
  background: #f8fafc url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3E%3C/svg%3E") no-repeat right 12px center;
  background-size: 10px 10px;
  appearance: none;
  cursor: pointer;
  width: 100%;
}
.form-select-sptjm:focus {
  border-color: #1a56db;
  background-color: #fff;
}

/* Modern instruction text styling */
.sptjm-instruction-text {
  font-size: 0.9rem !important;
  font-weight: 600 !important;
  color: #2c3e50 !important;
  margin-bottom: 6px !important;
  line-height: 1.5 !important;
}
</style>
@endsection

@section('content')
<style>
  /* make Jabatan and Jenis columns fit their content */
  .fit-col{ white-space:nowrap; width:1%; }
  /* widen important identifier/name columns */
  .wide-col{ min-width:160px; }
  /* highlight mismatch cells (cek data) */
  .golmasa-mismatch{ background-color:#ffecec !important; }
</style>
@php
  $isPic = auth()->check() && method_exists(auth()->user(), 'isPIC') && auth()->user()->isPIC();
  $routePrefix = $isPic ? 'pic' : 'admin';
  $months = [
    1 => 'Januari',
    2 => 'Februari',
    3 => 'Maret',
    4 => 'April',
    5 => 'Mei',
    6 => 'Juni',
    7 => 'Juli',
    8 => 'Agustus',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember',
  ];
@endphp

<div class="md2-page-header">
    <div class="page-titles">
        <h1>Sinkronisasi Data</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Monitoring</a></li>
            <li class="breadcrumb-item active">Sinkronisasi Data</li>
        </ol></nav>
    </div>
</div>

<div id="alertBox" class="alert d-none" role="alert"></div>

<!-- Combined card: Gaji + Pajak (tabs) -->
<div class="card" style="width: 100%; padding: 10px; margin-top: 1rem;">
  <div class="d-flex flex-wrap align-items-center">
    <div class="px-2 pt-2 pb-0 w-100">
      <ul class="nav nav-tabs custom-tabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="tab-gaji-btn" data-bs-toggle="tab" data-bs-target="#tab-gaji" type="button" role="tab" aria-controls="tab-gaji" aria-selected="true">Gaji</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="tab-pajak-btn" data-bs-toggle="tab" data-bs-target="#tab-pajak" type="button" role="tab" aria-controls="tab-pajak" aria-selected="false">Pajak</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="tab-golmasa-btn" data-bs-toggle="tab" data-bs-target="#tab-golmasa" type="button" role="tab" aria-controls="tab-golmasa" aria-selected="false">Gol/Masa Kerja</button>
        </li>
      </ul>
    </div>
  </div>
  <hr style="margin: 6px 0 0 0;">
  <div class="card-body px-2 pb-2 pt-1">
    <div class="tab-content">
      <div class="tab-pane fade show active" id="tab-gaji" role="tabpanel" aria-labelledby="tab-gaji-btn">
    <p class="sptjm-instruction-text">
      Gunakan tombol di bawah untuk mengecek gaji yang tidak sesuai untuk semua NIDN/NUPTK pada tahun versi aktif.
    </p>
    <div class="row mb-2" style="font-size: 12px;">
      <div class="col-sm-4">
        <label class="col-form-label mb-1"><b>Cek Data Gaji per NIDN/NUPTK (Semua Bulan)</b></label>
        <input type="text" id="gajiSingleNidn" class="form-control-sptjm" placeholder="Masukkan NIDN/NUPTK untuk cek data gaji (semua bulan)" />
      </div>
      <div class="col-sm-3 d-flex align-items-end mt-2 mt-sm-0">
        <button type="button" id="btnSyncGajiNidnAll" class="btn-sptjm-primary">
          <span class="tf-icons bx bx-search"></span>&nbsp; Cek data
        </button>
      </div>
    </div>
    <div class="d-flex flex-wrap gap-2 mb-2">
      <button type="button" id="btnCheckGajiMismatch" class="btn-sptjm-warning">
        <span class="tf-icons bx bx-search"></span>&nbsp; Cek Gaji Tidak Sesuai
      </button>
      <button type="button" id="btnSyncGajiAll" class="btn-sptjm-success">
        <span class="tf-icons bx bx-refresh"></span>&nbsp; Sinkronisasi Gaji ke Semua NIDN/NUPTK
      </button>
    </div>
    <div id="syncGajiStatus" class="text-muted mb-2" style="font-size: 12px;">Belum ada proses sinkronisasi gaji.</div>
    <div id="gajiMismatchStatus" class="text-muted mb-1" style="font-size: 12px;">Belum ada data pengecekan gaji.</div>
    <div id="gajiMismatchTableWrapper" class="table-responsive" style="font-size: 12px;">
      <table id="gajiMismatchTable" class="table table-sm table-bordered mb-0" style="width: 100%;">
        <thead>
          <tr>
            <th>NIDN</th>
            <th>NUPTK</th>
            <th>Nama</th>
            <th>Kode PT</th>
            <th>Wilayah</th>
            <th>Tidak Sesuai</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
    <div id="gajiMismatchDetailTitle" class="mt-2 text-muted" style="font-size: 12px; display:none;"></div>
    <div id="gajiMismatchDetailWrapper" class="table-responsive mt-2" style="max-height: 400px; overflow:auto; font-size: 12px; display:none;"></div>
      </div>

      <div class="tab-pane fade" id="tab-pajak" role="tabpanel" aria-labelledby="tab-pajak-btn">
    <p class="sptjm-instruction-text">
      Gunakan tombol di bawah untuk mengecek pajak yang tidak sesuai untuk semua NIDN/NUPTK pada tahun versi aktif.
    </p>
    <div class="row mb-2" style="font-size: 12px;">
      <div class="col-sm-4">
        <label class="col-form-label mb-1"><b>Cek Data Pajak per NIDN/NUPTK (Semua Bulan)</b></label>
        <input type="text" id="pajakSingleNidn" class="form-control-sptjm" placeholder="Masukkan NIDN/NUPTK untuk cek data pajak (semua bulan)" />
      </div>
      <div class="col-sm-3 d-flex align-items-end mt-2 mt-sm-0">
        <button type="button" id="btnSyncPajakNidnAll" class="btn-sptjm-primary">
          <span class="tf-icons bx bx-search"></span>&nbsp; Cek data
        </button>
      </div>
    </div>
    <div class="d-flex flex-wrap gap-2 mb-2">
      <button type="button" id="btnCheckMismatch" class="btn-sptjm-warning">
        <span class="tf-icons bx bx-search"></span>&nbsp; Cek Pajak Tidak Sesuai
      </button>
      <button type="button" id="btnSyncAllMonths" class="btn-sptjm-success" disabled>
        <span class="tf-icons bx bx-refresh"></span>&nbsp; Sinkronisasi Pajak ke Semua NIDN/NUPTK
      </button>
    </div>
    <div id="mismatchStatus" class="text-muted mb-1" style="font-size: 12px;">Belum ada data pengecekan pajak.</div>
    <div id="mismatchTableWrapper" class="table-responsive" style="font-size: 12px;">
      <table id="mismatchTable" class="table table-sm table-bordered mb-0" style="width: 100%;">
        <thead>
          <tr>
            <th>NIDN</th>
            <th>NUPTK</th>
            <th>Nama</th>
            <th>Kode PT</th>
            <th>Wilayah</th>
            <th>Tidak Sesuai</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
    <div id="mismatchDetailTitle" class="mt-2 text-muted" style="font-size: 12px; display:none;"></div>
    <div id="mismatchDetailWrapper" class="table-responsive mt-2" style="max-height: 400px; overflow:auto; font-size: 12px; display:none;"></div>
      </div>

      <div class="tab-pane fade" id="tab-golmasa" role="tabpanel" aria-labelledby="tab-golmasa-btn">
        <p class="sptjm-instruction-text">
          Gunakan tombol di bawah untuk mengecek data Golongan/Masa Kerja yang tidak sesuai untuk semua NIDN/NUPTK pada tahun versi aktif.
        </p>
        <div class="row mb-2" style="font-size: 12px;">
          <div class="col-sm-4">
            <label class="col-form-label mb-1"><b>Cek Data Gol/Masa Kerja per NIDN/NUPTK (Semua Bulan)</b></label>
            <input type="text" id="golmasaSingleNidn" class="form-control-sptjm" placeholder="Masukkan NIDN/NUPTK untuk cek data Gol/Masa Kerja (semua bulan)" />
          </div>
          <div class="col-sm-3 d-flex align-items-end mt-2 mt-sm-0">
            <select id="golmasaSyncScope" class="form-select-sptjm" title="Pilih data yang disinkronisasi">
              <option value="both" selected>Keduanya (Golongan + Masa Kerja)</option>
              <option value="gol">Hanya Golongan</option>
              <option value="masa">Hanya Masa Kerja</option>
            </select>
          </div>
          <div class="col-sm-5 d-flex align-items-end gap-2 mt-2 mt-sm-0">
            <button type="button" id="btnSyncGolmasaNidnAll" class="btn-sptjm-primary">
              <span class="tf-icons bx bx-search"></span>&nbsp; Cek data
            </button>
            <button type="button" id="btnSyncGolmasaNow" class="btn-sptjm-success" disabled onclick="if(window.syncGolmasaTrigger) window.syncGolmasaTrigger();">
              <span class="tf-icons bx bx-refresh"></span>&nbsp; Sinkronisasi
            </button>
          </div>
        </div>
        <div id="golmasaMismatchStatus" class="text-muted mb-1" style="font-size: 12px;">Belum ada data pengecekan Gol/Masa Kerja.</div>
        <div class="text-muted mb-1" style="font-size: 12px; font-weight:600;">Sebelum sinkronisasi</div>
        <div id="golmasaMismatchTableWrapper" class="table-responsive" style="font-size: 12px;">
          <table id="golmasaMismatchTable" class="table table-sm table-bordered mb-0" style="width: 100%;">
            <thead>
              <tr>
                <th class="wide-col" rowspan="2">NIDN</th>
                <th class="wide-col" rowspan="2">NUPTK</th>
                <th class="wide-col" rowspan="2">NAMA</th>
                <th rowspan="2">TMT Pertama</th>
                <th rowspan="2">TMT AKHIR</th>
                <th colspan="12" class="text-center">Jabatan</th>
                <th colspan="12" class="text-center">Golongan</th>
                <th colspan="12" class="text-center">Masa Kerja (Tahun)</th>
              </tr>
              <tr>
                <th>Jan</th>
                <th>Feb</th>
                <th>Mar</th>
                <th>Apr</th>
                <th>Mei</th>
                <th>Jun</th>
                <th>Jul</th>
                <th>Ags</th>
                <th>Sep</th>
                <th>Okt</th>
                <th>Nov</th>
                <th>Des</th>

                <th>Jan</th>
                <th>Feb</th>
                <th>Mar</th>
                <th>Apr</th>
                <th>Mei</th>
                <th>Jun</th>
                <th>Jul</th>
                <th>Ags</th>
                <th>Sep</th>
                <th>Okt</th>
                <th>Nov</th>
                <th>Des</th>

                <th>Jan</th>
                <th>Feb</th>
                <th>Mar</th>
                <th>Apr</th>
                <th>Mei</th>
                <th>Jun</th>
                <th>Jul</th>
                <th>Ags</th>
                <th>Sep</th>
                <th>Okt</th>
                <th>Nov</th>
                <th>Des</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>

        <div class="text-muted mt-3 mb-1" style="font-size: 12px; font-weight:600;">Sesudah sinkronisasi</div>
        <div id="golmasaAfterTableWrapper" class="table-responsive" style="font-size: 12px;">
          <table id="golmasaAfterTable" class="table table-sm table-bordered mb-0" style="width: 100%;">
            <thead>
              <tr>
                <th class="wide-col" rowspan="2">NIDN</th>
                <th class="wide-col" rowspan="2">NUPTK</th>
                <th class="wide-col" rowspan="2">NAMA</th>
                <th rowspan="2">TMT Pertama</th>
                <th rowspan="2">TMT AKHIR</th>
                <th colspan="12" class="text-center">Jabatan</th>
                <th colspan="12" class="text-center">Golongan</th>
                <th colspan="12" class="text-center">Masa Kerja (Tahun)</th>
              </tr>
              <tr>
                <th>Jan</th><th>Feb</th><th>Mar</th><th>Apr</th><th>Mei</th><th>Jun</th><th>Jul</th><th>Ags</th><th>Sep</th><th>Okt</th><th>Nov</th><th>Des</th>
                <th>Jan</th><th>Feb</th><th>Mar</th><th>Apr</th><th>Mei</th><th>Jun</th><th>Jul</th><th>Ags</th><th>Sep</th><th>Okt</th><th>Nov</th><th>Des</th>
                <th>Jan</th><th>Feb</th><th>Mar</th><th>Apr</th><th>Mei</th><th>Jun</th><th>Jul</th><th>Ags</th><th>Sep</th><th>Okt</th><th>Nov</th><th>Des</th>
              </tr>
            </thead>
            <tbody>
              <tr><td colspan="41" class="text-muted">Belum ada hasil sesudah sinkronisasi.</td></tr>
            </tbody>
          </table>
        </div>
        <div id="golmasaMismatchDetailTitle" class="mt-2 text-muted" style="font-size: 12px; display:none;"></div>
        <div id="golmasaMismatchDetailWrapper" class="table-responsive mt-2" style="max-height: 400px; overflow:auto; font-size: 12px; display:none;"></div>
      </div>
    </div>
  </div>
</div>



<!-- Overlay status proses (modal sederhana) -->
<div id="progressOverlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.35); z-index:1050; align-items:center; justify-content:center;">
  <div class="card shadow" style="min-width:280px; max-width:420px;">
    <div class="card-body py-3 px-3">
      <div>
        <div id="progressStatusTitle" style="font-weight:600; font-size:14px;">Sedang memproses...</div>
        <div id="progressStatusText" style="font-size:12px; color:#6c757d;">Mohon tunggu, proses sedang berjalan.</div>
      </div>
      <div class="progress mt-3" style="height:4px;">
        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width:100%"></div>
      </div>
    </div>
  </div>
</div>

<!-- Modal konfirmasi sinkronisasi ke semua bulan -->
<div class="modal fade" id="confirmSyncAllModal" tabindex="-1" aria-labelledby="confirmSyncAllLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h6 class="modal-title" id="confirmSyncAllLabel">Konfirmasi Sinkronisasi</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" style="font-size: 12px;">
        Apakah Anda yakin ingin melakukan sinkronisasi pajak ke semua bulan <strong>hanya untuk bagian yang tidak sesuai (ditandai merah)</strong> pada semua NIDN/NUPTK di tahun versi aktif?
      </div>
      <div class="modal-footer py-2">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
        <button type="button" id="confirmSyncAllBtn" class="btn btn-success btn-sm">Ya, Lanjutkan</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal konfirmasi sinkronisasi per bulan -->
<div class="modal fade" id="confirmSingleSyncModal" tabindex="-1" aria-labelledby="confirmSingleSyncLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h6 class="modal-title" id="confirmSingleSyncLabel">Konfirmasi Sinkronisasi Bulan</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="confirmSingleSyncBody" style="font-size: 12px;">
        <!-- Diisi dinamis: NIDN & bulan -->
      </div>
      <div class="modal-footer py-2">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
        <button type="button" id="confirmSingleSyncBtn" class="btn btn-success btn-sm">Ya, Lanjutkan</button>
      </div>
    </div>
  </div>
  </div>

<!-- Modal informasi umum (sinkronisasi selesai, hasil cek pajak, dll.) -->
<div class="modal fade" id="infoModal" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h6 class="modal-title" id="infoModalLabel">Informasi</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="infoModalBody" style="font-size: 12px;">
        <!-- Diisi dinamis dari JavaScript -->
      </div>
      <div class="modal-footer py-2">
        <button type="button" class="btn btn-primary btn-sm" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('page-script')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const alertBox = document.getElementById('alertBox');
    const btnCheckMismatch = document.getElementById('btnCheckMismatch');
    const btnSyncAllMonths = document.getElementById('btnSyncAllMonths');
    const mismatchTableWrapper = document.getElementById('mismatchTableWrapper');
    const mismatchStatus = document.getElementById('mismatchStatus');
    const mismatchDetailWrapper = document.getElementById('mismatchDetailWrapper');
    const mismatchDetailTitle = document.getElementById('mismatchDetailTitle');
    const progressOverlay = document.getElementById('progressOverlay');
    const progressStatusTitle = document.getElementById('progressStatusTitle');
    const progressStatusText = document.getElementById('progressStatusText');
    const confirmSyncAllModal = document.getElementById('confirmSyncAllModal');
    const confirmSyncAllBtn = document.getElementById('confirmSyncAllBtn');
    const confirmSingleSyncModal = document.getElementById('confirmSingleSyncModal');
    const confirmSingleSyncBody = document.getElementById('confirmSingleSyncBody');
    const confirmSingleSyncBtn = document.getElementById('confirmSingleSyncBtn');
    const infoModal = document.getElementById('infoModal');
    const infoModalLabel = document.getElementById('infoModalLabel');
    const infoModalBody = document.getElementById('infoModalBody');
    const btnSyncGajiAll = document.getElementById('btnSyncGajiAll');
    const btnSyncGajiNidnAll = document.getElementById('btnSyncGajiNidnAll');
    const gajiSingleNidnInput = document.getElementById('gajiSingleNidn');
    const btnSyncPajakNidnAll = document.getElementById('btnSyncPajakNidnAll');
    const pajakSingleNidnInput = document.getElementById('pajakSingleNidn');
    const golmasaSingleNidnInput = document.getElementById('golmasaSingleNidn');
    const golmasaSyncScopeSelect = document.getElementById('golmasaSyncScope');
    const btnSyncGolmasaNidnAll = document.getElementById('btnSyncGolmasaNidnAll');
    const golmasaMismatchTableWrapper = document.getElementById('golmasaMismatchTableWrapper');
    const golmasaMismatchTable = document.getElementById('golmasaMismatchTable');
    const golmasaMismatchDetailWrapper = document.getElementById('golmasaMismatchDetailWrapper');
    const golmasaMismatchDetailTitle = document.getElementById('golmasaMismatchDetailTitle');
    const golmasaAfterTableWrapper = document.getElementById('golmasaAfterTableWrapper');
    const golmasaAfterTable = document.getElementById('golmasaAfterTable');
    const syncGajiStatus = document.getElementById('syncGajiStatus');
    const btnCheckGajiMismatch = document.getElementById('btnCheckGajiMismatch');
    const gajiMismatchTableWrapper = document.getElementById('gajiMismatchTableWrapper');
    const gajiMismatchStatus = document.getElementById('gajiMismatchStatus');
    const gajiMismatchDetailWrapper = document.getElementById('gajiMismatchDetailWrapper');
    const gajiMismatchDetailTitle = document.getElementById('gajiMismatchDetailTitle');

    // Tab guard + deep link
    const tabGajiBtn = document.getElementById('tab-gaji-btn');
    const tabGolmasaBtn = document.getElementById('tab-golmasa-btn');

    // Normalize query string to handle users pasting URLs that still contain HTML entities like "&amp;".
    const rawSearch = window.location.search || '';
    const normalizedSearch = rawSearch.replace(/&amp;/gi, '&');
    const queryParams = new URLSearchParams(normalizedSearch);

    const getFirstParam = (...names) => {
      for (const n of names) {
        const v = queryParams.get(n);
        if (v !== null && String(v).trim() !== '') return String(v);
      }
      return '';
    };

    const requestedTab = (getFirstParam('tab', 'open', 'amp;tab', 'amp;open') || '').toLowerCase();
    const prefillIdentifier = (getFirstParam('nidn', 'nuptk', 'identifier', 'amp;nidn', 'amp;nuptk', 'amp;identifier') || '').trim();
    const autofillRaw = (getFirstParam('autofill', 'amp;autofill') || '').toLowerCase();
    const shouldAutofill = autofillRaw === '1' || autofillRaw === 'true' || autofillRaw === 'yes';

    const activateTab = (btnEl) => {
      if (!btnEl) return;
      try {
        if (window.bootstrap && bootstrap.Tab) {
          bootstrap.Tab.getOrCreateInstance(btnEl).show();
          return;
        }
      } catch (e) {
        // fallback to click
      }
      btnEl.click();
    };

    // Handle deep-link request to Gol/Masa Kerja tab (direct allowed)
    if (requestedTab === 'golmasa' || requestedTab === 'gol_masa_kerja' || requestedTab === 'gol-masa-kerja' || requestedTab === 'gol/masa kerja') {
      setTimeout(() => {
        activateTab(tabGolmasaBtn);
        if (shouldAutofill && prefillIdentifier && golmasaSingleNidnInput) {
          golmasaSingleNidnInput.value = prefillIdentifier;
          golmasaSingleNidnInput.focus();
          // trigger cek data automatically
          if (btnSyncGolmasaNidnAll) {
            setTimeout(() => btnSyncGolmasaNidnAll.click(), 150);
          }
        }
      }, 150);
    }

    // state sementara untuk sinkron per-bulan melalui modal
    let pendingSingleSync = { nidn: null, bulan: null };

    // golmasa state: enforce cek-data-before-sync and prevent sync when no mismatch
    let golmasaLastCheck = { nidn: null, checked: false, hasMismatch: false, mismatchCount: 0 };

    const getGolmasaSyncScope = () => {
      const v = (golmasaSyncScopeSelect && golmasaSyncScopeSelect.value) ? String(golmasaSyncScopeSelect.value) : 'both';
      return (v === 'gol' || v === 'masa' || v === 'both') ? v : 'both';
    };

    const updateGolmasaSyncButtonState = () => {
      const btn = document.getElementById('btnSyncGolmasaNow');
      if (!btn) return;

      if (!golmasaLastCheck || !golmasaLastCheck.checked) {
        btn.disabled = true;
        return;
      }

      const scope = getGolmasaSyncScope();
      const hasMismatch = !!golmasaLastCheck.hasMismatch;
      const canMasa = !!golmasaLastCheck.tmtPertamaAvailable;
      const canGol = !!golmasaLastCheck.tmtAkhirAvailable;

      if (!hasMismatch) {
        btn.disabled = true;
        return;
      }

      if (scope === 'gol') {
        btn.disabled = !canGol;
      } else if (scope === 'masa') {
        btn.disabled = !canMasa;
      } else {
        // both: allow if at least one side can be updated
        btn.disabled = (!canMasa && !canGol);
      }
    };

    // If using tabs, DataTables sometimes needs a resize/adjust when shown
    document.querySelectorAll('button[data-bs-toggle="tab"]').forEach((btn) => {
      btn.addEventListener('shown.bs.tab', function () {
        try {
          if (window.jQuery && window.jQuery.fn && window.jQuery.fn.dataTable) {
            if (window.jQuery.fn.dataTable.isDataTable('#gajiMismatchTable')) {
              window.jQuery('#gajiMismatchTable').DataTable().columns.adjust().draw(false);
            }
            if (window.jQuery.fn.dataTable.isDataTable('#mismatchTable')) {
              window.jQuery('#mismatchTable').DataTable().columns.adjust().draw(false);
            }
          }
        } catch (e) {
          // ignore
        }
      });
    });

    const showAlert = (type, message) => {
      if (!alertBox) return;
      alertBox.className = 'alert alert-' + type;
      alertBox.textContent = message;
      alertBox.classList.remove('d-none');
      // Auto hide after 3s for success/warning
      if (type !== 'danger') {
        setTimeout(() => alertBox.classList.add('d-none'), 3000);
      }
    };

    const showProgress = (title, text) => {
      if (!progressOverlay) return;
      if (progressStatusTitle) progressStatusTitle.textContent = title || 'Sedang memproses...';
      if (progressStatusText) progressStatusText.textContent = text || '';
      progressOverlay.style.display = 'flex';
    };

    const hideProgress = () => {
      if (!progressOverlay) return;
      progressOverlay.style.display = 'none';
    };

    const showInfoModal = (title, text) => {
      const t = title || 'Informasi';
      const body = text || '';
      if (infoModal && window.jQuery && typeof $('#infoModal').modal === 'function') {
        if (infoModalLabel) infoModalLabel.textContent = t;
        if (infoModalBody) infoModalBody.textContent = body;
        $('#infoModal').modal('show');
      } else {
        // Fallback ke alert biasa jika modal tidak tersedia
        alert(body || t);
      }
    };

    const loadGajiDetail = (nidn) => {
      const identifier = (nidn || '').trim();
      if (!identifier || !gajiMismatchDetailWrapper) return;

      showProgress('Memuat data gaji...', 'Mengambil rincian data gaji untuk semua bulan NIDN/NUPTK ' + identifier + '.');

      let html = `
        <div class="mb-2 text-muted" style="font-size:12px;">Detail Data Gaji (Semua Bulan)</div>
        <table class="table table-sm table-bordered mb-0">
          <thead>
            <tr>
              <th>Bulan</th>
              <th>Jabatan</th>
              <th>Kode Usulan</th>
              <th>Gol</th>
              <th>Masa Kerja</th>
              <th>Gaji Saat Ini</th>
              <th>Gaji Seharusnya</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
      `;

      fetch("{{ route($routePrefix . '.sinkronisasi.detailGajiMismatch') }}", {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ NIDN: identifier })
      }).then(async (resp) => {
        const res = await resp.json().catch(() => ({}));
        if (!resp.ok) {
          throw new Error(res.message || 'Gagal mengambil detail gaji.');
        }
        return res;
      }).then((data) => {
        const rows = Array.isArray(data.rows) ? data.rows : [];

        rows.forEach(row => {
          const isMismatch = !!row.is_mismatch;
          const mismatchCellStyle = isMismatch ? ' style="background-color:#ffecec;"' : '';

          // prepare gol & masa kerja display (use row.gol fallback to row.golongan)
          const golText = (row.gol !== undefined && row.gol !== null && String(row.gol).trim() !== '')
            ? row.gol
            : (row.golongan || '');
          const masaKerjaText = (row.masa_kerja !== undefined && row.masa_kerja !== null && String(row.masa_kerja).trim() !== '')
            ? row.masa_kerja
            : '';

          // Kasus: hanya ada KodeUsulan tanpa kode cair -> tunjukkan Gol & Masa Kerja, tapi merge Gaji/GajiSeharusnya/Aksi
          const isKodeUsulanOnly = (row.kode_usulan && (row.can_sync === false) && (row.gaji_db === null || row.gaji_db === undefined));
          if (isKodeUsulanOnly) {
              html += `<tr data-bulan="${row.bulan_index}">` +
              `<td>${row.bulan_label || row.bulan_index}</td>` +
              `<td>${row.jabatan || ''}</td>` +
              `<td>${row.kode_usulan || ''}</td>` +
              `<td><div class="text-center">${golText}</div></td>` +
              `<td><div class="text-center">${masaKerjaText}</div></td>` +
              `<td colspan="3" class="text-center text-muted">Tidak ada kode cair</td>` +
            `</tr>`;
            return;
          }

          const canSync = !!row.can_sync;
          const actionBtn = canSync
            ? `<button type="button" class="btn-sptjm-action btn-sptjm-action-success btn-sync-gaji-month" data-nidn="${identifier}" data-bulan="${row.bulan_index}">Sinkron</button>`
            : '';

          html += `<tr data-bulan="${row.bulan_index}">` +
            `<td>${row.bulan_label || row.bulan_index}</td>` +
            `<td>${row.jabatan || ''}</td>` +
            `<td>${row.kode_usulan || ''}</td>` +
            `<td><div class="text-center">${golText}</div></td>` +
            `<td><div class="text-center">${row.masa_kerja ?? ''}</div></td>` +
            `<td${mismatchCellStyle}><div class="text-end">${formatNumber(row.gaji_db)}</div></td>` +
            `<td${mismatchCellStyle}><div class="text-end">${formatNumber(row.gaji_expected)}</div></td>` +
            `<td class="text-center">${actionBtn}</td>` +
          `</tr>`;
        });

        html += '</tbody></table>';
        gajiMismatchDetailWrapper.innerHTML = html;
        gajiMismatchDetailWrapper.style.display = 'block';
        if (gajiMismatchDetailTitle) {
          gajiMismatchDetailTitle.textContent = 'Detail data gaji untuk NIDN/NUPTK: ' + identifier;
          gajiMismatchDetailTitle.style.display = 'block';
        }
        gajiMismatchDetailWrapper.scrollIntoView({ behavior: 'smooth' });
      }).catch((err) => {
        gajiMismatchDetailWrapper.innerHTML = '<div class="text-danger">' + (err.message || 'Terjadi kesalahan saat mengambil detail gaji.') + '</div>';
        gajiMismatchDetailWrapper.style.display = 'block';
      }).finally(() => {
        hideProgress();
      });
    };

    const loadPajakDetail = (nidn) => {
      const identifier = (nidn || '').trim();
      if (!identifier || !mismatchDetailWrapper) return;

      showProgress('Memuat data pajak...', 'Mengambil rincian data pajak untuk semua bulan NIDN/NUPTK ' + identifier + '.');

      let html = `
        <div class="mb-2 text-muted" style="font-size:12px;">Detail Data Pajak (Semua Bulan)</div>
        <table class="table table-sm table-bordered mb-0">
          <thead>
            <tr>
              <th>Bulan</th>
              <th class="fit-col">Jabatan</th>
              <th>Gol</th>
              <th class="fit-col">Jenis</th>
              <th>TPD</th>
              <th>TKGB</th>
              <th>Pajak TPD</th>
              <th>Pajak TPD (Seharusnya)</th>
              <th>Pajak TKGB</th>
              <th>Pajak TKGB (Seharusnya)</th>
              <th>Nilai Pajak TPD</th>
              <th>Nilai Pajak TPD (Seharusnya)</th>
              <th>Nilai Pajak TKGB</th>
              <th>Nilai Pajak<br>TKGB (Seharusnya)</th>
              <th>Bersih TPD</th>
              <th>Bersih TPD (Seharusnya)</th>
              <th>Bersih TKGB</th>
              <th>Bersih TKGB <br>(Seharusnya)</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
      `;

      fetch("{{ route($routePrefix . '.sinkronisasi.detailMismatch') }}", {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ NIDN: identifier })
      }).then(async (resp) => {
        const res = await resp.json().catch(() => ({}));
        if (!resp.ok) {
          throw new Error(res.message || 'Gagal mengambil detail pajak.');
        }
        return res;
      }).then((data) => {
        const rows = Array.isArray(data.rows) ? data.rows : [];
        rows.forEach(row => {
          const jenis = (row.jenis || '').toString().toUpperCase();
          const jabatan = (row.jabatan || '');
          const gol = (row.golongan || '')

          const kodeCair = row.kode_cair;
          if (!kodeCair) {
            html += `<tr data-bulan="${row.bulan_index}">` +
              `<td>${row.bulan_label || row.bulan_index}</td>` +
              `<td class="fit-col">${jabatan}</td>` +
              `<td><div class="text-center">${gol}</div></td>` +
              `<td class="fit-col">${jenis}</td>` +
              `<td><div class="text-end">${formatNumber(row.tpd)}</div></td>` +
              `<td><div class="text-end">${formatNumber(row.tkgb)}</div></td>` +
              `<td colspan="13" class="text-center text-muted">Kode Cair Tidak ada</td>` +
            `</tr>`;
            return;
          }

          const m_pajak_tpd = !!row.pajak_tpd_mismatch;
          const m_pajak_tkgb = !!row.pajak_tkgb_mismatch;
          const m_nilai_pajak_tpd = !!row.nilai_tpd_mismatch;
          const m_nilai_pajak_tkgb = !!row.nilai_tkgb_mismatch;
          const m_bersih_tpd = !!row.bersih_tpd_mismatch;
          const m_bersih_tkgb = !!row.bersih_tkgb_mismatch;

          const cell = (content, isMismatch, cls) => {
            const classAttr = cls ? ` class="${cls}"` : '';
            const styleAttr = isMismatch ? ' style="background-color:#ffecec;"' : '';
            return `<td${classAttr}${styleAttr}>${content}</td>`;
          };

          html += `<tr data-bulan="${row.bulan_index}">` +
            `<td>${row.bulan_label || row.bulan_index}</td>` +
            cell(jabatan, false, 'fit-col') +
            cell(`<div class="text-center">${gol}</div>`, false) +
            cell(jenis, false, 'fit-col') +
            cell(`<div class="text-end">${formatNumber(row.tpd)}</div>`, false) +
            cell(`<div class="text-end">${formatNumber(row.tkgb)}</div>`, false) +
            cell(`<div class="text-end">${formatNumber(row.pajak_tpd_db)}</div>`, m_pajak_tpd) +
            cell(`<div class="text-end">${formatNumber(row.tarif_pajak)}</div>`, m_pajak_tpd) +
            cell(`<div class="text-end">${formatNumber(row.pajak_tkgb_db)}</div>`, m_pajak_tkgb) +
            cell(`<div class="text-end">${formatNumber(row.tarif_pajak_tkgb_seharusnya)}</div>`, m_pajak_tkgb) +
            cell(`<div class="text-end">${formatNumber(row.nilai_pajak_tpd_db)}</div>`, m_nilai_pajak_tpd) +
            cell(`<div class="text-end">${formatNumber(row.nilai_pajak_tpd_calc)}</div>`, m_nilai_pajak_tpd) +
            cell(`<div class="text-end">${formatNumber(row.nilai_pajak_tkgb_db)}</div>`, m_nilai_pajak_tkgb) +
            cell(`<div class="text-end">${formatNumber(row.nilai_pajak_tkgb_calc)}</div>`, m_nilai_pajak_tkgb) +
            cell(`<div class="text-end">${formatNumber(row.bersih_tpd_db)}</div>`, m_bersih_tpd) +
            cell(`<div class="text-end">${formatNumber(row.bersih_tpd_calc)}</div>`, m_bersih_tpd) +
            cell(`<div class="text-end">${formatNumber(row.bersih_tkgb_db)}</div>`, m_bersih_tkgb) +
            cell(`<div class="text-end">${formatNumber(row.bersih_tkgb_calc)}</div>`, m_bersih_tkgb) +
            cell(`<div class="text-center"><button type="button" class="btn-sptjm-action btn-sptjm-action-success btn-sync-month" data-nidn="${identifier}" data-bulan="${row.bulan_index}">Sinkron</button></div>`, false) +
          `</tr>`;
        });

        html += '</tbody></table>';
        mismatchDetailWrapper.innerHTML = html;
        mismatchDetailWrapper.style.display = 'block';
        if (mismatchDetailTitle) {
          mismatchDetailTitle.textContent = 'Detail data pajak untuk NIDN/NUPTK: ' + identifier;
          mismatchDetailTitle.style.display = 'block';
        }
        mismatchDetailWrapper.scrollIntoView({ behavior: 'smooth' });
      }).catch((err) => {
        mismatchDetailWrapper.innerHTML = '<div class="text-danger">' + (err.message || 'Terjadi kesalahan saat mengambil detail pajak.') + '</div>';
        mismatchDetailWrapper.style.display = 'block';
      }).finally(() => {
        hideProgress();
      });
    };

    const clearGolmasaAfterTable = () => {
      if (!golmasaAfterTable) return;
      const tbody = golmasaAfterTable.querySelector('tbody');
      if (!tbody) return;
      tbody.innerHTML = '<tr><td colspan="41" class="text-muted">Belum ada hasil sesudah sinkronisasi.</td></tr>';
    };

    const isBlank = (v) => {
      return v === null || v === undefined || String(v).trim() === '';
    };

    const renderGolmasaRow = (tableEl, row, opts) => {
      const options = opts || {};
      const highlightMismatch = !!options.highlightMismatch;
      const mismatch = (row && row.mismatch) ? row.mismatch : null;

      if (!tableEl) throw new Error('Tabel Gol/Masa Kerja tidak ditemukan di halaman.');
      const tbody = tableEl.querySelector('tbody');
      if (!tbody) throw new Error('Tabel Gol/Masa Kerja tidak memiliki <tbody>.');

      const masaMismatchArr = mismatch && Array.isArray(mismatch.masa) ? mismatch.masa : [];
      const golMismatchArr = mismatch && Array.isArray(mismatch.gol) ? mismatch.gol : [];

      const tmtPertamaAvailable = !isBlank(row.TMT_Pertama);
      const tmtAkhirAvailable = !isBlank(row.TMT_Akhir);

      const td = (content, isMismatch) => {
        const cls = (highlightMismatch && isMismatch) ? ' class="golmasa-mismatch"' : '';
        return `<td${cls}>${content}</td>`;
      };

      let tr = '<tr>';
      tr += `<td>${row.NIDN || ''}</td>`;
      tr += `<td>${row.NUPTK || ''}</td>`;
      tr += `<td>${row.Nama || ''}</td>`;
      tr += `<td>${!isBlank(row.TMT_Pertama) ? row.TMT_Pertama : 'Tidak Tersedia'}</td>`;
      tr += `<td>${!isBlank(row.TMT_Akhir) ? row.TMT_Akhir : 'Tidak Tersedia'}</td>`;
      for (let i = 0; i < 12; i++) tr += `<td>${(row.jabatan && row.jabatan[i]) || ''}</td>`;

      // Golongan columns (12). If TMT Akhir missing, merge the whole block.
      if (!tmtAkhirAvailable) {
        tr += '<td colspan="12" class="text-center text-muted">TMT Akhir Tidak Tersedia</td>';
      } else {
        for (let i = 0; i < 12; i++) tr += td((row.gol && row.gol[i]) || '', !!golMismatchArr[i]);
      }

      // Masa Kerja columns (12). If TMT Pertama missing, merge the whole block.
      if (!tmtPertamaAvailable) {
        tr += '<td colspan="12" class="text-center text-muted">TMT Pertama Tidak Tersedia</td>';
      } else {
        for (let i = 0; i < 12; i++) tr += td((row.masa && row.masa[i]) || '', !!masaMismatchArr[i]);
      }
      tr += '</tr>';

      tbody.innerHTML = tr;
    };

    const loadGolmasaDetail = (nidn) => {
      const identifier = (nidn || '').trim();
      if (!identifier || !golmasaMismatchDetailWrapper) return;

      showProgress('Memuat data Gol/Masa Kerja...', 'Mengambil rincian data Gol/Masa Kerja untuk semua bulan NIDN/NUPTK ' + identifier + '.');

      fetch("{{ route($routePrefix . '.sinkronisasi.golmasa.search') }}", {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ NIDN: identifier })
      }).then(async (resp) => {
        const res = await resp.json().catch(() => ({}));
        if (!resp.ok) throw new Error(res.message || 'Gagal mengambil data Gol/Masa Kerja.');
        return res;
      }).then((data) => {
        const row = data.row || null;
        if (!row) throw new Error('Data tidak ditemukan.');
        // Populate BEFORE table with mismatch highlighting
        renderGolmasaRow(golmasaMismatchTable, row, { highlightMismatch: true });

        const tmtPertamaAvailable = !isBlank(row.TMT_Pertama);
        const tmtAkhirAvailable = !isBlank(row.TMT_Akhir);

        // determine mismatch state
        const mm = (row && row.mismatch) ? row.mismatch : {};
        const mmGol = Array.isArray(mm.gol) ? mm.gol : [];
        const mmMasa = Array.isArray(mm.masa) ? mm.masa : [];
        const mismatchCount = [...(tmtAkhirAvailable ? mmGol : []), ...(tmtPertamaAvailable ? mmMasa : [])].filter(v => v === true).length;
        const hasMismatch = mismatchCount > 0;
        golmasaLastCheck = { nidn: identifier, checked: true, hasMismatch, mismatchCount, tmtPertamaAvailable, tmtAkhirAvailable };

        // enable sync only if cek data already run AND there are mismatches
        updateGolmasaSyncButtonState();
        if (golmasaMismatchStatus) {
          let base = hasMismatch
            ? ('Ditemukan ' + mismatchCount + ' kolom tidak sesuai (ditandai merah).')
            : 'Data sudah benar. Tidak ada yang perlu disinkronisasi.';
          const notes = [];
          if (!tmtPertamaAvailable) notes.push('TMT Pertama tidak tersedia: Masa Kerja tidak akan disinkronisasi.');
          if (!tmtAkhirAvailable) notes.push('TMT Akhir tidak tersedia: Golongan tidak akan disinkronisasi.');
          if (notes.length) base += ' ' + notes.join(' ');
          golmasaMismatchStatus.textContent = base;
        }

        golmasaMismatchTableWrapper.scrollIntoView({ behavior: 'smooth' });
      }).catch((err) => {
        if (golmasaMismatchTable && golmasaMismatchTable.querySelector('tbody')) {
          golmasaMismatchTable.querySelector('tbody').innerHTML = `<tr><td colspan="41" class="text-danger">${(err.message || 'Terjadi kesalahan saat mengambil data Gol/Masa Kerja.')}</td></tr>`;
        } else {
          golmasaMismatchDetailWrapper.innerHTML = '<div class="text-danger">' + (err.message || 'Terjadi kesalahan saat mengambil data Gol/Masa Kerja.') + '</div>';
          golmasaMismatchDetailWrapper.style.display = 'block';
        }

        golmasaLastCheck = { nidn: identifier, checked: false, hasMismatch: false, mismatchCount: 0 };
        if (btnSyncGolmasaNow) btnSyncGolmasaNow.disabled = true;
      }).finally(() => { hideProgress(); });
    };

    const loadGolmasaAfter = (nidn) => {
      const identifier = (nidn || '').trim();
      if (!identifier) return;

      showProgress('Memuat hasil sesudah sinkronisasi...', 'Mengambil data terbaru setelah sinkronisasi untuk NIDN/NUPTK ' + identifier + '.');

      fetch("{{ route($routePrefix . '.sinkronisasi.golmasa.search') }}", {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ NIDN: identifier })
      }).then(async (resp) => {
        const res = await resp.json().catch(() => ({}));
        if (!resp.ok) throw new Error(res.message || 'Gagal mengambil data terbaru.');
        return res;
      }).then((data) => {
        const row = data.row || null;
        if (!row) throw new Error('Data tidak ditemukan.');
        renderGolmasaRow(golmasaAfterTable, row, { highlightMismatch: false });
        if (golmasaAfterTableWrapper) golmasaAfterTableWrapper.scrollIntoView({ behavior: 'smooth' });
      }).catch((err) => {
        if (golmasaAfterTable && golmasaAfterTable.querySelector('tbody')) {
          golmasaAfterTable.querySelector('tbody').innerHTML = `<tr><td colspan="41" class="text-danger">${(err.message || 'Terjadi kesalahan saat memuat hasil sesudah sinkronisasi.')}</td></tr>`;
        }
      }).finally(() => { hideProgress(); });
    };

    const runSyncAll = () => {
      showProgress('Sedang sinkronisasi pajak...', 'Mohon tunggu, sistem sedang memperbarui pajak hanya untuk data yang tidak sesuai pada semua NIDN/NUPTK dan semua bulan pada tahun versi aktif.');
      btnSyncAllMonths.disabled = true;

      fetch("{{ route($routePrefix . '.sinkronisasi.syncAll') }}", {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({})
      }).then(async (resp) => {
        const res = await resp.json().catch(() => ({}));
        if (!resp.ok) {
          throw new Error(res.message || 'Gagal melakukan sinkronisasi semua bulan.');
        }
        return res;
      }).then((data) => {
        showAlert(data.status === 'success' ? 'success' : 'warning', data.message || 'Sinkronisasi semua bulan selesai.');
        // Refresh hasil pengecekan setelah sinkronisasi
        if (btnCheckMismatch) {
          btnCheckMismatch.click();
        }
        // Modal informasi sinkronisasi selesai
        showInfoModal('Sinkronisasi Selesai', data.message || 'Sinkronisasi pajak ke semua bulan selesai.');
      }).catch((err) => {
        showAlert('danger', err.message || 'Terjadi kesalahan saat sinkronisasi semua bulan.');
      }).finally(() => {
        btnSyncAllMonths.disabled = false;
        hideProgress();
      });
    };

    const runSyncGajiAll = () => {
      showProgress('Sedang sinkronisasi gaji...', 'Mohon tunggu, sistem sedang memperbarui Gaji berdasarkan golongan dan masa kerja hanya pada NIDN/NUPTK per bulan yang gajinya tidak sesuai dan memenuhi aturan Kode Usulan / Kode Cair.');
      if (btnSyncGajiAll) btnSyncGajiAll.disabled = true;
      if (syncGajiStatus) syncGajiStatus.textContent = 'Sedang memproses sinkronisasi gaji...';

      fetch("{{ route($routePrefix . '.sinkronisasi.syncGajiAll') }}", {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({})
      }).then(async (resp) => {
        const res = await resp.json().catch(() => ({}));
        if (!resp.ok) {
          throw new Error(res.message || 'Gagal melakukan sinkronisasi gaji.');
        }
        return res;
      }).then((data) => {
        if (syncGajiStatus) syncGajiStatus.textContent = data.message || 'Sinkronisasi gaji selesai.';
        showAlert(data.status === 'success' ? 'success' : (data.status === 'warning' ? 'warning' : 'danger'), data.message || 'Sinkronisasi gaji selesai.');
        showInfoModal('Sinkronisasi Gaji', data.message || 'Sinkronisasi gaji 1-12 selesai diproses.');
      }).catch((err) => {
        if (syncGajiStatus) syncGajiStatus.textContent = err.message || 'Terjadi kesalahan saat sinkronisasi gaji.';
        showAlert('danger', err.message || 'Terjadi kesalahan saat sinkronisasi gaji.');
      }).finally(() => {
        if (btnSyncGajiAll) btnSyncGajiAll.disabled = false;
        hideProgress();
      });
    };

    // Cek data pajak 1 NIDN/NUPTK untuk semua bulan (ditampilkan ke tabel detail)
    if (btnSyncPajakNidnAll && pajakSingleNidnInput) {
      btnSyncPajakNidnAll.addEventListener('click', function () {
        const nidn = (pajakSingleNidnInput.value || '').trim();
        if (!nidn) {
          showAlert('warning', 'NIDN/NUPTK untuk cek data pajak wajib diisi.');
          pajakSingleNidnInput.focus();
          return;
        }

        btnSyncPajakNidnAll.disabled = true;
        if (mismatchStatus) mismatchStatus.textContent = 'Memuat detail data pajak untuk NIDN/NUPTK ' + nidn + '...';
        loadPajakDetail(nidn);
        setTimeout(() => { btnSyncPajakNidnAll.disabled = false; }, 400);
      });
    }

    // Cek data Gol/Masa Kerja per NIDN/NUPTK (semua bulan)
    if (btnSyncGolmasaNidnAll && golmasaSingleNidnInput) {
      btnSyncGolmasaNidnAll.addEventListener('click', function () {
        const nidn = (golmasaSingleNidnInput.value || '').trim();
        if (!nidn) {
          showAlert('warning', 'NIDN/NUPTK untuk cek data Gol/Masa Kerja wajib diisi.');
          golmasaSingleNidnInput.focus();
          return;
        }

        btnSyncGolmasaNidnAll.disabled = true;
        if (golmasaMismatchDetailTitle) golmasaMismatchDetailTitle.textContent = 'Memuat data Gol/Masa Kerja untuk NIDN/NUPTK ' + nidn + '...';
        golmasaLastCheck = { nidn, checked: false, hasMismatch: false, mismatchCount: 0 };
        if (btnSyncGolmasaNow) btnSyncGolmasaNow.disabled = true;
        clearGolmasaAfterTable();
        loadGolmasaDetail(nidn);
        setTimeout(() => { btnSyncGolmasaNidnAll.disabled = false; }, 400);
      });
    }

    const trySyncGolmasaNow = async () => {
      const nidn = (golmasaSingleNidnInput && golmasaSingleNidnInput.value || '').trim();
      if (!nidn) {
        showAlert('warning', 'NIDN/NUPTK untuk sinkronisasi wajib diisi.');
        if (golmasaSingleNidnInput) golmasaSingleNidnInput.focus();
        return;
      }

      // must run cek data first for the same identifier
      if (!golmasaLastCheck.checked || golmasaLastCheck.nidn !== nidn) {
        showAlert('warning', 'Silakan klik "Cek data" terlebih dahulu sebelum sinkronisasi.');
        if (btnSyncGolmasaNow) btnSyncGolmasaNow.disabled = true;
        return;
      }

      // if no mismatch, block sync
      if (!golmasaLastCheck.hasMismatch) {
        showAlert('warning', 'Data sudah benar. Tidak ada yang perlu disinkronisasi.');
        if (btnSyncGolmasaNow) btnSyncGolmasaNow.disabled = true;
        return;
      }

      const scope = getGolmasaSyncScope();
      const canMasa = !!golmasaLastCheck.tmtPertamaAvailable;
      const canGol = !!golmasaLastCheck.tmtAkhirAvailable;
      let updateMasa = false;
      let updateGol = false;

      if (scope === 'gol') {
        updateGol = canGol;
        if (!updateGol) {
          showAlert('warning', 'TMT Akhir tidak tersedia: Golongan tidak bisa disinkronisasi.');
          updateGolmasaSyncButtonState();
          return;
        }
      } else if (scope === 'masa') {
        updateMasa = canMasa;
        if (!updateMasa) {
          showAlert('warning', 'TMT Pertama tidak tersedia: Masa Kerja tidak bisa disinkronisasi.');
          updateGolmasaSyncButtonState();
          return;
        }
      } else {
        // both: sync what is available
        updateMasa = canMasa;
        updateGol = canGol;
      }

      if (!updateMasa && !updateGol) {
        showAlert('warning', 'TMT Pertama dan TMT Akhir tidak tersedia. Tidak ada data yang bisa disinkronisasi.');
        if (btnSyncGolmasaNow) btnSyncGolmasaNow.disabled = true;
        return;
      }

      let confirmText = 'Sinkronisasi akan memperbarui ';
      if (updateMasa && updateGol) {
        confirmText += 'Masa Kerja (Tahun1..Tahun12) dan Golongan (Gol1..Gol12)';
      } else if (updateMasa) {
        confirmText += 'Masa Kerja (Tahun1..Tahun12)';
      } else {
        confirmText += 'Golongan (Gol1..Gol12)';
      }
      confirmText += ' hanya pada data yang tidak sesuai. Lanjutkan?';
      try {
        const resp = await SptjmAlert.question('Konfirmasi Sinkronisasi', confirmText, { confirmButtonText: 'Lanjutkan', cancelButtonText: 'Batal' });
        if (!resp || !resp.isConfirmed) return;
      } catch (e) {
        return;
      }

      if (btnSyncGolmasaNow) btnSyncGolmasaNow.disabled = true;
      showProgress('Sedang sinkronisasi...', 'Menghitung masa kerja & golongan, lalu memperbarui data...');

      fetch("{{ route($routePrefix . '.sinkronisasi.golmasa.sync') }}", {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ NIDN: nidn, update_masa: updateMasa, update_gol: updateGol, sync_scope: scope })
      }).then(async (resp) => {
        const res = await resp.json().catch(() => ({}));
        if (!resp.ok) throw new Error(res.message || 'Gagal melakukan sinkronisasi.');
        return res;
      }).then((data) => {
        showAlert(data.status === 'success' ? 'success' : 'warning', data.message || 'Sinkronisasi selesai.');
        // Do NOT overwrite the BEFORE table; show results in AFTER table
        loadGolmasaAfter(nidn);
      }).catch((err) => {
        showAlert('danger', err.message || 'Terjadi kesalahan saat sinkronisasi.');
      }).finally(() => {
        // Keep disabled until user re-runs cek data (ensures state is consistent)
        if (typeof hideProgress === 'function') hideProgress();
      });
    };

    // Sinkronisasi Gol/Masa Kerja sekarang
    const btnSyncGolmasaNow = document.getElementById('btnSyncGolmasaNow');
    if (btnSyncGolmasaNow && golmasaSingleNidnInput) {
      btnSyncGolmasaNow.addEventListener('click', function () {
        trySyncGolmasaNow();
      });
    }

    if (golmasaSyncScopeSelect) {
      golmasaSyncScopeSelect.addEventListener('change', function () {
        updateGolmasaSyncButtonState();
      });
    }

    // Cek data gaji 1 NIDN/NUPTK untuk semua bulan (ditampilkan ke tabel detail)
    if (btnSyncGajiNidnAll && gajiSingleNidnInput) {
      btnSyncGajiNidnAll.addEventListener('click', function () {
        const nidn = (gajiSingleNidnInput.value || '').trim();
        if (!nidn) {
          showAlert('warning', 'NIDN/NUPTK untuk cek data gaji wajib diisi.');
          gajiSingleNidnInput.focus();
          return;
        }

        btnSyncGajiNidnAll.disabled = true;
        if (syncGajiStatus) syncGajiStatus.textContent = 'Memuat detail data gaji untuk NIDN/NUPTK ' + nidn + '...';
        loadGajiDetail(nidn);
        setTimeout(() => { btnSyncGajiNidnAll.disabled = false; }, 400);
      });
    }

    // pending action for confirm-all modal: 'pajak' or 'gaji'
    let pendingSyncAllAction = null;

    if (btnSyncGajiAll) {
      // disable until user runs 'Cek Gaji Tidak Sesuai'
      btnSyncGajiAll.disabled = true;
      btnSyncGajiAll.addEventListener('click', function () {
        // prepare modal body for gaji
        pendingSyncAllAction = 'gaji';
        const modalBody = confirmSyncAllModal ? confirmSyncAllModal.querySelector('.modal-body') : null;
        if (modalBody) {
          modalBody.innerHTML = 'Apakah Anda yakin ingin melakukan sinkronisasi gaji ke semua NIDN/NUPTK <strong>hanya untuk bagian yang tidak sesuai</strong> pada semua NIDN/NUPTK di tahun versi aktif?';
        }
        if (confirmSyncAllModal && window.jQuery && typeof $('#confirmSyncAllModal').modal === 'function') {
          $('#confirmSyncAllModal').modal('show');
        } else {
          if (confirm('Apakah Anda yakin ingin melakukan sinkronisasi gaji ke semua NIDN/NUPTK hanya untuk bagian yang tidak sesuai?')) {
            runSyncGajiAll();
          }
        }

        // expose helpers to global scope as safe fallbacks (so inline onclick can call them)
        window.loadGolmasaDetail = loadGolmasaDetail;
        window.loadGolmasaAfter = loadGolmasaAfter;
        window.syncGolmasaTrigger = trySyncGolmasaNow;
      });
    }

    let mismatchData = [];

    // Inisialisasi DataTable untuk daftar NIDN bermasalah
    let mismatchTable = null;
    if (window.jQuery && $('#mismatchTable').length) {
      mismatchTable = $('#mismatchTable').DataTable({
        processing: true,
        serverSide: false,
        paging: true,
        lengthChange: true,
        searching: true,
        ordering: true,
        autoWidth: false,
        responsive: true,
        scrollY: 300,
        scrollCollapse: true,
        data: [],
        columns: [
          { data: 'nidn' },
          { data: 'nuptk' },
          { data: 'nama' },
          { data: 'Kode_PT' },
          { data: 'Pemegang_Wilayah' },
          { data: 'count', className: 'text-start' },
          {
            data: null,
            orderable: false,
            searchable: false,
            className: 'text-center',
            render: function (data) {
              const nidn = (data.nidn || '').trim();
              const nuptk = (data.nuptk || '').trim();
              const identifier = nidn || nuptk;
              return identifier
                ? `<button type="button" class="btn-sptjm-action btn-sptjm-action-primary btn-detail-mismatch" data-nidn="${identifier}">Detail</button>`
                : '';
            }
          }
        ]
      });
    }

    // Helper untuk format angka: tanpa koma desimal (dan tanpa pemisah ribuan)
    const formatNumber = (val) => {
      if (val === null || val === undefined) return '-';
      const num = Number(val);
      if (!isFinite(num)) return val;

      // Jika hampir bulat, tampilkan sebagai bilangan bulat murni (tanpa koma/titik desimal)
      if (Math.abs(num - Math.round(num)) <= 0.01) {
        return String(Math.round(num));
      }

      // Jika ada pecahan, tampilkan dengan 2 digit desimal memakai titik, bukan koma
      return num.toFixed(2); // contoh: 1234.50
    };

    // Inisialisasi DataTable untuk daftar NIDN dengan gaji bermasalah
    let gajiMismatchTable = null;
    if (window.jQuery && $('#gajiMismatchTable').length) {
      gajiMismatchTable = $('#gajiMismatchTable').DataTable({
        processing: true,
        serverSide: false,
        paging: true,
        lengthChange: true,
        searching: true,
        ordering: true,
        autoWidth: false,
        responsive: true,
        scrollY: 300,
        scrollCollapse: true,
        data: [],
        columns: [
          { data: 'nidn' },
          { data: 'nuptk' },
          { data: 'nama' },
          { data: 'Kode_PT' },
          { data: 'Pemegang_Wilayah' },
          { data: 'count', className: 'text-start' },
          {
            data: null,
            orderable: false,
            searchable: false,
            className: 'text-center',
            render: function (data) {
              const nidn = (data.nidn || '').trim();
              const nuptk = (data.nuptk || '').trim();
              const identifier = nidn || nuptk;
              return identifier
                ? `<button type="button" class="btn-sptjm-action btn-sptjm-action-primary btn-detail-gaji-mismatch" data-nidn="${identifier}">Detail</button>`
                : '';
            }
          }
        ]
      });
    }

    // Cek gaji tidak sesuai untuk semua bulan
    if (btnCheckGajiMismatch) {
      btnCheckGajiMismatch.addEventListener('click', function () {
        showProgress('Sedang mengecek gaji...', 'Mohon tunggu, sistem sedang memeriksa gaji tidak sesuai untuk semua NIDN/NUPTK pada tahun versi aktif.');
        btnCheckGajiMismatch.disabled = true;
        if (gajiMismatchStatus) gajiMismatchStatus.textContent = 'Memproses pengecekan gaji...';
        if (gajiMismatchTable) {
          gajiMismatchTable.clear().draw();
        }

        if (gajiMismatchDetailWrapper) {
          gajiMismatchDetailWrapper.style.display = 'none';
          gajiMismatchDetailWrapper.innerHTML = '';
        }
        if (gajiMismatchDetailTitle) {
          gajiMismatchDetailTitle.style.display = 'none';
          gajiMismatchDetailTitle.textContent = '';
        }

        fetch("{{ route($routePrefix . '.sinkronisasi.checkGajiMismatch') }}", {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify({})
        }).then(async (resp) => {
          const res = await resp.json().catch(() => ({}));
          if (!resp.ok) {
            throw new Error(res.message || 'Gagal memproses pengecekan gaji.');
          }
          return res;
        }).then((data) => {
          const mismatches = Array.isArray(data.mismatches) ? data.mismatches : [];

          if (!mismatches.length) {
            if (gajiMismatchStatus) gajiMismatchStatus.textContent = 'Semua gaji sudah sesuai untuk semua NIDN/NUPTK pada tahun versi aktif.';
            if (gajiMismatchTable) gajiMismatchTable.clear().draw();
            showInfoModal('Informasi', 'Tidak ditemukan data dengan gaji tidak sesuai pada tahun versi aktif.');
            if (btnSyncGajiAll) btnSyncGajiAll.disabled = true;
          } else {
            const byNidn = {};
            mismatches.forEach(row => {
              const nidn = (row.nidn || '').trim();
              const nuptk = (row.nuptk || '').trim();
              const identifier = nidn || nuptk;
              if (!identifier) return;
              if (!byNidn[identifier]) {
                byNidn[identifier] = {
                  nidn,
                  nuptk,
                  nama: row.nama || '',
                  Kode_PT: row.Kode_PT || '',
                  Pemegang_Wilayah: row.Pemegang_Wilayah || '',
                  count: 0
                };
              }
              byNidn[identifier].count += 1;
            });

            const list = Object.values(byNidn);
            if (gajiMismatchStatus) gajiMismatchStatus.textContent = 'Ditemukan ' + list.length + ' data dengan gaji tidak sesuai.';
            if (gajiMismatchTable) {
              gajiMismatchTable.clear();
              gajiMismatchTable.rows.add(list).draw();
            }
            if (btnSyncGajiAll) btnSyncGajiAll.disabled = false;
          }
        }).catch((err) => {
          if (gajiMismatchStatus) gajiMismatchStatus.textContent = (err.message || 'Terjadi kesalahan saat pengecekan gaji.');
        }).finally(() => {
          btnCheckGajiMismatch.disabled = false;
          hideProgress();
        });
      });
    }

    // Detail gaji per NIDN (tampilkan ke tabel detail)
    if (gajiMismatchTableWrapper) {
      gajiMismatchTableWrapper.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-detail-gaji-mismatch');
        if (!btn) return;

        const nidn = (btn.getAttribute('data-nidn') || '').trim();
        if (!nidn) return;
        loadGajiDetail(nidn);
      });
    }

    // Sinkronisasi gaji per bulan (tiap baris di tabel detail gaji)
    if (gajiMismatchDetailWrapper) {
      gajiMismatchDetailWrapper.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-sync-gaji-month');
        if (!btn) return;

        const nidn = (btn.getAttribute('data-nidn') || '').trim();
        const bulan = parseInt(btn.getAttribute('data-bulan') || '0', 10);

        if (!nidn || !bulan) return;

        // gunakan modal konfirmasi untuk sinkron gaji per-bulan
        pendingSingleSync.nidn = nidn;
        pendingSingleSync.bulan = bulan;
        pendingSingleSync.action = 'gaji';

        const bulanLabel = 'bulan ke-' + bulan;
        if (confirmSingleSyncBody) {
          confirmSingleSyncBody.innerHTML = 'Apakah Anda yakin ingin melakukan sinkronisasi gaji untuk NIDN/NUPTK <strong>' + nidn + '</strong> pada <strong>' + bulanLabel + '</strong>?';
        }

        if (confirmSingleSyncModal && window.jQuery && typeof $('#confirmSingleSyncModal').modal === 'function') {
          $('#confirmSingleSyncModal').modal('show');
        } else {
          // fallback to direct confirm if modal unavailable
          if (!confirm('Apakah Anda yakin ingin melakukan sinkronisasi gaji untuk NIDN/NUPTK ' + nidn + ' pada ' + bulanLabel + '?')) {
            pendingSingleSync = { nidn: null, bulan: null };
            return;
          }
          // if confirmed fallback, call API directly
          showProgress('Sedang sinkronisasi gaji...', 'Mohon tunggu, sistem sedang memperbarui gaji untuk NIDN/NUPTK ' + nidn + ' pada ' + bulanLabel + '.');
          fetch("{{ route($routePrefix . '.sinkronisasi.syncGajiSingle') }}", {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ NIDN: nidn, bulan: bulan })
          }).then(async (resp) => {
            const res = await resp.json().catch(() => ({}));
            if (!resp.ok) {
              throw new Error(res.message || 'Gagal memproses sinkronisasi gaji per bulan.');
            }
            return res;
          }).then((data) => {
            if (data.status === 'success') {
              showAlert('success', data.message || 'Sinkronisasi gaji per bulan berhasil.');
            } else if (data.status === 'warning') {
              showAlert('warning', data.message || 'Sinkronisasi gaji per bulan selesai dengan peringatan.');
            } else {
              showAlert('danger', data.message || 'Terjadi kesalahan saat sinkronisasi gaji per bulan.');
            }

            // Refresh detail langsung (agar bekerja walau detail dibuka dari input, bukan dari tabel list)
            loadGajiDetail(nidn);
          }).catch((err) => {
            showAlert('danger', err.message || 'Terjadi kesalahan saat sinkronisasi gaji per bulan.');
          }).finally(() => {
            hideProgress();
            pendingSingleSync = { nidn: null, bulan: null };
          });
        }
      });
    }

    // Cek pajak tidak sesuai untuk semua bulan
    if (btnCheckMismatch) {
      btnCheckMismatch.addEventListener('click', function () {
        showProgress('Sedang mengecek pajak...', 'Mohon tunggu, sistem sedang memeriksa pajak tidak sesuai untuk semua NIDN/NUPTK pada tahun versi aktif.');
        btnCheckMismatch.disabled = true;
        btnSyncAllMonths.disabled = true;
        if (mismatchStatus) mismatchStatus.textContent = 'Memproses pengecekan pajak...';
        if (mismatchTable) {
          mismatchTable.clear().draw();
        }

        fetch("{{ route($routePrefix . '.sinkronisasi.checkMismatch') }}", {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify({})
        }).then(async (resp) => {
          const res = await resp.json().catch(() => ({}));
          if (!resp.ok) {
            throw new Error(res.message || 'Gagal memproses pengecekan pajak.');
          }
          return res;
        }).then((data) => {
          const mismatches = Array.isArray(data.mismatches) ? data.mismatches : [];
          mismatchData = mismatches;

          if (mismatchDetailWrapper) {
            mismatchDetailWrapper.style.display = 'none';
            mismatchDetailWrapper.innerHTML = '';
          }
          if (mismatchDetailTitle) {
            mismatchDetailTitle.style.display = 'none';
            mismatchDetailTitle.textContent = '';
          }

          if (!mismatches.length) {
            if (mismatchStatus) mismatchStatus.textContent = 'Semua pajak sudah sesuai untuk semua NIDN/NUPTK pada tahun versi aktif.';
            if (mismatchTable) mismatchTable.clear().draw();
            btnSyncAllMonths.disabled = true;
            // Tampilkan modal info jika tidak ada data tidak sesuai
            showInfoModal('Informasi', 'Tidak ditemukan data dengan pajak tidak sesuai pada tahun versi aktif.');
          } else {
            const byNidn = {};
            mismatches.forEach(row => {
              const nidn = (row.nidn || '').trim();
              const nuptk = (row.nuptk || '').trim();
              const identifier = nidn || nuptk;
              if (!identifier) return;
              if (!byNidn[identifier]) {
                byNidn[identifier] = {
                  nidn,
                  nuptk,
                  nama: row.nama || '',
                  Kode_PT: row.Kode_PT || '',
                  Pemegang_Wilayah: row.Pemegang_Wilayah || '',
                  count: 0
                };
              }
              byNidn[identifier].count += 1;
            });

            const list = Object.values(byNidn);
            if (mismatchStatus) mismatchStatus.textContent = 'Ditemukan ' + list.length + ' NIDN dengan pajak tidak sesuai.';
            if (mismatchTable) {
              mismatchTable.clear();
              mismatchTable.rows.add(list).draw();
            }
            btnSyncAllMonths.disabled = false;
          }
        }).catch((err) => {
          if (mismatchStatus) mismatchStatus.textContent = (err.message || 'Terjadi kesalahan saat pengecekan pajak.');
        }).finally(() => {
          btnCheckMismatch.disabled = false;
          hideProgress();
        });
      });
    }

    // Detail pajak per NIDN (tampilkan ke tabel detail)
    if (mismatchTableWrapper) {
      mismatchTableWrapper.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-detail-mismatch');
        if (!btn) return;

        const nidn = (btn.getAttribute('data-nidn') || '').trim();
        if (!nidn) return;
        loadPajakDetail(nidn);
      });
    }

    // Sinkronisasi pajak ke semua bulan
    if (btnSyncAllMonths) {
      btnSyncAllMonths.addEventListener('click', function () {
        if (confirmSyncAllModal && window.jQuery && typeof $('#confirmSyncAllModal').modal === 'function') {
          $('#confirmSyncAllModal').modal('show');
        } else {
          if (confirm('Apakah Anda yakin ingin melakukan sinkronisasi pajak ke semua bulan untuk semua NIDN/NUPTK pada tahun versi aktif?')) {
            runSyncAll();
          }
        }
      });
    }

    if (confirmSyncAllBtn && confirmSyncAllModal && window.jQuery && typeof $('#confirmSyncAllModal').modal === 'function') {
      confirmSyncAllBtn.addEventListener('click', function () {
        $('#confirmSyncAllModal').modal('hide');
        if (pendingSyncAllAction === 'gaji') {
          runSyncGajiAll();
        } else {
          runSyncAll();
        }
        pendingSyncAllAction = null;
      });
    }

    // Sinkronisasi pajak per bulan (tiap baris di tabel detail)
    if (mismatchDetailWrapper) {
      mismatchDetailWrapper.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-sync-month');
        if (!btn) return;

        const nidn = (btn.getAttribute('data-nidn') || '').trim();
        const bulan = parseInt(btn.getAttribute('data-bulan') || '0', 10);

        if (!nidn || !bulan) return;
        // simpan state dan tampilkan modal konfirmasi untuk pajak
        pendingSingleSync.nidn = nidn;
        pendingSingleSync.bulan = bulan;
        pendingSingleSync.action = 'pajak';

        const bulanLabel = 'bulan ke-' + bulan;
        if (confirmSingleSyncBody) {
          confirmSingleSyncBody.innerHTML = 'Apakah Anda yakin ingin melakukan sinkronisasi pajak untuk NIDN/NUPTK <strong>' + nidn + '</strong> pada <strong>' + bulanLabel + '</strong>?';
        }

        if (confirmSingleSyncModal && window.jQuery && typeof $('#confirmSingleSyncModal').modal === 'function') {
          $('#confirmSingleSyncModal').modal('show');
        }
      });
    }

    // tombol konfirmasi di modal sinkron per-bulan
    if (confirmSingleSyncBtn && confirmSingleSyncModal && window.jQuery && typeof $('#confirmSingleSyncModal').modal === 'function') {
      confirmSingleSyncBtn.addEventListener('click', function () {
        const nidn = pendingSingleSync.nidn;
        const bulan = pendingSingleSync.bulan;
        const action = pendingSingleSync.action || 'pajak';

        if (!nidn || !bulan) {
          $('#confirmSingleSyncModal').modal('hide');
          pendingSingleSync = { nidn: null, bulan: null };
          return;
        }

        $('#confirmSingleSyncModal').modal('hide');

        if (action === 'gaji') {
          // sinkronisasi gaji per bulan
          showProgress('Sedang sinkronisasi gaji...', 'Mohon tunggu, sistem sedang memperbarui gaji untuk NIDN/NUPTK ' + nidn + ' pada bulan ke-' + bulan + '.');
          fetch("{{ route($routePrefix . '.sinkronisasi.syncGajiSingle') }}", {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ NIDN: nidn, bulan: bulan })
          }).then(async (resp) => {
            const res = await resp.json().catch(() => ({}));
            if (!resp.ok) {
              throw new Error(res.message || 'Gagal memproses sinkronisasi gaji per bulan.');
            }
            return res;
          }).then((data) => {
            if (data.status === 'success') {
              showAlert('success', data.message || 'Sinkronisasi gaji per bulan berhasil.');
            } else if (data.status === 'warning') {
              showAlert('warning', data.message || 'Sinkronisasi gaji per bulan selesai dengan peringatan.');
            } else {
              showAlert('danger', data.message || 'Terjadi kesalahan saat sinkronisasi gaji per bulan.');
            }

            // Refresh detail langsung (agar bekerja walau detail dibuka dari input, bukan dari tabel list)
            loadGajiDetail(nidn);
          }).catch((err) => {
            showAlert('danger', err.message || 'Terjadi kesalahan saat sinkronisasi gaji per bulan.');
          }).finally(() => {
            hideProgress();
            pendingSingleSync = { nidn: null, bulan: null };
          });
        } else {
          // sinkronisasi pajak per bulan (existing behavior)
          showProgress('Sedang sinkronisasi pajak...', 'Mohon tunggu, sistem sedang memperbarui pajak untuk NIDN/NUPTK ' + nidn + ' pada bulan ke-' + bulan + '.');
          fetch("{{ route($routePrefix . '.sinkronisasi.process') }}", {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ NIDN: nidn, pencairan_ke: bulan })
          }).then(async (resp) => {
            const res = await resp.json().catch(() => ({}));
            if (!resp.ok) {
              throw new Error(res.message || 'Gagal memproses sinkronisasi bulan.');
            }
            return res;
          }).then((data) => {
            if (data.status === 'success') {
              showAlert('success', data.message || 'Sinkronisasi pajak per bulan berhasil.');
            } else if (data.status === 'warning') {
              showAlert('warning', data.message || 'Sinkronisasi pajak per bulan selesai dengan peringatan.');
            } else {
              showAlert('danger', data.message || 'Terjadi kesalahan saat sinkronisasi pajak per bulan.');
            }

            // Refresh detail langsung (agar bekerja walau detail dibuka dari input, bukan dari tabel list)
            loadPajakDetail(nidn);
          }).catch((err) => {
            showAlert('danger', err.message || 'Terjadi kesalahan saat sinkronisasi pajak per bulan.');
          }).finally(() => {
            hideProgress();
            pendingSingleSync = { nidn: null, bulan: null };
          });
        }
      });
    }
  });
</script>
@endsection
