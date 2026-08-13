<?php

namespace App\Http\Controllers\Pic;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\HistoriDosen;
use App\Models\Jabatan;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class LihatDataDosenController extends Controller
{
  public function index()
  {
    $wilayah = Auth::user()->email;
    $emails = User::pluck('email');
    $jabatans = Jabatan::pluck('jabatan');
    $banks = Bank::pluck('nama_bank');

    if (request()->ajax()) {
      // Tentukan kolom berdasarkan sesi bulan (1-12). Jika tidak ada, default ke 12.
      $bulanSession = (int) session('bulan') ?: 12;
      $golCol = 'Gol' . $bulanSession;
      $tahunCol = 'Tahun' . $bulanSession;
      $jabCol = 'Jabatan' . $bulanSession;

      $dataDosen = DB::table('s_transaksi_2 as d')
        ->leftJoin('p_sister_genap as genap_tl', 'd.nidn', '=', 'genap_tl.nidn')
        ->leftJoin('p_sister_ganjil as ganjil', 'd.nidn', '=', 'ganjil.nidn')
        ->leftJoin('p_sister_genap as genap_bj', 'd.nidn', '=', 'genap_bj.nidn')
        ->select(
          'd.*',
          'genap_tl.kesimpulan_bkd as bkd_genap_tl',
          'ganjil.kesimpulan_bkd as bkd_ganjil',
          'genap_bj.kesimpulan_bkd as bkd_genap_bj'
        )
        ->where('d.pemegang_wilayah', $wilayah)
        ->where('tahun_versi', session('tahun'))
        ->orderBy('d.nama');

      $searchValue = request()->input('search.value');

      return DataTables::of($dataDosen)
        ->filter(function ($query) use ($searchValue) {
          if ($searchValue) {
            $s = "%{$searchValue}%";
            $query->where(function ($q) use ($s) {
              $q->where('d.NIDN', 'like', $s)
                ->orWhere('d.NUPTK', 'like', $s)
                ->orWhere('d.nama', 'like', $s)
                ->orWhere('d.Kode_PT', 'like', $s)
                ->orWhere('d.PTS', 'like', $s);
            });
          }
        })
        ->addIndexColumn()
        ->addColumn('NUPTK', function ($row) {
          return $row->NUPTK ?? $row->nuptk ?? '';
        })
        ->editColumn('aktif', function ($row) {
          if ($row->Aktif == 1) {
            return '<span class="badge bg-label-success border border-success">Aktif</span>';
          }
          return '<span class="badge bg-label-danger">Tidak Aktif</span>';
        })
        ->addColumn('aksi', function ($row) {
          // Determine identifier: prefer NIDN, fallback to NUPTK
          $nidnOnly = trim((string) ($row->NIDN ?? $row->nidn ?? ''));
          $nuptkOnly = trim((string) ($row->NUPTK ?? $row->nuptk ?? ''));
          $identifier = $nidnOnly !== '' ? $nidnOnly : ($nuptkOnly !== '' ? $nuptkOnly : null);
          if (!$identifier) {
            return '<div class="text-center">-</div>';
          }

          // URL targets: provide Admin-like navigation alias for PIC.
          $basePerubahanUrl = route('pic.perubahan-data-dosen.show', ['nidn' => $identifier]);
          $urlPengaktifan = $basePerubahanUrl;
          $urlPerubahan = $basePerubahanUrl . '?open=perubahan';

          // View button
          $urlView = route('dosen.showData', ['nidn' => $identifier]);
          $viewBtn = '<a href="' . $urlView . '" class="sptjm-icon-btn sptjm-btn-view" title="Lihat"><span class="tf-icons bx bx-show"></span></a>';

          // Status button
          $isActive = false;
          if (isset($row->Aktif) || isset($row->aktif)) {
            $val = $row->Aktif ?? $row->aktif;
            $isActive = ($val == 1 || $val === '1');
          }
          if ($isActive) {
            $statusBtn = '<a href="' . $urlPengaktifan . '" class="sptjm-icon-btn sptjm-btn-reset" title="Pengaktifan"><i class="bx bx-check"></i></a>';
          } else {
            $statusBtn = '<a href="' . $urlPengaktifan . '" class="sptjm-icon-btn sptjm-btn-delete" title="Tidak Aktif"><i class="bx bx-block"></i></a>';
          }

          // Edit button
          $editBtn = '<a href="' . $urlPerubahan . '" class="sptjm-icon-btn sptjm-btn-edit" title="Edit"><i class="bx bx-edit-alt"></i></a>';

          // Histori button (uses NIDN as key; when missing, show a disabled placeholder to keep 2x2)
          if ($nidnOnly !== '') {
            $histUrl = route('pic.lihat.histori.dosen', $nidnOnly);
            $histBtn = '<a href="' . $histUrl . '" class="sptjm-icon-btn sptjm-btn-secondary" title="Histori"><span class="tf-icons bx bx-history"></span></a>';
          } else {
            $histBtn = '<span class="sptjm-icon-btn sptjm-btn-secondary disabled" title="Histori"><span class="tf-icons bx bx-history"></span></span>';
          }

          return $viewBtn . ' ' . $statusBtn . ' ' . $editBtn . ' ' . $histBtn;
        })
        // tambahkan alias kolom dinamis berdasarkan bulan sesi
        ->addColumn('gol', function ($row) use ($golCol) {
          $val = $row->{$golCol} ?? $row->Gol12 ?? null;
          return $val && trim((string) $val) !== '' ? $val : '-';
        })
        ->addColumn('masa_kerja', function ($row) use ($tahunCol) {
          $val = $row->{$tahunCol} ?? null;
          return $val && trim((string) $val) !== '' ? $val : '-';
        })
        ->addColumn('jabatan', function ($row) use ($jabCol) {
          $val = $row->{$jabCol} ?? $row->Jabatan12 ?? null;
          return $val && trim((string) $val) !== '' ? $val : '-';
        })
        ->rawColumns(['aktif', 'aksi'])
        ->make(true);
    }

    return view('pic.lihat-data-dosen');
  }

  public function showData($nidn)
  {
    // Match Admin detail behavior: use session month fields + lookup by NIDN OR NUPTK.
    $bulanSession = (int) session('bulan') ?: 12;
    $bulanSession = max(1, min(12, $bulanSession));

    $masa_kerja = "NULLIF(Tahun{$bulanSession}, '')";
    $golongan   = "NULLIF(Gol{$bulanSession}, '')";
    $jabatan    = "NULLIF(Jabatan{$bulanSession}, '')";
    $gaji       = "NULLIF(Gaji{$bulanSession}, 0)";

    $wilayah = Auth::user()->email;
    $dosen = Transaksi::where(function ($q) use ($nidn) {
        $q->where('NIDN', $nidn)
          ->orWhere('NUPTK', $nidn);
      })
      ->where('Pemegang_Wilayah', $wilayah)
      ->where('Tahun_Versi', session('tahun'))
      ->select(
        "*",
        DB::raw("$masa_kerja AS masa_kerja"),
        DB::raw("$golongan AS gol"),
        DB::raw("$jabatan AS jabatan"),
        DB::raw("$gaji AS gaji")
      )
      ->first();

    if (!$dosen) {
      return redirect()->back()->with('error', 'Data dosen tidak ditemukan!');
    }

    // Reuse Admin blade (now role-aware) so PIC layout/content match Admin exactly.
    return view('admin.view-data-dosen', ['dosen' => $dosen]);
  }

  public function show($nidn)
  {
    $dosen = HistoriDosen::where('nidn', $nidn)
      ->orderBy('created_at', 'DESC')
      ->get();

    return view('pic.lihat-histori-dosen', compact('dosen'));
  }
}
