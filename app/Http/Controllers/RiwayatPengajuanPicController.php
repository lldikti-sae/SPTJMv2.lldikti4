<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RiwayatPengajuanPicController extends Controller
{
  public function index(Request $request)
  {
    // Fetch riwayat pengajuan from q_sptjm filtered by wilayah of logged-in user
    $pemegangWilayah = Auth::user()->email; // project convention: user's email stores wilayah key
    $tahunActive = session('tahun', date('Y'));

    $riwayatsQuery = DB::table('q_sptjm')
      ->select(
        'id_usulan',
        'tanggal_usulan',
        'bulan',
        'nama_pts',
        'status',
        'alasan_penolakan',
        'wilayah',
        'tahun',
        DB::raw('MIN(no) as no'),
        DB::raw('MIN(file) as file'),
        DB::raw('MIN(nidn) as nidn')
      )
      ->where('wilayah', $pemegangWilayah)
      ->where('tahun', $tahunActive)
      ->groupBy('id_usulan', 'tanggal_usulan', 'bulan', 'nama_pts', 'status', 'alasan_penolakan', 'wilayah', 'tahun')
      ->orderByRaw("FIELD(bulan, 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember') DESC")
      ->orderBy('tanggal_usulan', 'desc');

    // Remove hard limit to return all matching records for the selected wilayah and year
    $riwayats = $riwayatsQuery->get();

    return view('pic.riwayat-pengajuan', compact('riwayats'));
  }
}
