<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dosen;
use App\Models\Transaksi;
use App\Models\Pts;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use DB;
use Yajra\DataTables\Facades\DataTables;

class DashboardController extends Controller
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
        // coba format berikutnya
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
    $tahun = session('tahun');

    // Bypass cache to guarantee fresh data
    $dataDashboard = function () use ($tahun) {
      // Hitung agregat utama dosen dengan query yang efisien
      $jumlahDosenPNSAktif = DB::table('s_transaksi_2')
        ->where('jenis', 'PNS')
        ->where('aktif', '1')
        ->where('tahun_versi', $tahun)
        ->count();

      $jumlahDosenPNSNon = DB::table('s_transaksi_2')
        ->where('jenis', 'PNS')
        ->where(function($q) {
            $q->where('aktif', '!=', '1')->orWhereNull('aktif');
        })
        ->where('tahun_versi', $tahun)
        ->count();

      $jumlahDosenNonPNSAktif = DB::table('s_transaksi_2')
        ->where('jenis', 'NON PNS')
        ->where('aktif', '1')
        ->where('tahun_versi', $tahun)
        ->count();

      $jumlahDosenNonPNSNon = DB::table('s_transaksi_2')
        ->where('jenis', 'NON PNS')
        ->where(function($q) {
            $q->where('aktif', '!=', '1')->orWhereNull('aktif');
        })
        ->where('tahun_versi', $tahun)
        ->count();

      $totalDosen = Transaksi::where('tahun_versi', $tahun)->count();
      $ptsCount = Pts::where('aktif', '1')->count();

      // Ambil data dosen yang mendekati pensiun
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
                ->whereIn('jabatan12', ['Guru Besar', 'Guru Besar 1050']);
            });
        })
        ->get();

      $filteredDosenPensiun = [];
      foreach ($dosenPensiun as $dosen) {
        $pensiunAge = in_array($dosen->jabatan_terakhir, ['Guru Besar', 'Guru Besar 1050']) ? 70 : 65;
        // Parsing tanggal lahir dilakukan di PHP karena format data beragam
        $tanggalLahir = $this->parseTanggalLahir($dosen->Tanggal_Lahir);
        if ($tanggalLahir) {
          // Hitung TMT Pensiun (Usia Pensiun + 1 Bulan dari bulan lahir)
          $tmtPensiun = $tanggalLahir
            ->copy()
            ->addYears($pensiunAge)
            ->addMonthNoOverflow();
          $tmtPensiunYear = $tmtPensiun->year;
          $tmtPensiunMonth = $tmtPensiun->month; // Ambil bulan pensiun

          // Hanya tampilkan jika pensiun di tahun berjalan
          if ($tmtPensiunYear == $tahun) {
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

      // Urutkan berdasarkan bulan pensiun (Januari ke Desember)
      usort($filteredDosenPensiun, function ($a, $b) {
        return $a['tmt_pensiun_bulan'] <=> $b['tmt_pensiun_bulan'];
      });

      return compact(
        'jumlahDosenPNSAktif',
        'jumlahDosenPNSNon',
        'jumlahDosenNonPNSAktif',
        'jumlahDosenNonPNSNon',
        'filteredDosenPensiun',
        'totalDosen',
        'ptsCount'
      );
    };

    return view('admin.dashboard', $dataDashboard());
  }

  /**
   * DataTables AJAX source for daftar dosen pensiun berjalan.
   */
  public function dosenPensiunData(Request $request)
  {
    $tahun = session('tahun');

    // Ambil data dasar dosen mendekati pensiun (logika sama dengan index())
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
              ->whereIn('jabatan12', ['Guru Besar', 'Guru Besar 1050']);
          });
      })
      ->get();

    $filteredDosenPensiun = [];
    foreach ($dosenPensiun as $dosen) {
      $pensiunAge = in_array($dosen->jabatan_terakhir, ['Guru Besar', 'Guru Besar 1050']) ? 70 : 65;
      $tanggalLahir = $this->parseTanggalLahir($dosen->Tanggal_Lahir);
      if ($tanggalLahir) {
        $tmtPensiun = $tanggalLahir
          ->copy()
          ->addYears($pensiunAge)
          ->addMonthNoOverflow();
        $tmtPensiunYear = $tmtPensiun->year;
        $tmtPensiunMonth = $tmtPensiun->month;

        if ($tmtPensiunYear == $tahun) {
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

    // Urutkan berdasarkan bulan pensiun (Januari ke Desember)
    usort($filteredDosenPensiun, function ($a, $b) {
      return $a['tmt_pensiun_bulan'] <=> $b['tmt_pensiun_bulan'];
    });

    return DataTables::of(collect($filteredDosenPensiun))
      ->addColumn('status', function ($row) {
        return 'Aktif';
      })
      ->addColumn('aksi', function ($row) {
        $nidn = $row['nidn'] ?? null;
        if (!$nidn) {
          return '';
        }
        $url = url('admin/view-data-dosen/' . $nidn);
        return '<a href="' . $url . '" class="sptjm-icon-btn sptjm-btn-view" title="Lihat"><span class="tf-icons bx bx-show"></span></a>';
      })
      ->rawColumns(['status', 'aksi'])
      ->make(true);
  }

  public function pic()
  {
    return view('dashboard.pic');
  }

  public function pts()
  {
    return view('dashboard.pts');
  }
}
