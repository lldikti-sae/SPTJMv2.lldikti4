<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use App\Exports\MonitoringUsulanDosenExport;
use Maatwebsite\Excel\Facades\Excel;

class MonitoringUsulanDosenController extends Controller
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

    $awal = max(1, min(12, (int) $request->input('awalPeriode', 1)));
    $akhir = max(1, min(12, (int) $request->input('akhirPeriode', now()->month)));

    //swap nilai
    if ($awal > $akhir) {
      [$awal, $akhir] = [$akhir, $awal];
    }

    $search = $request->filled('search') ? trim((string) $request->input('search')) : '';

    $allowedPerPage = [10, 15, 25, 50, 100, 500];
    $perPage = (int) $request->input('perPage', 10);
    if (!in_array($perPage, $allowedPerPage, true)) {
      $perPage = 10;
    }

    $currentPage = max(1, (int) $request->input('page', 1));
    $dosenList = $this->buildMonitoringPageInChunks($awal, $akhir, $bulanIndonesia, $search, $perPage, $currentPage);

    $dosenList->onEachSide(1);
    $dosenList->appends($request->query());

    return view('admin.monitoring-usulan-dosen', compact('dosenList', 'bulanIndonesia'));
  }

  public function exportExcel(Request $request)
  {
    $namaFile = 'monitoring_dosen_belum_usulan_' . now()->format('Ymd_His') . '.xlsx';
    return Excel::download(new MonitoringUsulanDosenExport($request), $namaFile);
  }

  private function buildMonitoringPageInChunks(
    int $awal,
    int $akhir,
    array $bulanIndonesia,
    string $search,
    int $perPage,
    int $currentPage
  )
  {
    $tahun = (string) session('tahun');

    $activePts = DB::table('a_pts')
      ->where('aktif', '1')
      ->pluck('kode_pts')
      ->filter()
      ->values()
      ->all();

    if (empty($activePts)) {
      return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage, $currentPage);
    }

    $bulanCaseStr = [];
    for ($i = $awal; $i <= $akhir; $i++) {
      $bulanCaseStr[] = "(CASE WHEN s_transaksi_2.KodeUsulan{$i} IS NULL OR s_transaksi_2.KodeUsulan{$i} = '' THEN 1 ELSE 0 END)";
    }
    $bulanBelumExpr = implode(' + ', $bulanCaseStr);

    $query = DB::table('s_transaksi_2')
      ->select(
        's_transaksi_2.no as no',
        's_transaksi_2.NIDN',
        's_transaksi_2.NUPTK',
        's_transaksi_2.Nama',
        's_transaksi_2.Jenis',
        's_transaksi_2.Kode_PT',
        's_transaksi_2.PTS',
        DB::raw("({$bulanBelumExpr}) as bulan_belum_usulan")
      )
      ->where('s_transaksi_2.Aktif', '1')
      ->where('s_transaksi_2.tahun_versi', $tahun)
      ->whereIn('s_transaksi_2.Kode_PT', $activePts);

    for ($i = $awal; $i <= $akhir; $i++) {
      $query->addSelect('s_transaksi_2.KodeUsulan' . $i);
    }

    if ($search !== '') {
      $query->where(function ($q) use ($search) {
        $q->where('s_transaksi_2.NIDN', 'like', "%{$search}%")
          ->orWhere('s_transaksi_2.NUPTK', 'like', "%{$search}%")
          ->orWhere('s_transaksi_2.Nama', 'like', "%{$search}%")
          ->orWhere('s_transaksi_2.PTS', 'like', "%{$search}%")
          ->orWhere('s_transaksi_2.Kode_PT', 'like', "%{$search}%");
      });
    }

    $query->having('bulan_belum_usulan', '>', 0);
    $query->orderBy('bulan_belum_usulan', 'DESC')->orderBy('s_transaksi_2.Nama', 'ASC');

    $paginator = $query->paginate($perPage, ['*'], 'page', $currentPage);

    $paginator->getCollection()->transform(function ($row) use ($awal, $akhir, $bulanIndonesia) {
      $bulanKosong = [];
      for ($i = $awal; $i <= $akhir; $i++) {
        $kolom = 'KodeUsulan' . $i;
        if (empty($row->$kolom)) {
          $bulanKosong[] = $bulanIndonesia[$i];
        }
      }
      $row->kode_belum_usulan = implode(', ', $bulanKosong);
      return $row;
    });

    return $paginator;
  }
}
