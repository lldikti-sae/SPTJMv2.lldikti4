<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UsulanSptjmController extends Controller
{
  public function index()
  {
    return view('admin.usulan-sptjm');
  }

  public function getData(Request $request)
  {
    $tipeSptjm = $request->input('pilihsptjm');
    $bulan = $request->input('bulan');
    $status = $request->input('status');
    $currentYear = session('tahun') ?? date('Y'); // prefer session year

    // Helper closure to build base query with current filters
    $getQueryForCounts = function() use ($tipeSptjm, $bulan, $currentYear) {
      $q = DB::table('q_sptjm')->where('tahun', $currentYear);

      if ($tipeSptjm !== 'All') {
        switch ($tipeSptjm) {
          case 'SPTJM Berjalan':
            $q->where('id_usulan', 'LIKE', 'B%')
              ->whereRaw("id_usulan NOT LIKE 'BT%'");
            break;
          case 'SPTJM Susulan':
            $q->where('id_usulan', 'LIKE', 'S%')
              ->whereRaw("id_usulan NOT LIKE 'ST%'");
            break;
          case 'TUKIN Berjalan':
            $q->where('id_usulan', 'LIKE', 'BT%');
            break;
          case 'TUKIN Susulan':
            $q->where('id_usulan', 'LIKE', 'ST%');
            break;
        }
      }

      if ($bulan !== 'All') {
        $q->where('bulan', $bulan);
      }

      return $q;
    };

    // Calculate real-time counts under current filters
    $countUsulan = (clone $getQueryForCounts())->where('status', 'Usulan')->count();
    $countValidasi = (clone $getQueryForCounts())->where('status', 'Validasi')->count();
    $countProses = (clone $getQueryForCounts())->where('status', 'Proses')->count();
    $countSelesai = (clone $getQueryForCounts())->where('status', 'Selesai')->count();

    $countTolakMain = (clone $getQueryForCounts())->where('status', 'Tolak')->count();
    $countTolakZero = DB::table('q_sptjm')
      ->where('id_usulan', 0)
      ->where('status', 'Tolak')
      ->where('tahun', $currentYear)
      ->when($bulan !== 'All', function ($q) use ($bulan) {
        return $q->where('bulan', $bulan);
      })
      ->count();
    $countTolak = $countTolakMain + $countTolakZero;

    // Get main data
    $query = $getQueryForCounts();
    if (!empty($status) && $status !== 'All') {
      $query->where('status', $status);
    }
    $dataUtama = $query->get();

    // Get zero ID usulan if status is Tolak
    $dataZero = collect();
    if ($status === 'Tolak') {
      $dataZero = DB::table('q_sptjm')
        ->where('id_usulan', 0)
        ->where('status', 'Tolak')
        ->where('tahun', $currentYear)
        ->when($bulan !== 'All', function ($q) use ($bulan) {
          return $q->where('bulan', $bulan);
        })
        ->get();
    }

    $dataUsulan = $dataUtama->merge($dataZero);

    return response()->json([
      'success' => true,
      'data' => $dataUsulan,
      'counts' => [
        'Usulan' => $countUsulan,
        'Validasi' => $countValidasi,
        'Proses' => $countProses,
        'Selesai' => $countSelesai,
        'Tolak' => $countTolak
      ]
    ]);
  }
}