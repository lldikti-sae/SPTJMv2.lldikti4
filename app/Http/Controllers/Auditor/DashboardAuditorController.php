<?php

namespace App\Http\Controllers\Auditor;

use App\Http\Controllers\Controller;
use App\Models\Pts;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class DashboardAuditorController extends Controller
{
  /**
   * Attempt to parse a date string that may come in various formats.
   */
  private function parseTanggalLahir($value)
  {
    if (empty($value)) {
      return null;
    }

    $value = trim((string) $value);
    if ($value === '') {
      return null;
    }

    $formats = ['d/m/Y', 'd-m-Y', 'Y-m-d', 'Y/m/d', 'd/m/y', 'd-m-y'];
    foreach ($formats as $format) {
      try {
        return Carbon::createFromFormat($format, $value);
      } catch (\Throwable $e) {
        // try next format
      }
    }

    try {
      return Carbon::parse($value);
    } catch (\Throwable $e) {
      return null;
    }
  }

  public function index()
  {
    $tahun = session('tahun') ?? date('Y');
    $bulan = (int) (session('bulan') ?: date('n'));
    $bulan = max(1, min(12, $bulan));

    $baseQuery = DB::table('s_transaksi_2')->where('Tahun_Versi', $tahun);

    $totalDosen = (clone $baseQuery)->count();

    $ptsAktif = Pts::query()->where('aktif', '1')->count();
    $ptsTidakAktif = Pts::query()->where('aktif', '0')->count();

    $aktifValues = ['1', 1, 'YA', 'Ya', 'ya', 'Y', 'y'];
    $nonAktifValues = ['0', 0, 'TIDAK', 'Tidak', 'tidak', 'N', 'n'];

    // Total active / inactive overall
    $dosenAktif = (clone $baseQuery)->whereIn('Aktif', $aktifValues)->count();
    $dosenTidakAktif = max(0, $totalDosen - $dosenAktif);

    // Breakdowns by Jenis (PNS / NON PNS)
    $jumlahPnsAktif = (clone $baseQuery)->where('Jenis', 'PNS')->whereIn('Aktif', $aktifValues)->count();
    $jumlahPnsTidakAktif = (clone $baseQuery)->where('Jenis', 'PNS')->whereNotIn('Aktif', $aktifValues)->count();

    $jumlahNonPnsAktif = (clone $baseQuery)->where('Jenis', 'NON PNS')->whereIn('Aktif', $aktifValues)->count();
    $jumlahNonPnsTidakAktif = (clone $baseQuery)->where('Jenis', 'NON PNS')->whereNotIn('Aktif', $aktifValues)->count();

    $eligibleValues = ['1', 1, 'YA', 'Ya', 'ya', 'Y', 'y', 'TRUE', 'true'];
    $dosenEligible = (clone $baseQuery)->whereIn('Eligible_span', $eligibleValues)->count();
    $dosenTidakEligible = max(0, $totalDosen - $dosenEligible);

    $dosenMissingIdentifier = (clone $baseQuery)
      ->where(function ($q) {
        $q->whereNull('NIDN')->orWhere('NIDN', '');
      })
      ->where(function ($q) {
        $q->whereNull('NUPTK')->orWhere('NUPTK', '');
      })
      ->count();

    // Financial aggregates (TPD + TKGB) across months
    $tpdParts = [];
    $tkgbParts = [];
    $bayarParts = [];
    for ($i = 1; $i <= 12; $i++) {
      $tpdParts[] = "COALESCE(`TPD{$i}`,0)";
      $tkgbParts[] = "COALESCE(`TKGB{$i}`,0)";
      $bayarParts[] = "(COALESCE(`TPD{$i}`,0) + COALESCE(`TKGB{$i}`,0))";
    }

    $finance = (clone $baseQuery)
      ->selectRaw('SUM(' . implode(' + ', $tpdParts) . ') as total_tpd')
      ->selectRaw('SUM(' . implode(' + ', $tkgbParts) . ') as total_tkgb')
      ->selectRaw('SUM(' . implode(' + ', $bayarParts) . ') as total_bayar')
      ->first();

    $topPtsByDosen = (clone $baseQuery)
      ->selectRaw('TRIM(`Kode_PT`) as kode_pt, MAX(`PTS`) as pts, COUNT(*) as total')
      ->groupBy(DB::raw('TRIM(`Kode_PT`)'))
      ->orderByDesc('total')
      ->limit(5)
      ->get();

    return view('auditor.dashboard', [
      'tahun' => $tahun,
      'bulan' => $bulan,
      'totalDosen' => $totalDosen,
      'ptsAktif' => $ptsAktif,
      'ptsTidakAktif' => $ptsTidakAktif,
      'dosenAktif' => $dosenAktif,
      'dosenTidakAktif' => $dosenTidakAktif,
      'dosenEligible' => $dosenEligible,
      'dosenTidakEligible' => $dosenTidakEligible,
      'jumlahPnsAktif' => $jumlahPnsAktif,
      'jumlahPnsTidakAktif' => $jumlahPnsTidakAktif,
      'jumlahNonPnsAktif' => $jumlahNonPnsAktif,
      'jumlahNonPnsTidakAktif' => $jumlahNonPnsTidakAktif,
      'dosenMissingIdentifier' => $dosenMissingIdentifier,
      'finance' => $finance,
      'topPtsByDosen' => $topPtsByDosen,
    ]);
  }

  /**
   * DataTables AJAX source for daftar dosen pensiun berjalan (auditor read-only).
   */
  public function dosenPensiunData(Request $request)
  {
    $tahun = session('tahun');

    $dosenPensiun = DB::table('s_transaksi_2')
      ->select(
        'NIDN',
        'nuptk',
        'Nama',
        'PTS',
        'Usia',
        'Tanggal_Lahir',
        DB::raw("COALESCE(
            NULLIF(jabatan12, ''),
            NULLIF(jabatan11, ''),
            NULLIF(jabatan10, ''),
            NULLIF(jabatan9, ''),
            NULLIF(jabatan8, ''),
            NULLIF(jabatan7, ''),
            NULLIF(jabatan6, ''),
            NULLIF(jabatan5, ''),
            NULLIF(jabatan4, ''),
            NULLIF(jabatan3, ''),
            NULLIF(jabatan2, ''),
            NULLIF(jabatan1, '')
          ) as jabatan_terakhir")
      )
      ->where('aktif', '1')
      ->where('tahun_versi', $tahun)
      ->where(function ($query) {
        $query
          ->where('usia', '>=', 65)
          ->orWhere(function ($query) {
            $query
              ->where('usia', '>=', 70)
              ->where('jabatan12', 'Guru Besar');
          });
      })
      ->get();

    $filteredDosenPensiun = [];
    foreach ($dosenPensiun as $dosen) {
      $pensiunAge = $dosen->jabatan_terakhir == 'Guru Besar' ? 70 : 65;
      $tanggalLahir = $this->parseTanggalLahir($dosen->Tanggal_Lahir);
      if ($tanggalLahir) {
        $tmtPensiun = $tanggalLahir
          ->copy()
          ->addYears($pensiunAge)
          ->addMonthNoOverflow();
        $tmtPensiunYear = $tmtPensiun->year;
        $tmtPensiunMonth = $tmtPensiun->month;

        if ((string) $tmtPensiunYear === (string) $tahun) {
          $filteredDosenPensiun[] = [
            'nidn' => $dosen->NIDN,
            'nuptk' => $dosen->nuptk ?? null,
            'nama' => $dosen->Nama,
            'pts' => $dosen->PTS,
            'usia' => $dosen->Usia,
            'status' => 'Aktif',
            'tmt_pensiun' => $tmtPensiun->format('F Y'),
            'tmt_pensiun_bulan' => $tmtPensiunMonth,
          ];
        }
      }
    }

    usort($filteredDosenPensiun, function ($a, $b) {
      return $a['tmt_pensiun_bulan'] <=> $b['tmt_pensiun_bulan'];
    });

    return DataTables::of(collect($filteredDosenPensiun))
      ->addColumn('status', function ($row) {
        return '<span class="badge bg-label-primary">Aktif</span>';
      })
      ->addColumn('aksi', function ($row) {
        $nidn = $row['nidn'] ?? null;
        if (!$nidn) {
          return '';
        }
        $url = url('auditor/view-data-dosen/' . $nidn);
        return '<a href="' . $url . '" class="sptjm-icon-btn sptjm-btn-view" title="Lihat"><span class="tf-icons bx bx-show"></span></a>';
      })
      ->rawColumns(['status', 'aksi'])
      ->make(true);
  }
}
