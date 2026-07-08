<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class RiwayatPengajuanPtsController extends Controller
{
  public function index(Request $request)
  {
    // Cek siapa yang login
    if (Auth::guard('pts')->check()) {
      $kodePtsLogin = Auth::guard('pts')->user()->kode_pts;
    } elseif (Auth::guard('web')->check()) {
      $kodePtsLogin = Auth::user()->email;
    } else {
      return redirect()
        ->route('login')
        ->with('error', 'Silakan login terlebih dahulu.');
    }

    $tahun = session('tahun', date('Y'));

    // Query dasar berdasarkan kode_pts dan tahun dari session (fallback: tahun sekarang)
    $query = DB::table('q_sptjm')
      ->where('kode_pts', $kodePtsLogin)
      ->where('tahun', $tahun)
      ->orderByRaw("FIELD(bulan, 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember') DESC")
      ->orderBy('no', 'desc');

    // Jika request dari DataTables (AJAX), kembalikan JSON untuk server-side processing
    if ($request->ajax()) {
      return DataTables::of($query)
        ->addColumn('aksi', function ($row) {
          // determine folder based on id_usulan prefix
          $idUs = isset($row->id_usulan) ? trim($row->id_usulan) : '';
          $prefix2 = strtoupper(substr($idUs, 0, 2));
          $prefix1 = strtoupper(substr($idUs, 0, 1));
          if (in_array($prefix2, ['TB', 'TS'])) {
            $folder = $prefix2 === 'TB' ? 'uploadFile_TUKIN_B' : 'uploadFile_TUKIN_S';
          } else {
            if ($prefix1 === 'B') {
              $folder = 'uploadFile_SPTJM_B';
            } elseif ($prefix1 === 'S') {
              $folder = 'uploadFile_SPTJM_S';
            } else {
              $folder = '';
            }
          }

          $filePath = $row->file ?? '';
          if ($filePath && strpos($filePath, '/') === false && $folder) {
            $filePath = trim($folder, '/') . '/' . ltrim($filePath, '/');
          }

          $fileUrl = asset('storage/' . $filePath);
          $detailUrl = route('pts.detail-riwayat-pengajuan', ['no' => $row->no]);

          return '<a href="' . $fileUrl . '" target="_blank" class="sptjm-icon-btn sptjm-btn-print" title="Lihat PDF" style="margin-right: 10px;">'
            . '<i class="bx bx-file"></i>'
            . '</a>'
            . '<a href="' . $detailUrl . '" class="sptjm-icon-btn sptjm-btn-view" title="Detail">'
            . '<i class="bx bx-info-circle"></i>'
            . '</a>';
        })
        ->rawColumns(['aksi'])
        ->make(true);
    }

    // Non-AJAX: render halaman awal, data akan di-load via DataTables AJAX
    return view('pts.riwayat-pengajuan', compact('tahun'));
  }
}
