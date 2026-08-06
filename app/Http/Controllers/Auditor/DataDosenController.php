<?php

namespace App\Http\Controllers\Auditor;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class DataDosenController extends Controller
{
  public function index(Request $request)
  {
    $tahun = session('tahun');

    if ($request->ajax()) {
      $query = DB::table('s_transaksi_2')
        ->select([
          'NIDN as nidn',
          'NUPTK as nuptk',
          'Nama as nama',
          'Kode_PT as kode_pt',
          'PTS as pts',
          'Aktif as aktif',
          'Eligible_span as eligible_span',
        ])
        ->where('Tahun_Versi', $tahun)
        ->orderByRaw("(CASE WHEN `Aktif` = '1' THEN 1 ELSE 0 END) DESC")
        ->orderBy('Nama');

      return DataTables::of($query)
        ->editColumn('nidn', function ($row) {
          return !empty($row->nidn) ? $row->nidn : '-';
        })
        ->editColumn('nuptk', function ($row) {
          return !empty($row->nuptk) ? $row->nuptk : '-';
        })
        ->editColumn('aktif', function ($row) {
          $val = $row->aktif ?? null;
          $isActive = ($val === 1 || $val === '1');
          if ($isActive) {
            return '<span class="badge bg-label-success border border-success">Aktif</span>';
          }
          return '<span class="badge bg-label-danger">Tidak Aktif</span>';
        })
        ->addColumn('aksi', function ($row) {
          $identifier = !empty($row->nidn) ? $row->nidn : (!empty($row->nuptk) ? $row->nuptk : null);
          if (!$identifier) {
            return '<div class="text-center">-</div>';
          }

          $urlView = route('auditor.data-dosen.show', ['identifier' => $identifier]);
          return '<a href="' . $urlView . '" class="sptjm-icon-btn sptjm-btn-view" title="Lihat"><span class="tf-icons bx bx-show"></span></a>';
        })
        ->rawColumns(['aksi', 'aktif'])
        ->make(true);
    }

    return view('auditor.data-dosen');
  }

  public function show($identifier)
  {
    $bulanSession = (int) session('bulan') ?: 12;
    $bulanSession = max(1, min(12, $bulanSession));

    $masa_kerja = "NULLIF(Tahun{$bulanSession}, '')";
    $golongan = "NULLIF(Gol{$bulanSession}, '')";
    $jabatan = "NULLIF(Jabatan{$bulanSession}, '')";
    $gaji = "NULLIF(Gaji{$bulanSession}, 0)";

    $dataDosen = Transaksi::where(function ($q) use ($identifier) {
        $q->where('NIDN', $identifier)
          ->orWhere('NUPTK', $identifier);
      })
      ->where('Tahun_Versi', session('tahun'))
      ->select(
        '*',
        DB::raw("$masa_kerja AS masa_kerja"),
        DB::raw("$golongan AS gol"),
        DB::raw("$jabatan AS jabatan"),
        DB::raw("$gaji AS gaji")
      )
      ->first();

    if (!$dataDosen) {
      return redirect()->route('auditor.data-dosen')->with('error', 'Data dosen tidak ditemukan!');
    }

    return view('auditor.view-data-dosen', ['dosen' => $dataDosen]);
  }
}
