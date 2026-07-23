<?php

namespace App\Http\Controllers\Dosen;

use App\Exports\LaporanKeuanganExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class LaporanKeuanganDosenController extends Controller
{
  private function resolveDosenIdentifier($dosen): string
  {
    $nidn = trim((string) ($dosen->nidn ?? ''));
    if ($nidn !== '') {
      return $nidn;
    }

    $nuptk = trim((string) ($dosen->nuptk ?? ''));
    return $nuptk;
  }

  public function index(Request $request)
  {
    $dosen = Auth::guard('dosen')->user();
    $nidnOrNuptk = $this->resolveDosenIdentifier($dosen);

    $tahun = session('tahun') ?: date('Y');

    // DataTables server-side (AJAX)
    if ($request->ajax()) {
      if ($nidnOrNuptk === '') {
        return DataTables::of(collect())
          ->with(['totals' => $this->emptyTotals()])
          ->make(true);
      }

      $bulanSession = (int) session('bulan') ?: 12;
      if ($bulanSession < 1 || $bulanSession > 12) {
        $bulanSession = 12;
      }
      $jabCol = 'Jabatan' . $bulanSession;

      $coalesceGaji = '(COALESCE(t.Gaji1,0)+COALESCE(t.Gaji2,0)+COALESCE(t.Gaji3,0)+COALESCE(t.Gaji4,0)+COALESCE(t.Gaji5,0)+COALESCE(t.Gaji6,0)+COALESCE(t.Gaji7,0)+COALESCE(t.Gaji8,0)+COALESCE(t.Gaji9,0)+COALESCE(t.Gaji10,0)+COALESCE(t.Gaji11,0)+COALESCE(t.Gaji12,0)) as total_gaji';

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
        ->where('t.Tahun_Versi', $tahun)
        ->where(function ($q) use ($nidnOrNuptk) {
          $q->whereRaw('TRIM(`t`.`NIDN`) = ?', [$nidnOrNuptk])
            ->orWhereRaw('TRIM(`t`.`NUPTK`) = ?', [$nidnOrNuptk]);
        });

      $totals = $this->computeTotals(clone $query);

      $dt = DataTables::of($query);

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

    return view('dosen.laporan-keuangan', [
      'nidnOrNuptk' => $nidnOrNuptk,
    ]);
  }

  public function export(Request $request)
  {
    $dosen = Auth::guard('dosen')->user();
    $nidnOrNuptk = $this->resolveDosenIdentifier($dosen);
    $tahun = session('tahun') ?: (string) date('Y');

    if ($nidnOrNuptk === '') {
      return redirect()->route('dosen.laporan-keuangan')->with('error', 'Akun dosen tidak memiliki NIDN/NUPTK.');
    }

    $fileName = 'laporan_keuangan_' . $nidnOrNuptk . '.xlsx';
    return Excel::download(new LaporanKeuanganExport(null, $tahun, $nidnOrNuptk), $fileName);
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
    $text = preg_replace('/[^0-9\-]/', '', $text);
    if ($text === '' || $text === '-') return 0.0;
    return (float) $text;
  }
}
