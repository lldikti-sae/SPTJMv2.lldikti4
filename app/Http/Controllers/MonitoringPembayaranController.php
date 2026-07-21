<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\MonitoringPembayaranExport;
use App\Helpers\SelisihBayar;
use Maatwebsite\Excel\Facades\Excel;

class MonitoringPembayaranController extends Controller
{
  private function parseMoney($value): float
  {
    if ($value === null) {
      return 0.0;
    }
    if (is_int($value) || is_float($value)) {
      return (float) $value;
    }

    $text = trim((string) $value);
    if ($text === '') {
      return 0.0;
    }

    $text = preg_replace('/[^0-9\-]/', '', $text);
    if ($text === '' || $text === '-') {
      return 0.0;
    }

    return (float) $text;
  }

  /** @return array{potong:string, belakang6_3:string, belakang3:string} */
  private function splitNpwpInto3Parts($npwp): array
  {
    $digits = preg_replace('/[^0-9]/', '', (string) $npwp);
    $digits = $digits === null ? '' : $digits;

    if ($digits === '') {
      return ['potong' => '', 'belakang6_3' => '', 'belakang3' => ''];
    }

    $belakang3 = (string) substr($digits, -3);
    $belakang6_3 = (string) substr($digits, -6, 3);
    $potongLen = max(strlen($digits) - 6, 0);
    $potong = $potongLen > 0 ? (string) substr($digits, 0, $potongLen) : '';

    return [
      'potong' => $potong,
      'belakang6_3' => $belakang6_3,
      'belakang3' => $belakang3,
    ];
  }

  private function formatRupiah(float $value): string
  {
    return number_format((float) $value, 0, ',', '.');
  }

  private function formatTarifPercent(float $tarif): string
  {
    $tarif = (float) $tarif;
    $pct = $tarif * 100.0;
    $text = number_format($pct, 2, ',', '.');
    $text = rtrim(rtrim($text, '0'), ',');
    return $text . '%';
  }

  private function resolveIsPns(?object $transaksi, Request $request): bool
  {
    $jenis = null;

    if ($transaksi) {
      // s_transaksi_2 is not consistent in column casing across environments.
      $jenis = $transaksi->jenis ?? $transaksi->Jenis ?? $transaksi->JENIS ?? null;
    }

    if ($jenis === null || $jenis === '') {
      $jenis = $request->input('jenis');
    }

    if (!is_string($jenis) || trim($jenis) === '') {
      return false;
    }

    $upper = \Illuminate\Support\Str::upper(trim($jenis));

    // Treat any value that clearly indicates PNS as PNS.
    if ($upper === 'PNS') {
      return true;
    }
    if (\Illuminate\Support\Str::startsWith($upper, 'PNS')) {
      return true;
    }
    if (\Illuminate\Support\Str::contains($upper, 'PNS') && !\Illuminate\Support\Str::contains($upper, 'NON')) {
      return true;
    }

    return false;
  }

  private function findTransaksiForSpt(string $nidn, ?string $tahunVersi): ?object
  {
    if ($nidn === '' || $tahunVersi === null || trim($tahunVersi) === '') {
      return null;
    }

    return \Illuminate\Support\Facades\DB::table('s_transaksi_2')
      ->where(function ($q) use ($nidn) {
        $q->where('nidn', $nidn)
          ->orWhere('NUPTK', $nidn);
      })
      ->where('tahun_versi', $tahunVersi)
      ->first();
  }

  /** @return array{pdf:string, html:string} */
  private function resolveSptTemplatePaths(bool $isPns): array
  {
    if ($isPns) {
      return [
        'pdf' => 'dokumen/SPT PNS Formulir 1721 - VII.pdf',
        'html' => 'dokumen/SPT PNS Formulir 1721 - VII.html',
      ];
    }

    return [
      'pdf' => 'dokumen/SPT NON PNS Formulir 1721 - VI.pdf',
      'html' => 'dokumen/SPT NON PNS Formulir 1721 - VI.html',
    ];
  }

  public function index()
  {
    // Ambil daftar tahun yang tersedia dari kolom Tahun_Versi pada tabel s_transaksi_2
    $years = ['2023', '2024', '2025', '2026'];

    return view('admin.monitoring-pembayaran', compact('years'));
  }

  public function cari(Request $request)
  {
    $nidn = trim((string) $request->input('nidn'));
    // Ambil pilihan tahun dari request (start..end). Jika tidak ada, ambil dari data yang tersedia.
    $startYear = $request->input('start_year');
    $endYear = $request->input('end_year');
    $selectedYear = $request->input('tahun_versi');
    $jenisTunjangan = strtolower($request->input('jenis_tunjangan', 'sptjm'));

    $availableYears = ['2023', '2024', '2025', '2026'];

    if (!empty($startYear) && !in_array($startYear, $availableYears)) {
        $startYear = '2023';
    }
    if (!empty($endYear) && !in_array($endYear, $availableYears)) {
        $endYear = '2026';
    }
    if (!empty($selectedYear) && !in_array($selectedYear, $availableYears)) {
        $selectedYear = null;
    }

    if (empty($availableYears)) {
      return redirect()->back()->with('error', 'Data tahun tidak tersedia.');
    }

    // expose years for the view
    $years = $availableYears;

    // Default jika input kosong: ambil rentang penuh dari data (min..max)
    if (empty($startYear)) {
      $startYear = $availableYears[0];
    }
    if (empty($endYear)) {
      $endYear = end($availableYears);
    }

    // Pastikan urutan benar
    if ($startYear > $endYear) {
      $tmp = $startYear;
      $startYear = $endYear;
      $endYear = $tmp;
    }

    // Tahun untuk dropdown hasil: tampilkan semua tahun dari startYear s.d endYear
    // meskipun pada beberapa tahun tidak ada data (tabel akan kosong/strip).
    $startYearInt = (int) $startYear;
    $endYearInt = (int) $endYear;
    $yearsForNidn = [];
    for ($y = $startYearInt; $y <= $endYearInt; $y++) {
      $yearsForNidn[] = (string) $y;
    }

    // Ambil data profil (paling baru) dalam rentang terpilih untuk menampilkan header (Nama/PT/dll)
    $transaksi = DB::table('s_transaksi_2')
      ->where(function ($q) use ($nidn) {
        $q->where('nidn', $nidn)
          ->orWhere('NUPTK', $nidn);
      })
      ->whereBetween('tahun_versi', [$startYear, $endYear])
      ->orderBy('tahun_versi', 'desc')
      ->first();

    if (!$transaksi) {
      return redirect()
        ->back()
        ->with('error', 'Data dengan NIDN tersebut tidak ditemukan untuk rentang tahun ' . $startYear . ' s.d. ' . $endYear);
    }

    $isPns = $this->resolveIsPns($transaksi, $request);
    if (!$isPns && $jenisTunjangan !== 'sptjm') {
        $jenisTunjangan = 'sptjm';
    }

    // Override header properties from a_dosen (master) to ensure it shows the latest PTS
    $masterDosen = DB::table('a_dosen')->where(function ($q) use ($nidn) {
        $q->where('nidn', $nidn)->orWhere('nuptk', $nidn);
    })->first();
    if ($masterDosen) {
        $transaksi->Nama = $masterDosen->nama_dosen ?? $transaksi->Nama;
        $transaksi->Kode_PT = $masterDosen->kode_pts ?? $transaksi->Kode_PT;
        $transaksi->PTS = $masterDosen->nama_pts ?? $transaksi->PTS;
    }

    // Default tahun yang ditampilkan: tahun awal (agar saat klik Cari langsung baca tahun awal)
    if (empty($selectedYear)) {
      $selectedYear = $startYear;
    }

    if (!empty($selectedYear) && is_string($selectedYear)) {
      $selectedYear = trim($selectedYear);
    }

    // Ambil transaksi untuk tahun yang dipilih (bisa null jika tidak ada data pada tahun tsb)
    $transaksiTahun = null;
    if (!empty($selectedYear)) {
      $transaksiTahun = DB::table('s_transaksi_2')
        ->where(function ($q) use ($nidn) {
          $q->where('nidn', $nidn)
            ->orWhere('NUPTK', $nidn);
        })
        ->where('tahun_versi', $selectedYear)
        ->first();
    }

    // Fallback: jika query tahun spesifik tidak mengembalikan data tapi transaksi utama sudah ada
    // dan tahun_versi-nya sama dengan yang dipilih, gunakan transaksi utama.
    if (!$transaksiTahun && !empty($selectedYear) && (string) ($transaksi->tahun_versi ?? '') === (string) $selectedYear) {
      $transaksiTahun = $transaksi;
    }
    
    // Gunakan status Aktif dari transaksi tahun yang dipilih agar header sesuai dengan tahun tersebut
    if ($transaksiTahun) {
        $transaksi->Aktif = $transaksiTahun->Aktif ?? $transaksi->Aktif;
    }

    $selisihTotals = SelisihBayar::computeFromTransaksi($transaksiTahun);

    // Ambil data pencairan SPTJM (TPD) dari r_proses_cair
    $prosesCairTpd = [];
    if ($jenisTunjangan === 'sptjm' || $jenisTunjangan === 'semua') {
        $cairData = DB::table('r_proses_cair')
            ->where('tahun', $selectedYear)
            ->where('jenis', 'TPD')
            ->whereRaw('FIND_IN_SET(?, nidns)', [$nidn])
            ->get();
        foreach ($cairData as $c) {
            $prosesCairTpd[$c->pencairan_ke] = $c;
        }
    }

    // Ambil data TUKIN jika diperlukan
    $tukinRecords = [];
    if ($jenisTunjangan === 'tukin' || $jenisTunjangan === 'semua') {
        $tukinData = DB::table('s_tunjangan_kinerja')
            ->where(function($q) use ($nidn) {
                $q->where('NIDN', $nidn)->orWhere('NUPTK', $nidn);
            })
            ->where('Tahun', $selectedYear)
            ->get();
            
        $monthMap = ['Januari'=>1, 'Februari'=>2, 'Maret'=>3, 'April'=>4, 'Mei'=>5, 'Juni'=>6, 'Juli'=>7, 'Agustus'=>8, 'September'=>9, 'Oktober'=>10, 'November'=>11, 'Desember'=>12];
        foreach($tukinData as $t) {
            $m = $monthMap[$t->Bulan] ?? 0;
            if ($m > 0) {
                $tukinRecords[$m] = $t;
            }
        }
    }

    // Ambil data dari bulan 1–12
    $golonganBulanan = [];
    $jabatanBulanan = [];
    $gajiBulanan = [];
    $tahunBulanan = [];
    $kotorTpd = [];
    $tukinDasar = [];
    $tukinPrestasi = [];
    $tukinPotongan = [];
    $tukinBersihSerdos = [];
    $kotorTkgb = [];
    $pajakTpd = [];
    $pajakTkgb = [];
    $bersihTpd = [];
    $bersihTkgb = [];
    $noSp2d = [];
    $tglSp2d = [];

    for ($i = 1; $i <= 12; $i++) {
      $suffix = $i;
      $golonganBulanan[] = $transaksiTahun ? ($transaksiTahun->{'Gol' . $suffix} ?? '-') : '-';
      $jabatanBulanan[] = $transaksiTahun ? ($transaksiTahun->{'Jabatan' . $suffix} ?? '-') : '-';
      $tahunBulanan[] = $transaksiTahun ? ($transaksiTahun->{'Tahun' . $suffix} ?? '-') : '-';
      $gajiAsli = $transaksiTahun ? (float) ($transaksiTahun->{'Gaji' . $suffix} ?? 0) : 0;
      $kotorTpdVal = $transaksiTahun ? (float) ($transaksiTahun->{'TPD' . $suffix} ?? 0) : 0;
      $kotorTkgbVal = $transaksiTahun ? (float) ($transaksiTahun->{'TKGB' . $suffix} ?? 0) : 0;
      $pajakTpdVal = $transaksiTahun ? (float) ($transaksiTahun->{'nilaiPajakTPD' . $suffix} ?? 0) : 0;
      $pajakTkgbVal = $transaksiTahun ? (float) ($transaksiTahun->{'nilaiPajakTKGB' . $suffix} ?? 0) : 0;
      $bersihTpdVal = $transaksiTahun ? (float) ($transaksiTahun->{'bersihTPD' . $suffix} ?? 0) : 0;
      $bersihTkgbVal = $transaksiTahun ? (float) ($transaksiTahun->{'bersihTKGB' . $suffix} ?? 0) : 0;

      $tDasar = 0; $tPrestasi = 0; $tPotongan = 0; $tBersihSerdos = 0;

      if ($jenisTunjangan === 'sptjm') {
          // Do not zero out TKGB values so we can display them in the UI
      } elseif ($jenisTunjangan === 'tukin') {
          $kotorTkgbVal = 0;
          $pajakTkgbVal = 0;
          $bersihTkgbVal = 0;
          if (isset($tukinRecords[$i])) {
              $tk = $tukinRecords[$i];
              
              $jabatanBulanan[$i - 1] = $tk->Jabatan ?? '-';
              $golonganBulanan[$i - 1] = $tk->Kelas_Jabatan ?? '-';
              $tahunBulanan[$i - 1] = '-';

              $gajiAsli = (float) ($tk->Nilai_tukin_Jabatan ?? 0);
              // Tidak menimpa $kotorTpdVal, $pajakTpdVal, dan $bersihTpdVal secara global, 
              // tapi untuk kolom Bersih TPD pada TUKIN kita ambil dari Nilai_Bersih_Serdos
              
              $tDasar = (float) ($tk->KD ?? 0.60);
              $tPrestasi = (float) ($tk->KP ?? 0);
              $tPotongan = (float) ($tk->PP ?? 0);
              $tBersihSerdos = (float) ($tk->Nilai_Bersih_Serdos ?? 0);
              
              $bersihTpdVal = $tBersihSerdos;
              
          } else {
              $gajiAsli = 0;
              $tDasar = 0.60;
          }
      } elseif ($jenisTunjangan === 'semua') {
          if (isset($tukinRecords[$i])) {
              $tk = $tukinRecords[$i];
              $nominalTukin = (float) ($tk->Nilai_tukin_Jabatan ?? 0);
              $tDasar = (float) ($tk->KD ?? 0.60);
              $tPrestasi = (float) ($tk->KP ?? 0);
              $tPotongan = (float) ($tk->PP ?? 0);
              
              $nominalDasar = $nominalTukin * $tDasar;
              $nominalPrestasi = $nominalTukin * $tPrestasi;
              $nominalPotongan = $nominalTukin * $tPotongan;
              
              $kotorTkgbVal = $nominalTukin;
              $bersihTkgbVal = $nominalDasar + $nominalPrestasi - $bersihTpdVal - $nominalPotongan;
          } else {
              $kotorTkgbVal = 0;
              $bersihTkgbVal = 0;
          }
      }
      
      $tukinDasar[] = $tDasar;
      $tukinPrestasi[] = $tPrestasi;
      $tukinPotongan[] = $tPotongan;
      $tukinBersihSerdos[] = $tBersihSerdos;

      $gajiBulanan[] = $gajiAsli;

      $kotorTpd[] = $kotorTpdVal;
      $kotorTkgb[] = $kotorTkgbVal;
      $pajakTpd[] = $pajakTpdVal;
      $pajakTkgb[] = $pajakTkgbVal;
      $bersihTpd[] = $bersihTpdVal;
      $bersihTkgb[] = $bersihTkgbVal;
      
      $noSp2dVal = $transaksiTahun ? ($transaksiTahun->{'No_sp2d_' . $suffix} ?? '-') : '-';
      $tglSp2dVal = $transaksiTahun ? ($transaksiTahun->{'Tgl_sp2d_' . $suffix} ?? '-') : '-';

      if (($jenisTunjangan === 'sptjm' || $jenisTunjangan === 'semua') && isset($prosesCairTpd[$i])) {
          $noSp2dVal = !empty($prosesCairTpd[$i]->no_sp2d) ? $prosesCairTpd[$i]->no_sp2d : $noSp2dVal;
      }

      if ($jenisTunjangan === 'tukin') {
          if (isset($tukinRecords[$i])) {
              $tk = $tukinRecords[$i];
              $noSp2dVal = !empty($tk->No_SP2D) ? $tk->No_SP2D : '-';
              $tglSp2dVal = !empty($tk->Tanggal_SP2D) ? $tk->Tanggal_SP2D : '-';
          } else {
              $noSp2dVal = '-';
              $tglSp2dVal = '-';
          }
      }

      $noSp2d[] = $noSp2dVal;
      $tglSp2d[] = $tglSp2dVal;
    }

    $kodeUsulanBulanan = [];
    $kodeCairMapping = [1=>'Jan', 2=>'Feb', 3=>'Mar', 4=>'Apr', 5=>'May', 6=>'Jun', 7=>'Jul', 8=>'Ags', 9=>'Sep', 10=>'Okt', 11=>'Nov', 12=>'Des'];
    $kodeCairBulanan = [];

    for ($i = 1; $i <= 12; $i++) {
      $kode = $transaksiTahun ? ($transaksiTahun->{'KodeUsulan' . $i} ?? null) : null;
      if ($jenisTunjangan === 'tukin') {
          $kode = isset($tukinRecords[$i]) ? $tukinRecords[$i]->Kode_Usulan : null;
      }
      $kodeUsulanBulanan[] = $kode;
      $kcCol = $kodeCairMapping[$i];
      $kodeCairBulanan[] = $transaksiTahun ? ($transaksiTahun->{$kcCol} ?? null) : null;
    }

    // --- Per-month selisih & status computation ---
    $selisihBulanan = [];
    $originalSelisihBulanan = [];
    $statusBulanan = [];
    $summaryKewajiban = 0.0;
    $summaryDibayar = 0.0;

    // Cek bulan-bulan yang sudah diproses SP2D kekurangan/kelebihan (dari t_kekurangan & s_transaksi_2 JSON)
    $resolvedMonths = [];
    try {
      // 1. Ambil dari t_kekurangan (aktif)
      $resolvedRows = DB::table('t_kekurangan')
          ->where('tahun', $selectedYear)
          ->where(function ($q) use ($nidn) {
              $q->where('nidn', $nidn)
                ->orWhere('nuptk', $nidn);
          })
          ->where('jenis_pembayaran', 'like', 'PEMBAYARAN_%')
          ->get();

      foreach ($resolvedRows as $r) {
        $parts = explode('_', $r->jenis_pembayaran);
        $m = isset($parts[1]) ? (int) $parts[1] : 0;
        if ($m > 0) {
            if (!isset($resolvedMonths[$m])) {
              $resolvedMonths[$m] = [
                'nomor' => $r->kode_bayar_k,
                'tanggal' => $r->tgl_bayar_k,
                'nominal' => 0
              ];
            }
            $resolvedMonths[$m]['nominal'] += (float) $r->selisih;
        }
      }

      // 2. Ambil dari s_transaksi_2 JSON (arsip/backup) dihapus karena kolom Riwayat_Pembayaran sudah dihapus.
    } catch (\Throwable $e) { /* table might not exist yet */ }
    $totalPajakAll = array_sum($pajakTpd) + array_sum($pajakTkgb);
    $totalKotorAll = array_sum($kotorTpd) + array_sum($kotorTkgb);
    $globalTarif = $totalKotorAll > 0 ? ($totalPajakAll / $totalKotorAll) : 0;

    $poolKurangBayar = 0.0;
    $poolLebihBayar = 0.0;

    $remainingKurangGross = 0; $remainingKurangPajak = 0; $remainingKurangNet = 0;
    $remainingLebihGross = 0; $remainingLebihPajak = 0; $remainingLebihNet = 0;

    $asliKurangGross = 0; $asliKurangPajak = 0; $asliKurangNet = 0;
    $asliLebihGross = 0; $asliLebihPajak = 0; $asliLebihNet = 0;

    for ($i = 0; $i < 12; $i++) {
      $bulanNum = $i + 1;

      // Use ORIGINAL SP2D data (from s_transaksi_2) for selisih computation
      $origSp2dNo = trim((string) ($noSp2d[$i] ?? ''));
      $origSp2dTgl = trim((string) ($tglSp2d[$i] ?? ''));
      $origHasSp2d = ($origSp2dNo !== '' && $origSp2dNo !== '-' && $origSp2dTgl !== '' && $origSp2dTgl !== '-');

      // (Removed SP2D override logic per user request. Original monthly SP2D remains in main table, new SP2D stays in Uraian Pembayaran)

      // Recalculate hasSp2d after override for status display
      $sp2dNo = trim((string) ($noSp2d[$i] ?? ''));
      $sp2dTgl = trim((string) ($tglSp2d[$i] ?? ''));
      $hasSp2d = ($sp2dNo !== '' && $sp2dNo !== '-' && $sp2dTgl !== '' && $sp2dTgl !== '-');
      
      $kode = $kodeUsulanBulanan[$i] ?? null;
      $gaji = $gajiBulanan[$i] ?? 0;
      $kotor = ($kotorTpd[$i] ?? 0) + ($kotorTkgb[$i] ?? 0);
      $pajak = ($pajakTpd[$i] ?? 0) + ($pajakTkgb[$i] ?? 0);
      $bersih = ($bersihTpd[$i] ?? 0) + ($bersihTkgb[$i] ?? 0);
      $hasData = ($kotor > 0 || $gaji > 0);

      $summaryKewajiban += $kotor;
      $summaryDibayar += $bersih;

      // Selisih = expected gross (kotor) - actual paid (gaji)
      if ($jenisTunjangan === 'tukin') {
          $selisihBulan = 0;
      } else {
          $selisihBulan = ($hasData && $origHasSp2d) ? ($kotor - $gaji) : 0;
      }
      $originalSelisihBulan = $selisihBulan;
      $originalSelisihBulanan[] = (float) $originalSelisihBulan;

      // Tambahkan pembayaran bulan berjalan ke pool
      $paymentForMonth = $resolvedMonths[$bulanNum] ?? null;
      if ($paymentForMonth) {
        $pNominal = $paymentForMonth['nominal'];
        $tarif = $kotor > 0 ? ($pajak / $kotor) : $globalTarif;
        
        $pGross = abs($pNominal);
        
        // $pNominal > 0 berarti Kurang Bayar (pembayaran kekurangan)
        // $pNominal < 0 berarti Lebih Bayar (pengembalian kelebihan)
        if ($pNominal > 0) {
            $poolKurangBayar += $pGross;
        } else {
            $poolLebihBayar += $pGross;
        }
      }

      // Aplikasikan pool pembayaran/potongan (carry over)
      if ($origHasSp2d) {
        if ($selisihBulan > 0.01) { // Lebih Bayar (Surplus)
            if ($poolLebihBayar > 0.01) {
                $applied = min($selisihBulan, $poolLebihBayar);
                $selisihBulan -= $applied;
                $poolLebihBayar -= $applied;
            }
        } elseif ($selisihBulan < -0.01) { // Kurang Bayar (Defisit)
            $absSelisih = abs($selisihBulan);
            if ($poolKurangBayar > 0.01) {
                $applied = min($absSelisih, $poolKurangBayar);
                $absSelisih -= $applied;
                $poolKurangBayar -= $applied;
                $selisihBulan = -$absSelisih;
            }
        }
      }

      $selisihBulanan[] = (float) $selisihBulan;
      
      $tarifM = $kotor > 0 ? ($pajak / $kotor) : 0;
      if ($selisihBulan > 0.01) { // Lebih Bayar (Aktual > Hak)
          $gross = abs($selisihBulan);
          $pjk = $gross * $tarifM;
          $net = $gross - $pjk;
          $remainingLebihGross += $gross;
          $remainingLebihPajak += $pjk;
          $remainingLebihNet += $net;
      } elseif ($selisihBulan < -0.01) { // Kurang Bayar (Aktual < Hak)
          $gross = abs($selisihBulan);
          $pjk = $gross * $tarifM;
          $net = $gross - $pjk;
          $remainingKurangGross += $gross;
          $remainingKurangPajak += $pjk;
          $remainingKurangNet += $net;
      }

      $kc = $kodeCairBulanan[$i] ?? null;
      $hasKodeCair = ($kc !== null && trim((string)$kc) !== '' && trim((string)$kc) !== '-');

      // Status logic — use origHasSp2d for original status, hasSp2d for resolved display
      $isResolved = (abs($originalSelisihBulan) > 0.01 && abs($selisihBulan) < 0.01) || isset($resolvedMonths[$bulanNum]);
      
      if ($jenisTunjangan === 'tukin' && isset($tukinRecords[$bulanNum])) {
          $tk = $tukinRecords[$bulanNum];
          if (!empty($tk->Keterangan_Status)) {
              $statusBulanan[] = 'kode:' . $tk->Keterangan_Status;
          } else {
              $statusBulanan[] = 'usulan';
          }
      } else {
          if (!$hasData && !$kode && !$hasKodeCair) {
            $statusBulanan[] = null;
          } elseif ($isResolved && $hasData && abs($selisihBulan) < 0.01) {
            $statusBulanan[] = 'selesai';
          } elseif ($origHasSp2d) { // SP2D is present -> it's evaluating selisih immediately
            if (abs($selisihBulan) <= 0.01) {
              $statusBulanan[] = 'selesai';
            } elseif ($selisihBulan < -0.01) {
              $statusBulanan[] = 'kurang';
            } elseif ($selisihBulan > 0.01) {
              $statusBulanan[] = 'lebih';
            }
          } elseif ($hasKodeCair && !$origHasSp2d) { // Has kode cair but no SP2D -> Proses
            $statusBulanan[] = 'proses';
          } elseif (!$hasKodeCair && ($hasData || $kode)) { // No kode cair
            $kodeStr = is_string($kode) ? trim($kode) : '';
            if ($kodeStr !== '' && $kodeStr !== '-' && $gaji == 0) {
              $statusBulanan[] = 'kode:' . $kodeStr;
            } else {
              $statusBulanan[] = 'usulan';
            }
          } else {
            $statusBulanan[] = null;
          }
      }
    }
    
    // Netting Subsidi Silang untuk Baris Bulanan
    $netKurang = 0;
    $netLebih = 0;
    foreach ($selisihBulanan as $val) {
        if ($val > 0.01) $netLebih += $val;
        elseif ($val < -0.01) $netKurang += abs($val);
    }
    $kompensasi = min($netKurang, $netLebih);

    if ($kompensasi > 0.01) {
        $poolKurang = $kompensasi;
        $poolLebih = $kompensasi;
        
        foreach ($selisihBulanan as $k => $v) {
            if ($v < -0.01 && $poolKurang > 0.01) { // Kurang Bayar (Negatif)
                $potong = min(abs($v), $poolKurang);
                $selisihBulanan[$k] += $potong; // Mendekati 0
                $poolKurang -= $potong;
                if (abs($selisihBulanan[$k]) < 0.01 && $statusBulanan[$k] == 'kurang') {
                    $statusBulanan[$k] = 'selesai';
                }
            } elseif ($v > 0.01 && $poolLebih > 0.01) { // Lebih Bayar (Positif)
                $potong = min($v, $poolLebih);
                $selisihBulanan[$k] -= $potong; // Mendekati 0
                $poolLebih -= $potong;
                if (abs($selisihBulanan[$k]) < 0.01 && $statusBulanan[$k] == 'lebih') {
                    $statusBulanan[$k] = 'selesai';
                }
            }
        }
    }

    // Summary totalSelisih = sum of per-row selisih (guaranteed sync with table)
    $summary = [
      'totalKewajiban' => $summaryKewajiban,
      'totalDibayar' => $summaryDibayar,
      'totalSelisih' => array_sum($selisihBulanan),
    ];

    // determine selected jabatan based on session month and attach to transaksi for view
    $bulanSession = (int) session('bulan') ?: 12;
    if ($bulanSession < 1 || $bulanSession > 12) {
      $bulanSession = 12;
    }
    $jabatanField = 'Jabatan' . $bulanSession;
    if ($transaksi) {
      $transaksi->JabatanSelected = $transaksi->{$jabatanField} ?? $transaksi->Jabatan12 ?? null;
    }

    // Calculate global tarif based on total
    $totalPajakAll = array_sum($pajakTpd) + array_sum($pajakTkgb);
    $totalKotorAll = array_sum($kotorTpd) + array_sum($kotorTkgb);
    $globalTarif = $totalKotorAll > 0 ? ($totalPajakAll / $totalKotorAll) : 0;

    // Query uraian pembayaran dari t_kekurangan & s_transaksi_2 JSON
    $riwayatPembayaran = [];
    try {
      $riwayatRowsRaw = [];

      // 1. Ambil dari t_kekurangan (pembayaran aktif)
      $activeRows = DB::table('t_kekurangan')
          ->where('tahun', $selectedYear)
          ->where(function ($q) use ($nidn) {
              $q->where('nidn', $nidn)->orWhere('nuptk', $nidn);
          })
          ->where('jenis_pembayaran', 'like', 'PEMBAYARAN_%')
          ->get();
      foreach ($activeRows as $r) {
          $riwayatRowsRaw[] = [
              'nidn' => $r->nidn,
              'tahun' => $r->tahun,
              'nominal' => (float) $r->selisih,
              'jenis_pembayaran' => $r->jenis_pembayaran,
              'nomor' => $r->kode_bayar_k,
              'tanggal' => $r->tgl_bayar_k,
              'uraian_pembayaran' => $r->keterangan,
              'created_at' => $r->created_at ?? null,
              'updated_at' => $r->updated_at ?? null,
          ];
      }

      // 2. Ambil dari s_transaksi_2 (arsip/backup) dihapus karena kolom Riwayat_Pembayaran sudah dihapus.

      $riwayatRows = collect($riwayatRowsRaw)->map(function($r) {
          return (object) $r;
      });
        
      // Map to add 'bulan' property for backward compatibility with the view
      $riwayatPembayaran = $riwayatRows->map(function ($item) use ($globalTarif) {
          $parts = explode('_', $item->jenis_pembayaran);
          $item->bulan = isset($parts[1]) ? (int) $parts[1] : 0;
          
          // Calculate pajak and bersih based on globalTarif
          $nominalAsli = (float) $item->nominal;
          $item->pajak = abs($nominalAsli) * $globalTarif;
          $item->bersih = abs($nominalAsli) - $item->pajak;
          
          // Adjust signs if it was a negative nominal (Kelebihan)
          if ($nominalAsli < 0) {
              $item->pajak = -$item->pajak;
              $item->bersih = -$item->bersih;
          }
          
          return $item;
      })->sortBy('bulan')->values();
    } catch (\Throwable $e) {
      // Table may not exist yet — silently continue
      $riwayatPembayaran = collect();
    }

    // Netting SISA (Setelah Cicilan)
    $totalSisaGross = array_sum($selisihBulanan);
    $sisaKurangGross = 0; $sisaKurangPajak = 0; $sisaKurangNet = 0;
    $sisaLebihGross = 0; $sisaLebihPajak = 0; $sisaLebihNet = 0;

    if ($totalSisaGross > 0.01) { // Lebih Bayar (Aktual > Hak)
        $sisaLebihGross = $totalSisaGross;
        $sisaLebihPajak = $totalSisaGross * $globalTarif;
        $sisaLebihNet = $totalSisaGross - $sisaLebihPajak;
    } elseif ($totalSisaGross < -0.01) { // Kurang Bayar (Aktual < Hak)
        $sisaKurangGross = abs($totalSisaGross);
        $sisaKurangPajak = abs($totalSisaGross) * $globalTarif;
        $sisaKurangNet = abs($totalSisaGross) - $sisaKurangPajak;
    }

    $summaryRekap = [
        'k_gross' => $sisaKurangGross,
        'k_pajak' => $sisaKurangPajak,
        'k_net' => $sisaKurangNet,
        'l_gross' => $sisaLebihGross,
        'l_pajak' => $sisaLebihPajak,
        'l_net' => $sisaLebihNet,
    ];

    // Kalkulasi Total yang SUDAH DIBAYAR (Cicilan/Pengembalian) dari riwayat
    $paidKurangGross = 0;
    $paidLebihGross = 0;
    foreach ($riwayatPembayaran as $rp) {
        if ($rp->nominal > 0) {
            $paidKurangGross += abs($rp->nominal);
        } else {
            $paidLebihGross += abs($rp->nominal);
        }
    }
    
    $paidKurangPajak = $paidKurangGross * $globalTarif;
    $paidKurangNet = $paidKurangGross - $paidKurangPajak;

    $paidLebihPajak = $paidLebihGross * $globalTarif;
    $paidLebihNet = $paidLebihGross - $paidLebihPajak;

    $summaryOriginal = [
        'k_gross' => $paidKurangGross,
        'k_pajak' => $paidKurangPajak,
        'k_net' => $paidKurangNet,
        'l_gross' => $paidLebihGross,
        'l_pajak' => $paidLebihPajak,
        'l_net' => $paidLebihNet,
    ];

    return view(
      'admin.monitoring-pembayaran',
      compact(
        'years',
        'yearsForNidn',
        'startYear',
        'endYear',
        'selectedYear',
        'jenisTunjangan',
        'transaksi',
        'nidn',
        'kodeUsulanBulanan',
        'noSp2d',
        'tglSp2d',
        'kotorTpd',
        'kotorTkgb',
        'pajakTpd',
        'pajakTkgb',
        'bersihTpd',
        'bersihTkgb',
        'isPns',
        'golonganBulanan',
        'jabatanBulanan',
        'gajiBulanan',
        'tahunBulanan',
        'tukinDasar',
        'tukinPrestasi',
        'tukinPotongan',
        'tukinBersihSerdos',
        'selisihTotals',
        'selisihBulanan',
        'statusBulanan',
        'summary',
        'summaryRekap',
        'summaryOriginal',
        'riwayatPembayaran',
      )
    );
  }

  // Return JSON for AJAX table refresh
  public function data(Request $request)
  {
    $nidn = $request->input('nidn');
    $startYear = $request->input('start_year');
    $endYear = $request->input('end_year');
    $selectedYear = $request->input('tahun_versi');
    $jenisTunjangan = strtolower($request->input('jenis_tunjangan', 'sptjm'));

    $availableYears = ['2023', '2024', '2025', '2026'];
    
    if (!empty($startYear) && !in_array($startYear, $availableYears)) {
        $startYear = '2023';
    }
    if (!empty($endYear) && !in_array($endYear, $availableYears)) {
        $endYear = '2026';
    }
    if (!empty($selectedYear) && !in_array($selectedYear, $availableYears)) {
        $selectedYear = null;
    }

    if (empty($availableYears)) {
      return response()->json(['success' => false, 'message' => 'Data tahun tidak tersedia.']);
    }

    if (empty($startYear)) {
      $startYear = $availableYears[0];
    }
    if (empty($endYear)) {
      $endYear = end($availableYears);
    }

    if ($startYear > $endYear) {
      $tmp = $startYear;
      $startYear = $endYear;
      $endYear = $tmp;
    }

    // get profile transaksi (latest in range)
    $transaksi = DB::table('s_transaksi_2')
      ->where(function ($q) use ($nidn) {
        $q->where('nidn', $nidn)
          ->orWhere('NUPTK', $nidn);
      })
      ->whereBetween('tahun_versi', [$startYear, $endYear])
      ->orderBy('tahun_versi', 'desc')
      ->first();

    if (!$transaksi) {
      return response()->json(['success' => false, 'message' => 'Data profil tidak ditemukan untuk rentang tahun.']);
    }

    $isPns = $this->resolveIsPns($transaksi, $request);
    if (!$isPns && $jenisTunjangan !== 'sptjm') {
        $jenisTunjangan = 'sptjm';
    }

    // Override header properties from a_dosen (master) to ensure it shows the latest PTS
    $masterDosen = DB::table('a_dosen')->where(function ($q) use ($nidn) {
        $q->where('nidn', $nidn)->orWhere('nuptk', $nidn);
    })->first();
    if ($masterDosen) {
        $transaksi->Nama = $masterDosen->nama_dosen ?? $transaksi->Nama;
        $transaksi->Kode_PT = $masterDosen->kode_pts ?? $transaksi->Kode_PT;
        $transaksi->PTS = $masterDosen->nama_pts ?? $transaksi->PTS;
    }

    // default selectedYear to the latest transaction year if not provided
    if (empty($selectedYear)) {
      $selectedYear = $transaksi->tahun_versi ?? $transaksi->Tahun_Versi ?? $transaksi->Tahun_versi ?? $startYear;
    }
    $versi = $selectedYear;

    $transaksiTahun = DB::table('s_transaksi_2')
      ->where(function ($q) use ($nidn) {
        $q->where('nidn', $nidn)
          ->orWhere('NUPTK', $nidn);
      })
      ->where('tahun_versi', $selectedYear)
      ->first();

    // fallback
    if (!$transaksiTahun && (string) ($transaksi->tahun_versi ?? '') === (string) $selectedYear) {
      $transaksiTahun = $transaksi;
    }

    $selisihTotals = SelisihBayar::computeFromTransaksi($transaksiTahun);

    // Ambil data pencairan SPTJM (TPD) dari r_proses_cair
    $prosesCairTpd = [];
    if ($jenisTunjangan === 'sptjm' || $jenisTunjangan === 'semua') {
        $cairData = DB::table('r_proses_cair')
            ->where('tahun', $selectedYear)
            ->where('jenis', 'TPD')
            ->whereRaw('FIND_IN_SET(?, nidns)', [$nidn])
            ->get();
        foreach ($cairData as $c) {
            $prosesCairTpd[$c->pencairan_ke] = $c;
        }
    }

    // Ambil data TUKIN jika diperlukan
    $tukinRecords = [];
    if ($jenisTunjangan === 'tukin' || $jenisTunjangan === 'semua') {
        $tukinData = DB::table('s_tunjangan_kinerja')
            ->where(function($q) use ($nidn) {
                $q->where('NIDN', $nidn)->orWhere('NUPTK', $nidn);
            })
            ->where('Tahun', $selectedYear)
            ->get();
            
        $monthMap = ['Januari'=>1, 'Februari'=>2, 'Maret'=>3, 'April'=>4, 'Mei'=>5, 'Juni'=>6, 'Juli'=>7, 'Agustus'=>8, 'September'=>9, 'Oktober'=>10, 'November'=>11, 'Desember'=>12];
        foreach($tukinData as $t) {
            $m = $monthMap[$t->Bulan] ?? 0;
            if ($m > 0) {
                $tukinRecords[$m] = $t;
            }
        }
    }

    // prepare monthly arrays
    $months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $kodeCairMapping = [1=>'Jan', 2=>'Feb', 3=>'Mar', 4=>'Apr', 5=>'May', 6=>'Jun', 7=>'Jul', 8=>'Ags', 9=>'Sep', 10=>'Okt', 11=>'Nov', 12=>'Des'];
    $kodeUsulanBulanan = [];
    $kodeCairBulanan = [];
    $golonganBulanan = [];
    $jabatanBulanan = [];
    $gajiBulanan = [];
    $tahunBulanan = [];
    $kotorTpd = [];
    $tukinDasar = [];
    $tukinPrestasi = [];
    $tukinPotongan = [];
    $tukinBersihSerdos = [];
    $kotorTkgb = [];
    $pajakTpd = [];
    $pajakTkgb = [];
    $bersihTpd = [];
    $bersihTkgb = [];
    $noSp2d = [];
    $tglSp2d = [];

    for ($i = 1; $i <= 12; $i++) {
      $s = $i;
      $kode = $transaksiTahun ? ($transaksiTahun->{'KodeUsulan' . $s} ?? null) : null;
      if ($jenisTunjangan === 'tukin') {
          $kode = isset($tukinRecords[$i]) ? $tukinRecords[$i]->Kode_Usulan : null;
      }
      $kodeUsulanBulanan[] = $kode;
      $kcCol = $kodeCairMapping[$i];
      $kodeCairBulanan[] = $transaksiTahun ? ($transaksiTahun->{$kcCol} ?? null) : null;
      $golonganBulanan[] = $transaksiTahun ? ($transaksiTahun->{'Gol' . $s} ?? '-') : '-';
      $jabatanBulanan[] = $transaksiTahun ? ($transaksiTahun->{'Jabatan' . $s} ?? '-') : '-';
      $tahunBulanan[] = $transaksiTahun ? ($transaksiTahun->{'Tahun' . $s} ?? '-') : '-';
      $gajiAsli = $transaksiTahun ? (float) ($transaksiTahun->{'Gaji' . $s} ?? 0) : 0;
      $kotorTpdVal = $transaksiTahun ? (float) ($transaksiTahun->{'TPD' . $s} ?? 0) : 0;
      $kotorTkgbVal = $transaksiTahun ? (float) ($transaksiTahun->{'TKGB' . $s} ?? 0) : 0;
      $pajakTpdVal = $transaksiTahun ? (float) ($transaksiTahun->{'nilaiPajakTPD' . $s} ?? 0) : 0;
      $pajakTkgbVal = $transaksiTahun ? (float) ($transaksiTahun->{'nilaiPajakTKGB' . $s} ?? 0) : 0;
      $bersihTpdVal = $transaksiTahun ? (float) ($transaksiTahun->{'bersihTPD' . $s} ?? 0) : 0;
      $bersihTkgbVal = $transaksiTahun ? (float) ($transaksiTahun->{'bersihTKGB' . $s} ?? 0) : 0;

      $tDasar = 0; $tPrestasi = 0; $tPotongan = 0; $tBersihSerdos = 0;

      if ($jenisTunjangan === 'sptjm') {
          // Do not zero out TKGB values so we can display them in the UI
      } elseif ($jenisTunjangan === 'tukin') {
          $kotorTkgbVal = 0;
          $pajakTkgbVal = 0;
          $bersihTkgbVal = 0;
          if (isset($tukinRecords[$i])) {
              $tk = $tukinRecords[$i];
              $gajiAsli = (float) ($tk->Nilai_tukin_Jabatan ?? 0);
              // Tidak menimpa $kotorTpdVal, $pajakTpdVal, dan $bersihTpdVal agar nilai SPTJM tetap ada untuk kolom Bersih TPD
              
              $tDasar = (float) ($tk->KD ?? 0.60);
              $tPrestasi = (float) ($tk->KP ?? 0);
              $tPotongan = (float) ($tk->PP ?? 0);
              $tBersihSerdos = (float) ($tk->Nilai_Bersih_Serdos ?? 0);
              
          } else {
              $gajiAsli = 0;
              $tDasar = 0.60;
          }
      } elseif ($jenisTunjangan === 'semua') {
          if (isset($tukinRecords[$i])) {
              $tk = $tukinRecords[$i];
              $nominalTukin = (float) ($tk->Nilai_tukin_Jabatan ?? 0);
              $tDasar = (float) ($tk->KD ?? 0.60);
              $tPrestasi = (float) ($tk->KP ?? 0);
              $tPotongan = (float) ($tk->PP ?? 0);
              
              $nominalDasar = $nominalTukin * $tDasar;
              $nominalPrestasi = $nominalTukin * $tPrestasi;
              $nominalPotongan = $nominalTukin * $tPotongan;
              
              $kotorTkgbVal = $nominalTukin;
              $bersihTkgbVal = $nominalDasar + $nominalPrestasi - $bersihTpdVal - $nominalPotongan;
          } else {
              $kotorTkgbVal = 0;
              $bersihTkgbVal = 0;
          }
      }

      $tukinDasar[] = $tDasar;
      $tukinPrestasi[] = $tPrestasi;
      $tukinPotongan[] = $tPotongan;
      $tukinBersihSerdos[] = $tBersihSerdos;

      $gajiBulanan[] = $gajiAsli;

      $kotorTpd[] = $kotorTpdVal;
      $kotorTkgb[] = $kotorTkgbVal;
      $pajakTpd[] = $pajakTpdVal;
      $pajakTkgb[] = $pajakTkgbVal;
      $bersihTpd[] = $bersihTpdVal;
      $bersihTkgb[] = $bersihTkgbVal;
      
      $noSp2dVal = $transaksiTahun ? ($transaksiTahun->{'No_sp2d_' . $s} ?? '-') : '-';
      $tglSp2dVal = $transaksiTahun ? ($transaksiTahun->{'Tgl_sp2d_' . $s} ?? '-') : '-';

      if (($jenisTunjangan === 'sptjm' || $jenisTunjangan === 'semua') && isset($prosesCairTpd[$i])) {
          $noSp2dVal = !empty($prosesCairTpd[$i]->no_sp2d) ? $prosesCairTpd[$i]->no_sp2d : $noSp2dVal;
      }

      if ($jenisTunjangan === 'tukin') {
          if (isset($tukinRecords[$i])) {
              $tk = $tukinRecords[$i];
              $noSp2dVal = !empty($tk->No_SP2D) ? $tk->No_SP2D : '-';
              $tglSp2dVal = !empty($tk->Tanggal_SP2D) ? $tk->Tanggal_SP2D : '-';
          } else {
              $noSp2dVal = '-';
              $tglSp2dVal = '-';
          }
      }

      $noSp2d[] = $noSp2dVal;
      $tglSp2d[] = $tglSp2dVal;
    }

    $totals = [
      'gaji' => array_sum($gajiBulanan),
      'kotorTpd' => array_sum($kotorTpd),
      'kotorTkgb' => array_sum($kotorTkgb),
      'pajakTpd' => array_sum($pajakTpd),
      'pajakTkgb' => array_sum($pajakTkgb),
      'bersihTpd' => array_sum($bersihTpd),
      'bersihTkgb' => array_sum($bersihTkgb),
    ];

    $header = [
      'NIDN' => !empty($transaksi->NIDN) ? $transaksi->NIDN : ($transaksi->NUPTK ?? ''),
      'Nama' => $transaksi->Nama ?? '',
      'JabatanSelected' => ($transaksiTahun && isset($transaksiTahun->{'Jabatan' . ((int)session('bulan') ?: 12)}) ? $transaksiTahun->{'Jabatan' . ((int)session('bulan') ?: 12)} : ($transaksi->Jabatan12 ?? '')),
      'Aktif' => $transaksiTahun ? ($transaksiTahun->Aktif ?? 0) : ($transaksi->Aktif ?? 0),
      'Kode_PT' => $transaksi->Kode_PT ?? '',
      'PTS' => $transaksi->PTS ?? '',
      'Jenis' => $transaksi->Jenis ?? '',
    ];

    // --- Per-month selisih & status computation ---
    $selisihBulanan = [];
    $originalSelisihBulanan = [];
    $statusBulanan = [];
    $summaryKewajiban = 0.0;
    $summaryDibayar = 0.0;
    $remainingKurangGross = 0; $remainingKurangPajak = 0; $remainingKurangNet = 0;
    $remainingLebihGross = 0; $remainingLebihPajak = 0; $remainingLebihNet = 0;
    
    $asliKurangGross = 0; $asliKurangPajak = 0; $asliKurangNet = 0;
    $asliLebihGross = 0; $asliLebihPajak = 0; $asliLebihNet = 0;

    // Cek bulan-bulan yang sudah diproses SP2D kekurangan/kelebihan (dari t_kekurangan & s_transaksi_2 JSON)
    $resolvedMonths = [];
    try {
      // 1. Ambil dari t_kekurangan (aktif)
      $resolvedRows = DB::table('t_kekurangan')
          ->where('tahun', $versi)
          ->where(function ($q) use ($nidn) {
              $q->where('nidn', $nidn)
                ->orWhere('nuptk', $nidn);
          })
          ->where('jenis_pembayaran', 'like', 'PEMBAYARAN_%')
          ->get();

      foreach ($resolvedRows as $r) {
        $parts = explode('_', $r->jenis_pembayaran);
        $m = isset($parts[1]) ? (int) $parts[1] : 0;
        if ($m > 0) {
            if (!isset($resolvedMonths[$m])) {
              $resolvedMonths[$m] = [
                'nomor' => $r->kode_bayar_k,
                'tanggal' => $r->tgl_bayar_k,
                'nominal' => 0
              ];
            }
            $resolvedMonths[$m]['nominal'] += (float) $r->selisih;
        }
      }

      // 2. Ambil dari s_transaksi_2 JSON (arsip/backup) dihapus karena kolom Riwayat_Pembayaran sudah dihapus.
    } catch (\Throwable $e) { /* table might not exist yet */ }

    $totalPajakAll = array_sum($pajakTpd) + array_sum($pajakTkgb);
    $totalKotorAll = array_sum($kotorTpd) + array_sum($kotorTkgb);
    $globalTarif = $totalKotorAll > 0 ? ($totalPajakAll / $totalKotorAll) : 0;

    $poolKurangBayar = 0.0;
    $poolLebihBayar = 0.0;

    for ($i = 0; $i < 12; $i++) {
      $bulanNum = $i + 1;

      // Use ORIGINAL SP2D data (from s_transaksi_2) for selisih computation
      $origSp2dNo = trim((string) ($noSp2d[$i] ?? ''));
      $origSp2dTgl = trim((string) ($tglSp2d[$i] ?? ''));
      $origHasSp2d = ($origSp2dNo !== '' && $origSp2dNo !== '-' && $origSp2dTgl !== '' && $origSp2dTgl !== '-');

      // Override SP2D No & Tgl untuk DISPLAY only (setelah computing original selisih)
      if (isset($resolvedMonths[$bulanNum])) {
        if (!empty($resolvedMonths[$bulanNum]['nomor'])) {
          $noSp2d[$i] = $resolvedMonths[$bulanNum]['nomor'];
        }
        if (!empty($resolvedMonths[$bulanNum]['tanggal'])) {
          $tglSp2d[$i] = $resolvedMonths[$bulanNum]['tanggal'];
        }
      }

      // Recalculate hasSp2d after override for status display
      $sp2dNo = trim((string) ($noSp2d[$i] ?? ''));
      $sp2dTgl = trim((string) ($tglSp2d[$i] ?? ''));
      $hasSp2d = ($sp2dNo !== '' && $sp2dNo !== '-' && $sp2dTgl !== '' && $sp2dTgl !== '-');
      
      $kode = $kodeUsulanBulanan[$i] ?? null;
      $gaji = $gajiBulanan[$i] ?? 0;
      $kotor = ($kotorTpd[$i] ?? 0) + ($kotorTkgb[$i] ?? 0);
      $pajak = ($pajakTpd[$i] ?? 0) + ($pajakTkgb[$i] ?? 0);
      $bersih = ($bersihTpd[$i] ?? 0) + ($bersihTkgb[$i] ?? 0);
      $hasData = ($kotor > 0 || $gaji > 0);

      $summaryKewajiban += $kotor;
      $summaryDibayar += $bersih;

      // Selisih = expected gross (kotor) - actual paid (gaji)
      if ($jenisTunjangan === 'tukin') {
          $selisihBulan = 0;
      } else {
          $selisihBulan = ($hasData && $origHasSp2d) ? ($kotor - $gaji) : 0;
      }
      $originalSelisihBulan = $selisihBulan;
      $originalSelisihBulanan[] = (float) $originalSelisihBulan;

      // Tambahkan pembayaran bulan berjalan ke pool
      $paymentForMonth = $resolvedMonths[$bulanNum] ?? null;
      if ($paymentForMonth) {
        $pNominal = $paymentForMonth['nominal'];
        $tarif = $kotor > 0 ? ($pajak / $kotor) : $globalTarif;
        
        $pGross = abs($pNominal);
        
        // $pNominal > 0 berarti Kurang Bayar (pembayaran kekurangan)
        // $pNominal < 0 berarti Lebih Bayar (pengembalian kelebihan)
        if ($pNominal > 0) {
            $poolKurangBayar += $pGross;
        } else {
            $poolLebihBayar += $pGross;
        }
      }

      // Aplikasikan pool pembayaran/potongan (carry over)
      if ($origHasSp2d) {
        if ($selisihBulan > 0.01) { // Lebih Bayar (Surplus)
            if ($poolLebihBayar > 0.01) {
                $applied = min($selisihBulan, $poolLebihBayar);
                $selisihBulan -= $applied;
                $poolLebihBayar -= $applied;
            }
        } elseif ($selisihBulan < -0.01) { // Kurang Bayar (Defisit)
            $absSelisih = abs($selisihBulan);
            if ($poolKurangBayar > 0.01) {
                $applied = min($absSelisih, $poolKurangBayar);
                $absSelisih -= $applied;
                $poolKurangBayar -= $applied;
                $selisihBulan = -$absSelisih;
            }
        }
      }

      $selisihBulanan[] = (float) $selisihBulan;
      
      $tarifM = $kotor > 0 ? ($pajak / $kotor) : 0;
      if ($selisihBulan > 0.01) { // Lebih Bayar (Aktual > Hak)
          $gross = abs($selisihBulan);
          $pjk = $gross * $tarifM;
          $net = $gross - $pjk;
          $remainingLebihGross += $gross;
          $remainingLebihPajak += $pjk;
          $remainingLebihNet += $net;
      } elseif ($selisihBulan < -0.01) { // Kurang Bayar (Aktual < Hak)
          $gross = abs($selisihBulan);
          $pjk = $gross * $tarifM;
          $net = $gross - $pjk;
          $remainingKurangGross += $gross;
          $remainingKurangPajak += $pjk;
          $remainingKurangNet += $net;
      }

      $kc = $kodeCairBulanan[$i] ?? null;
      $hasKodeCair = ($kc !== null && trim((string)$kc) !== '' && trim((string)$kc) !== '-');

      // Status logic — use origHasSp2d for original status, hasSp2d for resolved display
      $isResolved = (abs($originalSelisihBulan) > 0.01 && abs($selisihBulan) < 0.01) || isset($resolvedMonths[$bulanNum]);
      
      if (!$hasData && !$kode && !$hasKodeCair) {
        $statusBulanan[] = null;
      } elseif ($isResolved && $hasData && abs($selisihBulan) < 0.01) {
        $statusBulanan[] = 'selesai';
      } elseif ($origHasSp2d) { // SP2D is present -> it's evaluating selisih immediately
        if (abs($selisihBulan) <= 0.01) {
          $statusBulanan[] = 'selesai';
        } elseif ($selisihBulan < -0.01) {
          $statusBulanan[] = 'kurang';
        } elseif ($selisihBulan > 0.01) {
          $statusBulanan[] = 'lebih';
        }
      } elseif ($hasKodeCair && !$origHasSp2d) { // Has kode cair but no SP2D -> Proses
        $statusBulanan[] = 'proses';
      } elseif (!$hasKodeCair && ($hasData || $kode)) { // No kode cair
        // Jika ada kode usulan tapi gaji null/0, status = nama kode usulan (misal: Tugas Belajar, Mutasi, dll)
        // Jika ada kode usulan DAN gaji > 0, status tetap 'usulan'
        $kodeStr = is_string($kode) ? trim($kode) : '';
        if ($kodeStr !== '' && $kodeStr !== '-' && $gaji == 0) {
          $statusBulanan[] = 'kode:' . $kodeStr;
        } else {
          $statusBulanan[] = 'usulan';
        }
      } else {
        $statusBulanan[] = null;
      }
    }

    // Netting Subsidi Silang untuk Baris Bulanan (AJAX)
    $netKurang = 0;
    $netLebih = 0;
    foreach ($selisihBulanan as $val) {
        if ($val > 0.01) $netLebih += $val;
        elseif ($val < -0.01) $netKurang += abs($val);
    }
    $kompensasi = min($netKurang, $netLebih);

    if ($kompensasi > 0.01) {
        $poolKurang = $kompensasi;
        $poolLebih = $kompensasi;
        
        foreach ($selisihBulanan as $k => $v) {
            if ($v < -0.01 && $poolKurang > 0.01) { // Kurang Bayar (Negatif)
                $potong = min(abs($v), $poolKurang);
                $selisihBulanan[$k] += $potong; // Mendekati 0
                $poolKurang -= $potong;
                if (abs($selisihBulanan[$k]) < 0.01 && $statusBulanan[$k] == 'kurang') {
                    $statusBulanan[$k] = 'selesai';
                }
            } elseif ($v > 0.01 && $poolLebih > 0.01) { // Lebih Bayar (Positif)
                $potong = min($v, $poolLebih);
                $selisihBulanan[$k] -= $potong; // Mendekati 0
                $poolLebih -= $potong;
                if (abs($selisihBulanan[$k]) < 0.01 && $statusBulanan[$k] == 'lebih') {
                    $statusBulanan[$k] = 'selesai';
                }
            }
        }
    }

    $summaryData = [
      'totalKewajiban' => $summaryKewajiban,
      'totalDibayar' => $summaryDibayar,
      'totalSelisih' => array_sum($selisihBulanan),
    ];
    
    // Netting SISA (Setelah Cicilan)
    $totalSisaGross = array_sum($selisihBulanan);
    $sisaKurangGross = 0; $sisaKurangPajak = 0; $sisaKurangNet = 0;
    $sisaLebihGross = 0; $sisaLebihPajak = 0; $sisaLebihNet = 0;
    
    // Calculate global tarif based on total
    $totalPajakAll = array_sum($pajakTpd) + array_sum($pajakTkgb);
    $totalKotorAll = array_sum($kotorTpd) + array_sum($kotorTkgb);
    $globalTarif = $totalKotorAll > 0 ? ($totalPajakAll / $totalKotorAll) : 0;

    if ($totalSisaGross > 0.01) { // Lebih Bayar (Aktual > Hak)
        $sisaLebihGross = $totalSisaGross;
        $sisaLebihPajak = $totalSisaGross * $globalTarif;
        $sisaLebihNet = $totalSisaGross - $sisaLebihPajak;
    } elseif ($totalSisaGross < -0.01) { // Kurang Bayar (Aktual < Hak)
        $sisaKurangGross = abs($totalSisaGross);
        $sisaKurangPajak = abs($totalSisaGross) * $globalTarif;
        $sisaKurangNet = abs($totalSisaGross) - $sisaKurangPajak;
    }

    $summaryRekap = [
        'k_gross' => $sisaKurangGross,
        'k_pajak' => $sisaKurangPajak,
        'k_net' => $sisaKurangNet,
        'l_gross' => $sisaLebihGross,
        'l_pajak' => $sisaLebihPajak,
        'l_net' => $sisaLebihNet,
    ];

    // Query uraian pembayaran dari t_kekurangan & s_transaksi_2 JSON
    $riwayatPembayaran = [];
    try {
      $riwayatRowsRaw = [];

      // 1. Ambil dari t_kekurangan (pembayaran aktif)
      $activeRows = DB::table('t_kekurangan')
          ->where('tahun', $versi)
          ->where(function ($q) use ($nidn) {
              $q->where('nidn', $nidn)->orWhere('nuptk', $nidn);
          })
          ->where('jenis_pembayaran', 'like', 'PEMBAYARAN_%')
          ->get();
      foreach ($activeRows as $r) {
          $riwayatRowsRaw[] = [
              'nidn' => $r->nidn,
              'tahun' => $r->tahun,
              'nominal' => (float) $r->selisih,
              'selisih' => (float) $r->selisih,
              'jenis_pembayaran' => $r->jenis_pembayaran,
              'kode_bayar_k' => $r->kode_bayar_k,
              'tgl_bayar_k' => $r->tgl_bayar_k,
              'keterangan' => $r->keterangan,
              'created_at' => $r->created_at ?? null,
              'updated_at' => $r->updated_at ?? null,
          ];
      }

      $riwayatRows = collect($riwayatRowsRaw)->map(function($r) {
          return (object) $r;
      });
        
      $riwayatPembayaran = $riwayatRows->map(function ($item) use ($globalTarif) {
          $parts = explode('_', $item->jenis_pembayaran);
          $item->bulan = isset($parts[1]) ? (int) $parts[1] : 0;
          
          $nominalAsli = (float) $item->selisih;
          $item->nominal = $nominalAsli;
          $item->pajak = abs($nominalAsli) * $globalTarif;
          $item->bersih = abs($nominalAsli) - $item->pajak;
          
          if ($nominalAsli < 0) {
              $item->pajak = -$item->pajak;
              $item->bersih = -$item->bersih;
          }
          
          // Uraian pembayaran should strictly match keterangan in database as requested by the user
          $item->uraian_pembayaran = $item->keterangan;
          $item->nomor = $item->kode_bayar_k;
          $item->tanggal = $item->tgl_bayar_k;
          
          return $item;
      })->sortBy('bulan')->values();
    } catch (\Throwable $e) {
      $riwayatPembayaran = collect();
    }

    // Kalkulasi Total yang SUDAH DIBAYAR (Cicilan/Pengembalian) dari riwayat
    $paidKurangGross = 0;
    $paidLebihGross = 0;
    foreach ($riwayatPembayaran as $rp) {
        if ($rp->nominal > 0) {
            $paidKurangGross += abs($rp->nominal);
        } else {
            $paidLebihGross += abs($rp->nominal);
        }
    }
    
    $paidKurangPajak = $paidKurangGross * $globalTarif;
    $paidKurangNet = $paidKurangGross - $paidKurangPajak;

    $paidLebihPajak = $paidLebihGross * $globalTarif;
    $paidLebihNet = $paidLebihGross - $paidLebihPajak;

    $summaryOriginal = [
        'k_gross' => $paidKurangGross,
        'k_pajak' => $paidKurangPajak,
        'k_net' => $paidKurangNet,
        'l_gross' => $paidLebihGross,
        'l_pajak' => $paidLebihPajak,
        'l_net' => $paidLebihNet,
    ];

    return response()->json([
      'success' => true,
      'header' => $header,
      'selectedYear' => $selectedYear,
      'months' => $months,
      'kodeUsulanBulanan' => $kodeUsulanBulanan,
      'golonganBulanan' => $golonganBulanan,
      'jabatanBulanan' => $jabatanBulanan,
      'tahunBulanan' => $tahunBulanan,
      'tukinDasar' => $tukinDasar,
      'tukinPrestasi' => $tukinPrestasi,
      'tukinPotongan' => $tukinPotongan,
      'tukinBersihSerdos' => $tukinBersihSerdos,
      'gajiBulanan' => $gajiBulanan,
      'kotorTpd' => $kotorTpd,
      'kotorTkgb' => $kotorTkgb,
      'pajakTpd' => $pajakTpd,
      'pajakTkgb' => $pajakTkgb,
      'bersihTpd' => $bersihTpd,
      'bersihTkgb' => $bersihTkgb,
      'noSp2d' => $noSp2d,
      'tglSp2d' => $tglSp2d,
      'totals' => $totals,
      'selisihTotals' => $selisihTotals,
      'selisihBulanan' => $selisihBulanan,
      'statusBulanan' => $statusBulanan,
      'summary' => $summaryData,
      'summaryRekap' => $summaryRekap,
      'summaryOriginal' => $summaryOriginal,
      'riwayatPembayaran' => $riwayatPembayaran,
      'isPns' => $isPns,
    ]);
  }

  public function exportExcel(Request $request)
  {
    $request->validate([
      'nidn' => ['required', 'string'],
      'tahun_versi' => ['required'],
    ]);

    $nidn = trim((string) $request->input('nidn'));
    $selectedYear = trim((string) $request->input('tahun_versi'));
    $jenisTunjangan = trim((string) $request->input('jenis_tunjangan', 'sptjm'));

    $transaksi = DB::table('s_transaksi_2')
      ->where(function ($q) use ($nidn) {
        $q->where('nidn', $nidn)
          ->orWhere('NUPTK', $nidn);
      })
      ->where('tahun_versi', $selectedYear)
      ->first();

    $monthNames = [
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

    $tukinRecords = [];
    if ($jenisTunjangan === 'tukin') {
        $tukinData = DB::table('s_tunjangan_kinerja')
            ->where(function ($q) use ($nidn) {
                $q->where('NIDN', $nidn)->orWhere('NUPTK', $nidn);
            })
            ->where('Tahun', $selectedYear)
            ->get();
            
        $monthMap = ['Januari'=>1, 'Februari'=>2, 'Maret'=>3, 'April'=>4, 'Mei'=>5, 'Juni'=>6, 'Juli'=>7, 'Agustus'=>8, 'September'=>9, 'Oktober'=>10, 'November'=>11, 'Desember'=>12];
        foreach($tukinData as $t) {
            $m = $monthMap[$t->Bulan] ?? 0;
            if ($m > 0) {
                $tukinRecords[$m] = $t;
            }
        }
    }

    $rows = [];
    $totals = [
      'gaji' => 0,
      'kotorTpd' => 0,
      'kotorTkgb' => 0,
      'pajakTpd' => 0,
      'pajakTkgb' => 0,
      'bersihTpd' => 0,
      'bersihTkgb' => 0,
    ];

    for ($m = 1; $m <= 12; $m++) {
      $kodeUsulan = $transaksi?->{'KodeUsulan' . $m} ?? '-';
      $gol = $transaksi?->{'Gol' . $m} ?? '-';
      $tahun = $transaksi?->{'Tahun' . $m} ?? '-';

      $gaji = (float) ($transaksi?->{'Gaji' . $m} ?? 0);
      $kotorTpd = (float) ($transaksi?->{'TPD' . $m} ?? 0);
      $kotorTkgb = (float) ($transaksi?->{'TKGB' . $m} ?? 0);
      $pajakTpd = (float) ($transaksi?->{'nilaiPajakTPD' . $m} ?? 0);
      $pajakTkgb = (float) ($transaksi?->{'nilaiPajakTKGB' . $m} ?? 0);
      $bersihTpd = (float) ($transaksi?->{'bersihTPD' . $m} ?? 0);
      $bersihTkgb = (float) ($transaksi?->{'bersihTKGB' . $m} ?? 0);
      if ($jenisTunjangan === 'tukin') {
          $kotorTkgb = 0;
          $pajakTkgb = 0;
          $bersihTkgb = 0;
          
          if (isset($tukinRecords[$m])) {
              $tk = $tukinRecords[$m];
              $kodeUsulan = $tk->Kode_Usulan ?? '-';
              $gaji = (float) ($tk->Nilai_tukin_Jabatan ?? 0);
              $kotorTpd = (float) ($tk->Nilai_Tukin ?? 0);
              $pajakTpd = 0;
              $bersihTpd = $kotorTpd;
          } else {
              $kodeUsulan = '-';
              $gaji = 0;
              $kotorTpd = 0;
              $pajakTpd = 0;
              $bersihTpd = 0;
          }
      }

      $noSp2d = $transaksi?->{'No_sp2d_' . $m} ?? '-';
      $tglSp2d = $transaksi?->{'Tgl_sp2d_' . $m} ?? '-';

      $totals['gaji'] += $gaji;
      $totals['kotorTpd'] += $kotorTpd;
      $totals['kotorTkgb'] += $kotorTkgb;
      $totals['pajakTpd'] += $pajakTpd;
      $totals['pajakTkgb'] += $pajakTkgb;
      $totals['bersihTpd'] += $bersihTpd;
      $totals['bersihTkgb'] += $bersihTkgb;

      $rows[] = [
        $selectedYear,
        $monthNames[$m],
        $kodeUsulan,
        $gol . ' - ' . $tahun,
        (int) round($gaji),
        (int) round($kotorTpd),
        (int) round($kotorTkgb),
        (int) round($pajakTpd),
        (int) round($pajakTkgb),
        (int) round($bersihTpd),
        (int) round($bersihTkgb),
        $noSp2d,
        $tglSp2d,
      ];
    }

    $selisihTotals = SelisihBayar::computeFromTransaksi($transaksi);

    $rows[] = [
      $selectedYear,
      'Jumlah',
      '-',
      '-',
      (int) round($totals['gaji']),
      (int) round($totals['kotorTpd']),
      (int) round($totals['kotorTkgb']),
      (int) round($totals['pajakTpd']),
      (int) round($totals['pajakTkgb']),
      (int) round($totals['bersihTpd']),
      (int) round($totals['bersihTkgb']),
      '-',
      '-',
    ];

    $rows[] = [
      $selectedYear,
      'Jumlah Selisih Bayar',
      '-',
      '-',
      '-',
      (int) round((float) ($selisihTotals['selisihTpd'] ?? 0)),
      (int) round((float) ($selisihTotals['selisihTkgb'] ?? 0)),
      (int) round((float) ($selisihTotals['selisihPajakTpd'] ?? 0)),
      (int) round((float) ($selisihTotals['selisihPajakTkgb'] ?? 0)),
      (int) round((float) ($selisihTotals['selisihBersihTpd'] ?? 0)),
      (int) round((float) ($selisihTotals['selisihBersihTkgb'] ?? 0)),
      '-',
      '-',
    ];

    $fileName = 'monitoring-pembayaran_' . $nidn . '_' . $selectedYear . '.xlsx';
    return Excel::download(new MonitoringPembayaranExport($rows), $fileName);
  }

  public function cetakSpt(Request $request)
  {
    $request->validate([
      'nidn' => ['required', 'string'],
      'tahun_versi' => ['nullable'],
    ]);

    $pdfOverlay = app(\App\Services\SptPdfOverlayService::class);
    $pemotongStore = app(\App\Services\IdentitasPemotongJsonStore::class);
        
    $nidn = trim((string) $request->input('nidn'));
    $selectedYear = trim((string) $request->input('tahun_versi'));
        
    $tahunVersi = $request->input('cetak_spt_tahun_versi') ?? $request->input('tahun_versi') ?? $request->input('tahunVersi');
    $tahunVersiNorm = $tahunVersi !== null ? trim((string) $tahunVersi) : null;
    $transaksi = $this->findTransaksiForSpt($nidn, $tahunVersiNorm);

    // If the row isn't found, keep prior behavior by falling back to request input.
    $isPns = $this->resolveIsPns($transaksi, $request);

    $templates = $this->resolveSptTemplatePaths($isPns);
    $templatePdf = $templates['pdf'];
    $templateHtml = $templates['html'];
        
    // If FPDI isn't installed yet, we still return the original template PDF.
    // The overlay will start working after `composer update` installs setasign/fpdi-fpdf.
        
    $css = \App\Services\Pdf2HtmlExCssMap::fromHtmlFile(public_path($templateHtml));
        
    $tx = $transaksi ? (array) $transaksi : [];
    $penerimaNpwp = $tx['NPWP'] ?? $tx['npwp'] ?? null;
    $penerimaNik = $tx['NIK'] ?? $tx['nik'] ?? null;
    $penerimaNama = $tx['Nama'] ?? $tx['nama'] ?? null;
    $penerimaAlamat = $tx['Alamat'] ?? $tx['alamat'] ?? ($tx['ALAMAT'] ?? null);

    $penerimaNpwpParts = $this->splitNpwpInto3Parts($penerimaNpwp);

    $bulanSession = (int) session('bulan') ?: 12;
    if ($bulanSession < 1 || $bulanSession > 12) {
      $bulanSession = 12;
    }

    $tahunSession = session('tahun');
    if ($tahunSession === null || trim((string) $tahunSession) === '') {
      $tahunSession = $tahunVersiNorm ?: $selectedYear;
    }

    $sptBulan = str_pad((string) $bulanSession, 2, '0', STR_PAD_LEFT);
    $sptTahun = $tahunSession !== null ? trim((string) $tahunSession) : '';
    $sptNidnOrNuptk = $nidn;

    $jenisTarif = null;
    if ($transaksi) {
      $jenisTarif = $transaksi->Jenis ?? $transaksi->jenis ?? $transaksi->JENIS ?? null;
    }
    if ($jenisTarif === null || trim((string) $jenisTarif) === '') {
      $jenisTarif = $request->input('jenis');
    }
    $jenisTarif = is_string($jenisTarif) ? trim($jenisTarif) : trim((string) $jenisTarif);

    $golKey = 'Gol' . $bulanSession;
    $golTarif = isset($tx[$golKey]) ? trim((string) $tx[$golKey]) : '';

    $tarifPajak = 0.0;
    if ($jenisTarif !== '' && $golTarif !== '') {
      try {
        $tarifPajak = (float) (DB::table('d_pajak')
          ->where('status', $jenisTarif)
          ->where('akumulasi', $golTarif)
          ->value('tarif_pajak') ?? 0);
      } catch (\Throwable $e) {
        $tarifPajak = 0.0;
      }
    }

    $jumlahPenghasilanBruto = 0.0;
    for ($m = 1; $m <= 12; $m++) {
      $jumlahPenghasilanBruto += $this->parseMoney($tx['TPD' . $m] ?? 0);
      $jumlahPenghasilanBruto += $this->parseMoney($tx['TKGB' . $m] ?? 0);
    }
    $pphDipotong = $jumlahPenghasilanBruto * $tarifPajak;
        
    $pemotong = null;
    $allPemotong = $pemotongStore->all();
    if (!empty($allPemotong)) {
      $vals = array_values($allPemotong);
      $pemotong = $vals[count($vals) - 1];
    }
        
    $pemotongNpwp = is_array($pemotong) ? ($pemotong['npwp'] ?? null) : null;
    $pemotongNama = is_array($pemotong) ? ($pemotong['nama'] ?? null) : null;
    $pemotongTanggal = is_array($pemotong) ? ($pemotong['tanggal'] ?? null) : null;
    $pemotongTtdPath = is_array($pemotong) ? ($pemotong['tanda_tangan_path'] ?? null) : null;
    $pemotongCapPath = is_array($pemotong) ? ($pemotong['cap_path'] ?? null) : null;

    $pemotongNpwpParts = $this->splitNpwpInto3Parts($pemotongNpwp);
        
    $tanggalCetak = $pemotongTanggal ?: now()->format('d-m-Y');
        
    // Layout uses class names from the pdf2htmlEX templates.
    // Coordinates are resolved via Pdf2HtmlExCssMap (no hardcoded pixel numbers).
    $layout = $isPns
      ? [
        'template_pdf_path' => $templatePdf,
        'fields' => [
          'spt_bulan' => ['x' => $css->xPt('x4') - 5, 'y' => $css->yPt('y14') + 4, 'font' => 10],
          'spt_tahun' => ['x' => $css->xPt('x17') - 2, 'y' => $css->yPt('y14')+ 4, 'font' => 10],
          'spt_nidn_nuptk' => ['x' => $css->xPt('x1e'), 'y' => $css->yPt('y14') + 4, 'font' => 10],

          // Dummy coordinates (offset from old single-field NPWP coordinate)
          'penerima_npwp_potong' => ['x' => $css->xPt('x12'), 'y' => $css->yPt('y16') - 8, 'font' => 10],
          'penerima_npwp_3_selanjutnya' => ['x' => $css->xPt('x1a'), 'y' => $css->yPt('y16') - 8, 'font' => 10],
          'penerima_npwp_3_belakang' => ['x' => $css->xPt('x23'), 'y' => $css->yPt('y16') - 8, 'font' => 10],
          'penerima_nik' => ['x' => $css->xPt('x20'), 'y' => $css->yPt('y16') - 8, 'font' => 10],
          'penerima_nama' => ['x' => $css->xPt('xc') + 12, 'y' => $css->yPt('y18') - 4, 'font' => 10],
          'penerima_alamat' => ['x' => $css->xPt('xc') +12, 'y' => $css->yPt('yd'), 'font' => 10],

          'pemotong_npwp_potong' => ['x' => $css->xPt('x9'), 'y' => $css->yPt('yf') - 18, 'font' => 10],
          'pemotong_npwp_3_selanjutnya' => ['x' => $css->xPt('xb'), 'y' => $css->yPt('yf') - 18, 'font' => 10],
          'pemotong_npwp_3_belakang' => ['x' => $css->xPt('x3'), 'y' => $css->yPt('yf') - 18, 'font' => 10],
          'pemotong_nama' => ['x' => $css->xPt('xc'), 'y' => $css->yPt('yf') - 43, 'font' => 10],
          'pemotong_tanggal_dd' => ['x' => $css->xPt('x18'), 'y' => $css->yPt('yf') - 43, 'font' => 10],
          'pemotong_tanggal_mm' => ['x' => $css->xPt('x1e') + 4, 'y' => $css->yPt('yf') - 43, 'font' => 10],
          'pemotong_tanggal_yyyy' => ['x' => $css->xPt('x25') - 20, 'y' => $css->yPt('yf') - 43, 'font' => 10],

          // Kode Objek Pajak (dummy coordinates)
          'kode_objek_pajak1' => ['x' => $css->xPt('x1b') + 5, 'y' => $css->yPt('yb') - 10, 'font' => 10],
          'kode_objek_pajak2' => ['x' => $css->xPt('x11'), 'y' => $css->yPt('yb') - 10, 'font' => 10],
          'kode_objek_pajak3' => ['x' => $css->xPt('x15') + 25, 'y' => $css->yPt('yb') - 10, 'font' => 10],

          // BAGIAN B. PPh PASAL 21 YANG DIPOTONG
          'bagianb_jumlah_penghasilan_bruto' => ['x' => $css->xPt('x7'), 'y' => $css->yPt('yc'), 'font' => 10],
          'bagianb_tarif' => ['x' => $css->xPt('x24') - 10, 'y' => $css->yPt('yc'), 'font' => 10],
          'bagianb_pph_dipotong' => ['x' => $css->xPt('x22'), 'y' => $css->yPt('yc'), 'font' => 10],
        ],
        'signature' => ['x' => $css->xPt('x22') - 5, 'y' => $css->yPt('yf') - 53, 'w' => 75, 'h' => 40.5],
        // Dummy coordinates for cap/stamp (can be adjusted later)
        'cap' => ['x' => $css->xPt('x22') - 50, 'y' => $css->yPt('yf') - 80, 'w' => 120, 'h' => 120],
      ]
      : [
        'template_pdf_path' => $templatePdf,
        'fields' => [
          'spt_bulan' => ['x' => $css->xPt('x28') - 5, 'y' => $css->yPt('y24') + 13, 'font' => 10],
          'spt_tahun' => ['x' => $css->xPt('x5') - 11, 'y' => $css->yPt('y24') + 13, 'font' => 10],
          'spt_nidn_nuptk' => ['x' => $css->xPt('x27'), 'y' => $css->yPt('y24') + 13, 'font' => 10],

          'penerima_npwp_potong' => ['x' => $css->xPt('xd') + 10, 'y' => $css->yPt('y2f') - 7, 'font' => 10],
          'penerima_npwp_3_selanjutnya' => ['x' => $css->xPt('x23'), 'y' => $css->yPt('y2f') - 7, 'font' => 10],
          'penerima_npwp_3_belakang' => ['x' => $css->xPt('x2d'), 'y' => $css->yPt('y2f') - 7, 'font' => 10],
          'penerima_nik' => ['x' => $css->xPt('x9'), 'y' => $css->yPt('y2f') - 7, 'font' => 10],
          'penerima_nama' => ['x' => $css->xPt('xd') + 10, 'y' => $css->yPt('y32') + 10, 'font' => 10],
          'penerima_alamat' => ['x' => $css->xPt('x2b'), 'y' => $css->yPt('yd'), 'font' => 10],

          'pemotong_npwp_potong' => ['x' => $css->xPt('xd'), 'y' => $css->yPt('y18') + 3, 'font' => 10],
          'pemotong_npwp_3_selanjutnya' => ['x' => $css->xPt('x4'), 'y' => $css->yPt('y18') + 3, 'font' => 10],
          'pemotong_npwp_3_belakang' => ['x' => $css->xPt('x3'), 'y' => $css->yPt('y18') + 3, 'font' => 10],
          'pemotong_nama' => ['x' => $css->xPt('xd'), 'y' => $css->yPt('y1a'), 'font' => 10],
          'pemotong_tanggal_dd' => ['x' => $css->xPt('x25'), 'y' => $css->yPt('y1a'), 'font' => 10],
          'pemotong_tanggal_mm' => ['x' => $css->xPt('x27') + 4, 'y' => $css->yPt('y1a'), 'font' => 10],
          'pemotong_tanggal_yyyy' => ['x' => $css->xPt('x30') - 20, 'y' => $css->yPt('y1a'), 'font' => 10],

          'kode_objek_pajak1' => ['x' => $css->xPt('xf'), 'y' => $css->yPt('y13') - 2, 'font' => 10],
          'kode_objek_pajak2' => ['x' => $css->xPt('x1d'), 'y' => $css->yPt('y13') - 2, 'font' => 10],
          'kode_objek_pajak3' => ['x' => $css->xPt('xd') + 40, 'y' => $css->yPt('y13') - 2, 'font' => 10],

          'bagianb_jumlah_penghasilan_bruto' => ['x' => $css->xPt('xe') -15, 'y' => $css->yPt('y13') - 2, 'font' => 10],
          'bagianb_tarif' => ['x' => $css->xPt('x20') - 10, 'y' => $css->yPt('y13') - 2, 'font' => 10],
          'bagianb_pph_dipotong' => ['x' => $css->xPt('x14'), 'y' => $css->yPt('y13') - 2, 'font' => 10],
          'bagianb_dasar_pengenaan_pajak' => ['x' => $css->xPt('x24') - 15, 'y' => $css->yPt('y13') - 2, 'font' => 10],
        ],
        'signature' => ['x' => $css->xPt('x22') - 5, 'y' => $css->yPt('y1b'), 'w' => 75, 'h' => 40.5],
        'cap' => ['x' => $css->xPt('x20') - 15, 'y' => $css->yPt('y1c') - 20, 'w' => 120, 'h' => 120],
      ];
        
    $dd = null; $mm = null; $yyyy = null;
    try {
      $dt = \DateTime::createFromFormat('d-m-Y', $tanggalCetak);
      if ($dt) {
        $dd = $dt->format('d');
        $mm = $dt->format('m');
        $yyyy = $dt->format('Y');
      } else {
        // fallback: try generic parse
        $dt2 = new \DateTime($tanggalCetak);
        $dd = $dt2->format('d');
        $mm = $dt2->format('m');
        $yyyy = $dt2->format('Y');
      }
    } catch (\Throwable $ex) {
      $dd = substr($tanggalCetak, 0, 2);
      $mm = substr($tanggalCetak, 3, 2);
      $yyyy = substr($tanggalCetak, -4);
    }

    $values = [
      'spt_bulan' => $sptBulan,
      'spt_tahun' => $sptTahun,
      'spt_nidn_nuptk' => $sptNidnOrNuptk,

      'penerima_npwp_potong' => $penerimaNpwpParts['potong'] ?? '',
      'penerima_npwp_3_selanjutnya' => $penerimaNpwpParts['belakang6_3'] ?? '',
      'penerima_npwp_3_belakang' => $penerimaNpwpParts['belakang3'] ?? '',
      'penerima_nik' => $penerimaNik,
      'penerima_nama' => $penerimaNama,
      'penerima_alamat' => $penerimaAlamat,

      'pemotong_npwp_potong' => $pemotongNpwpParts['potong'] ?? '',
      'pemotong_npwp_3_selanjutnya' => $pemotongNpwpParts['belakang6_3'] ?? '',
      'pemotong_npwp_3_belakang' => $pemotongNpwpParts['belakang3'] ?? '',
      'pemotong_nama' => $pemotongNama,
      'pemotong_tanggal_dd' => $dd,
      'pemotong_tanggal_mm' => $mm,
      'pemotong_tanggal_yyyy' => $yyyy,

      'kode_objek_pajak1' => '21',
      'kode_objek_pajak2' => $isPns ? '402' : '100',
      'kode_objek_pajak3' => $isPns ? '01' : '08',

      'bagianb_jumlah_penghasilan_bruto' => $this->formatRupiah($jumlahPenghasilanBruto),
      'bagianb_tarif' => $this->formatTarifPercent($tarifPajak),
      'bagianb_pph_dipotong' => $this->formatRupiah($pphDipotong),
    ];

    if (!$isPns) {
      $values['bagianb_dasar_pengenaan_pajak'] = $this->formatRupiah($jumlahPenghasilanBruto);
    }
        
    $downloadName = $isPns
      ? 'SPT-1721-VII-' . ($nidn ?: 'NIDN') . '-' . ($tahunVersi ?: 'TAHUN') . '.pdf'
      : 'SPT-1721-VI-' . ($nidn ?: 'NIDN') . '-' . ($tahunVersi ?: 'TAHUN') . '.pdf';
        
    return $pdfOverlay->renderFromTemplate($downloadName, $layout, $values, $pemotongTtdPath, $pemotongCapPath);
  }

  public function cekKoordinatSpt(Request $request)
  {
    $request->validate([
      'nidn' => ['required', 'string'],
      'tahun_versi' => ['nullable'],
    ]);

    $nidn = trim((string) $request->input('nidn'));
    $tahunVersi = $request->input('tahun_versi') ?? $request->input('cetak_spt_tahun_versi') ?? $request->input('tahunVersi');

    $tahunVersiNorm = $tahunVersi !== null ? trim((string) $tahunVersi) : null;
    $transaksi = $this->findTransaksiForSpt($nidn, $tahunVersiNorm);
    $isPns = $this->resolveIsPns($transaksi, $request);

    $templates = $this->resolveSptTemplatePaths($isPns);
    $templateHtml = $templates['html'];

    $templatePath = public_path($templateHtml);
    if (!is_file($templatePath)) {
      return response('Template HTML tidak ditemukan', 404);
    }

    $html = file_get_contents($templatePath);
    if ($html === false) {
      return response('Gagal membaca template HTML', 500);
    }

    $baseHref = url('/dokumen/') . '/';
    if (stripos($html, '<base') === false && stripos($html, '</head>') !== false) {
         $html = str_ireplace('</head>', '<base href="' . $baseHref . '">' . "\n</head>", $html);
    }

    $css = \App\Services\Pdf2HtmlExCssMap::fromHtmlFile($templatePath);
    $ptPerPx = $css->ptPerPx();
    $xMap = $css->allX();
    $yMap = $css->allY();

    $inject = "\n" .
      '<style>' .
      '#coord-panel{position:fixed;top:12px;right:12px;z-index:999999;background:rgba(0,0,0,.85);color:#fff;padding:10px 12px;border-radius:8px;font:12px/1.4 -apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Arial,sans-serif;max-width:320px}' .
      '#coord-panel .row{margin:2px 0;word-break:break-all}' .
      '#coord-panel button{margin-top:6px;padding:4px 8px;border-radius:6px;border:1px solid rgba(255,255,255,.25);background:transparent;color:#fff;cursor:pointer}' .
      '#coord-panel small{opacity:.8}' .
      '</style>' .
      '<div id="coord-panel">' .
      '<div class="row"><strong>Cek Koordinat Overlay</strong></div>' .
      '<div class="row"><small>Klik di halaman untuk ambil koordinat.</small></div>' .
      '<div class="row">page: <span id="cp-page">-</span></div>' .
      '<div class="row">px (left,bottom): <span id="cp-px">-</span></div>' .
      '<div class="row">pt (left,bottom): <span id="cp-pt">-</span></div>' .
      '<div class="row">snippet: <span id="cp-php">-</span></div>' .
      '<button type="button" id="cp-copy">Copy snippet</button>' .
      '</div>' .
      '<script>' .
      '(function(){' .
      'var ptPerPx=' . json_encode($ptPerPx) . ';' .
      'var xMap=' . json_encode($xMap) . ';' .
      'var yMap=' . json_encode($yMap) . ';' .
      'var lastPtText="";' .
      'function getScale(el){' .
      '  var t=getComputedStyle(el).transform;' .
      '  if(!t||t==="none") return 1;' .
      '  var m=t.match(/matrix\\(([^)]+)\\)/);' .
      '  if(!m) return 1;' .
      '  var parts=m[1].split(",").map(function(s){return parseFloat(s.trim())});' .
      '  if(parts.length>=4 && !isNaN(parts[0])) return parts[0];' .
      '  return 1;' .
      '}' .
      'function nearestKey(map, value){' .
      '  var bestKey=null; var bestDiff=Infinity;' .
      '  for (var k in map){' .
      '    if(!Object.prototype.hasOwnProperty.call(map,k)) continue;' .
      '    var v=parseFloat(map[k]);' .
      '    if(isNaN(v)) continue;' .
      '    var d=Math.abs(v - value);' .
      '    if(d < bestDiff){ bestDiff=d; bestKey=k; }' .
      '  }' .
      '  return bestKey;' .
      '}' .
      'function keyFromTarget(target, map, prefix){' .
      '  var node=target;' .
      '  while(node && node!==document){' .
      '    if(node.classList){' .
      '      for(var i=0;i<node.classList.length;i++){' .
      '        var c=node.classList[i];' .
      '        if(c && c.charAt(0)===prefix && map[c]!==undefined){ return c; }' .
      '      }' .
      '    }' .
      '    if(node.classList && node.classList.contains("pc")) break;' .
      '    node=node.parentNode;' .
      '  }' .
      '  return null;' .
      '}' .
      'function findPf(node){while(node && node!==document){if(node.classList && node.classList.contains("pf")) return node; node=node.parentNode;} return null;}' .
      'function findPc(node){while(node && node!==document){if(node.classList && node.classList.contains("pc")) return node; node=node.parentNode;} return null;}' .
      'document.addEventListener("click", function(e){' .
      '  var pf=findPf(e.target); if(!pf) return;' .
      '  var pc=findPc(e.target) || pf.querySelector(".pc"); if(!pc) return;' .
      '  var scale=getScale(pc);' .
      '  var rect=pc.getBoundingClientRect();' .
      '  var x=(e.clientX-rect.left)/scale;' .
      '  var yTop=(e.clientY-rect.top)/scale;' .
      '  var pcHeight=rect.height/scale;' .
      '  var bottom=pcHeight - yTop;' .
      '  var xKey = keyFromTarget(e.target, xMap, "x");' .
      '  var yKey = keyFromTarget(e.target, yMap, "y");' .
      '  if(!xKey){ xKey = nearestKey(xMap, x) || "x0"; }' .
      '  if(!yKey){ yKey = nearestKey(yMap, bottom) || "y0"; }' .
      '  var leftCssPx=parseFloat(xMap[xKey]); if(isNaN(leftCssPx)) leftCssPx=x;' .
      '  var bottomCssPx=parseFloat(yMap[yKey]); if(isNaN(bottomCssPx)) bottomCssPx=bottom;' .
      '  var xPt=leftCssPx*ptPerPx; var bottomPt=bottomCssPx*ptPerPx;' .
      '  var snippet = "[\'x\' => $css->xPt(\'" + xKey + "\'), \'y\' => $css->yPt(\'" + yKey + "\')]";' .
      '  var pageId=pf.id || "-";' .
      '  document.getElementById("cp-page").textContent=pageId;' .
      '  document.getElementById("cp-px").textContent=leftCssPx.toFixed(3)+", "+bottomCssPx.toFixed(3) + " (" + xKey + "," + yKey + ")";' .
      '  document.getElementById("cp-pt").textContent=xPt.toFixed(3)+", "+bottomPt.toFixed(3);' .
      '  document.getElementById("cp-php").textContent=snippet;' .
      '  lastPtText = snippet;' .
      '}, true);' .
      'document.getElementById("cp-copy").addEventListener("click", function(){' .
      '  if(!lastPtText) return;' .
      '  if(navigator.clipboard && navigator.clipboard.writeText){navigator.clipboard.writeText(lastPtText);}' .
      '});' .
      '})();' .
      '</script>';

    if (stripos($html, '</body>') !== false) {
      $html = str_ireplace('</body>', $inject . "\n</body>", $html);
    } else {
      $html .= $inject;
    }

    return response($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
  }

  public function cekKoordinatSptPdf(Request $request)
  {
    $request->validate([
      'nidn' => ['required', 'string'],
      'tahun_versi' => ['nullable'],
    ]);

    $nidn = trim((string) $request->input('nidn'));
    $tahunVersi = $request->input('tahun_versi') ?? $request->input('cetak_spt_tahun_versi') ?? $request->input('tahunVersi');

    $tahunVersiNorm = $tahunVersi !== null ? trim((string) $tahunVersi) : null;
    $transaksi = $this->findTransaksiForSpt($nidn, $tahunVersiNorm);
    $isPns = $this->resolveIsPns($transaksi, $request);

    $templates = $this->resolveSptTemplatePaths($isPns);
    $templatePdf = $templates['pdf'];
    $templateHtml = $templates['html'];

    $css = \App\Services\Pdf2HtmlExCssMap::fromHtmlFile(public_path($templateHtml));
    $ptPerPx = $css->ptPerPx();
    $xMap = $css->allX();
    $yMap = $css->allY();

    $pdfPath = public_path($templatePdf);
    if (!is_file($pdfPath)) {
      return response('Template PDF tidak ditemukan', 404);
    }

    $pdfBytes = file_get_contents($pdfPath);
    if ($pdfBytes === false) {
      return response('Gagal membaca template PDF', 500);
    }

    return view('admin.monitoring-pembayaran.cek-koordinat-spt-pdf', [
      // Embed to avoid HTTP fetch quirks (range/head/204) and ensure 1:1.
      'pdfBase64' => base64_encode($pdfBytes),
      'templateName' => basename($templatePdf),
      'isPns' => $isPns,
      'ptPerPx' => $ptPerPx,
      'xMap' => $xMap,
      'yMap' => $yMap,
    ]);
  }

}
