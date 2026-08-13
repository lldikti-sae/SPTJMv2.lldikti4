<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class CekDataDosenPtsController extends Controller
{
  public function index()
  {
    // Ambil kode_pts dari user yang login via guard pts
    $kodePts = Auth::guard('pts')->user()->kode_pts;
    $tahun = session('tahun');
    $bulan = (int) session('bulan');
    if ($bulan < 1 || $bulan > 12) {
      $bulan = 12;
    }

    $masaKerjaCol = "Tahun{$bulan}";
    $golonganCol = "Gol{$bulan}";
    $jabatanCol = "Jabatan{$bulan}";

    //datatables
    if (request()->ajax()) {
      // Query utama dengan join ke 3 tabel BKD, dan filter sesuai halaman native (M/null/empty)
      $dataDosen = DB::table('s_transaksi_2 as d')
        ->leftJoin('p_sister_genap as genap_tl', 'd.nidn', '=', 'genap_tl.nidn')
        ->leftJoin('p_sister_ganjil as ganjil', 'd.nidn', '=', 'ganjil.nidn')
        ->leftJoin('p_sister_genap as genap_bj', 'd.nidn', '=', 'genap_bj.nidn')
        ->select(
          'd.*',
          DB::raw("NULLIF(d.{$golonganCol}, '') as gol"),
          DB::raw("NULLIF(d.{$masaKerjaCol}, '') as masa_kerja"),
          DB::raw("NULLIF(d.{$jabatanCol}, '') as jabatan"),
          'genap_tl.kesimpulan_bkd as bkd_genap_tl',
          'ganjil.kesimpulan_bkd as bkd_ganjil',
          'genap_bj.kesimpulan_bkd as bkd_genap_bj'
        )
        ->where('d.kode_pt', $kodePts)
        ->where('d.Tahun_Versi', $tahun)
        ->where(function ($q) {
          $q->where('genap_bj.kesimpulan_bkd', 'M')
            ->orWhereNull('genap_bj.kesimpulan_bkd')
            ->orWhere('genap_bj.kesimpulan_bkd', '=','');
        })
        ->where(function ($q) {
          $q->where('genap_tl.kesimpulan_bkd', 'M')
            ->orWhereNull('genap_tl.kesimpulan_bkd')
            ->orWhere('genap_tl.kesimpulan_bkd', '=','');
        })
        ->where(function ($q) {
          $q->where('ganjil.kesimpulan_bkd', 'M')
            ->orWhereNull('ganjil.kesimpulan_bkd')
            ->orWhere('ganjil.kesimpulan_bkd', '=','');
        })
        // Tampilkan dosen aktif dulu lalu urutkan berdasarkan nama
        ->orderByDesc('d.Aktif')
        ->orderBy('d.nama');
        // Jika ada pencarian global dari DataTables, tambahkan filter ke beberapa kolom
        $search = request()->input('search.value');
        if (!empty($search)) {
        $dataDosen = $dataDosen->where(function ($q) use ($search, $golonganCol, $masaKerjaCol, $jabatanCol) {
            $q->where('d.NIDN', 'like', '%' . $search . '%')
              ->orWhere('d.NUPTK', 'like', '%' . $search . '%')
              ->orWhere('d.Nama', 'like', '%' . $search . '%')
            ->orWhere('d.Keterangan', 'like', '%' . $search . '%')
            // cari juga di kolom bulanan (golongan/masa kerja/jabatan)
            ->orWhereRaw("COALESCE(NULLIF(d.{$golonganCol}, ''), '') LIKE ?", ['%' . $search . '%'])
            ->orWhereRaw("COALESCE(NULLIF(d.{$masaKerjaCol}, ''), '') LIKE ?", ['%' . $search . '%'])
            ->orWhereRaw("COALESCE(NULLIF(d.{$jabatanCol}, ''), '') LIKE ?", ['%' . $search . '%'])
            // cari juga di kolom BKD join
            ->orWhere('genap_tl.kesimpulan_bkd', 'like', '%' . $search . '%')
            ->orWhere('ganjil.kesimpulan_bkd', 'like', '%' . $search . '%')
            ->orWhere('genap_bj.kesimpulan_bkd', 'like', '%' . $search . '%');
          });
        }
      $dt = DataTables::of($dataDosen);

      // custom filter untuk kolom alias yang dibangun dari kolom bulanan
      $dt->filterColumn('gol', function ($query, $keyword) use ($golonganCol) {
        $query->whereRaw("COALESCE(NULLIF(d.{$golonganCol}, ''), '') LIKE ?", ["%{$keyword}%"]);
      });
      $dt->filterColumn('masa_kerja', function ($query, $keyword) use ($masaKerjaCol) {
        $query->whereRaw("COALESCE(NULLIF(d.{$masaKerjaCol}, ''), '') LIKE ?", ["%{$keyword}%"]);
      });
      $dt->filterColumn('jabatan', function ($query, $keyword) use ($jabatanCol) {
        $query->whereRaw("COALESCE(NULLIF(d.{$jabatanCol}, ''), '') LIKE ?", ["%{$keyword}%"]);
      });

      return $dt
        ->addIndexColumn()
        ->editColumn('aktif', function ($row) {
          if ($row->Aktif == 1) {
            return '<span class="badge bg-label-success border border-success">Aktif</span>';
          }
          return '<span class="badge bg-label-danger">Tidak Aktif</span>';
        })
        ->addColumn('lihat', function ($row) {
          $identifier = $row->NIDN ?? $row->nidn ?? $row->NUPTK ?? $row->nuptk ?? null;
          if ($identifier) {
            $url = route('pts.detail-data-dosen', $identifier);
            return '<a href="' . $url . '" class="sptjm-icon-btn sptjm-btn-view" title="Lihat"><span class="tf-icons bx bx-show"></span></a>';
          }
          return '<span class="sptjm-icon-btn sptjm-btn-secondary disabled" title="Lihat"><span class="tf-icons bx bx-show"></span></span>';
        })
        ->rawColumns(['aktif', 'lihat'])
        ->make(true);
    }
    // Kirim data ke view
    return view('pts.cek-data-dosen', compact('tahun'));
  }
}
