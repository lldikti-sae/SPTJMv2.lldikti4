<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class LihatDataDosenPtsController extends Controller
{

  public function index(Request $request)
  {
    // Ambil kode_pts dari user yang login via guard pts
    $kodePts = Auth::guard('pts')->user()->kode_pts;
    $tahun = session('tahun');
    // gunakan bulan dari session (1-12) — tidak melakukan fallback otomatis
    $bulan = (int) session('bulan');

    // kolom untuk bulan yang dipilih
    $masa_kerja_col = "Tahun{$bulan}"; // mis. Tahun1..Tahun12
    $golongan_col = "Gol{$bulan}"; // mis. Gol1..Gol12
    $jabatan_col = "Jabatan{$bulan}"; // mis. Jabatan1..Jabatan12
    // Ambil data dosen dari s_transaksi_2 berdasarkan kode_pt
    if ($request->ajax()) {
      $query = DB::table('s_transaksi_2')->select(
        [
          'nidn',
          'nuptk',
          'nama',
          DB::raw("NULLIF({$masa_kerja_col}, '') as masa_kerja"),
          DB::raw("NULLIF({$golongan_col}, '') as gol"),
          DB::raw("NULLIF({$jabatan_col}, '') as jabatan"),
          'aktif'
        ]
      )
        ->where('kode_pt', $kodePts)
        ->where('Tahun_Versi', $tahun)
        // default ordering: aktif desc (1 first), then nidn asc
        ->orderByDesc('aktif')
        ->orderBy('nidn');

      return DataTables::of($query)
        ->addIndexColumn()
        ->filter(function ($query) use ($request, $golongan_col, $jabatan_col) {
          // DataTables global search -> apply to multiple useful columns
          if ($request->has('search') && $request->get('search')['value']) {
            $search = $request->get('search')['value'];
            $query->where(function ($q) use ($search, $golongan_col, $jabatan_col) {
              $q->where('nidn', 'like', "%{$search}%")
                ->orWhere('nuptk', 'like', "%{$search}%")
                ->orWhere('nama', 'like', "%{$search}%")
                // search in golongan/jabatan column for the selected month
                ->orWhereRaw("COALESCE(NULLIF({$golongan_col}, ''), '') LIKE ?", ["%{$search}%"]) 
                ->orWhereRaw("COALESCE(NULLIF({$jabatan_col}, ''), '') LIKE ?", ["%{$search}%"]);
            });
          }
        })
        ->editColumn('nidn', function ($row) {
          return $row->nidn;
        })
        ->editColumn('nuptk', function ($row) {
          return !empty($row->nuptk) ? $row->nuptk : '-';
        })
        ->addColumn('aksi', function ($row) {
          // Determine identifier: prefer NIDN, fallback to NUPTK
          $identifier = !empty($row->nidn) ? $row->nidn : (!empty($row->nuptk) ? $row->nuptk : null);
          if (!$identifier) {
            return '<div class="text-center">-</div>';
          }

          // URL targets: open perubahan-data-dosen view. Use query param to indicate which tab to open.
          $basePerubahanUrl = route('pts.perubahan-data-dosen.show', ['nidn' => $identifier]);
          $urlPengaktifan = $basePerubahanUrl; // default shows Pengaktifan
          $urlPerubahan = $basePerubahanUrl . '?open=perubahan';

          // Icon for active / inactive status
          $statusBtn = '';
          $linkEmpat = 'https://empat.lldikti4.id/login';
          $isActive = false;
          if (isset($row->aktif)) {
            $val = $row->aktif;
            $isActive = ($val === 1 || $val === '1' || strcasecmp($val, 'YA') === 0 || strcasecmp($val, 'Y') === 0);
          }

          if ($isActive) {
            $statusBtn = '<a href="' . $linkEmpat . '" target="_blank" rel="noopener" class="sptjm-icon-btn sptjm-btn-reset" title="Pengaktifan (cek di EMpat)"><i class="bx bx-check"></i></a>';
          } else {
            $statusBtn = '<a href="' . $urlPengaktifan . '" class="sptjm-icon-btn sptjm-btn-delete" title="Tidak Aktif"><i class="bx bx-block"></i></a>';
          }

          // View button opens the regular detail view
          $urlView = route('pts.detail-data-dosen', ['nidn' => $identifier]);
          $viewBtn = '<a href="' . $urlView . '" class="sptjm-icon-btn sptjm-btn-view" title="Lihat"><span class="tf-icons bx bx-show"></span></a>';

          // Edit button opens the "Perubahan Data" tab
          $editBtn = '<a href="' . $urlPerubahan . '" class="sptjm-icon-btn sptjm-btn-edit" title="Edit"><i class="bx bx-edit-alt"></i></a>';

          return $viewBtn . ' ' . $statusBtn . ' ' . $editBtn;
        })
        ->rawColumns(['aksi'])
        ->make(true);
    }

    // Kirim data ke view
    // return view('pts.lihat-data-dosen', compact('dosenList'));
    //pakai datatables
    return view('pts.lihat-data-dosen');
  }

  public function detailDataDosenPTS($nidn)
  {
    $tahun = session('tahun');
    // determine month from session, fallback to 12 if missing/invalid
    $bulan = (int) session('bulan');
    if ($bulan < 1 || $bulan > 12) {
      $bulan = 12;
    }

    $jabCol = "Jabatan{$bulan}";
    $golCol = "Gol{$bulan}";
    $tahunCol = "Tahun{$bulan}";
    $gajiCol = "Gaji{$bulan}";

    // Select the whole row plus computed aliases for the selected month
    $dosen = DB::table('s_transaksi_2')
      ->select(
        's_transaksi_2.*',
        DB::raw("NULLIF({$jabCol}, '') as jabatan"),
        DB::raw("NULLIF({$golCol}, '') as gol"),
        DB::raw("NULLIF({$tahunCol}, '') as masa_kerja"),
        DB::raw("NULLIF({$gajiCol}, '') as gaji")
      )
      // Support lookup by NIDN or NUPTK: allow passing either identifier in the URL
      ->where(function ($q) use ($nidn) {
        $q->where('nidn', $nidn)
          ->orWhere('nuptk', $nidn);
      })
      ->where('Tahun_Versi', $tahun)
      ->first();

    // Reuse the same blade as admin for an identical layout/structure.
    return view('admin.view-data-dosen', ['dosen' => $dosen]);
  }
}