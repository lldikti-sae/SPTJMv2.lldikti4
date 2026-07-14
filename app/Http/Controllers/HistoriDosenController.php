<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HistoriDosen;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class HistoriDosenController extends Controller
{
  public function index(Request $request)
  {
    // Data is loaded via DataTables AJAX
    return view('admin.histori-dosen');
  }

  public function data(Request $request)
  {
    // Latest histori row per unique identifier (NIDN or NUPTK)
    $identifierExpr = "COALESCE(NULLIF(nidn,''), NULLIF(nuptk,''))";

    $subLatestHistori = DB::table('j_histori_dosen')
      // Only select the aggregated PK to avoid ONLY_FULL_GROUP_BY issues
      // in environments that are strict about functional dependencies.
      ->selectRaw('MAX(no) as latest_no')
      ->whereRaw("{$identifierExpr} IS NOT NULL")
      ->groupByRaw($identifierExpr);

    $query = DB::table('j_histori_dosen as h')
      ->joinSub($subLatestHistori, 'latest', function ($join) {
        $join->on('h.no', '=', 'latest.latest_no');
      })
      ->select([
        'h.no',
        'h.nidn',
        'h.nuptk',
        'h.nama',
        'h.pts',
        'h.aktif',
        'h.pengguna',
        'h.tgl_dokumen_ubah',
      ])
      ->orderByDesc('h.tgl_dokumen_ubah');

    return DataTables::of($query)
      ->editColumn('nidn', function ($row) {
        return !empty($row->nidn) ? $row->nidn : '-';
      })
      ->editColumn('nuptk', function ($row) {
        return !empty($row->nuptk) ? $row->nuptk : '-';
      })
      ->editColumn('aktif', function ($row) {
        $isAktif = (string)($row->aktif ?? '') === '1';
        return $isAktif
          ? '<span class="badge bg-label-primary">Aktif</span>'
          : '<span class="badge bg-label-danger">Tidak Aktif</span>';
      })
      ->editColumn('tgl_dokumen_ubah', function ($row) {
        if (empty($row->tgl_dokumen_ubah)) return '-';
        try {
          return date('d-m-Y', strtotime($row->tgl_dokumen_ubah));
        } catch (\Throwable $e) {
          return (string) $row->tgl_dokumen_ubah;
        }
      })
      ->addColumn('aksi', function ($row) {
        $identifier = null;
        if (!empty($row->nidn)) {
          $identifier = $row->nidn;
        } elseif (!empty($row->nuptk)) {
          $identifier = $row->nuptk;
        }
        if (!$identifier) {
          return '<span class="text-muted">-</span>';
        }
        $url = route('admin.lihat.histori.dosen', ['nidn' => $identifier]);
        return '<a href="' . $url . '" class="sptjm-icon-btn sptjm-btn-view" title="Lihat Histori"><i class="bx bx-show"></i></a>';
      })
      ->rawColumns(['aktif', 'aksi'])
      ->make(true);
  }

  public function show($nidn)
  {
    // Support lookup by NIDN or NUPTK: allow passing either identifier in the URL
    $dosen = HistoriDosen::where(function($q) use ($nidn) {
        $q->where('nidn', $nidn)
          ->orWhere('nuptk', $nidn);
      })
      ->orderBy('created_at', 'DESC')
      ->get();

    return view('admin.lihat-histori-dosen', compact('dosen'));
  }
}
