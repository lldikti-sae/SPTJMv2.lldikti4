<?php

namespace App\Http\Controllers;

use App\Exports\MonitoringPembayaranExport;
use App\Helpers\SelisihBayar;
use App\Http\Controllers\MonitoringPembayaranController as AdminMonitoringPembayaranController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class MonitoringPembayaranPicController extends Controller
{
  private function picEmail(): string
  {
    $user = Auth::guard('web')->user();
    return trim((string) ($user->email ?? ''));
  }

  public function index()
  {
    $email = $this->picEmail();
    if ($email === '') {
      abort(403);
    }

    $years = DB::table('s_transaksi_2')
      ->select('tahun_versi')
      ->whereRaw('TRIM(`Pemegang_Wilayah`) = ?', [$email])
      ->distinct()
      ->orderBy('tahun_versi')
      ->pluck('tahun_versi')
      ->toArray();

    return view('pic.monitoring-pembayaran', compact('years'));
  }

  public function cari(Request $request)
  {
    $email = $this->picEmail();
    if ($email === '') {
      abort(403);
    }

    $response = $this->data($request);
    $responseData = json_decode($response->getContent());

    if (!isset($responseData->success) || !$responseData->success) {
      return redirect()->back()->with('error', $responseData->message ?? 'Data tidak ditemukan.');
    }

    $dataView = (array) $responseData;
    $dataView['transaksi'] = $dataView['header'] ?? null;
    
    $availableYears = DB::table('s_transaksi_2')
      ->select('tahun_versi')
      ->whereRaw('TRIM(`Pemegang_Wilayah`) = ?', [$email])
      ->distinct()
      ->orderBy('tahun_versi')
      ->pluck('tahun_versi')
      ->toArray();

    $startYear = $request->input('start_year') ?? ($availableYears[0] ?? null);
    $endYear = $request->input('end_year') ?? (end($availableYears) ?? null);

    if ($startYear > $endYear) {
      $tmp = $startYear;
      $startYear = $endYear;
      $endYear = $tmp;
    }

    $yearsForNidn = [];
    for ($y = (int)$startYear; $y <= (int)$endYear; $y++) {
      $yearsForNidn[] = (string) $y;
    }

    $dataView['years'] = $availableYears;
    $dataView['yearsForNidn'] = $yearsForNidn;
    $dataView['startYear'] = $startYear;
    $dataView['endYear'] = $endYear;
    $dataView['nidn'] = $request->input('nidn');

    // Convert objects to arrays for blade array access
    $dataView['summary'] = (array) ($dataView['summary'] ?? []);
    $dataView['summaryRekap'] = (array) ($dataView['summaryRekap'] ?? []);
    $dataView['summaryOriginal'] = (array) ($dataView['summaryOriginal'] ?? []);
    $dataView['selisihTotals'] = (array) ($dataView['selisihTotals'] ?? []);
    $dataView['totals'] = (array) ($dataView['totals'] ?? []);

    return view('pic.monitoring-pembayaran', $dataView);
  }

  public function data(Request $request)
  {
    $email = $this->picEmail();
    if ($email === '') {
      abort(403);
    }

    $nidn = $request->input('nidn');

    // Pastikan transaksi milik wilayah PIC ini (di tahun mana pun)
    $transaksi = DB::table('s_transaksi_2')
      ->where(function ($q) use ($nidn) {
        $q->where('nidn', $nidn)
          ->orWhere('NUPTK', $nidn);
      })
      ->whereRaw('TRIM(`Pemegang_Wilayah`) = ?', [$email])
      ->first();

    if (!$transaksi) {
      return response()->json(['success' => false, 'message' => 'Data tidak ditemukan untuk wilayah Anda.']);
    }

    return app(AdminMonitoringPembayaranController::class)->data($request);
  }

  public function exportExcel(Request $request)
  {
    $email = $this->picEmail();
    if ($email === '') {
      abort(403);
    }

    $request->validate([
      'nidn' => ['required', 'string'],
      'tahun_versi' => ['required'],
    ]);

    $nidn = trim((string) $request->input('nidn'));
    $selectedYear = trim((string) $request->input('tahun_versi'));

    $transaksi = DB::table('s_transaksi_2')
      ->where(function ($q) use ($nidn) {
        $q->where('nidn', $nidn)
          ->orWhere('NUPTK', $nidn);
      })
      ->whereRaw('TRIM(`Pemegang_Wilayah`) = ?', [$email])
      ->where('tahun_versi', $selectedYear)
      ->first();

    if (!$transaksi) {
      return redirect()->back()->with('error', 'Data tidak ditemukan untuk wilayah Anda.');
    }

    $monthNames = [
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

    $rows = [];
    $totals = [
      'gaji' => 0,
      'kotorTpd' => 0,
      'kotorTkgb' => 0,
      'pajakTpd' => 0,
      'pajakTkgb' => 0,
      'bersihTpd' => 0,
      'bersihTkgb' => 0,
    ];

    for ($m = 1; $m <= 12; $m++) {
      $kodeUsulan = $transaksi?->{'KodeUsulan' . $m} ?? '-';
      $gol = $transaksi?->{'Gol' . $m} ?? '-';
      $tahun = $transaksi?->{'Tahun' . $m} ?? '-';

      $gaji = (float) ($transaksi?->{'Gaji' . $m} ?? 0);
      $kotorTpd = (float) ($transaksi?->{'TPD' . $m} ?? 0);
      $kotorTkgb = (float) ($transaksi?->{'TKGB' . $m} ?? 0);
      $pajakTpd = (float) ($transaksi?->{'nilaiPajakTPD' . $m} ?? 0);
      $pajakTkgb = (float) ($transaksi?->{'nilaiPajakTKGB' . $m} ?? 0);
      $bersihTpd = (float) ($transaksi?->{'bersihTPD' . $m} ?? 0);
      $bersihTkgb = (float) ($transaksi?->{'bersihTKGB' . $m} ?? 0);

      $noSp2d = $transaksi?->{'No_sp2d_' . $m} ?? '-';
      $tglSp2d = $transaksi?->{'Tgl_sp2d_' . $m} ?? '-';

      $totals['gaji'] += $gaji;
      $totals['kotorTpd'] += $kotorTpd;
      $totals['kotorTkgb'] += $kotorTkgb;
      $totals['pajakTpd'] += $pajakTpd;
      $totals['pajakTkgb'] += $pajakTkgb;
      $totals['bersihTpd'] += $bersihTpd;
      $totals['bersihTkgb'] += $bersihTkgb;

      $rows[] = [
        $selectedYear,
        $monthNames[$m],
        $kodeUsulan,
        $gol . ' - ' . $tahun,
        (int) round($gaji),
        (int) round($kotorTpd),
        (int) round($kotorTkgb),
        (int) round($pajakTpd),
        (int) round($pajakTkgb),
        (int) round($bersihTpd),
        (int) round($bersihTkgb),
        $noSp2d,
        $tglSp2d,
      ];
    }

    $selisihTotals = SelisihBayar::computeFromTransaksi($transaksi);

    $rows[] = [
      $selectedYear,
      'Jumlah',
      '-',
      '-',
      (int) round($totals['gaji']),
      (int) round($totals['kotorTpd']),
      (int) round($totals['kotorTkgb']),
      (int) round($totals['pajakTpd']),
      (int) round($totals['pajakTkgb']),
      (int) round($totals['bersihTpd']),
      (int) round($totals['bersihTkgb']),
      '-',
      '-',
    ];

    $rows[] = [
      $selectedYear,
      'Jumlah Selisih Bayar',
      '-',
      '-',
      '-',
      (int) round((float) ($selisihTotals['selisihTpd'] ?? 0)),
      (int) round((float) ($selisihTotals['selisihTkgb'] ?? 0)),
      (int) round((float) ($selisihTotals['selisihPajakTpd'] ?? 0)),
      (int) round((float) ($selisihTotals['selisihPajakTkgb'] ?? 0)),
      (int) round((float) ($selisihTotals['selisihBersihTpd'] ?? 0)),
      (int) round((float) ($selisihTotals['selisihBersihTkgb'] ?? 0)),
      '-',
      '-',
    ];

    $fileName = 'monitoring-pembayaran_' . $nidn . '_' . $selectedYear . '.xlsx';
    return Excel::download(new MonitoringPembayaranExport($rows), $fileName);
  }

  public function cetakSpt(Request $request)
  {
    $email = $this->picEmail();
    if ($email === '') {
      abort(403);
    }

    $request->validate([
      'nidn' => ['required', 'string'],
      'tahun_versi' => ['required'],
    ]);

    $nidn = trim((string) $request->input('nidn'));
    $tahunVersi = $request->input('tahun_versi') ?? $request->input('cetak_spt_tahun_versi') ?? $request->input('tahunVersi');
    $selectedYear = trim((string) $tahunVersi);

    $transaksi = DB::table('s_transaksi_2')
      ->where(function ($q) use ($nidn) {
        $q->where('nidn', $nidn)
          ->orWhere('NUPTK', $nidn);
      })
      ->whereRaw('TRIM(`Pemegang_Wilayah`) = ?', [$email])
      ->where('tahun_versi', $selectedYear)
      ->first();

    if (!$transaksi) {
      return redirect()->back()->with('error', 'Data tidak ditemukan untuk wilayah Anda.');
    }

    // Delegate PDF rendering to the existing Admin implementation.
    // Force nidn/tahun_versi to the validated values to avoid request tampering.
    $request->merge([
      'nidn' => $nidn,
      'tahun_versi' => $selectedYear,
      'cetak_spt_tahun_versi' => $selectedYear,
    ]);

    return app(AdminMonitoringPembayaranController::class)->cetakSpt($request);
  }
}
