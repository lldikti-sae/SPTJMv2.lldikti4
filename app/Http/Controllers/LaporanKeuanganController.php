<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\APts;
use App\Exports\LaporanKeuanganExport;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class LaporanKeuanganController extends Controller
{
  public function index(Request $request)
  {
    $kode_pt = trim((string) $request->input('kode_pt', ''));
    $nidn = trim((string) $request->input('nidn', ''));
    $tahun = session('tahun');
    $dosenList = [];

    $ptsList = APts::query()
      ->select(['kode_pts', 'nama_pts'])
      ->orderBy('kode_pts')
      ->get();

    // DataTables server-side (AJAX)
    if ($request->ajax()) {
      $isSemua = ($kode_pt === 'Semua');
      $kodePtFilter = $kode_pt;
      if ($kodePtFilter === 'Semua' || $kodePtFilter === '') {
        $kodePtFilter = null;
      }

      $searchValue = trim((string) data_get($request->input('search', []), 'value', ''));

      // keep old behavior: if no filter and no search, return empty table
      if (empty($kodePtFilter) && !$isSemua && $nidn === '' && $searchValue === '') {
        return DataTables::of(collect())
          ->with(['totals' => $this->emptyTotals()])
          ->make(true);
      }

      $bulanSession = (int) session('bulan') ?: 12;
      if ($bulanSession < 1 || $bulanSession > 12) {
        $bulanSession = 12;
      }
      $jabCol = 'Jabatan' . $bulanSession;

      $coalesceGaji = 'COALESCE(t.Gaji1,t.Gaji2,t.Gaji3,t.Gaji4,t.Gaji5,t.Gaji6,t.Gaji7,t.Gaji8,t.Gaji9,t.Gaji10,t.Gaji11,t.Gaji12,0) as total_gaji';

      $selects = [
        't.NIDN as nidn',
        't.NUPTK as nuptk',
        't.Nama as nama',
        't.Jenis as jenis',
        DB::raw("COALESCE(t.`{$jabCol}`, t.Jabatan12, '-') as jabatan"),
        't.Aktif as aktif',
        't.Eligible_span as eligible_span',
        't.Bank as bank',
        't.Kode_PT as kode_pt',
        't.PTS as pts',
        DB::raw($coalesceGaji),
      ];
      for ($i = 1; $i <= 12; $i++) {
        $selects[] = DB::raw("t.`KodeUsulan{$i}` as `kodeusulan{$i}`");
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

      $query = DB::table('s_transaksi_2 as t')
        ->select($selects)
        ->where('t.Tahun_Versi', $tahun);

      if (!empty($kodePtFilter)) {
        $query->whereRaw('TRIM(`t`.`Kode_PT`) = ?', [$kodePtFilter]);
      }

      if ($nidn !== '') {
        $query->where(function ($q) use ($nidn) {
          $q->where('t.NIDN', 'like', "%{$nidn}%")
            ->orWhere('t.NUPTK', 'like', "%{$nidn}%");
        });
      }

      $totals = $this->computeTotals(clone $query);

      $dt = DataTables::of($query);

      // Disable Yajra's automatic global search because it may prefix selected aliases
      // with `t.` (e.g. `t`.`nidn`, `t`.`jabatan`) which don't exist in the table.
      // We implement our own safe global search against real DB columns.
      $dt->filter(function ($q) use ($searchValue) {
        $searchValue = trim((string) $searchValue);
        if ($searchValue === '') {
          return;
        }

        $s = "%{$searchValue}%";
        $q->where(function ($qq) use ($s) {
          $qq->where('t.NIDN', 'like', $s)
            ->orWhere('t.NUPTK', 'like', $s)
            ->orWhere('t.Nama', 'like', $s)
            ->orWhere('t.Kode_PT', 'like', $s)
            ->orWhere('t.PTS', 'like', $s);
        });
      });

      // prevent DataTables from generating WHERE `t`.`jabatan` which doesn't exist;
      // map search on 'jabatan' to the COALESCE expression we select
      $dt->filterColumn('jabatan', function ($q, $keyword) use ($jabCol) {
        $s = '%' . strtolower($keyword) . '%';
        $q->whereRaw("LOWER(COALESCE(t.`{$jabCol}`, t.Jabatan12, '-')) LIKE ?", [$s]);
      });

      return $dt
        ->editColumn('aktif', function ($row) {
          return ((string) ($row->aktif ?? '0') === '1' || $row->aktif === 1) ? 'Aktif' : 'Tidak Aktif';
        })
        ->addColumn('jumlah_gaji', function ($row) {
          $totalGaji = ((float) ($row->total_gaji ?? 0)) * 12;
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
            $noSp2d = trim((string) ($row->{"no_sp2d_{$i}"} ?? ''));
            $tglSp2d = trim((string) ($row->{"tgl_sp2d_{$i}"} ?? ''));
            if ($noSp2d === '' || $tglSp2d === '') {
              continue;
            }

            $dbTpd = $this->parseMoney($row->{"tpd{$i}"} ?? 0);
            $gaji = $this->parseMoney($row->{"gaji{$i}"} ?? 0);
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
            $noSp2d = trim((string) ($row->{"no_sp2d_{$i}"} ?? ''));
            $tglSp2d = trim((string) ($row->{"tgl_sp2d_{$i}"} ?? ''));
            if ($noSp2d === '' || $tglSp2d === '') {
              continue;
            }

            $dbTkgb = $this->parseMoney($row->{"tkgb{$i}"} ?? 0);
            $gaji = $this->parseMoney($row->{"gaji{$i}"} ?? 0);
            $jabatan = $row->{"jabatan{$i}"} ?? ($row->jabatan12 ?? $row->jabatan ?? '');
            $kenaTkgb = $this->isGuruBesarAtauProfesor($jabatan);
            [$aktTpd, $aktTkgb] = $this->splitAktualKotorFromGaji($gaji, $kenaTkgb);

            $sum += ($dbTkgb - $aktTkgb);
          }
          return $sum;
        })
        ->addColumn('total_gaji', function ($row) {
          $totalGaji = ((float) ($row->total_gaji ?? 0)) * 12;
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

    $bulanFields = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'ags', 'sep', 'okt', 'nov', 'des'];

    $tpdFields = array_map(fn($i) => "TPD$i", range(1, 12));
    $tkgbFields = array_map(fn($i) => "TKGB$i", range(1, 12));


    if ($kode_pt !== '' || $nidn !== '') {
      $kodePtFilter = $kode_pt;
      if ($kodePtFilter === 'Semua' || $kodePtFilter === '') {
        $kodePtFilter = null;
      }

      $query = DB::table('s_transaksi_2')
        ->where('tahun_versi', $tahun);

      if (!empty($kodePtFilter)) {
        // Guard against whitespace and different column casing in DB
        $query->whereRaw('TRIM(`Kode_PT`) = ?', [$kodePtFilter]);
      }

      if ($nidn) {
        // support partial matches and allow searching by NIDN or NUPTK
        $query->where(function($q) use ($nidn) {
          $q->where('NIDN', 'like', "%{$nidn}%")
            ->orWhere('NUPTK', 'like', "%{$nidn}%");
        });
      }

      // Do NOT load $dosenList via ->get() because it consumes too much memory (OOM) for 'Semua' (~45k records).
      // DataTables handles the actual fetching via AJAX.
      $dosenList = [];

      if (!$query->exists()) {
        session()->flash('error', 'Data dengan Kode PT atau identifier tersebut tidak ditemukan.');
      }
    }

    return view('admin.laporan-keuangan', [
      'dosenList' => $dosenList,
      'kode_pt' => $kode_pt,
      'nidn' => $nidn,
      'months' => $bulanFields,
      'ptsList' => $ptsList,
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
        $gaji = $tpd + $tkgb;
        $totals['gajiPerMonth'][$i - 1] += $gaji;
        $totals['tpdPerMonth'][$i - 1] += $tpd;
        $totals['tkgbPerMonth'][$i - 1] += $tkgb;

        $noSp2d = trim((string) ($row->{"no_sp2d_{$i}"} ?? ''));
        $tglSp2d = trim((string) ($row->{"tgl_sp2d_{$i}"} ?? ''));
        if ($noSp2d !== '' && $tglSp2d !== '') {
          $gajiDb = $this->parseMoney($row->{"gaji{$i}"} ?? 0);
          $jabatan = $row->{"jabatan{$i}"} ?? ($row->jabatan12 ?? $row->jabatan ?? '');
          $kenaTkgb = $this->isGuruBesarAtauProfesor($jabatan);
          [$aktTpd, $aktTkgb] = $this->splitAktualKotorFromGaji($gajiDb, $kenaTkgb);

          $totals['grandSelisihTpd'] += ($tpd - $aktTpd);
          $totals['grandSelisihTkgb'] += ($tkgb - $aktTkgb);
        }
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
    // - Non Guru Besar/Profesor: TPD = Gaji, TKGB = 0
    // - Guru Besar/Profesor: TPD = 1/3 Gaji, TKGB = 2/3 Gaji
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
    // Handle formatted strings like "1.234.567" by stripping non-digits (keep minus)
    $text = preg_replace('/[^0-9\-]/', '', $text);
    if ($text === '' || $text === '-') return 0.0;
    return (float) $text;
  }

  public function export(Request $request)
  {
    set_time_limit(0);
    ini_set('memory_limit', '2048M');

    $kode_pt = trim((string) $request->input('kode_pt', ''));
    $nidn = trim((string) $request->input('nidn', ''));
    $tahun = $request->input('tahun') ?: date('Y');

    $kodePtFilter = $kode_pt;
    if ($kodePtFilter === 'Semua' || $kodePtFilter === '') {
      $kodePtFilter = null;
    }

    $fileName = 'laporan_keuangan_' . ($kodePtFilter ?: 'all');
    if ($nidn !== '') {
      $fileName .= '_' . $nidn;
    }
    $fileName .= '.xlsx';

    return Excel::download(new LaporanKeuanganExport($kodePtFilter, $tahun, ($nidn !== '' ? $nidn : null)), $fileName);
  }
}
