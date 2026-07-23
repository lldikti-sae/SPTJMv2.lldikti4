<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanKeuanganPtsExport;
use Yajra\DataTables\Facades\DataTables;

class LaporanKeuanganPtsController extends Controller
{
  public function index(Request $request)
  {
    $dosenList = [];

    // Ambil input pencarian (bisa NIDN atau NUPTK)
    $nidnSearch = trim((string) $request->input('nidn', ''));

    // Ambil kode_pts dari user yang login sebagai PTS
    $kode_pts = Auth::guard('pts')->user()->kode_pts;
    $tahunVersi = session('tahun') ?: date('Y');

    // Kolom bulan dan TPD, TKGB (alias ke lowercase untuk view)
    $bulanFields = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'ags', 'sep', 'okt', 'nov', 'des'];
    $bulanDbMap = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
    $tpdFields = array_map(fn($i) => "tpd$i", range(1, 12));
    $tkgbFields = array_map(fn($i) => "tkgb$i", range(1, 12));

    // Select kolom langsung dari s_transaksi_2 (seperti admin), dibatasi ke kode_pt PTS login & tahun versi aktif
    // pilih jabatan sesuai bulan session
    $bulanIndex = (int) session('bulan');
    if ($bulanIndex < 1 || $bulanIndex > 12) $bulanIndex = 12;
    $jabatanCol = 'Jabatan' . $bulanIndex;

    $selects = [
        't.NIDN as nidn',
        't.NUPTK as nuptk',
        't.Nama as nama',
        't.Jenis as jenis',
        DB::raw("t.`{$jabatanCol}` as jabatan"),
        't.Aktif as aktif',
        't.Eligible_span as eligible_span',
        't.Bank as bank',
        't.Kode_PT as kode_pt',
        't.PTS as pts',
      ];
      // Map bulan uppercase dari s_transaksi_2 ke alias lowercase untuk view
      foreach ($bulanFields as $idx => $alias) {
        $col = $bulanDbMap[$idx];
        $selects[] = DB::raw("t.`$col` as `$alias`");
      }
      // Map KodeUsulan per bulan (kodeusulan1..12) untuk keperluan cek pembayaran
      for ($i = 1; $i <= 12; $i++) {
        $selects[] = DB::raw("t.`KodeUsulan{$i}` as `kodeusulan{$i}`");
      }
      // Map TPD/TKGB per bulan
      for ($i = 1; $i <= 12; $i++) {
        $selects[] = DB::raw("t.`TPD{$i}` as `tpd{$i}`");
        $selects[] = DB::raw("t.`TKGB{$i}` as `tkgb{$i}`");
        $selects[] = DB::raw("t.`bersihTPD{$i}` as `bersihtpd{$i}`");
        $selects[] = DB::raw("t.`bersihTKGB{$i}` as `bersihtkgb{$i}`");

        // Fields needed to compute selisih bayar like /admin/kekurangan-bayar/cek
        $selects[] = DB::raw("t.`Jabatan{$i}` as `jabatan{$i}`");
        $selects[] = DB::raw("t.`Gaji{$i}` as `gaji{$i}`");
        $selects[] = DB::raw("t.`No_sp2d_{$i}` as `no_sp2d_{$i}`");
        $selects[] = DB::raw("t.`Tgl_sp2d_{$i}` as `tgl_sp2d_{$i}`");
      }
      // Hitung base gaji bulanan dari salah satu kolom Gaji1..Gaji12 (ambil yang pertama tidak null)
      $coalesceGaji = '(COALESCE(t.Gaji1,0)+COALESCE(t.Gaji2,0)+COALESCE(t.Gaji3,0)+COALESCE(t.Gaji4,0)+COALESCE(t.Gaji5,0)+COALESCE(t.Gaji6,0)+COALESCE(t.Gaji7,0)+COALESCE(t.Gaji8,0)+COALESCE(t.Gaji9,0)+COALESCE(t.Gaji10,0)+COALESCE(t.Gaji11,0)+COALESCE(t.Gaji12,0)) as total_gaji';

      $query = DB::table('s_transaksi_2 as t')
        ->select(array_merge($selects, [DB::raw($coalesceGaji)]))
        ->where('t.Kode_PT', $kode_pts)
        ->where('t.Tahun_Versi', $tahunVersi)
        ->orderBy('t.Nama');

      // If search provided, apply partial match on NIDN or NUPTK
      if (!empty($nidnSearch)) {
        $query->where(function ($q) use ($nidnSearch) {
          $q->where('t.NIDN', 'like', "%{$nidnSearch}%")
            ->orWhere('t.NUPTK', 'like', "%{$nidnSearch}%");
        });
      }

      // DataTables server-side (AJAX)
      if ($request->ajax()) {
        $searchValue = trim((string) data_get($request->input('search', []), 'value', ''));

        $totals = $this->computeTotals(clone $query);

        $dt = DataTables::of($query);

        // Disable Yajra automatic global search to avoid `t`.`alias` column issues.
        $dt->filter(function ($q) use ($searchValue) {
          $searchValue = trim((string) $searchValue);
          if ($searchValue === '') {
            return;
          }

          $s = "%{$searchValue}%";
          $q->where(function ($qq) use ($s) {
            $qq->where('t.NIDN', 'like', $s)
              ->orWhere('t.NUPTK', 'like', $s)
              ->orWhere('t.Nama', 'like', $s);
          });
        });

        // map 'jabatan' searches to the actual COALESCE expression
        $dt->filterColumn('jabatan', function ($q, $keyword) use ($jabatanCol) {
          $s = '%' . strtolower($keyword) . '%';
          $q->whereRaw("LOWER(COALESCE(t.`{$jabatanCol}`, t.Jabatan12, '-')) LIKE ?", [$s]);
        });

        return $dt
          ->editColumn('aktif', function ($row) {
            return ((string) ($row->aktif ?? '0') === '1' || $row->aktif === 1) ? 'Aktif' : 'Tidak Aktif';
          })
          ->addColumn('jumlah_gaji', function ($row) {
            $totalGaji = (float) ($row->total_gaji ?? 0);
            $sum = 0.0;
            for ($i = 1; $i <= 12; $i++) {
              $tpd = (float) ($row->{"tpd{$i}"} ?? 0);
              $tkgb = (float) ($row->{"tkgb{$i}"} ?? 0);
              $sum += ($tpd + $tkgb);
            }
            return $totalGaji + $sum;
          })
          ->addColumn('jumlah_tpd', function ($row) {
            $sum = 0.0;
            for ($i = 1; $i <= 12; $i++) {
              $sum += (float) ($row->{"tpd{$i}"} ?? 0);
            }
            return $sum;
          })
          ->addColumn('jumlah_tkgb', function ($row) {
            $sum = 0.0;
            for ($i = 1; $i <= 12; $i++) {
              $sum += (float) ($row->{"tkgb{$i}"} ?? 0);
            }
            return $sum;
          })
          ->addColumn('selisih_tpd', function ($row) {
            $sum = 0.0;
            for ($i = 1; $i <= 12; $i++) {
              $dbTpd = $this->parseMoney($row->{"tpd{$i}"} ?? 0);
              $gaji = $this->parseMoney($row->{"gaji{$i}"} ?? 0);
              if ($dbTpd == 0 && $gaji == 0) {
                continue;
              }
              $jabatan = $row->{"jabatan{$i}"} ?? ($row->jabatan12 ?? $row->jabatan ?? '');
              $kenaTkgb = $this->isGuruBesarAtauProfesor($jabatan);
              [$aktTpd, $aktTkgb] = $this->splitAktualKotorFromGaji($gaji, $kenaTkgb);

              $sum += ($dbTpd - $aktTpd);
            }
            return $sum;
          })
          ->addColumn('selisih_tkgb', function ($row) {
            $sum = 0.0;
            for ($i = 1; $i <= 12; $i++) {
              $dbTkgb = $this->parseMoney($row->{"tkgb{$i}"} ?? 0);
              $gaji = $this->parseMoney($row->{"gaji{$i}"} ?? 0);
              if ($dbTkgb == 0 && $gaji == 0) {
                continue;
              }
              $jabatan = $row->{"jabatan{$i}"} ?? ($row->jabatan12 ?? $row->jabatan ?? '');
              $kenaTkgb = $this->isGuruBesarAtauProfesor($jabatan);
              [$aktTpd, $aktTkgb] = $this->splitAktualKotorFromGaji($gaji, $kenaTkgb);

              $sum += ($dbTkgb - $aktTkgb);
            }
            return $sum;
          })
          ->addColumn('total_gaji', function ($row) {
            $totalGaji = (float) ($row->total_gaji ?? 0);
            $sum = 0.0;
            for ($i = 1; $i <= 12; $i++) {
              $tpd = (float) ($row->{"tpd{$i}"} ?? 0);
              $tkgb = (float) ($row->{"tkgb{$i}"} ?? 0);
              $sum += ($tpd + $tkgb);
            }
            return $totalGaji + $sum;
          })
          ->addColumn('total_tpd', function ($row) {
            $sum = 0.0;
            for ($i = 1; $i <= 12; $i++) {
              $sum += (float) ($row->{"tpd{$i}"} ?? 0);
            }
            return $sum;
          })
          ->addColumn('total_tkgb', function ($row) {
            $sum = 0.0;
            for ($i = 1; $i <= 12; $i++) {
              $sum += (float) ($row->{"tkgb{$i}"} ?? 0);
            }
            return $sum;
          })
          ->with(['totals' => $totals])
          ->make(true);
      }

      // Do NOT load $dosenList via ->get() because it consumes too much memory.
      // DataTables handles the actual fetching via AJAX.
      $dosenList = [];

    return view('pts.laporan-keuangan', [
      'dosenList' => $dosenList,
      'months' => $bulanFields,
      'nidnSearch' => $nidnSearch,
    ]);
  }

  private function emptyTotals(): array
  {
    return [
      'gajiPerMonth' => array_fill(0, 12, 0),
      'tpdPerMonth' => array_fill(0, 12, 0),
      'tkgbPerMonth' => array_fill(0, 12, 0),
      'grandGaji' => 0,
      'grandTpd' => 0,
      'grandTkgb' => 0,
      'grandSelisihTpd' => 0,
      'grandSelisihTkgb' => 0,
    ];
  }

  private function computeTotals($query): array
  {
    $totals = $this->emptyTotals();
    foreach ($query->cursor() as $row) {
      for ($i = 1; $i <= 12; $i++) {
        $tpd = $this->parseMoney($row->{"tpd{$i}"} ?? 0);
        $tkgb = $this->parseMoney($row->{"tkgb{$i}"} ?? 0);
        $gajiDb = $this->parseMoney($row->{"gaji{$i}"} ?? 0);
        $totals['gajiPerMonth'][$i - 1] += $gajiDb;
        $totals['tpdPerMonth'][$i - 1] += $tpd;
        $totals['tkgbPerMonth'][$i - 1] += $tkgb;

        $gajiDb = $this->parseMoney($row->{"gaji{$i}"} ?? 0);
        if ($tpd == 0 && $tkgb == 0 && $gajiDb == 0) {
          continue;
        }
        $jabatan = $row->{"jabatan{$i}"} ?? ($row->jabatan12 ?? $row->jabatan ?? '');
        $kenaTkgb = $this->isGuruBesarAtauProfesor($jabatan);
        [$aktTpd, $aktTkgb] = $this->splitAktualKotorFromGaji($gajiDb, $kenaTkgb);

        $totals['grandSelisihTpd'] += ($tpd - $aktTpd);
        $totals['grandSelisihTkgb'] += ($tkgb - $aktTkgb);
      }
    }

    $totals['grandGaji'] = array_sum($totals['gajiPerMonth']);
    $totals['grandTpd'] = array_sum($totals['tpdPerMonth']);
    $totals['grandTkgb'] = array_sum($totals['tkgbPerMonth']);

    return $totals;
  }

  private function isGuruBesarAtauProfesor($jabatan): bool
  {
    $text = strtolower(trim((string) $jabatan));
    if ($text === '') {
      return false;
    }
    return strpos($text, 'guru besar') !== false || strpos($text, 'profesor') !== false;
  }

  private function splitAktualKotorFromGaji(float $gaji, bool $kenaTKGB): array
  {
    // Sama seperti KekuranganBayarController::splitAktualKotorFromGaji
    if ($gaji == 0.0) {
      return [0.0, 0.0];
    }
    if (!$kenaTKGB) {
      return [$gaji, 0.0];
    }

    $tpd = $gaji / 3.0;
    $tkgb = $gaji - $tpd;
    return [$tpd, $tkgb];
  }

  private function parseMoney($value): float
  {
    if ($value === null) return 0.0;
    if (is_int($value) || is_float($value)) return (float) $value;
    $text = trim((string) $value);
    if ($text === '') return 0.0;
    $text = preg_replace('/[^0-9\-]/', '', $text);
    if ($text === '' || $text === '-') return 0.0;
    return (float) $text;
  }

  public function exportPts(Request $request)
  {
    set_time_limit(0);
    ini_set('memory_limit', '2048M');

    // Ambil parameter sesuai input pencarian di halaman (nidn)
    $search = $request->query('nidn');
    $kode_pts = Auth::guard('pts')->user()->kode_pts;
    $tahun = session('tahun') ?: date('Y');

    $fileName = 'laporan_keuangan_' . $kode_pts . '.xlsx';

    return Excel::download(new LaporanKeuanganPtsExport($kode_pts, $search, $tahun), $fileName);
  }
}
