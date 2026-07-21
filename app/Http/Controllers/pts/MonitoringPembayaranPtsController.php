<?php

namespace App\Http\Controllers\Pts;

use App\Exports\MonitoringPembayaranExport;
use App\Helpers\SelisihBayar;
use App\Http\Controllers\MonitoringPembayaranController as AdminMonitoringPembayaranController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class MonitoringPembayaranPtsController extends Controller
{
  private const SESSION_KEY = 'pts_monitoring_identifier';

  private function getTransaksiYearColumn(): string
  {
    static $cached = null;
    if ($cached) {
      return $cached;
    }

    $table = 's_transaksi_2';
    foreach (['Tahun_Versi', 'tahun_versi', 'Tahun_versi'] as $col) {
      try {
        if (Schema::hasColumn($table, $col)) {
          $cached = $col;
          return $cached;
        }
      } catch (\Throwable $e) {
        // ignore
      }
    }

    $cached = 'tahun_versi';
    return $cached;
  }

  private function getAvailableYears(): array
  {
    try {
      if (Storage::disk('local')->exists('active_years.json')) {
        $raw = Storage::disk('local')->get('active_years.json');
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
          $years = [];
          foreach ($decoded as $y) {
            $yi = (int) $y;
            if ($yi > 0) {
              $years[] = (string) $yi;
            }
          }
          $years = array_values(array_unique($years));
          sort($years);
          if (!empty($years)) {
            return $years;
          }
        }
      }
    } catch (\Throwable $e) {
      // ignore
    }

    $yearColumn = $this->getTransaksiYearColumn();
    return DB::table('s_transaksi_2')
      ->select($yearColumn)
      ->distinct()
      ->orderBy($yearColumn)
      ->pluck($yearColumn)
      ->map(fn($v) => (string) $v)
      ->toArray();
  }

  private function getDefaultYear(): ?string
  {
    $tahun = trim((string) session('tahun'));
    if ($tahun === '') {
      return null;
    }
    $tahunInt = (int) $tahun;
    return $tahunInt > 0 ? (string) $tahunInt : null;
  }

  private function getIdentifierFromSession(): string
  {
    return trim((string) session(self::SESSION_KEY));
  }

  public function index(Request $request)
  {
    return $this->renderMonitoring($request);
  }

  public function cari(Request $request)
  {
    $request->validate([
      'nidn' => ['required'],
      'start_year' => ['nullable'],
      'end_year' => ['nullable'],
      'tahun_versi' => ['nullable'],
    ]);

    $identifier = trim((string) $request->input('nidn'));
    session([self::SESSION_KEY => $identifier]);

    return $this->renderMonitoring($request);
  }

  public function data(Request $request)
  {
    $identifier = $this->getIdentifierFromSession();
    if ($identifier === '') {
      return response()->json(['success' => false, 'message' => 'Sesi tidak valid atau NIDN/NUPTK belum dicari.']);
    }

    $request->merge([
      'nidn' => $identifier
    ]);

    $ptsUser = Auth::guard('pts')->user();
    $kodePts = trim((string) ($ptsUser->kode_pts ?? ''));

    // Pastikan transaksi milik PTS ini (di tahun mana pun)
    $transaksi = DB::table('s_transaksi_2')
      ->where(function ($q) use ($identifier) {
        $q->whereRaw('TRIM(`NIDN`) = ?', [$identifier])
          ->orWhereRaw('TRIM(`NUPTK`) = ?', [$identifier]);
      })
      ->where('Kode_PT', $kodePts)
      ->first();

    if (!$transaksi) {
      return response()->json(['success' => false, 'message' => 'Data tidak ditemukan untuk PTS Anda.']);
    }

    return app(AdminMonitoringPembayaranController::class)->data($request);
  }

  public function exportExcel(Request $request)
  {
    $request->validate([
      'tahun_versi' => ['required'],
    ]);

    $ptsUser = Auth::guard('pts')->user();
    $kodePts = trim((string) ($ptsUser->kode_pts ?? ''));
    $identifier = $this->getIdentifierFromSession();

    if ($identifier === '') {
      return redirect()->back()->with('error', 'Sesi NIDN/NUPTK tidak valid.');
    }

    $selectedYear = trim((string) $request->input('tahun_versi'));

    $allowedYears = $this->getAvailableYears();
    if (!empty($allowedYears) && !in_array($selectedYear, array_map('strval', $allowedYears), true)) {
      return redirect()->back()->with('error', 'Tahun versi tidak valid.');
    }

    $yearColumn = $this->getTransaksiYearColumn();
    $transaksi = DB::table('s_transaksi_2')
      ->where(function ($q) use ($identifier) {
        $q->whereRaw('TRIM(`NIDN`) = ?', [$identifier])
          ->orWhereRaw('TRIM(`NUPTK`) = ?', [$identifier]);
      })
      ->where('Kode_PT', $kodePts)
      ->where($yearColumn, $selectedYear)
      ->first();

    if (!$transaksi) {
      return redirect()->back()->with('error', 'Data tidak ditemukan untuk NIDN/NUPTK tersebut pada tahun yang dipilih atau bukan milik PTS Anda.');
    }

    $request->merge([
      'nidn' => $identifier,
      'tahun_versi' => $selectedYear,
    ]);

    return app(AdminMonitoringPembayaranController::class)->exportExcel($request);
  }

  public function cetakSpt(Request $request)
  {
    $request->validate([
      'tahun_versi' => ['required'],
    ]);

    $ptsUser = Auth::guard('pts')->user();
    $kodePts = trim((string) ($ptsUser->kode_pts ?? ''));
    $identifier = $this->getIdentifierFromSession();

    if ($identifier === '') {
      return redirect()->back()->with('error', 'Sesi NIDN/NUPTK tidak valid.');
    }

    $tahunVersi = $request->input('tahun_versi') ?? $request->input('cetak_spt_tahun_versi') ?? $request->input('tahunVersi');
    $selectedYear = trim((string) $tahunVersi);

    $allowedYears = $this->getAvailableYears();
    if (!empty($allowedYears) && !in_array($selectedYear, array_map('strval', $allowedYears), true)) {
      return redirect()->back()->with('error', 'Tahun versi tidak valid.');
    }

    $yearColumn = $this->getTransaksiYearColumn();
    $transaksi = DB::table('s_transaksi_2')
      ->where(function ($q) use ($identifier) {
        $q->whereRaw('TRIM(`NIDN`) = ?', [$identifier])
          ->orWhereRaw('TRIM(`NUPTK`) = ?', [$identifier]);
      })
      ->where('Kode_PT', $kodePts)
      ->where($yearColumn, $selectedYear)
      ->first();

    if (!$transaksi) {
      return redirect()->back()->with('error', 'Data tidak ditemukan untuk NIDN/NUPTK tersebut pada tahun yang dipilih atau bukan milik PTS Anda.');
    }

    $request->merge([
      'nidn' => $identifier,
      'tahun_versi' => $selectedYear,
      'cetak_spt_tahun_versi' => $selectedYear,
    ]);

    return app(AdminMonitoringPembayaranController::class)->cetakSpt($request);
  }

  private function renderMonitoring(Request $request)
  {
    $identifier = $this->getIdentifierFromSession();
    if ($identifier !== '') {
        $request->merge(['nidn' => $identifier]);
    }

    $response = $this->data($request);
    $responseData = json_decode($response->getContent());

    if (!isset($responseData->success) || !$responseData->success) {
      $errorMessage = $responseData->message ?? 'Data tidak ditemukan.';
    }

    $dataView = (array) $responseData;
    $dataView['transaksi'] = $dataView['header'] ?? null;
    $dataView['errorMessage'] = $errorMessage ?? null;
    
    $years = $this->getAvailableYears();
    $dataView['years'] = $years;

    $dataView['startYear'] = $request->input('start_year') ?? ($years[0] ?? null);
    $dataView['endYear'] = $request->input('end_year') ?? (end($years) ?? null);
    $dataView['selectedYear'] = $request->input('tahun_versi') ?? $dataView['endYear'];
    $dataView['nidn'] = $identifier;

    if ($dataView['startYear'] > $dataView['endYear']) {
      $tmp = $dataView['startYear'];
      $dataView['startYear'] = $dataView['endYear'];
      $dataView['endYear'] = $tmp;
    }

    $yearsForNidn = [];
    for ($y = (int)$dataView['startYear']; $y <= (int)$dataView['endYear']; $y++) {
      $yearsForNidn[] = (string) $y;
    }
    $dataView['yearsForNidn'] = $yearsForNidn;

    // Convert objects to arrays for blade array access
    $dataView['summary'] = (array) ($dataView['summary'] ?? []);
    $dataView['summaryRekap'] = (array) ($dataView['summaryRekap'] ?? []);
    $dataView['summaryOriginal'] = (array) ($dataView['summaryOriginal'] ?? []);
    $dataView['selisihTotals'] = (array) ($dataView['selisihTotals'] ?? []);
    $dataView['totals'] = (array) ($dataView['totals'] ?? []);
    $dataView['jenisTunjangan'] = strtolower($request->input('jenis_tunjangan', 'semua'));

    return view('pts.monitoring-pembayaran', $dataView);
  }
}
