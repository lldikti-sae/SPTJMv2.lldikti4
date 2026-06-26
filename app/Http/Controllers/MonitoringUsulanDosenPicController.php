<?php

namespace App\Http\Controllers;

use App\Exports\MonitoringUsulanDosenExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class MonitoringUsulanDosenPicController extends Controller
{
  public function index(Request $request)
  {
    $bulanIndonesia = [
      1 => 'Januari',
      2 => 'Februari',
      3 => 'Maret',
      4 => 'April',
      5 => 'Mei',
      6 => 'Juni',
      7 => 'Juli',
      8 => 'Agustus',
      9 => 'September',
      10 => 'Oktober',
      11 => 'November',
      12 => 'Desember',
    ];

    $awal = (int) $request->input('awalPeriode', 1);
    $akhir = (int) $request->input('akhirPeriode', now()->month);

    if ($awal > $akhir) {
      [$awal, $akhir] = [$akhir, $awal];
    }

    // Ambil email user yang login (sebagai pemegang_wilayah)
    $pemegang_wilayah = Auth::user()->email;

    for ($i = $awal; $i <= $akhir; $i++) {
      $namaBulan = $bulanIndonesia[$i];
      $concatKodeBelumUsulan[] = "MAX(CASE WHEN KodeUsulan$i IS NULL THEN '$namaBulan' END)";
      $concatBulanBelumUsulan[] = "SUM(IF(KodeUsulan$i IS NULL, 1, 0))";
    }
    $kodeBelumUsulan = implode(',', $concatKodeBelumUsulan);
    $bulanBelumUsulan = implode("+", $concatBulanBelumUsulan);
    $dosenList = DB::Table('s_transaksi_2')
      ->select(
        'NIDN',
        DB::raw('MAX(NUPTK) as NUPTK'),
        'Nama',
        'Jenis',
        'Kode_PT',
        'PTS',
        DB::raw("($bulanBelumUsulan)" . " as bulan_belum_usulan"),
        DB::raw("CONCAT_WS(', ',$kodeBelumUsulan)" . " as kode_belum_usulan")
      )
      ->where('aktif', '1')
      ->where('pemegang_wilayah', $pemegang_wilayah)
      ->groupBy("NIDN", "Nama", "Jenis", "Kode_PT", "PTS")
      ->havingRaw("bulan_belum_usulan > 0")
      ->orderBy('bulan_belum_usulan', 'DESC')
      ->get();

    return view('pic.monitoring-usulan-dosen', compact('dosenList', 'bulanIndonesia'));
  }

  public function exportExcelMonitoringDosenUsulan(Request $request)
  {
    set_time_limit(0);
    ini_set('memory_limit', '2048M');
    $tgl = Carbon::now()->format('Ymd_His');
    $export =  Excel::download(new MonitoringUsulanDosenExport($request), 'monitoring-belum-usulan-' . $tgl . '.xlsx');
    return $export;
  }
}