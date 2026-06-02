<?php

namespace App\Http\Controllers;

use App\Helpers\ErrorAlias;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class KekuranganBayarController extends Controller
{
  private function parseMoney($value): float
  {
    if ($value === null) return 0.0;
    if (is_int($value) || is_float($value)) return (float) $value;
    
    $text = trim((string) $value);
    if ($text === '' || $text === '-') return 0.0;
    
    // Optimasi: Lebih cepat dari regex untuk format ribuan standar "1.234.567"
    $text = str_replace(['.', ','], '', $text);
    
    if (!is_numeric($text)) {
        $text = preg_replace('/[^0-9\-]/', '', $text);
    }
    
    if ($text === '' || $text === '-') return 0.0;
    
    return (float) $text;
  }

  private function insertRekapRow(array $payload): void
  {
    try {
      DB::table('rekap_kekurangan')->insert($payload);
    } catch (\Throwable $e) {
      DB::table('u_rekap_kekurangan')->insert($payload);
    }
  }

  private function ensurePublicFolder(string $folderName): string
  {
    $disk = Storage::disk('public');
    if (!$disk->exists($folderName)) {
      $disk->makeDirectory($folderName);
    }
    // Also ensure web-accessible directory exists (non-symlink deployments)
    $webDir = public_path('storage/' . $folderName);
    if (!is_dir($webDir)) {
      @mkdir($webDir, 0755, true);
    }
    return $folderName;
  }

  /**
   * Save content to both Storage::disk('public') AND the web-accessible public/storage/ folder.
   * Ensures files are downloadable in deployments where public/storage is NOT a symlink.
   */
  private function putPublicFile(string $relativePath, string $content): void
  {
    Storage::disk('public')->put($relativePath, $content);
    $webPath = public_path('storage/' . $relativePath);
    $webDir = dirname($webPath);
    if (!is_dir($webDir)) {
      @mkdir($webDir, 0755, true);
    }
    file_put_contents($webPath, $content);
  }

  private function toExcelTsv(array $rows, string $tipe): string
  {
    $lines = [];
    $header = ['NIDN', 'Nama', 'Jenis', 'Bank'];

    if ($tipe === 'Semua' || $tipe === 'TPD') {
      for ($i = 1; $i <= 12; $i++) {
        $header[] = "TPD{$i}";
      }
    }
    if ($tipe === 'Semua' || $tipe === 'TKGB') {
      for ($i = 1; $i <= 12; $i++) {
        $header[] = "TKGB{$i}";
      }
    }

    $header = array_merge($header, ['Jumlah TPD', 'Jumlah TKGB', 'Pajak TPD', 'Pajak TKGB', 'Total Bersih']);
    $lines[] = implode("\t", $header);

    foreach ($rows as $row) {
      $cols = [
        $row->NIDN ?? $row->nidn ?? '',
        $row->Nama ?? $row->nama ?? '',
        $row->Jenis ?? $row->jenis ?? '',
        $row->Bank ?? $row->bank ?? '',
      ];

      if ($tipe === 'Semua' || $tipe === 'TPD') {
        for ($i = 1; $i <= 12; $i++) {
          $key = 'k_tpd' . $i;
          $cols[] = $row->$key ?? 0;
        }
      }
      if ($tipe === 'Semua' || $tipe === 'TKGB') {
        for ($i = 1; $i <= 12; $i++) {
          $key = 'k_tkgb' . $i;
          $cols[] = $row->$key ?? 0;
        }
      }

      $cols[] = $row->jml_tpd ?? 0;
      $cols[] = $row->jml_tkgb ?? 0;
      $cols[] = $row->nilai_pjk_tpd ?? 0;
      $cols[] = $row->nilai_pjk_tkgb ?? 0;
      $cols[] = $row->bersih ?? 0;

      $lines[] = implode("\t", $cols);
    }

    return implode("\n", $lines) . "\n";
  }

  private function toExcelHtmlLikeTable(array $rows, array $tarifMap): string
  {
    $fmt = function ($value): string {
      $num = (float) ($value ?? 0);
      return number_format((int) round($num), 0, ',', '.');
    };

    $bg = function (float $diff): string {
      if ($diff == 0.0) return '';
      return $diff > 0 ? 'background-color:#d4edda;' : 'background-color:#f8d7da;';
    };

    $escape = function ($text): string {
      return htmlspecialchars((string) ($text ?? ''), ENT_QUOTES, 'UTF-8');
    };

    $months = [
      1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
      7 => 'Jul', 8 => 'Ags', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
    ];

    $html = [];
    $html[] = '<html><head><meta charset="utf-8" />';
    $html[] = '<style>';
    $html[] = 'table{border-collapse:collapse;font-family:Calibri,Arial,sans-serif;font-size:11pt;}';
    $html[] = 'th,td{border:1px solid #333;padding:4px;vertical-align:middle;}';
    $html[] = 'th{text-align:center;font-weight:bold;background-color:#f2f2f2;}';
    $html[] = 'td.num{text-align:right;}';
    $html[] = '</style></head><body>';
    $html[] = '<table>';

    $html[] = '<tr>'
      . '<th rowspan="3">No</th>'
      . '<th rowspan="3">NIDN</th>'
      . '<th rowspan="3">Nama</th>'
      . '<th rowspan="3">Jenis</th>'
      . '<th rowspan="3">Jabatan</th>'
      . '<th rowspan="3">Status</th>'
      . '<th rowspan="3">No Rekening</th>'
      . '<th rowspan="3">Nama Rekening</th>'
      . '<th rowspan="3">NPWP</th>'
      . '<th colspan="48">Januari - Desember</th>'
      . '<th colspan="11">Jumlah Kotor, Nilai Pajak, dan Bersih</th>'
      . '</tr>';

    $row2 = '<tr>';
    foreach ($months as $label) {
      $row2 .= '<th colspan="2">' . $label . '</th>';
      $row2 .= '<th colspan="2">' . $label . ' (Aktual)</th>';
    }
    $row2 .= '<th colspan="2">Jumlah Kotor</th>';
    $row2 .= '<th colspan="2">Nilai Pajak</th>';
    $row2 .= '<th rowspan="2">Bersih</th>';
    $row2 .= '<th colspan="2">Jumlah Kotor (Aktual)</th>';
    $row2 .= '<th colspan="2">Nilai Pajak (Aktual)</th>';
    $row2 .= '<th rowspan="2">Bersih (Aktual)</th>';
    $row2 .= '<th rowspan="2">Kesimpulan</th>';
    $row2 .= '</tr>';
    $html[] = $row2;

    $row3 = '<tr>';
    for ($i = 1; $i <= 12; $i++) {
      $row3 .= '<th>TPD</th><th>TKGB</th><th>TPD</th><th>TKGB</th>';
    }
    $row3 .= '<th>TPD</th><th>TKGB</th><th>TPD</th><th>TKGB</th>';
    $row3 .= '<th>TPD</th><th>TKGB</th><th>TPD</th><th>TKGB</th>';
    $row3 .= '</tr>';
    $html[] = $row3;

    $no = 1;
    $grandBersih = 0.0;
    $grandAktBersih = 0.0;
    $grandKesimpulan = 0.0;
    foreach ($rows as $r) {
      $jenisRow = (string) ($r->Jenis ?? $r->jenis ?? '');
      $jenisKey = trim($jenisRow);

      $sumDbKotorTPD = 0.0; $sumDbKotorTKGB = 0.0; $sumDbPajakTPD = 0.0; $sumDbPajakTKGB = 0.0; $sumDbBersih = 0.0;
      $sumAktKotorTPD = 0.0; $sumAktKotorTKGB = 0.0; $sumAktPajakTPD = 0.0; $sumAktPajakTKGB = 0.0; $sumAktBersih = 0.0;

      $status = ((int) ($r->Aktif ?? 0)) === 1 ? 'Aktif' : 'Tidak Aktif';
      $jabatan12 = (string) ($r->Jabatan12 ?? '');

      $tr = '<tr>'
        . '<td class="num">' . $no . '</td>'
        . '<td>' . $escape($r->NIDN ?? $r->nidn ?? '') . '</td>'
        . '<td>' . $escape($r->Nama ?? $r->nama ?? '') . '</td>'
        . '<td>' . $escape($jenisRow) . '</td>'
        . '<td>' . $escape($jabatan12) . '</td>'
        . '<td>' . $escape($status) . '</td>'
        . '<td>' . $escape($r->No_Rekening ?? '') . '</td>'
        . '<td>' . $escape($r->Nama_Rekening ?? '') . '</td>'
        . '<td>' . $escape($r->NPWP ?? '') . '</td>';

      for ($i = 1; $i <= 12; $i++) {
        $noSp2d = trim((string) ($r->{'No_sp2d_' . $i} ?? $r->{'NoSP2D' . $i} ?? ''));
        $tglSp2d = trim((string) ($r->{'Tgl_sp2d_' . $i} ?? $r->{'TglSP2D' . $i} ?? ''));
        $sp2dOk = ($noSp2d !== '' && $tglSp2d !== '');

        $dbKotorTPD = 0.0; $dbKotorTKGB = 0.0; $aktKotorTPD = 0.0; $aktKotorTKGB = 0.0; $tarif = 0.0; $kenaTKGB = false;

        if ($sp2dOk) {
          $dbKotorTPD = (float) $this->parseMoney($r->{'TPD' . $i} ?? 0);
          $dbKotorTKGB = (float) $this->parseMoney($r->{'TKGB' . $i} ?? 0);

          $gol = trim((string) ($r->{'Gol' . $i} ?? ''));
          $jabatan = (string) ($r->{'Jabatan' . $i} ?? $jabatan12);
          $kenaTKGB = $this->isGuruBesarAtauProfesor($jabatan);

          $gaji = (float) $this->parseMoney($r->{'Gaji' . $i} ?? 0);
          [$aktKotorTPD, $aktKotorTKGB] = $this->splitAktualKotorFromGaji($gaji, $kenaTKGB);

          $tarif = (float) (($tarifMap[$jenisKey][$gol] ?? 0) ?: 0);
        }

        $diffTpd = $dbKotorTPD - $aktKotorTPD;
        $diffTkgb = $dbKotorTKGB - $aktKotorTKGB;

        $tr .= '<td class="num" style="' . $bg($diffTpd) . '">' . $fmt($dbKotorTPD) . '</td>';
        $tr .= '<td class="num" style="' . $bg($diffTkgb) . '">' . $fmt($dbKotorTKGB) . '</td>';
        $tr .= '<td class="num">' . $fmt($aktKotorTPD) . '</td>';
        $tr .= '<td class="num">' . $fmt($aktKotorTKGB) . '</td>';

        if ($sp2dOk) {
          $dbPajakTPD = $dbKotorTPD * $tarif;
          $dbPajakTKGB = $kenaTKGB ? ($dbKotorTKGB * $tarif) : 0.0;
          $dbBersih = ($dbKotorTPD - $dbPajakTPD) + ($dbKotorTKGB - $dbPajakTKGB);

          $aktPajakTPD = $aktKotorTPD * $tarif;
          $aktPajakTKGB = $kenaTKGB ? ($aktKotorTKGB * $tarif) : 0.0;
          $aktBersih = ($aktKotorTPD - $aktPajakTPD) + ($aktKotorTKGB - $aktPajakTKGB);

          $sumDbKotorTPD += $dbKotorTPD; $sumDbKotorTKGB += $dbKotorTKGB; $sumDbPajakTPD += $dbPajakTPD; $sumDbPajakTKGB += $dbPajakTKGB; $sumDbBersih += $dbBersih;
          $sumAktKotorTPD += $aktKotorTPD; $sumAktKotorTKGB += $aktKotorTKGB; $sumAktPajakTPD += $aktPajakTPD; $sumAktPajakTKGB += $aktPajakTKGB; $sumAktBersih += $aktBersih;
        }
      }

      $clsSumTpd = $bg($sumDbKotorTPD - $sumAktKotorTPD);
      $clsSumTkgb = $bg($sumDbKotorTKGB - $sumAktKotorTKGB);
      $clsPjkTpd = $bg($sumDbPajakTPD - $sumAktPajakTPD);
      $clsPjkTkgb = $bg($sumDbPajakTKGB - $sumAktPajakTKGB);
      $clsBersih = $bg($sumDbBersih - $sumAktBersih);

      $kesimpulan = $sumDbBersih - $sumAktBersih;
      $clsKesimpulan = $bg($kesimpulan);

      $tr .= '<td class="num" style="' . $clsSumTpd . '">' . $fmt($sumDbKotorTPD) . '</td>';
      $tr .= '<td class="num" style="' . $clsSumTkgb . '">' . $fmt($sumDbKotorTKGB) . '</td>';
      $tr .= '<td class="num" style="' . $clsPjkTpd . '">' . $fmt($sumDbPajakTPD) . '</td>';
      $tr .= '<td class="num" style="' . $clsPjkTkgb . '">' . $fmt($sumDbPajakTKGB) . '</td>';
      $tr .= '<td class="num" style="' . $clsBersih . '">' . $fmt($sumDbBersih) . '</td>';

      $tr .= '<td class="num">' . $fmt($sumAktKotorTPD) . '</td>';
      $tr .= '<td class="num">' . $fmt($sumAktKotorTKGB) . '</td>';
      $tr .= '<td class="num">' . $fmt($sumAktPajakTPD) . '</td>';
      $tr .= '<td class="num">' . $fmt($sumAktPajakTKGB) . '</td>';
      $tr .= '<td class="num">' . $fmt($sumAktBersih) . '</td>';
      $tr .= '<td class="num" style="' . $clsKesimpulan . '">' . $fmt($kesimpulan) . '</td>';

      $tr .= '</tr>';
      $html[] = $tr;

      // Akumulasi grand total
      $grandBersih += $sumDbBersih;
      $grandAktBersih += $sumAktBersih;
      $grandKesimpulan += $kesimpulan;
      $no++;
    }

    // Grand Total row
    $totalColSpan = 9 + (12 * 4) + 10; // No+NIDN+Nama+Jenis+Jabatan+Status+No_Rek+Nama_Rek+NPWP + 12 bulan × 4 kolom + 10 kolom jumlah
    $html[] = '<tr style="background-color:#e8e8e8;font-weight:bold;">'
      . '<td colspan="' . ($totalColSpan) . '" style="text-align:right;font-weight:bold;">GRAND TOTAL PEMBAYARAN</td>'
      . '<td class="num" style="font-weight:bold;background-color:#d4edda;">' . $fmt(abs($grandKesimpulan)) . '</td>'
      . '</tr>';

    $html[] = '</table></body></html>';
    return implode("\n", $html);
  }

  private function loadTarifPajakMap(): array
  {
    $tarifMap = [];
    $pajakRows = DB::table('d_pajak')->select('status', 'akumulasi', 'tarif_pajak')->get();
    foreach ($pajakRows as $p) {
      $status = trim((string) ($p->status ?? ''));
      $akum = trim((string) ($p->akumulasi ?? ''));
      if ($status === '' || $akum === '') continue;
      $tarifMap[$status][$akum] = (float) ($p->tarif_pajak ?? 0);
    }
    return $tarifMap;
  }

  private function computeKekuranganPayload(string $versi, string $tipe, string $jenis, string $bank, array $tarifMap, bool $skipExisting = true): array
  {
    @set_time_limit(0);
    @ini_set('memory_limit', '-1');
    DB::disableQueryLog();

    $existingNidnSet = [];
    if ($skipExisting) {
      try {
        $existingNidn = DB::table('t_kekurangan')->where('tahun', $versi)->whereNotNull('nidn')->pluck('nidn');
        foreach ($existingNidn as $n) {
          $t = trim((string) $n);
          if ($t !== '') {
            $existingNidnSet[$t] = true;
          }
        }
      } catch (\Throwable $e) {
        Log::warning('KekuranganBayarController::compute - gagal memuat existing NIDN: ' . $e->getMessage());
      }
    }

    $baseQuery = DB::table('s_transaksi_2 as s')->where('s.Tahun_Versi', $versi);

    if ($jenis !== 'Semua') {
      if (strtoupper($jenis) === 'PNS') {
        $baseQuery->where(function ($q) {
          $q->whereRaw("UPPER(s.Jenis) LIKE '%PNS%'")
            ->whereRaw("UPPER(s.Jenis) NOT LIKE '%NON%'");
        });
      } elseif (strtoupper($jenis) === 'NON PNS') {
        $baseQuery->whereRaw("UPPER(s.Jenis) LIKE '%NON%'");
      } else {
        $baseQuery->whereRaw("TRIM(s.Jenis) = ?", [trim($jenis)]);
      }
    }
    if ($bank !== 'Semua') {
      $baseQuery->whereRaw('TRIM(s.Bank) = ?', [trim($bank)]);
    }

    $selects = [
      's.NIDN', 's.Nama', 's.Jenis', 's.Jabatan12', 's.Aktif', 's.Bank',
    ];

    for ($i = 1; $i <= 12; $i++) {
      $selects[] = DB::raw('s.Gol' . $i . ' as Gol' . $i);
      $selects[] = DB::raw('s.Jabatan' . $i . ' as Jabatan' . $i);
      $selects[] = DB::raw('s.TPD' . $i . ' as ExpTPD' . $i);
      $selects[] = DB::raw('s.TKGB' . $i . ' as ExpTKGB' . $i);
      $selects[] = DB::raw('s.No_sp2d_' . $i . ' as NoSP2D' . $i);
      $selects[] = DB::raw('s.Tgl_sp2d_' . $i . ' as TglSP2D' . $i);
      $selects[] = DB::raw('s.bersihTPD' . $i . ' as PaidTPD' . $i);
      $selects[] = DB::raw('s.bersihTKGB' . $i . ' as PaidTKGB' . $i);
      $selects[] = DB::raw('s.Gaji' . $i . ' as PaidGaji' . $i);
    }

    $cursor = $baseQuery->select($selects)->orderBy('s.NIDN')->cursor();
    $payloadRows = [];

    foreach ($cursor as $row) {
      $nidn = trim((string) ($row->NIDN ?? ''));
      if ($nidn === '') continue;
      if ($skipExisting && isset($existingNidnSet[$nidn])) continue;

      $nama = (string) ($row->Nama ?? '');
      $jenisRow = (string) ($row->Jenis ?? '');

      $sumTPD = 0.0; $sumTKGB = 0.0; $sumPajakTPD = 0.0; $sumPajakTKGB = 0.0; $sumPaid = 0.0; $sumExpectedBersih = 0.0;
      $hasAnySP2D = false;

      $rand = strtoupper(substr(md5(uniqid((string) mt_rand(), true)), 0, 6));
      $ts = date('YmdHis');
      $nidnCompact = preg_replace('/[^A-Za-z0-9]/', '', $nidn);

      $payload = [
        'nidn' => $nidn, 'nama' => $nama, 'tahun' => (string) $versi,
        'NIDN' => $nidn, 'Nama' => $nama, 'Jenis' => $jenisRow,
        'Jabatan12' => (string) ($row->Jabatan12 ?? ''),
        'Aktif' => (int) ($row->Aktif ?? 0),
      ];

      for ($i = 1; $i <= 12; $i++) {
        $noSp2d = trim((string) ($row->{'NoSP2D' . $i} ?? ''));
        $tglSp2d = trim((string) ($row->{'TglSP2D' . $i} ?? ''));
        $sp2dOk = ($noSp2d !== '' && $tglSp2d !== '');
        if ($sp2dOk) {
          $hasAnySP2D = true;
        }

        $kTPD = 0.0; $kTKGB = 0.0; $aktTPDInt = 0; $aktTKGBInt = 0; $dbTPDInt = 0; $dbTKGBInt = 0;

        if ($sp2dOk) {
          $gol = trim((string) ($row->{'Gol' . $i} ?? ''));
          $jabatan = (string) ($row->{'Jabatan' . $i} ?? '');
          $payload['Gol' . $i] = $gol;
          $payload['Jabatan' . $i] = $jabatan;
          $payload['sp2d_ok' . $i] = 1;
          $tarif = (float) (($tarifMap[$jenisRow][$gol] ?? 0) ?: 0);

          $dbKotorTPD = $this->parseMoney($row->{'ExpTPD' . $i} ?? 0);
          $dbKotorTKGB = $this->parseMoney($row->{'ExpTKGB' . $i} ?? 0);
          $dbTPDInt = (int) round($dbKotorTPD);
          $dbTKGBInt = (int) round($dbKotorTKGB);

          $gaji = $this->parseMoney($row->{'PaidGaji' . $i} ?? 0);
          $kenaTKGB = $this->isGuruBesarAtauProfesor($jabatan);
          [$aktKotorTPD, $aktKotorTKGB] = $this->splitAktualKotorFromGaji($gaji, $kenaTKGB);
          $aktTPDInt = (int) round($aktKotorTPD);
          $aktTKGBInt = (int) round($aktKotorTKGB);

          $kTPD = $dbKotorTPD - $aktKotorTPD;
          $kTKGB = $dbKotorTKGB - $aktKotorTKGB;

          $dbPajakTPD = $dbKotorTPD * $tarif;
          $dbPajakTKGB = $kenaTKGB ? ($dbKotorTKGB * $tarif) : 0.0;
          $aktPajakTPD = $aktKotorTPD * $tarif;
          $aktPajakTKGB = $aktKotorTKGB * $tarif; // Fix typo: was $aktKotorTKGB * $tarif

          $sumPajakTPD += ($dbPajakTPD - $aktPajakTPD);
          $sumPajakTKGB += ($dbPajakTKGB - $aktPajakTKGB);

          $dbBersih = ($dbKotorTPD - $dbPajakTPD) + ($dbKotorTKGB - $dbPajakTKGB);
          $aktBersih = ($aktKotorTPD - $aktPajakTPD) + ($aktKotorTKGB - $aktPajakTKGB);
          $sumExpectedBersih += ($dbBersih - $aktBersih);
          $sumPaid += $gaji;
        }
        if (!$sp2dOk) {
          $payload['sp2d_ok' . $i] = 0;
        }

        $payload['k_tpd' . $i] = $kTPD; $payload['k_tkgb' . $i] = $kTKGB;
        $payload['exp_tpd' . $i] = $aktTPDInt; $payload['exp_tkgb' . $i] = $aktTKGBInt;
        $payload['db_tpd' . $i] = $dbTPDInt; $payload['db_tkgb' . $i] = $dbTKGBInt;
        $sumTPD += $kTPD; $sumTKGB += $kTKGB;
      }

      if ($tipe === 'TPD' && $sumTPD == 0.0) continue;
      if ($tipe === 'TKGB' && $sumTKGB == 0.0) continue;
      if (!$hasAnySP2D) continue;
      if (abs($sumTPD) < 0.0000001 && abs($sumTKGB) < 0.0000001) continue;
      if (abs($sumExpectedBersih) < 0.0000001 && abs($sumPaid) < 0.0000001) continue;

      $payload['jml_tpd'] = $sumTPD; $payload['jml_tkgb'] = $sumTKGB;
      $payload['nilai_pjk_tpd'] = $sumPajakTPD; $payload['nilai_pjk_tkgb'] = $sumPajakTKGB;
      $payload['bersih'] = (($sumTPD + $sumTKGB) - ($sumPajakTPD + $sumPajakTKGB));
      $payload['total_pembayaran'] = $sumPaid;

      $prefix = ((float) $payload['bersih']) > 0 ? 'KLB' : 'KKB';
      $payload['id'] = substr(sprintf('%s-%s-%s-%s-%s', $prefix, $versi, $ts, $nidnCompact ?: 'X', $rand), 0, 50);

      $payloadRows[] = (object) $payload;
      if ($skipExisting) {
        $existingNidnSet[$nidn] = true;
      }
    }

    return $payloadRows;
  }

  private function getFullyPaidNidns($versi, $tarifMap = [])
  {
      $paidKotorByNidnMonth = [];
      try {
          $paidRecords = DB::table('t_kekurangan')
              ->where('tahun', $versi)
              ->where('jenis_pembayaran', 'like', 'PEMBAYARAN_%')
              ->select('nidn', 'jenis_pembayaran', 'selisih as nominal')
              ->get();
          foreach ($paidRecords as $pr) {
              $parts = explode('_', $pr->jenis_pembayaran);
              $m = isset($parts[1]) ? (int) $parts[1] : 0;
              if ($m > 0) {
                  if (!isset($paidKotorByNidnMonth[$pr->nidn][$m])) {
                      $paidKotorByNidnMonth[$pr->nidn][$m] = 0;
                  }
                  $paidKotorByNidnMonth[$pr->nidn][$m] += abs((float) $pr->nominal);
              }
          }
      } catch (\Throwable $e) {}

      $k2_sub = clone $this->getPivotSubquery($versi);
      $fullyPaidNidns = [];
      
      if (!empty($paidKotorByNidnMonth)) {
          $nids = array_keys($paidKotorByNidnMonth);
          
          $k2_sub = clone $this->getPivotSubquery($versi);
          
          $rows = DB::table('s_transaksi_2 as k')
              ->joinSub($k2_sub, 'k2', function ($join) {
                  $join->on('k.NIDN', '=', 'k2.nidn');
              })
              ->where('k.Tahun_Versi', $versi)
              ->whereIn('k.NIDN', $nids)
              ->select(
                  'k.NIDN', 'k.Jenis', 'k.Jabatan12',
                  'k2.k_tpd1', 'k2.k_tkgb1', 'k2.k_tpd2', 'k2.k_tkgb2', 'k2.k_tpd3', 'k2.k_tkgb3',
                  'k2.k_tpd4', 'k2.k_tkgb4', 'k2.k_tpd5', 'k2.k_tkgb5', 'k2.k_tpd6', 'k2.k_tkgb6',
                  'k2.k_tpd7', 'k2.k_tkgb7', 'k2.k_tpd8', 'k2.k_tkgb8', 'k2.k_tpd9', 'k2.k_tkgb9',
                  'k2.k_tpd10', 'k2.k_tkgb10', 'k2.k_tpd11', 'k2.k_tkgb11', 'k2.k_tpd12', 'k2.k_tkgb12',
                  'k.Gol1', 'k.Gol2', 'k.Gol3', 'k.Gol4', 'k.Gol5', 'k.Gol6', 'k.Gol7', 'k.Gol8', 'k.Gol9', 'k.Gol10', 'k.Gol11', 'k.Gol12',
                  'k.Jabatan1', 'k.Jabatan2', 'k.Jabatan3', 'k.Jabatan4', 'k.Jabatan5', 'k.Jabatan6', 'k.Jabatan7', 'k.Jabatan8', 'k.Jabatan9', 'k.Jabatan10', 'k.Jabatan11',
                  'k.TPD1', 'k.TPD2', 'k.TPD3', 'k.TPD4', 'k.TPD5', 'k.TPD6', 'k.TPD7', 'k.TPD8', 'k.TPD9', 'k.TPD10', 'k.TPD11', 'k.TPD12',
                  'k.TKGB1', 'k.TKGB2', 'k.TKGB3', 'k.TKGB4', 'k.TKGB5', 'k.TKGB6', 'k.TKGB7', 'k.TKGB8', 'k.TKGB9', 'k.TKGB10', 'k.TKGB11', 'k.TKGB12',
                  'k.No_sp2d_1', 'k.No_sp2d_2', 'k.No_sp2d_3', 'k.No_sp2d_4', 'k.No_sp2d_5', 'k.No_sp2d_6', 'k.No_sp2d_7', 'k.No_sp2d_8', 'k.No_sp2d_9', 'k.No_sp2d_10', 'k.No_sp2d_11', 'k.No_sp2d_12',
                  'k.Tgl_sp2d_1', 'k.Tgl_sp2d_2', 'k.Tgl_sp2d_3', 'k.Tgl_sp2d_4', 'k.Tgl_sp2d_5', 'k.Tgl_sp2d_6', 'k.Tgl_sp2d_7', 'k.Tgl_sp2d_8', 'k.Tgl_sp2d_9', 'k.Tgl_sp2d_10', 'k.Tgl_sp2d_11', 'k.Tgl_sp2d_12'
              )
              ->get();
          
          foreach ($rows as $row) {
              $nidn = $row->NIDN;
              $jenisKey = trim((string) ($row->Jenis ?? ''));
              $sumDbBersih = 0.0;
              $sumAktBersih = 0.0;
              
              for ($m = 1; $m <= 12; $m++) {
                  $noSp2d = trim((string) ($row->{'No_sp2d_' . $m} ?? ''));
                  $tglSp2d = trim((string) ($row->{'Tgl_sp2d_' . $m} ?? ''));
                  $sp2dOk = ($noSp2d !== '' && $tglSp2d !== '');
                  if (!$sp2dOk) continue;

                  $aktKotorTPD = (float) $this->parseMoney($row->{'TPD' . $m} ?? 0);
                  $aktKotorTKGB = (float) $this->parseMoney($row->{'TKGB' . $m} ?? 0);
                  
                  $gol = trim((string) ($row->{'Gol' . $m} ?? ''));
                  $jabatan = (string) ($row->{'Jabatan' . $m} ?? ($row->Jabatan12 ?? ''));
                  $kenaTKGB = $this->isGuruBesarAtauProfesor($jabatan);
                  
                  $k_tpd = (float) ($row->{'k_tpd' . $m} ?? 0);
                  $k_tkgb = (float) ($row->{'k_tkgb' . $m} ?? 0);
                  
                  $dbKotorTPD = $aktKotorTPD + $k_tpd;
                  $dbKotorTKGB = $aktKotorTKGB + $k_tkgb;
                  
                  $tarif = (float) (($tarifMap[$jenisKey][$gol] ?? 0) ?: 0);
                  
                  $paidNet = $paidKotorByNidnMonth[$nidn][$m] ?? 0;
                  if ($paidNet > 0) {
                      $paidGross = $paidNet;
                      if ($tarif < 1 && $tarif >= 0) {
                          $paidGross = $paidNet / (1 - $tarif);
                      }
                      
                      $diffTPD = $dbKotorTPD - $aktKotorTPD;
                      if ($diffTPD > 0 && $paidGross > 0) {
                          $addTPD = min($diffTPD, $paidGross);
                          $aktKotorTPD += $addTPD;
                          $paidGross -= $addTPD;
                      } elseif ($diffTPD < 0 && $paidGross > 0) {
                          $subTPD = min(abs($diffTPD), $paidGross);
                          $aktKotorTPD -= $subTPD;
                          $paidGross -= $subTPD;
                      }

                      $diffTKGB = $dbKotorTKGB - $aktKotorTKGB;
                      if ($diffTKGB > 0 && $paidGross > 0) {
                          $addTKGB = min($diffTKGB, $paidGross);
                          $aktKotorTKGB += $addTKGB;
                          $paidGross -= $addTKGB;
                      } elseif ($diffTKGB < 0 && $paidGross > 0) {
                          $subTKGB = min(abs($diffTKGB), $paidGross);
                          $aktKotorTKGB -= $subTKGB;
                          $paidGross -= $subTKGB;
                      }
                  }

                  $dbPajakTPD = $dbKotorTPD * $tarif;
                  $dbPajakTKGB = $kenaTKGB ? ($dbKotorTKGB * $tarif) : 0.0;
                  $dbBersih = ($dbKotorTPD - $dbPajakTPD) + ($dbKotorTKGB - $dbPajakTKGB);

                  $aktPajakTPD = $aktKotorTPD * $tarif;
                  $aktPajakTKGB = $kenaTKGB ? ($aktKotorTKGB * $tarif) : 0.0;
                  $aktBersih = ($aktKotorTPD - $aktPajakTPD) + ($aktKotorTKGB - $aktPajakTKGB);

                  $sumDbBersih += $dbBersih;
                  $sumAktBersih += $aktBersih;
              }
              
              if (abs($sumDbBersih - $sumAktBersih) < 0.01) {
                  $fullyPaidNidns[] = $nidn;
              }
          }
      }
      return [$fullyPaidNidns, $paidKotorByNidnMonth];
  }

  public function cek(Request $request)
  {
    $request->validate([
      'periode' => 'required',
      'tipe' => 'required',
      'jenis' => 'required',
      'bank' => 'required',
    ]);

    $versi = session('tahun');
    if (!$versi) {
      return redirect()->route('admin.kekurangan-bayar')->with('error', 'Tahun versi belum dipilih pada sesi.');
    }

    $tipe = $request->input('tipe', 'Semua');
    $jenis = $request->input('jenis', 'Semua');
    $bank = $request->input('bank', 'Semua');

    try {
      $tarifMap = $this->loadTarifPajakMap();
    } catch (\Throwable $e) {
      $alias = ErrorAlias::fromThrowable($e, 'ADM-KEKURANGAN-PAJAK');
      return redirect()->route('admin.kekurangan-bayar')->with('error', 'Gagal memuat tarif pajak. ' . $alias['message']);
    }

    try {
      $rowsPayload = $this->computeKekuranganPayload((string) $versi, $tipe, $jenis, $bank, $tarifMap, true);
      if (empty($rowsPayload)) {
        return redirect()->route('admin.kekurangan-bayar')->with('warning', 'Tidak ada data untuk dicek (mungkin sudah pernah diproses / total pembayaran 0 / belum ada SP2D).');
      }

      $kurangArr = [];
      $lebihArr = [];

      foreach ($rowsPayload as $obj) {
        $row = (object) ((array) $obj);
        $jenisRow = (string) ($row->Jenis ?? '');
        $jenisKey = trim($jenisRow);

        $sumDbKotorTPD = 0.0; $sumDbKotorTKGB = 0.0; $sumDbPajakTPD = 0.0; $sumDbPajakTKGB = 0.0; $sumDbBersih = 0.0;
        $sumAktKotorTPD = 0.0; $sumAktKotorTKGB = 0.0; $sumAktPajakTPD = 0.0; $sumAktPajakTKGB = 0.0; $sumAktBersih = 0.0;

        for ($i = 1; $i <= 12; $i++) {
          $sp2dOk = (int) ($row->{'sp2d_ok' . $i} ?? 0) === 1;
          if (!$sp2dOk) {
            continue;
          }

          $dbKotorTPD = (float) $this->parseMoney($row->{'db_tpd' . $i} ?? 0);
          $dbKotorTKGB = (float) $this->parseMoney($row->{'db_tkgb' . $i} ?? 0);
          $aktKotorTPD = (float) $this->parseMoney($row->{'exp_tpd' . $i} ?? 0);
          $aktKotorTKGB = (float) $this->parseMoney($row->{'exp_tkgb' . $i} ?? 0);

          $gol = trim((string) ($row->{'Gol' . $i} ?? ''));
          $jabatan = (string) ($row->{'Jabatan' . $i} ?? ($row->Jabatan12 ?? ''));
          $kenaTKGB = $this->isGuruBesarAtauProfesor($jabatan);
          $tarif = (float) (($tarifMap[$jenisKey][$gol] ?? 0) ?: 0);

          $dbPajakTPD = $dbKotorTPD * $tarif;
          $dbPajakTKGB = $kenaTKGB ? ($dbKotorTKGB * $tarif) : 0.0;
          $dbBersih = ($dbKotorTPD - $dbPajakTPD) + ($dbKotorTKGB - $dbPajakTKGB);

          $aktPajakTPD = $aktKotorTPD * $tarif;
          $aktPajakTKGB = $kenaTKGB ? ($aktKotorTKGB * $tarif) : 0.0;
          $aktBersih = ($aktKotorTPD - $aktPajakTPD) + ($aktKotorTKGB - $aktPajakTKGB);

          $sumDbKotorTPD += $dbKotorTPD; $sumDbKotorTKGB += $dbKotorTKGB; $sumDbPajakTPD += $dbPajakTPD; $sumDbPajakTKGB += $dbPajakTKGB; $sumDbBersih += $dbBersih;
          $sumAktKotorTPD += $aktKotorTPD; $sumAktKotorTKGB += $aktKotorTKGB; $sumAktPajakTPD += $aktPajakTPD; $sumAktPajakTKGB += $aktPajakTKGB; $sumAktBersih += $aktBersih;
        }

        $row->jml_tpd = $sumDbKotorTPD; $row->jml_tkgb = $sumDbKotorTKGB; $row->nilai_pjk_tpd = $sumDbPajakTPD; $row->nilai_pjk_tkgb = $sumDbPajakTKGB; $row->bersih = $sumDbBersih;
        $row->jml_tpd_akt = $sumAktKotorTPD; $row->jml_tkgb_akt = $sumAktKotorTKGB; $row->nilai_pjk_tpd_akt = $sumAktPajakTPD; $row->nilai_pjk_tkgb_akt = $sumAktPajakTKGB; $row->bersih_akt = $sumAktBersih;

        $kesimpulan = $sumAktBersih - $sumDbBersih;
        if ($kesimpulan < 0) {
            $kurangArr[] = $row;
        } elseif ($kesimpulan > 0) {
            $lebihArr[] = $row;
        }
      }

      return view('admin.kekurangan-bayar', [
        'versi' => $versi,
        'detailKurang' => collect($kurangArr),
        'detailLebih'  => collect($lebihArr),
        'rekapKurang'  => collect(),
        'rekapLebih'   => collect(),
        'bankList' => DB::table('b_bank')->select('nama_bank')->whereNotNull('nama_bank')->where('nama_bank','!=','')->distinct()->orderBy('nama_bank')->pluck('nama_bank'),
        'flashInfo' => 'Cek Data selesai (tanpa insert). Klik Proses untuk menyimpan ke database.',
        'processedRekapNidnsKurang' => [],
        'processedRekapNidnsLebih' => [],
      ]);
    } catch (\Throwable $e) {
      $alias = ErrorAlias::fromThrowable($e, 'ADM-KEKURANGAN-CEK');
      Log::error('KekuranganBayarController::cek - gagal hitung cek data', [
        'alias' => $alias['code'],
        'versi' => $versi,
        'tipe' => $tipe,
        'jenis' => $jenis,
        'bank' => $bank,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);
      return redirect()->route('admin.kekurangan-bayar')->with('error', 'Gagal cek data. ' . $alias['message']);
    }
  }

  private function isGuruBesarAtauProfesor($jabatan): bool
  {
    $text = strtolower(trim((string) $jabatan));
    if ($text === '') {
      return false;
    }
    return strpos($text, 'guru besar') !== false || strpos($text, 'profesor') !== false;
  }

  private function splitAktualKotorFromGaji(float $gaji, bool $kenaTKGB): array
  {
    if ($gaji == 0.0) {
      return [0.0, 0.0];
    }
    if (!$kenaTKGB) {
      return [$gaji, 0.0];
    }

    $tpd = $gaji / 3.0;
    $tkgb = $gaji - $tpd;
    return [$tpd, $tkgb];
  }

  /**
   * Compute grand total (sum of abs(kesimpulan) per dosen) — same logic as toExcelHtmlLikeTable.
   * kesimpulan = sumDbBersih - sumAktBersih per dosen.
   */
  private function computeGrandTotalFromRows($rows, array $tarifMap): float
  {
    $grandTotal = 0.0;
    foreach ($rows as $r) {
      $jenisKey = trim((string) ($r->Jenis ?? $r->jenis ?? ''));
      $jabatan12 = (string) ($r->Jabatan12 ?? '');
      $sumDbBersih = 0.0;
      $sumAktBersih = 0.0;

      for ($i = 1; $i <= 12; $i++) {
        $noSp2d = trim((string) ($r->{'No_sp2d_' . $i} ?? ''));
        $tglSp2d = trim((string) ($r->{'Tgl_sp2d_' . $i} ?? ''));
        if ($noSp2d === '' || $tglSp2d === '') continue;

        $dbKotorTPD = (float) $this->parseMoney($r->{'TPD' . $i} ?? 0);
        $dbKotorTKGB = (float) $this->parseMoney($r->{'TKGB' . $i} ?? 0);
        $gol = trim((string) ($r->{'Gol' . $i} ?? ''));
        $jabatan = (string) ($r->{'Jabatan' . $i} ?? $jabatan12);
        $kenaTKGB = $this->isGuruBesarAtauProfesor($jabatan);
        $gaji = (float) $this->parseMoney($r->{'Gaji' . $i} ?? 0);
        [$aktKotorTPD, $aktKotorTKGB] = $this->splitAktualKotorFromGaji($gaji, $kenaTKGB);
        $tarif = (float) (($tarifMap[$jenisKey][$gol] ?? 0) ?: 0);

        $dbPajakTPD = $dbKotorTPD * $tarif;
        $dbPajakTKGB = $kenaTKGB ? ($dbKotorTKGB * $tarif) : 0.0;
        $dbBersih = ($dbKotorTPD - $dbPajakTPD) + ($dbKotorTKGB - $dbPajakTKGB);

        $aktPajakTPD = $aktKotorTPD * $tarif;
        $aktPajakTKGB = $kenaTKGB ? ($aktKotorTKGB * $tarif) : 0.0;
        $aktBersih = ($aktKotorTPD - $aktPajakTPD) + ($aktKotorTKGB - $aktPajakTKGB);

        $sumDbBersih += $dbBersih;
        $sumAktBersih += $aktBersih;
      }
      $kesimpulan = $sumAktBersih - $sumDbBersih;
      $grandTotal += abs($kesimpulan);
    }
    return $grandTotal;
  }

  private function getPivotSubquery($versi)
  {
      $selectRaw = "nidn, tahun, SUM(selisih) as bersih";
      for($i=1; $i<=12; $i++) {
          $selectRaw .= ", SUM(CASE WHEN jenis_pembayaran IN ('K_TPD{$i}', 'L_TPD{$i}') THEN selisih * -1 ELSE 0 END) as k_tpd{$i}";
          $selectRaw .= ", SUM(CASE WHEN jenis_pembayaran IN ('K_TKGB{$i}', 'L_TKGB{$i}') THEN selisih * -1 ELSE 0 END) as k_tkgb{$i}";
      }
      
      $selectRaw .= ", SUM(CASE WHEN jenis_pembayaran LIKE '%TPD%' AND jenis_pembayaran NOT LIKE 'PEMBAYARAN%' THEN selisih * -1 ELSE 0 END) as jml_tpd";
      $selectRaw .= ", SUM(CASE WHEN jenis_pembayaran LIKE '%TKGB%' AND jenis_pembayaran NOT LIKE 'PEMBAYARAN%' THEN selisih * -1 ELSE 0 END) as jml_tkgb";

      return DB::table('t_kekurangan')
          ->where('tahun', $versi)
          ->where('jenis_pembayaran', 'NOT LIKE', 'PEMBAYARAN%')
          ->selectRaw($selectRaw)
          ->groupBy('nidn', 'tahun');
  }

  private function getNidnsInRekap($rekap, $versi, $isKurang)
  {
      $cTipe = trim($rekap->tipe ?? 'Semua');
      $cBank = trim($rekap->bank ?? 'Semua');
      $cJenis = trim($rekap->jenis ?? 'Semua');
      
      $excludeNidns = [];
      if (!empty($rekap->exclude_nidns)) {
          $excludeNidns = explode(',', $rekap->exclude_nidns);
          $excludeNidns = array_map('trim', $excludeNidns);
          $excludeNidns = array_filter($excludeNidns);
      }

      $k2_sub = clone $this->getPivotSubquery($versi);
      $bersihCondition = $isKurang ? '(ku.bersih + 0) < 0' : '(ku.bersih + 0) > 0';

      $base = DB::table('s_transaksi_2 as k')
          ->joinSub($k2_sub, 'ku', 'ku.nidn', '=', 'k.NIDN')
          ->where('k.Tahun_Versi', $versi)
          ->whereRaw($bersihCondition);

      if ($cTipe !== 'Semua') {
          if ($cTipe === 'TPD') { $base->whereRaw('(ku.jml_tpd + 0) <> 0'); } 
          elseif ($cTipe === 'TKGB') { $base->whereRaw('(ku.jml_tkgb + 0) <> 0'); }
      }
      if ($cJenis !== 'Semua') {
          if (strtoupper($cJenis) === 'PNS') {
              $base->where(function ($q) {
                  $q->whereRaw("UPPER(k.Jenis) LIKE '%PNS%'")
                    ->whereRaw("UPPER(k.Jenis) NOT LIKE '%NON%'");
              });
          } elseif (strtoupper($cJenis) === 'NON PNS') {
              $base->whereRaw("UPPER(k.Jenis) LIKE '%NON%'");
          } else {
              $base->whereRaw("TRIM(k.Jenis) = ?", [trim($cJenis)]);
          }
      }
      if ($cBank !== 'Semua') { 
          $base->whereRaw('TRIM(k.Bank) = ?', [trim($cBank)]); 
      }
      if (!empty($excludeNidns)) {
          $base->whereNotIn('k.NIDN', $excludeNidns);
      }

      return $base->pluck('k.NIDN')->toArray();
  }

  public function index()
  {
    $versi = session('tahun');

    try {
      $tarifMap = $this->loadTarifPajakMap();
    } catch (\Throwable $e) {
      $tarifMap = [];
    }

    list($fullyPaidNidns, $paidKotorByNidnMonth) = $this->getFullyPaidNidns($versi, $tarifMap);
    
    $k2_sub = clone $this->getPivotSubquery($versi);

    $baseQuery = DB::table('s_transaksi_2 as k')
      ->joinSub($k2_sub, 'k2', function ($join) {
        $join->on('k.NIDN', '=', 'k2.nidn');
      })
      ->where('k.Tahun_Versi', $versi);

    // fullyPaidNidns akan di-filter per tab (Kurang, Lebih, Selesai)
    $baseQuery->select(
        'k.NIDN', 'k.Nama', 'k.Jenis', 'k.Jabatan12', 'k.Aktif', 'k.Bank',
        'k2.k_tpd1', 'k2.k_tkgb1', 'k2.k_tpd2', 'k2.k_tkgb2',
        'k2.k_tpd3', 'k2.k_tkgb3', 'k2.k_tpd4', 'k2.k_tkgb4',
        'k2.k_tpd5', 'k2.k_tkgb5', 'k2.k_tpd6', 'k2.k_tkgb6',
        'k2.k_tpd7', 'k2.k_tkgb7', 'k2.k_tpd8', 'k2.k_tkgb8',
        'k2.k_tpd9', 'k2.k_tkgb9', 'k2.k_tpd10', 'k2.k_tkgb10',
        'k2.k_tpd11', 'k2.k_tkgb11', 'k2.k_tpd12', 'k2.k_tkgb12',
        DB::raw('0 as jml_tpd'), DB::raw('0 as jml_tkgb'), DB::raw('0 as nilai_pjk_tpd'), DB::raw('0 as nilai_pjk_tkgb'), 'k2.bersih',
        'k.Gol1', 'k.Gol2', 'k.Gol3', 'k.Gol4', 'k.Gol5', 'k.Gol6', 'k.Gol7', 'k.Gol8', 'k.Gol9', 'k.Gol10', 'k.Gol11', 'k.Gol12',
        'k.Jabatan1', 'k.Jabatan2', 'k.Jabatan3', 'k.Jabatan4', 'k.Jabatan5', 'k.Jabatan6', 'k.Jabatan7', 'k.Jabatan8', 'k.Jabatan9', 'k.Jabatan10', 'k.Jabatan11', 'k.Jabatan12 as Jabatan12Monthly',
        'k.TPD1', 'k.TPD2', 'k.TPD3', 'k.TPD4', 'k.TPD5', 'k.TPD6', 'k.TPD7', 'k.TPD8', 'k.TPD9', 'k.TPD10', 'k.TPD11', 'k.TPD12',
        'k.TKGB1', 'k.TKGB2', 'k.TKGB3', 'k.TKGB4', 'k.TKGB5', 'k.TKGB6', 'k.TKGB7', 'k.TKGB8', 'k.TKGB9', 'k.TKGB10', 'k.TKGB11', 'k.TKGB12',
        'k.Gaji1', 'k.Gaji2', 'k.Gaji3', 'k.Gaji4', 'k.Gaji5', 'k.Gaji6', 'k.Gaji7', 'k.Gaji8', 'k.Gaji9', 'k.Gaji10', 'k.Gaji11', 'k.Gaji12',
        'k.bersihTPD1', 'k.bersihTPD2', 'k.bersihTPD3', 'k.bersihTPD4', 'k.bersihTPD5', 'k.bersihTPD6', 'k.bersihTPD7', 'k.bersihTPD8', 'k.bersihTPD9', 'k.bersihTPD10', 'k.bersihTPD11', 'k.bersihTPD12',
        'k.bersihTKGB1', 'k.bersihTKGB2', 'k.bersihTKGB3', 'k.bersihTKGB4', 'k.bersihTKGB5', 'k.bersihTKGB6', 'k.bersihTKGB7', 'k.bersihTKGB8', 'k.bersihTKGB9', 'k.bersihTKGB10', 'k.bersihTKGB11', 'k.bersihTKGB12',
        'k.No_sp2d_1', 'k.No_sp2d_2', 'k.No_sp2d_3', 'k.No_sp2d_4', 'k.No_sp2d_5', 'k.No_sp2d_6', 'k.No_sp2d_7', 'k.No_sp2d_8', 'k.No_sp2d_9', 'k.No_sp2d_10', 'k.No_sp2d_11', 'k.No_sp2d_12',
        'k.Tgl_sp2d_1', 'k.Tgl_sp2d_2', 'k.Tgl_sp2d_3', 'k.Tgl_sp2d_4', 'k.Tgl_sp2d_5', 'k.Tgl_sp2d_6', 'k.Tgl_sp2d_7', 'k.Tgl_sp2d_8', 'k.Tgl_sp2d_9', 'k.Tgl_sp2d_10', 'k.Tgl_sp2d_11', 'k.Tgl_sp2d_12'
      );

    $searchKurang = request('search_kurang');
    $queryKurangBase = (clone $baseQuery)->whereRaw('(k2.bersih + 0) < 0');
    if (!empty($fullyPaidNidns)) {
        $queryKurangBase->whereNotIn('k.NIDN', $fullyPaidNidns);
    }
    if ($searchKurang) {
        $queryKurangBase->where(function($q) use ($searchKurang) {
            $q->where('k.NIDN', 'like', '%' . $searchKurang . '%')
              ->orWhere('k.Nama', 'like', '%' . $searchKurang . '%');
        });
    }
    $queryKurang = $queryKurangBase->paginate(50, ['*'], 'kurang_page')->appends(request()->query());

    $searchLebih = request('search_lebih');
    $queryLebihBase = (clone $baseQuery)->whereRaw('(k2.bersih + 0) > 0');
    if (!empty($fullyPaidNidns)) {
        $queryLebihBase->whereNotIn('k.NIDN', $fullyPaidNidns);
    }
    if ($searchLebih) {
        $queryLebihBase->where(function($q) use ($searchLebih) {
            $q->where('k.NIDN', 'like', '%' . $searchLebih . '%')
              ->orWhere('k.Nama', 'like', '%' . $searchLebih . '%');
        });
    }
    $queryLebih = $queryLebihBase->paginate(50, ['*'], 'lebih_page')->appends(request()->query());

    $searchSelesai = request('search_selesai');
    $querySelesaiBase = (clone $baseQuery)->whereRaw('(k2.bersih + 0) != 0');
    if (empty($fullyPaidNidns)) {
        $querySelesaiBase->whereRaw('1 = 0'); // Force empty if no fully paid
    } else {
        $querySelesaiBase->whereIn('k.NIDN', $fullyPaidNidns);
    }
    if ($searchSelesai) {
        $querySelesaiBase->where(function($q) use ($searchSelesai) {
            $q->where('k.NIDN', 'like', '%' . $searchSelesai . '%')
              ->orWhere('k.Nama', 'like', '%' . $searchSelesai . '%');
        });
    }
    $querySelesai = $querySelesaiBase->paginate(50, ['*'], 'selesai_page')->appends(request()->query());

    // tarifMap has been loaded at the top of the function

    $transformer = function ($row) use ($tarifMap, $paidKotorByNidnMonth) {
      $jenisRow = (string) ($row->Jenis ?? '');
      $jenisKey = trim($jenisRow);

      $sumDbKotorTPD = 0.0; $sumDbKotorTKGB = 0.0; $sumDbPajakTPD = 0.0; $sumDbPajakTKGB = 0.0; $sumDbBersih = 0.0;
      $sumAktKotorTPD = 0.0; $sumAktKotorTKGB = 0.0; $sumAktPajakTPD = 0.0; $sumAktPajakTKGB = 0.0; $sumAktBersih = 0.0;
      
      for ($i = 1; $i <= 12; $i++) {
        $noSp2d = trim((string) ($row->{'No_sp2d_' . $i} ?? ''));
        $tglSp2d = trim((string) ($row->{'Tgl_sp2d_' . $i} ?? ''));
        $sp2dOk = ($noSp2d !== '' && $tglSp2d !== '');

        $dbTPD = 0; $dbTKGB = 0; $aktTPD = 0; $aktTKGB = 0;
        if ($sp2dOk) {
          $aktKotorTPD = (float) $this->parseMoney($row->{'TPD' . $i} ?? 0);
          $aktKotorTKGB = (float) $this->parseMoney($row->{'TKGB' . $i} ?? 0);

          $gol = trim((string) ($row->{'Gol' . $i} ?? ''));
          $jabatan = (string) ($row->{'Jabatan' . $i} ?? ($row->Jabatan12 ?? ''));
          $kenaTKGB = $this->isGuruBesarAtauProfesor($jabatan);

          $k_tpd = (float) ($row->{'k_tpd' . $i} ?? 0);
          $k_tkgb = (float) ($row->{'k_tkgb' . $i} ?? 0);
          
          $dbKotorTPD = $aktKotorTPD + $k_tpd;
          $dbKotorTKGB = $aktKotorTKGB + $k_tkgb;
          
          $dbTPD = (int) round($dbKotorTPD);
          $dbTKGB = (int) round($dbKotorTKGB);
          
          $tarif = (float) (($tarifMap[$jenisKey][$gol] ?? 0) ?: 0);

          $paidNet = $paidKotorByNidnMonth[$row->NIDN][$i] ?? 0;
          if ($paidNet > 0) {
              // paidNet is the actual cash returned (net). We need to convert it to gross to apply to aktKotor
              $paidGross = $paidNet;
              if ($tarif < 1 && $tarif >= 0) {
                  $paidGross = $paidNet / (1 - $tarif);
              }
              
              $diffTPD = $dbKotorTPD - $aktKotorTPD;
              if ($diffTPD > 0 && $paidGross > 0) {
                  $addTPD = min($diffTPD, $paidGross);
                  $aktKotorTPD += $addTPD;
                  $paidGross -= $addTPD;
              } elseif ($diffTPD < 0 && $paidGross > 0) { // Lebih Bayar (Aktual > Hak)
                  $subTPD = min(abs($diffTPD), $paidGross);
                  $aktKotorTPD -= $subTPD; // Kurangi aktual (karena sudah dikembalikan dosen)
                  $paidGross -= $subTPD;
              }

              $diffTKGB = $dbKotorTKGB - $aktKotorTKGB;
              if ($diffTKGB > 0 && $paidGross > 0) {
                  $addTKGB = min($diffTKGB, $paidGross);
                  $aktKotorTKGB += $addTKGB;
                  $paidGross -= $addTKGB;
              } elseif ($diffTKGB < 0 && $paidGross > 0) { // Lebih Bayar TKGB
                  $subTKGB = min(abs($diffTKGB), $paidGross);
                  $aktKotorTKGB -= $subTKGB;
                  $paidGross -= $subTKGB;
              }
          }

          $aktTPD = (int) round($aktKotorTPD);
          $aktTKGB = (int) round($aktKotorTKGB);

          $dbPajakTPD = $dbKotorTPD * $tarif;
          $dbPajakTKGB = $kenaTKGB ? ($dbKotorTKGB * $tarif) : 0.0;
          $dbBersih = ($dbKotorTPD - $dbPajakTPD) + ($dbKotorTKGB - $dbPajakTKGB);

          $aktPajakTPD = $aktKotorTPD * $tarif;
          $aktPajakTKGB = $kenaTKGB ? ($aktKotorTKGB * $tarif) : 0.0;
          $aktBersih = ($aktKotorTPD - $aktPajakTPD) + ($aktKotorTKGB - $aktPajakTKGB);

          $row->{'db_bersih' . $i} = $dbBersih;
          $row->{'akt_bersih' . $i} = $aktBersih;

          $sumDbKotorTPD += $dbKotorTPD; $sumDbKotorTKGB += $dbKotorTKGB; $sumDbPajakTPD += $dbPajakTPD; $sumDbPajakTKGB += $dbPajakTKGB; $sumDbBersih += $dbBersih;
          $sumAktKotorTPD += $aktKotorTPD; $sumAktKotorTKGB += $aktKotorTKGB; $sumAktPajakTPD += $aktPajakTPD; $sumAktPajakTKGB += $aktPajakTKGB; $sumAktBersih += $aktBersih;
        } else {
          $dbBersih = 0; $aktBersih = 0;
        }
        $row->{'db_tpd' . $i} = $dbTPD; $row->{'db_tkgb' . $i} = $dbTKGB;
        $row->{'exp_tpd' . $i} = $aktTPD; $row->{'exp_tkgb' . $i} = $aktTKGB;
        $row->{'db_bersih' . $i} = $dbBersih; $row->{'akt_bersih' . $i} = $aktBersih;
      }

      $row->jml_tpd = $sumDbKotorTPD; $row->jml_tkgb = $sumDbKotorTKGB; $row->nilai_pjk_tpd = $sumDbPajakTPD; $row->nilai_pjk_tkgb = $sumDbPajakTKGB; $row->bersih = $sumDbBersih;
      $row->jml_tpd_akt = $sumAktKotorTPD; $row->jml_tkgb_akt = $sumAktKotorTKGB; $row->nilai_pjk_tpd_akt = $sumAktPajakTPD; $row->nilai_pjk_tkgb_akt = $sumAktPajakTKGB; $row->bersih_akt = $sumAktBersih;

      return $row;
    };

    foreach ($queryKurang as $row) {
        $transformer($row);
    }
    
    foreach ($queryLebih as $row) {
        $transformer($row);
    }

    foreach ($querySelesai as $row) {
        $transformer($row);
    }

    $detailKurang = $queryKurang;
    $detailLebih  = $queryLebih;
    $detailSelesai = $querySelesai;

    $rekapKurang = DB::table('u_rekap_kekurangan')->whereRaw('RIGHT(periode, 4) = ?', [$versi])->where(function ($q) {
        $q->where('excel', 'like', 'rekap_kekurangan/%')->orWhere('periode', 'like', 'Kurang%');
    })->orderByDesc('created_at')->get();

    $rekapLebih = DB::table('u_rekap_kekurangan')->whereRaw('RIGHT(periode, 4) = ?', [$versi])->where(function ($q) {
        $q->where('excel', 'like', 'rekap_kelebihan/%')->orWhere('periode', 'like', 'Lebih%');
    })->orderByDesc('created_at')->get();

    // Backfill total_nominal untuk rekap yang belum terisi (atau terisi 0)
    $allRekapIds = $rekapKurang->merge($rekapLebih);
    $tarifMapBackfill = null; // lazy-load
    foreach ($allRekapIds as $rekap) {
      if (!empty($rekap->total_nominal) && (float) $rekap->total_nominal > 0) continue;

      // Lazy-load tarif map
      if ($tarifMapBackfill === null) {
        $tarifMapBackfill = $this->loadTarifPajakMap();
      }

      $periodeText = strtolower(trim($rekap->periode ?? ''));
      $isKurang = (strpos($periodeText, 'kurang') !== false);
      $bersihCond = $isKurang ? '(ku.bersih + 0) < 0' : '(ku.bersih + 0) > 0';

      // Build query yang sama seperti proses()
      $k2_sub = clone $this->getPivotSubquery($versi);
      $q = DB::table('s_transaksi_2 as k')
        ->joinSub($k2_sub, 'ku', 'ku.nidn', '=', 'k.NIDN')
        ->where('k.Tahun_Versi', $versi)
        ->whereRaw($bersihCond);

      // Filter tipe
      $rekapTipe = trim($rekap->tipe ?? 'Semua');
      if ($rekapTipe !== 'Semua') {
        if ($rekapTipe === 'TPD') {
          $q->whereRaw('(ku.jml_tpd + 0) <> 0');
        } elseif ($rekapTipe === 'TKGB') {
          $q->whereRaw('(ku.jml_tkgb + 0) <> 0');
        }
      }

      // Filter bank
      $rekapBank = trim($rekap->bank ?? 'Semua');
      if ($rekapBank !== 'Semua') {
        $q->whereRaw('TRIM(k.Bank) = ?', [$rekapBank]);
      }

      // Filter jenis dari excel filename
      $excelPath = strtolower(trim($rekap->excel ?? ''));
      if (strpos($excelPath, '_non_pns_') !== false) {
        $q->whereRaw("UPPER(k.Jenis) LIKE '%NON%'");
      } elseif (strpos($excelPath, '_pns_') !== false && strpos($excelPath, '_non_pns_') === false) {
        $q->where(function ($sub) {
          $sub->whereRaw("UPPER(k.Jenis) LIKE '%PNS%'")
              ->whereRaw("UPPER(k.Jenis) NOT LIKE '%NON%'");
        });
      }

      // Select kolom yang sama seperti proses() agar computeGrandTotalFromRows bisa hitung akurat
      $selectCols = [
        'k.NIDN as NIDN', 'k.Nama as Nama', 'k.Jenis as Jenis', 'k.Bank as Bank',
        'k.Jabatan12 as Jabatan12', 'k.Aktif as Aktif',
        'k.No_Rekening as No_Rekening', 'k.Nama_Rekening as Nama_Rekening', 'k.NPWP as NPWP',
      ];
      for ($i = 1; $i <= 12; $i++) {
        $selectCols[] = 'k.Gol' . $i;
        $selectCols[] = 'k.Jabatan' . $i;
        $selectCols[] = 'k.TPD' . $i;
        $selectCols[] = 'k.TKGB' . $i;
        $selectCols[] = 'k.Gaji' . $i;
        $selectCols[] = 'k.No_sp2d_' . $i;
        $selectCols[] = 'k.Tgl_sp2d_' . $i;
      }

      try {
        $rows = $q->get($selectCols);
        if ($rows->isEmpty()) continue;

        $totalNominal = $this->computeGrandTotalFromRows($rows->all(), $tarifMapBackfill);
        if ($totalNominal > 0) {
          $rekap->total_nominal = $totalNominal;
          DB::table('u_rekap_kekurangan')->where('id', $rekap->id)->update(['total_nominal' => $totalNominal]);
        }
      } catch (\Throwable $e) { /* silent */ }
    }

    // Collect NIDNs from rekaps that have been processed (SP2D filled)
    $processedRekapNidnsKurang = [];
    $processedRekapNidnsLebih = [];
    $allRekapsForSp2d = DB::table('u_rekap_kekurangan')
        ->whereRaw('RIGHT(periode, 4) = ?', [$versi])
        ->whereNotNull('sp2d')
        ->where('sp2d', '!=', '')
        ->get();
    foreach ($allRekapsForSp2d as $rek) {
        $periode = strtolower(trim($rek->periode ?? ''));
        $isKurangRek = (strpos($periode, 'kurang') !== false);
        $nids = $this->getNidnsInRekap($rek, $versi, $isKurangRek);
        if ($isKurangRek) {
            $processedRekapNidnsKurang = array_merge($processedRekapNidnsKurang, $nids);
        } else {
            $processedRekapNidnsLebih = array_merge($processedRekapNidnsLebih, $nids);
        }
    }
    $processedRekapNidnsKurang = array_unique($processedRekapNidnsKurang);
    $processedRekapNidnsLebih = array_unique($processedRekapNidnsLebih);

    return view('admin.kekurangan-bayar', [
      'versi' => $versi,
      'detailKurang' => $detailKurang,
      'detailLebih'  => $detailLebih,
      'detailSelesai' => $detailSelesai,
      'rekapKurang'  => $rekapKurang,
      'rekapLebih'   => $rekapLebih,
      'bankList' => DB::table('b_bank')->select('nama_bank')->whereNotNull('nama_bank')->where('nama_bank','!=','')->distinct()->orderBy('nama_bank')->pluck('nama_bank'),
      'processedRekapNidnsKurang' => $processedRekapNidnsKurang,
      'processedRekapNidnsLebih' => $processedRekapNidnsLebih,
    ]);
  }

  public function proses(Request $request)
  {
    $request->validate([
      'periode' => 'required',
      'tipe' => 'required',
      'jenis' => 'required',
      'bank' => 'required',
    ]);

    $versi = session('tahun');
    if (!$versi) {
      return redirect()->route('admin.kekurangan-bayar')->with('error', 'Tahun versi belum dipilih pada sesi.');
    }

    $tipe = $request->input('tipe', 'Semua');
    $jenis = $request->input('jenis', 'Semua');
    $bank = $request->input('bank', 'Semua');

    @set_time_limit(0);
    @ini_set('memory_limit', '-1');
    DB::disableQueryLog();

    try {
      $tarifMap = $this->loadTarifPajakMap();
    } catch (\Throwable $e) {
      $alias = ErrorAlias::fromThrowable($e, 'ADM-KEKURANGAN-PAJAK');
      return redirect()->route('admin.kekurangan-bayar')->with('error', 'Gagal memuat tarif pajak. ' . $alias['message']);
    }

    try {
      $rowsPayload = $this->computeKekuranganPayload((string) $versi, $tipe, $jenis, $bank, $tarifMap, true);
      if (empty($rowsPayload)) {
        return redirect()->route('admin.kekurangan-bayar')->with('warning', 'Tidak ada data baru untuk diproses (mungkin sudah pernah diproses / total pembayaran 0 / belum ada SP2D).');
      }

      $batch = [];
      $batchSize = 500;
      foreach ($rowsPayload as $obj) {
        $arr = (array) $obj;
        for ($i = 1; $i <= 12; $i++) {
            $kTpd = (float) ($arr['k_tpd'.$i] ?? 0);
            $kTkgb = (float) ($arr['k_tkgb'.$i] ?? 0);
            
            if (abs($kTpd) > 0.01) {
                $batch[] = [
                    'nidn' => $arr['nidn'] ?? null,
                    'nuptk' => $arr['NUPTK'] ?? null,
                    'nama' => $arr['nama'] ?? null,
                    'tahun' => $arr['tahun'] ?? null,
                    'selisih' => round($kTpd, 2),
                    'jenis_pembayaran' => ($kTpd < 0 ? 'K_TPD' : 'L_TPD') . $i,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
            if (abs($kTkgb) > 0.01) {
                $batch[] = [
                    'nidn' => $arr['nidn'] ?? null,
                    'nuptk' => $arr['NUPTK'] ?? null,
                    'nama' => $arr['nama'] ?? null,
                    'tahun' => $arr['tahun'] ?? null,
                    'selisih' => round($kTkgb, 2),
                    'jenis_pembayaran' => ($kTkgb < 0 ? 'K_TKGB' : 'L_TKGB') . $i,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }

        if (count($batch) >= $batchSize) {
          DB::table('t_kekurangan')->insert($batch);
          $batch = [];
        }
      }
      if (!empty($batch)) {
        DB::table('t_kekurangan')->insert($batch);
      }

      $generatedCount = DB::table('t_kekurangan')->where('tahun', $versi)->count();
      if ($generatedCount <= 0) {
        return redirect()->route('admin.kekurangan-bayar')->with('error', 'Tidak ada data yang dapat diproses. Pastikan sudah ada No SP2D dan Tgl SP2D terisi di s_transaksi_2.');
      }
    } catch (\Throwable $e) {
      $alias = ErrorAlias::fromThrowable($e, 'ADM-KEKURANGAN-PROSES');
      return redirect()->route('admin.kekurangan-bayar')->with('error', 'Gagal menghitung kekurangan. ' . $alias['message']);
    }

    $isAllSemua = ($tipe === 'Semua' && $jenis === 'Semua' && $bank === 'Semua');
    $rekapCreated = 0;

    // Pastikan kolom total_nominal dan jenis_pegawai ada di tabel rekap
    $rekapTable = \Illuminate\Support\Facades\Schema::hasTable('u_rekap_kekurangan') ? 'u_rekap_kekurangan' : 'rekap_kekurangan';
    $colsToAdd = [];
    if (!\Illuminate\Support\Facades\Schema::hasColumn($rekapTable, 'total_nominal')) {
        $colsToAdd[] = 'total_nominal';
    }
    if (!\Illuminate\Support\Facades\Schema::hasColumn($rekapTable, 'jenis_pegawai')) {
        $colsToAdd[] = 'jenis_pegawai';
    }
    if (!empty($colsToAdd)) {
        try {
            \Illuminate\Support\Facades\Schema::table($rekapTable, function (\Illuminate\Database\Schema\Blueprint $table) use ($colsToAdd) {
                if (in_array('total_nominal', $colsToAdd)) {
                    $table->decimal('total_nominal', 18, 2)->nullable()->after('pdf');
                }
                if (in_array('jenis_pegawai', $colsToAdd)) {
                    $table->string('jenis_pegawai', 20)->nullable()->after('tipe');
                }
            });
        } catch (\Throwable $e) {
            // Kolom mungkin sudah ada, lanjutkan saja
        }
    }

    // Pastikan tabel t_uraian_pembayaran ada
    if (!\Illuminate\Support\Facades\Schema::hasTable('t_uraian_pembayaran')) {
        \Illuminate\Support\Facades\Schema::create('t_uraian_pembayaran', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('rekap_id')->nullable();
            $table->string('nidn', 50)->nullable();
            $table->string('tahun', 4)->nullable();
            $table->tinyInteger('bulan')->nullable();
            $table->string('uraian_pembayaran', 255)->nullable();
            $table->decimal('nominal', 15, 2)->default(0);
            $table->decimal('pajak', 15, 2)->default(0);
            $table->decimal('bersih', 15, 2)->default(0);
            $table->string('nomor', 100)->nullable();
            $table->date('tanggal')->nullable();
            $table->timestamps();
            $table->index(['nidn', 'tahun']);
            $table->index('rekap_id');
        });
    }

    try {
      $combinations = [];
      if ($isAllSemua) {
        // Mass Generate: Hanya KURANG, di-split per Bank, Tipe (TPD/TKGB), dan Jenis (PNS/NON PNS)
        $k2_sub = $this->getPivotSubquery($versi);
        
        $combinationsRaw = DB::table('s_transaksi_2 as k')
            ->joinSub($k2_sub, 'ku', 'ku.nidn', '=', 'k.NIDN')
            ->where('k.Tahun_Versi', $versi)
            ->whereRaw('(ku.bersih + 0) < 0')
            ->selectRaw("
                TRIM(k.Bank) as bank, 
                CASE WHEN UPPER(k.Jenis) LIKE '%NON%' THEN 'NON PNS' ELSE 'PNS' END as jenis,
                CASE WHEN (ku.jml_tpd + 0) <> 0 THEN 'TPD' ELSE '' END as has_tpd,
                CASE WHEN (ku.jml_tkgb + 0) <> 0 THEN 'TKGB' ELSE '' END as has_tkgb
            ")
            ->distinct()
            ->get();
            
        foreach ($combinationsRaw as $c) {
            $cb = trim($c->bank);
            if ($cb === '') continue;
            if ($c->has_tpd) {
                $combinations[] = ['bank' => $cb, 'jenis' => $c->jenis, 'tipe' => 'TPD', 'for' => 'kurang'];
            }
            if ($c->has_tkgb) {
                $combinations[] = ['bank' => $cb, 'jenis' => $c->jenis, 'tipe' => 'TKGB', 'for' => 'kurang'];
            }
        }
        $combinations = array_map("unserialize", array_unique(array_map("serialize", $combinations)));
      } else {
        // Individual filter: Tipe, Jenis, Bank sesuai input
        $combinations[] = ['bank' => $bank, 'jenis' => $jenis, 'tipe' => $tipe, 'for' => 'kurang'];
        $combinations[] = ['bank' => $bank, 'jenis' => $jenis, 'tipe' => $tipe, 'for' => 'lebih'];
      }

      // Filter out existing combinations to prevent duplicate generation
      $validCombinations = [];
      foreach ($combinations as $combo) {
        $cBank = $combo['bank'];
        $cJenis = $combo['jenis'];
        $cTipe = $combo['tipe'];
        $cFor = $combo['for'];
        
        $jenisLabel = strtolower(str_replace(' ', '_', $cJenis === 'Semua' ? 'semua' : $cJenis));
        $tipeLabel = strtolower($cTipe === 'Semua' ? 'semua' : $cTipe);
        $bankLabel = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $cBank === 'Semua' ? 'semua' : $cBank));
        
        $folder = $cFor === 'kurang' ? 'rekap_kekurangan' : 'rekap_kelebihan';
        $excelRelPath = $folder . '/' . $folder . '_jan_des_' . $jenisLabel . '_' . $tipeLabel . '_' . $bankLabel . '_' . $versi . '.xls';
        $periodeStr = ($cFor === 'kurang' ? 'Kurang' : 'Lebih') . ' Jan-Des ' . $versi;

        $exists = DB::table('u_rekap_kekurangan')
            ->where('periode', $periodeStr)
            ->where('tipe', $cTipe)
            ->where('bank', $cBank)
            ->where('excel', $excelRelPath)
            ->exists();
            
        if (!$exists) {
            $validCombinations[] = $combo;
        }
      }

      if (empty($validCombinations)) {
          return redirect()->route('admin.kekurangan-bayar')->with('warning', 'Semua data untuk kombinasi filter dan periode ini sudah pernah diproses. Tidak ada data rekap baru yang perlu ditambahkan.');
      }
      
      $combinations = $validCombinations;

      // Execution
      foreach ($combinations as $combo) {
        $cBank = $combo['bank'];
        $cJenis = $combo['jenis'];
        $cTipe = $combo['tipe'];
        $cFor = $combo['for'];

        $k2_sub = $this->getPivotSubquery($versi);

        $base = DB::table('s_transaksi_2 as k')
          ->joinSub($k2_sub, 'ku', 'ku.nidn', '=', 'k.NIDN')
          ->where('k.Tahun_Versi', $versi);

        if ($cTipe !== 'Semua') {
          if ($cTipe === 'TPD') { $base->whereRaw('(ku.jml_tpd + 0) <> 0'); } 
          elseif ($cTipe === 'TKGB') { $base->whereRaw('(ku.jml_tkgb + 0) <> 0'); }
        }
        if ($cJenis !== 'Semua') {
          if (strtoupper($cJenis) === 'PNS') {
            $base->where(function ($q) {
              $q->whereRaw("UPPER(k.Jenis) LIKE '%PNS%'")
                ->whereRaw("UPPER(k.Jenis) NOT LIKE '%NON%'");
            });
          } elseif (strtoupper($cJenis) === 'NON PNS') {
            $base->whereRaw("UPPER(k.Jenis) LIKE '%NON%'");
          } else {
            $base->whereRaw("TRIM(k.Jenis) = ?", [trim($cJenis)]);
          }
        }
        if ($cBank !== 'Semua') { $base->whereRaw('TRIM(k.Bank) = ?', [trim($cBank)]); }

        if ($cFor === 'kurang') {
            $base->whereRaw('(ku.bersih + 0) < 0');
        } else {
            $base->whereRaw('(ku.bersih + 0) > 0');
        }

        $select = [
          'k.NIDN as NIDN', 'k.Nama as Nama', 'k.Jenis as Jenis', 'k.Bank as Bank',
          'k.Jabatan12 as Jabatan12', 'k.Aktif as Aktif', 'ku.bersih as delta_bersih',
          'k.No_Rekening as No_Rekening', 'k.Nama_Rekening as Nama_Rekening', 'k.NPWP as NPWP',
        ];
        for ($i = 1; $i <= 12; $i++) {
          $select[] = 'k.Gol' . $i; $select[] = 'k.Jabatan' . $i;
          $select[] = 'k.TPD' . $i; $select[] = 'k.TKGB' . $i; $select[] = 'k.Gaji' . $i;
          $select[] = 'k.No_sp2d_' . $i; $select[] = 'k.Tgl_sp2d_' . $i;
        }

        $rows = $base->get($select);
        if ($rows->isEmpty()) continue;

        $jenisLabel = strtolower(str_replace(' ', '_', $cJenis === 'Semua' ? 'semua' : $cJenis));
        $tipeLabel = strtolower($cTipe === 'Semua' ? 'semua' : $cTipe);
        $bankLabel = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $cBank === 'Semua' ? 'semua' : $cBank));
        
        $folder = $cFor === 'kurang' ? 'rekap_kekurangan' : 'rekap_kelebihan';
        $excelRelPath = $folder . '/' . $folder . '_jan_des_' . $jenisLabel . '_' . $tipeLabel . '_' . $bankLabel . '_' . $versi . '.xls';
        $periodeStr = ($cFor === 'kurang' ? 'Kurang' : 'Lebih') . ' Jan-Des ' . $versi;

        $this->ensurePublicFolder($folder);
        $this->putPublicFile($excelRelPath, $this->toExcelHtmlLikeTable($rows->all(), $tarifMap));

        $totalNominal = $this->computeGrandTotalFromRows($rows->all(), $tarifMap);

        $this->insertRekapRow([
          'periode' => $periodeStr, 
          'pegawai' => (string) $rows->count(),
          'tipe' => $cTipe, 
          'jenis' => $cJenis,
          'bank' => $cBank, 
          'excel' => $excelRelPath, 
          'pdf' => null,
          'total_nominal' => $totalNominal,
          'created_at' => now(), 
          'updated_at' => now(),
        ]);
        $rekapCreated++;
      }
    } catch (\Throwable $e) {
      \Illuminate\Support\Facades\Log::error('Gagal membuat file excel di proses(): ' . $e->getMessage() . "\n" . $e->getTraceAsString());
      return redirect()->route('admin.kekurangan-bayar')->with('success', 'Proses hitung berhasil, namun gagal membuat file rekap.');
    }

    if ($rekapCreated <= 0) {
      return redirect()->route('admin.kekurangan-bayar')->with('success', 'Proses hitung berhasil. Tidak ada data kurang/lebih untuk direkap sesuai filter.');
    }
    return redirect()->route('admin.kekurangan-bayar')->with('success', 'Proses hitung & rekap berhasil.');
  }

  public function rekap()
  {
    $versi = session('tahun');
    if (!$versi) { return redirect()->route('admin.kekurangan-bayar')->with('error', 'Tahun belum dipilih.'); }

    $rekapKurang = DB::table('u_rekap_kekurangan')->whereRaw('RIGHT(periode, 4) = ?', [$versi])
      ->where(function ($q) { $q->where('excel', 'like', 'rekap_kekurangan/%')->orWhere('periode', 'like', 'Kurang%'); })
      ->orderByDesc('created_at')->get();

    $rekapLebih = DB::table('u_rekap_kekurangan')->whereRaw('RIGHT(periode, 4) = ?', [$versi])
      ->where(function ($q) { $q->where('excel', 'like', 'rekap_kelebihan/%')->orWhere('periode', 'like', 'Lebih%'); })
      ->orderByDesc('created_at')->get();

    return view('admin.kekurangan-bayar-rekap', ['versi' => $versi, 'rekapKurang' => $rekapKurang, 'rekapLebih' => $rekapLebih]);
  }

  public function prosesAksiSp2d(Request $request)
  {
    // Pastikan tabel t_uraian_pembayaran ada
    if (!\Illuminate\Support\Facades\Schema::hasTable('t_uraian_pembayaran')) {
        \Illuminate\Support\Facades\Schema::create('t_uraian_pembayaran', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('rekap_id')->nullable();
            $table->string('nidn', 50)->nullable();
            $table->string('tahun', 4)->nullable();
            $table->tinyInteger('bulan')->nullable();
            $table->string('uraian_pembayaran', 255)->nullable();
            $table->decimal('nominal', 15, 2)->default(0);
            $table->decimal('pajak', 15, 2)->default(0);
            $table->decimal('bersih', 15, 2)->default(0);
            $table->string('nomor', 100)->nullable();
            $table->date('tanggal')->nullable();
            $table->timestamps();
            $table->index(['nidn', 'tahun']);
            $table->index('rekap_id');
        });
    }

    $request->validate([
      'rekap_id' => 'nullable|integer',
      'nidn' => 'nullable|string',
      'jenis_sp2d' => 'nullable|string|in:kurang,lebih',
      'no_sp2d' => 'nullable|string|max:100',
      'tanggal_sp2d' => 'required|date',
      'uraian_pembayaran' => 'nullable|string|max:255',
      'bulan' => 'nullable|integer|min:1|max:12',
      'nominal_bayar' => 'nullable|numeric|min:0',
      'trx_type' => 'nullable|string',
    ]);

    $rekapId = $request->input('rekap_id') ? (int) $request->input('rekap_id') : null;
    $nidnInput = trim((string) $request->input('nidn'));
    $jenisSp2d = $request->input('jenis_sp2d');
    $noSp2d = trim((string) $request->input('no_sp2d'));
    $tanggalSp2d = $request->input('tanggal_sp2d');
    $inputUraian = trim((string) $request->input('uraian_pembayaran'));
    $inputBulan = $request->input('bulan');
    $inputNominal = $request->input('nominal_bayar');
    $trxType = trim((string) $request->input('trx_type'));
    $versi = session('tahun');

    if (!$versi) {
      return response()->json(['success' => false, 'message' => 'Tahun versi belum dipilih pada sesi.'], 422);
    }

    $isKurang = false;
    $isLebih = false;
    $rekap = null;

    if ($rekapId) {
        // Cek rekap exists & belum pernah diproses
        $rekap = DB::table('u_rekap_kekurangan')->where('id', $rekapId)->first();
        if (!$rekap) {
          return response()->json(['success' => false, 'message' => 'Data rekap tidak ditemukan.'], 404);
        }
        if (!empty($rekap->sp2d)) {
          return response()->json(['success' => false, 'message' => 'Rekap ini sudah pernah diproses SP2D.'], 422);
        }

        // Deteksi jenis rekap (kurang/lebih) dari periode
        $periode = strtolower(trim($rekap->periode ?? ''));
        $isKurang = (strpos($periode, 'kurang') !== false);
        $isLebih  = (strpos($periode, 'lebih') !== false);

        if (!$isKurang && !$isLebih) {
          return response()->json(['success' => false, 'message' => 'Tidak dapat menentukan jenis rekap (kurang/lebih).'], 422);
        }
    } else {
        if (!$nidnInput || !$jenisSp2d) {
            return response()->json(['success' => false, 'message' => 'NIDN dan jenis harus diisi jika bukan dari rekap.'], 422);
        }
        if ($jenisSp2d === 'kurang') $isKurang = true;
        if ($jenisSp2d === 'lebih') $isLebih = true;
    }

    $monthNames = [
      1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
      5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
      9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    @set_time_limit(0);
    @ini_set('memory_limit', '-1');
    DB::disableQueryLog();

    try {
      $tarifMap = $this->loadTarifPajakMap();
    } catch (\Throwable $e) {
      return response()->json(['success' => false, 'message' => 'Gagal memuat tarif pajak.'], 500);
    }

    DB::beginTransaction();
    try {
      // Update SP2D di rekap jika bulk
      if ($rekapId) {
          DB::table('u_rekap_kekurangan')->where('id', $rekapId)->update([
            'sp2d' => $noSp2d,
            'tgl_sp2d' => $tanggalSp2d,
            'updated_at' => now(),
          ]);
      }

      // Query semua dosen dari t_kekurangan yang sesuai
      $bersihCondition = $isKurang ? '(ku.bersih + 0) < 0' : '(ku.bersih + 0) > 0';
      
      $k2_sub = clone $this->getPivotSubquery($versi);
      
      $dosenQuery = DB::table('s_transaksi_2 as k')
        ->joinSub($k2_sub, 'ku', function ($join) {
          $join->on('k.NIDN', '=', 'ku.nidn');
        })
        ->where('k.Tahun_Versi', $versi)
        ->whereRaw($bersihCondition);

      if ($rekapId && $rekap) {
          $cTipe = trim($rekap->tipe ?? 'Semua');
          $cBank = trim($rekap->bank ?? 'Semua');
          $cJenis = trim($rekap->jenis ?? 'Semua');

          if ($cTipe !== 'Semua') {
              if ($cTipe === 'TPD') { $dosenQuery->whereRaw('(ku.jml_tpd + 0) <> 0'); } 
              elseif ($cTipe === 'TKGB') { $dosenQuery->whereRaw('(ku.jml_tkgb + 0) <> 0'); }
          }
          if ($cJenis !== 'Semua') {
              if (strtoupper($cJenis) === 'PNS') {
                  $dosenQuery->where(function ($q) {
                      $q->whereRaw("UPPER(k.Jenis) LIKE '%PNS%'")
                        ->whereRaw("UPPER(k.Jenis) NOT LIKE '%NON%'");
                  });
              } elseif (strtoupper($cJenis) === 'NON PNS') {
                  $dosenQuery->whereRaw("UPPER(k.Jenis) LIKE '%NON%'");
              } else {
                  $dosenQuery->whereRaw("TRIM(k.Jenis) = ?", [trim($cJenis)]);
              }
          }
          if ($cBank !== 'Semua') { 
              $dosenQuery->whereRaw('TRIM(k.Bank) = ?', [trim($cBank)]); 
          }
          if (!empty($rekap->exclude_nidns)) {
              $excludeNidns = explode(',', $rekap->exclude_nidns);
              $excludeNidns = array_map('trim', $excludeNidns);
              $dosenQuery->whereNotIn('k.NIDN', $excludeNidns);
          }
      }

      if (!$rekapId && $nidnInput) {
          $dosenQuery->where('ku.nidn', $nidnInput);
      }

      $dosenRows = $dosenQuery->select(
          'ku.nidn', 'k.Jenis as Jenis', 'k.Jabatan12 as Jabatan12',
          'ku.k_tpd1', 'ku.k_tpd2', 'ku.k_tpd3', 'ku.k_tpd4', 'ku.k_tpd5', 'ku.k_tpd6',
          'ku.k_tpd7', 'ku.k_tpd8', 'ku.k_tpd9', 'ku.k_tpd10', 'ku.k_tpd11', 'ku.k_tpd12',
          'ku.k_tkgb1', 'ku.k_tkgb2', 'ku.k_tkgb3', 'ku.k_tkgb4', 'ku.k_tkgb5', 'ku.k_tkgb6',
          'ku.k_tkgb7', 'ku.k_tkgb8', 'ku.k_tkgb9', 'ku.k_tkgb10', 'ku.k_tkgb11', 'ku.k_tkgb12',
          'k.Gol1', 'k.Gol2', 'k.Gol3', 'k.Gol4', 'k.Gol5', 'k.Gol6',
          'k.Gol7', 'k.Gol8', 'k.Gol9', 'k.Gol10', 'k.Gol11', 'k.Gol12',
          'k.Jabatan1', 'k.Jabatan2', 'k.Jabatan3', 'k.Jabatan4', 'k.Jabatan5', 'k.Jabatan6',
          'k.Jabatan7', 'k.Jabatan8', 'k.Jabatan9', 'k.Jabatan10', 'k.Jabatan11', 'k.Jabatan12 as Jabatan12Monthly',
          'k.No_sp2d_1', 'k.No_sp2d_2', 'k.No_sp2d_3', 'k.No_sp2d_4', 'k.No_sp2d_5', 'k.No_sp2d_6',
          'k.No_sp2d_7', 'k.No_sp2d_8', 'k.No_sp2d_9', 'k.No_sp2d_10', 'k.No_sp2d_11', 'k.No_sp2d_12',
          'k.Tgl_sp2d_1', 'k.Tgl_sp2d_2', 'k.Tgl_sp2d_3', 'k.Tgl_sp2d_4', 'k.Tgl_sp2d_5', 'k.Tgl_sp2d_6',
          'k.Tgl_sp2d_7', 'k.Tgl_sp2d_8', 'k.Tgl_sp2d_9', 'k.Tgl_sp2d_10', 'k.Tgl_sp2d_11', 'k.Tgl_sp2d_12',
          'k.Gaji1', 'k.Gaji2', 'k.Gaji3', 'k.Gaji4', 'k.Gaji5', 'k.Gaji6', 'k.Gaji7', 'k.Gaji8', 'k.Gaji9', 'k.Gaji10', 'k.Gaji11', 'k.Gaji12',
          'k.TPD1', 'k.TPD2', 'k.TPD3', 'k.TPD4', 'k.TPD5', 'k.TPD6', 'k.TPD7', 'k.TPD8', 'k.TPD9', 'k.TPD10', 'k.TPD11', 'k.TPD12',
          'k.TKGB1', 'k.TKGB2', 'k.TKGB3', 'k.TKGB4', 'k.TKGB5', 'k.TKGB6', 'k.TKGB7', 'k.TKGB8', 'k.TKGB9', 'k.TKGB10', 'k.TKGB11', 'k.TKGB12',
          'k.bersihTPD1', 'k.bersihTPD2', 'k.bersihTPD3', 'k.bersihTPD4', 'k.bersihTPD5', 'k.bersihTPD6', 'k.bersihTPD7', 'k.bersihTPD8', 'k.bersihTPD9', 'k.bersihTPD10', 'k.bersihTPD11', 'k.bersihTPD12',
          'k.bersihTKGB1', 'k.bersihTKGB2', 'k.bersihTKGB3', 'k.bersihTKGB4', 'k.bersihTKGB5', 'k.bersihTKGB6', 'k.bersihTKGB7', 'k.bersihTKGB8', 'k.bersihTKGB9', 'k.bersihTKGB10', 'k.bersihTKGB11', 'k.bersihTKGB12'
        )
        ->get();

      // Filter berdasarkan tipe dan bank dari rekap jika bukan 'Semua'
      $rekapTipe = trim($rekap->tipe ?? 'Semua');
      $rekapBank = trim($rekap->bank ?? 'Semua');

      // Pre-load existing NIDN+bulan combos from t_uraian_pembayaran to prevent duplicates
      $existingUraian = [];
      try {
        $existingRows = DB::table('t_kekurangan')
          ->where('tahun', (string) $versi)
          ->where('jenis_pembayaran', 'PEMBAYARAN')
          ->select('nidn', 'keterangan') // Using keterangan to loosely detect month
          ->get();
        foreach ($existingRows as $er) {
          // Since we dropped 'bulan', we can't easily detect duplicates per month from ledger, 
          // but we can assume 'keterangan' or just NIDN uniqueness. We'll skip this specific check for now 
          // or parse 'keterangan' if it contains the month.
        }
      } catch (\Throwable $e) { /* table might not exist yet */ }

      $insertBatch = [];
      $batchSize = 500;
      $totalGenerated = 0;
      $totalSkipped = 0;

      foreach ($dosenRows as $dosen) {
        $nidn = trim($dosen->nidn ?? '');
        if ($nidn === '') continue;

        $jenisRow = trim($dosen->Jenis ?? '');
        $jenisKey = $jenisRow;

        $startBulan = $inputBulan ? (int)$inputBulan : 1;
        $endBulan = $inputBulan ? (int)$inputBulan : 12;

        for ($i = $startBulan; $i <= $endBulan; $i++) {
          $selisihTpd = (float) ($dosen->{'k_tpd' . $i} ?? 0);
          $selisihTkgb = (float) ($dosen->{'k_tkgb' . $i} ?? 0);
          $selisihTotal = $selisihTpd + $selisihTkgb;

          // Skip bulan tanpa selisih
          if (abs($selisihTotal) < 0.01) continue;

          // Hanya proses jika SP2D bulan tersebut ada
          $noSp2dBulan = trim((string) ($dosen->{'No_sp2d_' . $i} ?? ''));
          $tglSp2dBulan = trim((string) ($dosen->{'Tgl_sp2d_' . $i} ?? ''));
          if ($noSp2dBulan === '' || $tglSp2dBulan === '') continue;

          // Skip jika NIDN+bulan ini sudah pernah diproses (cegah duplikat)
          $dupeKey = $nidn . '-' . $i;
          if (isset($existingUraian[$dupeKey])) {
            $totalSkipped++;
            continue;
          }

          // Hitung pajak (proporsional atau nol jika manual)
          $gol = trim((string) ($dosen->{'Gol' . $i} ?? ''));
          $jabatan = (string) ($dosen->{'Jabatan' . $i} ?? ($dosen->Jabatan12 ?? ''));
          $kenaTKGB = $this->isGuruBesarAtauProfesor($jabatan);
          $tarif = (float) (($tarifMap[$jenisKey][$gol] ?? 0) ?: 0);

          if ($inputNominal !== null) {
              // Jika manual input "dibayar_berapa", kita asumsikan inputNominal adalah nilai bersih yang disetor
              $totalBersih = (float) $inputNominal;
              $totalPajak = 0; // Kita biarkan 0 untuk cicilan manual karena sulit diproporsikan jika hanya sisa
              $nominalKotor = $totalBersih;
          } else {
              $nominalKotor = abs($selisihTpd) + abs($selisihTkgb);
              $pajakTpd = abs($selisihTpd) * $tarif;
              $pajakTkgb = $kenaTKGB ? (abs($selisihTkgb) * $tarif) : 0.0;
              $totalPajak = $pajakTpd + $pajakTkgb;
              $totalBersih = $nominalKotor - $totalPajak;
          }

          // Generate uraian (tanpa nama bulan karena sudah ada di kolom bulan)
          if ($inputUraian !== '') {
              $uraian = $inputUraian;
          } else {
              $uraian = $isKurang
                ? 'Pembayaran kekurangan'
                : 'Potongan kelebihan bayar';
          }

          $insertBatch[] = [
            'rekap_id' => $rekapId,
            'nidn' => $nidn,
            'tahun' => (string) $versi,
            'selisih' => $isKurang ? round($nominalKotor, 2) : -round($nominalKotor, 2),
            'jenis_pembayaran' => 'PEMBAYARAN_' . $i,
            'kode_bayar_k' => $noSp2d,
            'tgl_bayar_k' => $tanggalSp2d,
            'keterangan' => $uraian,
            'created_at' => now(),
            'updated_at' => now(),
          ];
          $totalGenerated++;

          if (count($insertBatch) >= $batchSize) {
            DB::table('t_kekurangan')->insert($insertBatch);
            $insertBatch = [];
          }
        }
        
        // --- LOGIC PEMOTONGAN KE BULAN DEPAN ---
        if ($trxType === 'Pemotongan' && $inputNominal !== null && (float)$inputNominal > 0) {
            $remainingDeductionNet = (float) $inputNominal;
            $updateData = [];
            
            for ($m = 1; $m <= 12; $m++) {
                if ($remainingDeductionNet <= 0) break;
                
                // Cek apakah belum gajian: GajiM kosong atau 0
                $gajiM = trim((string) ($dosen->{'Gaji' . $m} ?? ''));
                $gajiMVal = (float) str_replace(',', '', $gajiM);
                
                if ($gajiMVal == 0) {
                    $golM = trim((string) ($dosen->{'Gol' . $m} ?? ''));
                    $jabatanM = (string) ($dosen->{'Jabatan' . $m} ?? ($dosen->Jabatan12 ?? ''));
                    $kenaTKGBM = $this->isGuruBesarAtauProfesor($jabatanM);
                    $tarifM = (float) (($tarifMap[$jenisKey][$golM] ?? 0) ?: 0);
                    
                    // 1. Coba potong dari TPD
                    $currentNetTPD = (float) str_replace(',', '', ($dosen->{'bersihTPD' . $m} ?? 0));
                    if ($currentNetTPD > 0 && $remainingDeductionNet > 0) {
                        $deductionNet = min($currentNetTPD, $remainingDeductionNet);
                        $deductionGross = $deductionNet;
                        if ($tarifM < 1 && $tarifM >= 0) {
                            $deductionGross = $deductionNet / (1 - $tarifM);
                        }
                        
                        $currentGrossTPD = (float) str_replace(',', '', ($dosen->{'TPD' . $m} ?? 0));
                        $newGrossTPD = max(0, $currentGrossTPD - $deductionGross);
                        $newPajakTPD = $newGrossTPD * $tarifM;
                        $newNetTPD = $newGrossTPD - $newPajakTPD;
                        
                        $updateData['TPD' . $m] = $newGrossTPD;
                        $updateData['nilaiPajakTPD' . $m] = $newPajakTPD;
                        $updateData['bersihTPD' . $m] = $newNetTPD;
                        
                        $remainingDeductionNet -= $deductionNet;
                    }
                    
                    // 2. Jika masih ada sisa, coba potong dari TKGB
                    $currentNetTKGB = (float) str_replace(',', '', ($dosen->{'bersihTKGB' . $m} ?? 0));
                    if ($currentNetTKGB > 0 && $remainingDeductionNet > 0 && $kenaTKGBM) {
                        $deductionNet = min($currentNetTKGB, $remainingDeductionNet);
                        $deductionGross = $deductionNet;
                        if ($tarifM < 1 && $tarifM >= 0) {
                            $deductionGross = $deductionNet / (1 - $tarifM);
                        }
                        
                        $currentGrossTKGB = (float) str_replace(',', '', ($dosen->{'TKGB' . $m} ?? 0));
                        $newGrossTKGB = max(0, $currentGrossTKGB - $deductionGross);
                        $newPajakTKGB = $newGrossTKGB * $tarifM;
                        $newNetTKGB = $newGrossTKGB - $newPajakTKGB;
                        
                        $updateData['TKGB' . $m] = $newGrossTKGB;
                        $updateData['nilaiPajakTKGB' . $m] = $newPajakTKGB;
                        $updateData['bersihTKGB' . $m] = $newNetTKGB;
                        
                        $remainingDeductionNet -= $deductionNet;
                    }
                }
            }
            
            if (!empty($updateData)) {
                DB::table('s_transaksi_2')
                  ->where('NIDN', $nidn)
                  ->where('Tahun_Versi', $versi)
                  ->update($updateData);
            }
        }
      }

      if (!empty($insertBatch)) {
        DB::table('t_kekurangan')->insert($insertBatch);
      }

      DB::commit();

      $isLunas = false;
      if ($nidnInput) {
          $paidRecords = DB::table('t_kekurangan')
            ->where('tahun', $versi)
            ->where('nidn', $nidnInput)
            ->where('jenis_pembayaran', 'like', 'PEMBAYARAN_%')
            ->select('jenis_pembayaran', 'selisih as nominal')
            ->get();
          $paidKotorByMonth = [];
          foreach ($paidRecords as $pr) {
              $parts = explode('_', $pr->jenis_pembayaran);
              $m = isset($parts[1]) ? (int) $parts[1] : 0;
              if ($m > 0) {
                  if (!isset($paidKotorByMonth[$m])) $paidKotorByMonth[$m] = 0;
                  $paidKotorByMonth[$m] += abs((float) $pr->nominal);
              }
          }
          
          $k2_sub = clone $this->getPivotSubquery($versi);
          $dosenData = DB::table('s_transaksi_2 as k')
            ->joinSub($k2_sub, 'ku', function($join) { $join->on('k.NIDN', '=', 'ku.nidn'); })
            ->where('k.Tahun_Versi', $versi)
            ->where('k.NIDN', $nidnInput)
            ->first();
            
          if ($dosenData) {
              $hasUnpaid = false;
              for ($m = 1; $m <= 12; $m++) {
                  $selisihTpd = (float) ($dosenData->{'k_tpd' . $m} ?? 0);
                  $selisihTkgb = (float) ($dosenData->{'k_tkgb' . $m} ?? 0);
                  $selisihTotalKotor = abs($selisihTpd) + abs($selisihTkgb);
                  $paidKotor = $paidKotorByMonth[$m] ?? 0;
                  if ($selisihTotalKotor > 0.01 && $paidKotor < ($selisihTotalKotor - 0.01)) {
                      $hasUnpaid = true;
                      break;
                  }
              }
              $isLunas = !$hasUnpaid;
          }
      }

      $skipMsg = $totalSkipped > 0 ? " ({$totalSkipped} baris di-skip karena sudah pernah diproses.)" : '';
      return response()->json([
        'success' => true,
        'is_lunas' => $isLunas,
        'message' => "Berhasil memproses SP2D. {$totalGenerated} baris uraian pembayaran di-generate.{$skipMsg}",
      ]);
    } catch (\Throwable $e) {
      DB::rollBack();
      Log::error('prosesAksiSp2d failed', [
        'rekap_id' => $rekapId,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);
      return response()->json(['success' => false, 'message' => 'Gagal memproses SP2D: ' . $e->getMessage()], 500);
    }
  }

  public function destroyTahun(Request $request)
  {
    $versi = session('tahun');
    DB::table('t_kekurangan')->where('tahun', $versi)->delete();
    DB::table('u_rekap_kekurangan')->whereRaw('RIGHT(periode, 4) = ?', [$versi])->delete();
    return redirect()->route('admin.kekurangan-bayar')->with('success', "Semua data pada tahun {$versi} berhasil dihapus.");
  }

  public function destroyKurang(Request $request)
  {
    $versi = session('tahun');
    
    // Find NIDNs in existing Rekap Kurang so they are NOT deleted here
    $allRekaps = DB::table('u_rekap_kekurangan')
        ->whereRaw('RIGHT(periode, 4) = ?', [$versi])
        ->where(function ($q) { $q->where('excel', 'like', 'rekap_kekurangan/%')->orWhere('periode', 'like', 'Kurang%'); })
        ->get();
        
    $rekapsKurangNidns = [];
    foreach ($allRekaps as $rek) {
        $nids = $this->getNidnsInRekap($rek, $versi, true);
        $rekapsKurangNidns = array_merge($rekapsKurangNidns, $nids);
    }
    $rekapsKurangNidns = array_unique($rekapsKurangNidns);
    
    // Protect fully paid NIDNs
    list($fullyPaidNidns, ) = $this->getFullyPaidNidns($versi);
    $protectedNidns = array_unique(array_merge($rekapsKurangNidns, $fullyPaidNidns));

    $query = DB::table('t_kekurangan')
        ->where('tahun', $versi)
        ->where('jenis_pembayaran', 'like', 'K_%');
        
    if (!empty($protectedNidns)) {
        $query->whereNotIn('nidn', $protectedNidns);
    }
    $query->delete();
    
    return redirect()->route('admin.kekurangan-bayar')->with('success', "Data 'Kurang Bayar' tahun {$versi} (yang belum direkap) berhasil dihapus.");
  }

  public function destroyLebih(Request $request)
  {
    $versi = session('tahun');
    
    // Find NIDNs in existing Rekap Lebih so they are NOT deleted here
    $allRekaps = DB::table('u_rekap_kekurangan')
        ->whereRaw('RIGHT(periode, 4) = ?', [$versi])
        ->where(function ($q) { $q->where('excel', 'like', 'rekap_kelebihan/%')->orWhere('periode', 'like', 'Lebih%'); })
        ->get();
        
    $rekapsLebihNidns = [];
    foreach ($allRekaps as $rek) {
        $nids = $this->getNidnsInRekap($rek, $versi, false);
        $rekapsLebihNidns = array_merge($rekapsLebihNidns, $nids);
    }
    $rekapsLebihNidns = array_unique($rekapsLebihNidns);
    
    // Protect fully paid NIDNs
    list($fullyPaidNidns, ) = $this->getFullyPaidNidns($versi);
    $protectedNidns = array_unique(array_merge($rekapsLebihNidns, $fullyPaidNidns));

    $query = DB::table('t_kekurangan')
        ->where('tahun', $versi)
        ->where('jenis_pembayaran', 'like', 'L_%');
        
    if (!empty($protectedNidns)) {
        $query->whereNotIn('nidn', $protectedNidns);
    }
    $query->delete();
    
    return redirect()->route('admin.kekurangan-bayar')->with('success', "Data 'Lebih Bayar' tahun {$versi} (yang belum direkap) berhasil dihapus.");
  }

  public function destroyRekap(Request $request, $id)
  {
      $versi = session('tahun');
      $rekap = DB::table('u_rekap_kekurangan')->where('id', $id)->first();
      if (!$rekap) {
          return back()->with('error', 'Rekap tidak ditemukan.');
      }

      $periode = strtolower(trim($rekap->periode ?? ''));
      $isKurang = (strpos($periode, 'kurang') !== false);
      
      // Get NIDNs in this rekap
      $nidns = $this->getNidnsInRekap($rekap, $versi, $isKurang);
      
      DB::beginTransaction();
      try {
          // Delete underlying data for these NIDNs (original selisih + PEMBAYARAN rows)
          if (!empty($nidns)) {
              if ($isKurang) {
                  DB::table('t_kekurangan')->where('tahun', $versi)->where('jenis_pembayaran', 'like', 'K_%')->whereIn('nidn', $nidns)->delete();
              } else {
                  DB::table('t_kekurangan')->where('tahun', $versi)->where('jenis_pembayaran', 'like', 'L_%')->whereIn('nidn', $nidns)->delete();
              }
              // Also delete PEMBAYARAN rows generated by SP2D processing for this rekap
              DB::table('t_kekurangan')->where('tahun', $versi)
                  ->where('jenis_pembayaran', 'LIKE', 'PEMBAYARAN%')
                  ->whereIn('nidn', $nidns)
                  ->delete();
          }
          
          // Delete file
          foreach (['excel', 'pdf'] as $key) {
              if (!empty($rekap->$key)) {
                  $rel = str_replace('\\', '/', trim($rekap->$key));
                  if (strpos($rel, 'storage/') === 0) $rel = substr($rel, 8);
                  Storage::disk('public')->delete($rel);
              }
          }
          
          // Delete Rekap row
          DB::table('u_rekap_kekurangan')->where('id', $id)->delete();
          
          DB::commit();
          return back()->with('success', 'Rekap beserta data kurang/lebih bayar untuk dosen terkait berhasil dihapus.');
      } catch (\Throwable $e) {
          DB::rollBack();
          Log::error('Gagal hapus rekap tunggal: ' . $e->getMessage());
          return back()->with('error', 'Gagal menghapus rekap dan data terkait.');
      }
  }


  public function destroySemuaRekap(Request $request)
  {
      $versi = session('tahun');
      $rekaps = DB::table('u_rekap_kekurangan')->whereRaw('RIGHT(periode, 4) = ?', [$versi])->get();
      
      DB::beginTransaction();
      try {
          foreach ($rekaps as $rekap) {
              $periode = strtolower(trim($rekap->periode ?? ''));
              $isKurang = (strpos($periode, 'kurang') !== false);
              
              // Get NIDNs in this rekap
              $nidns = $this->getNidnsInRekap($rekap, $versi, $isKurang);
              
              // Delete underlying data for these NIDNs (original selisih + PEMBAYARAN rows)
              if (!empty($nidns)) {
                  if ($isKurang) {
                      DB::table('t_kekurangan')->where('tahun', $versi)->where('jenis_pembayaran', 'like', 'K_%')->whereIn('nidn', $nidns)->delete();
                  } else {
                      DB::table('t_kekurangan')->where('tahun', $versi)->where('jenis_pembayaran', 'like', 'L_%')->whereIn('nidn', $nidns)->delete();
                  }
                  // Also delete PEMBAYARAN rows generated by SP2D processing
                  DB::table('t_kekurangan')->where('tahun', $versi)
                      ->where('jenis_pembayaran', 'LIKE', 'PEMBAYARAN%')
                      ->whereIn('nidn', $nidns)
                      ->delete();
              }
              
              // Delete file
              foreach (['excel', 'pdf'] as $key) {
                  if (!empty($rekap->$key)) {
                      $rel = str_replace('\\', '/', trim($rekap->$key));
                      if (strpos($rel, 'storage/') === 0) $rel = substr($rel, 8);
                      Storage::disk('public')->delete($rel);
                  }
              }
              
              // Delete Rekap row
              DB::table('u_rekap_kekurangan')->where('id', $rekap->id)->delete();
          }
          
          DB::commit();
          return back()->with('success', 'Semua rekap beserta data terkait berhasil dihapus.');
      } catch (\Throwable $e) {
          DB::rollBack();
          Log::error('Gagal hapus semua rekap: ' . $e->getMessage());
          return back()->with('error', 'Gagal menghapus rekap dan data terkait.');
      }
  }

  public function getRiwayat(Request $request)
  {
      $nidn = $request->input('nidn');
      $tahun = session('versi', date('Y'));

      if (!$nidn) {
          return response()->json(['success' => false, 'message' => 'NIDN tidak valid']);
      }

      $riwayatRows = DB::table('t_kekurangan')
          ->where('nidn', $nidn)
          ->where('tahun', $tahun)
          ->where('jenis_pembayaran', 'like', 'PEMBAYARAN_%')
          ->select('id', 'jenis_pembayaran', 'kode_bayar_k as nomor', 'tgl_bayar_k as tanggal', 'selisih as nominal', 'keterangan as uraian_pembayaran')
          ->orderBy('id', 'asc')
          ->get();
      
      $riwayat = $riwayatRows->map(function($r) {
          $parts = explode('_', $r->jenis_pembayaran);
          $r->bulan = isset($parts[1]) ? (int)$parts[1] : 0;
          $r->bersih = $r->nominal; // Asumsi kotor = bersih jika tarif 0, tidak disimpan terpisah lagi. UI menggunakan bersih.
          return $r;
      })->sortBy('bulan')->values();

      return response()->json([
          'success' => true,
          'data' => $riwayat
      ]);
  }

  public function detailRekap(Request $request, $id)
  {
      $rekap = DB::table('u_rekap_kekurangan')->where('id', $id)->first();
      if (!$rekap) {
          return redirect()->route('admin.kekurangan-bayar')->with('error', 'Data rekap tidak ditemukan.');
      }

      $versi = session('tahun');
      if (!$versi) {
          return redirect()->route('admin.kekurangan-bayar')->with('error', 'Tahun belum dipilih.');
      }

      // Parse rekap details
      $periode = strtolower(trim($rekap->periode ?? ''));
      $isKurang = (strpos($periode, 'kurang') !== false);
      
      $cTipe = trim($rekap->tipe ?? 'Semua');
      $cBank = trim($rekap->bank ?? 'Semua');
      $cJenis = trim($rekap->jenis ?? 'Semua');
      
      // Parse exclude nidns
      $excludeNidns = [];
      if (!empty($rekap->exclude_nidns)) {
          $excludeNidns = explode(',', $rekap->exclude_nidns);
          $excludeNidns = array_map('trim', $excludeNidns);
          $excludeNidns = array_filter($excludeNidns);
      }

      $k2_sub = clone $this->getPivotSubquery($versi);
      $bersihCondition = $isKurang ? '(ku.bersih + 0) < 0' : '(ku.bersih + 0) > 0';

      $base = DB::table('s_transaksi_2 as k')
          ->joinSub($k2_sub, 'ku', 'ku.nidn', '=', 'k.NIDN')
          ->where('k.Tahun_Versi', $versi)
          ->whereRaw($bersihCondition);

      if ($cTipe !== 'Semua') {
          if ($cTipe === 'TPD') { $base->whereRaw('(ku.jml_tpd + 0) <> 0'); } 
          elseif ($cTipe === 'TKGB') { $base->whereRaw('(ku.jml_tkgb + 0) <> 0'); }
      }
      if ($cJenis !== 'Semua') {
          if (strtoupper($cJenis) === 'PNS') {
              $base->where(function ($q) {
                  $q->whereRaw("UPPER(k.Jenis) LIKE '%PNS%'")
                    ->whereRaw("UPPER(k.Jenis) NOT LIKE '%NON%'");
              });
          } elseif (strtoupper($cJenis) === 'NON PNS') {
              $base->whereRaw("UPPER(k.Jenis) LIKE '%NON%'");
          } else {
              $base->whereRaw("TRIM(k.Jenis) = ?", [trim($cJenis)]);
          }
      }
      if ($cBank !== 'Semua') { 
          $base->whereRaw('TRIM(k.Bank) = ?', [trim($cBank)]); 
      }

      $select = [
          'k.NIDN as NIDN', 'k.Nama as Nama', 'k.Jenis as Jenis', 'k.Bank as Bank',
          'k.Jabatan12 as Jabatan12', 'k.Aktif as Aktif', 'ku.bersih as delta_bersih',
      ];
      for ($i = 1; $i <= 12; $i++) {
          $select[] = 'k.Gol' . $i; $select[] = 'k.Jabatan' . $i;
          $select[] = 'k.TPD' . $i; $select[] = 'k.TKGB' . $i; $select[] = 'k.Gaji' . $i;
          $select[] = 'k.No_sp2d_' . $i; $select[] = 'k.Tgl_sp2d_' . $i;
      }

      if (!empty($excludeNidns)) {
          $base->whereNotIn('k.NIDN', $excludeNidns);
      }
      
      // JIKA REKAP BELUM DIPROSES SP2D, HILANGKAN DOSEN YANG SUDAH LUNAS SECARA INDIVIDU
      if (empty($rekap->sp2d)) {
          list($fullyPaidNidns, $paidKotorByNidnMonthDummy) = $this->getFullyPaidNidns($versi);
          if (!empty($fullyPaidNidns)) {
              $base->whereNotIn('k.NIDN', $fullyPaidNidns);
          }
      }
      
      $search = $request->input('search');
      if ($search) {
          $base->where(function($q) use ($search) {
              $q->where('k.NIDN', 'like', "%{$search}%")
                ->orWhere('k.Nama', 'like', "%{$search}%");
          });
      }

      $dosenRows = $base->paginate(50)->appends(request()->query());
      
      // Load tarif map
      $tarifMap = [];
      try { $tarifMap = $this->loadTarifPajakMap(); } catch (\Throwable $e) {}

      // Calculate totals for each row so we can display how much they owe
      $transformer = function ($row) use ($tarifMap) {
          $jenisRow = (string) ($row->Jenis ?? '');
          $jenisKey = trim($jenisRow);
          
          $sumDbBersih = 0.0; $sumAktBersih = 0.0;
          for ($i = 1; $i <= 12; $i++) {
              $sp2dOk = (trim((string) ($row->{'No_sp2d_' . $i} ?? '')) !== '' && trim((string) ($row->{'Tgl_sp2d_' . $i} ?? '')) !== '');
              if ($sp2dOk) {
                  $aktKotorTPD = (float) $this->parseMoney($row->{'TPD' . $i} ?? 0);
                  $aktKotorTKGB = (float) $this->parseMoney($row->{'TKGB' . $i} ?? 0);
                  $gaji = $this->parseMoney($row->{'Gaji' . $i} ?? 0);
                  
                  $gol = trim((string) ($row->{'Gol' . $i} ?? ''));
                  $jabatan = (string) ($row->{'Jabatan' . $i} ?? ($row->Jabatan12 ?? ''));
                  $kenaTKGB = $this->isGuruBesarAtauProfesor($jabatan);
                  
                  $k_tpd = (float) ($row->{'k_tpd' . $i} ?? 0);
                  $k_tkgb = (float) ($row->{'k_tkgb' . $i} ?? 0);
                  
                  $dbKotorTPD = $aktKotorTPD + $k_tpd;
                  $dbKotorTKGB = $aktKotorTKGB + $k_tkgb;
                  
                  $tarif = (float) (($tarifMap[$jenisKey][$gol] ?? 0) ?: 0);
                  
                  $dbPajakTPD = $dbKotorTPD * $tarif;
                  $dbPajakTKGB = $kenaTKGB ? ($dbKotorTKGB * $tarif) : 0.0;
                  $dbBersih = ($dbKotorTPD - $dbPajakTPD) + ($dbKotorTKGB - $dbPajakTKGB);
                  
                  $aktPajakTPD = $aktKotorTPD * $tarif;
                  $aktPajakTKGB = $kenaTKGB ? ($aktKotorTKGB * $tarif) : 0.0;
                  $aktBersih = ($aktKotorTPD - $aktPajakTPD) + ($aktKotorTKGB - $aktPajakTKGB);
                  
                  $sumDbBersih += $dbBersih;
                  $sumAktBersih += $aktBersih;
              }
          }
          $row->kesimpulan = $sumAktBersih - $sumDbBersih;
          return $row;
      };

      $dosenRows->getCollection()->transform($transformer);

      return view('admin.kekurangan-bayar-detail-rekap', compact('rekap', 'dosenRows', 'versi', 'isKurang'));
  }

  public function excludeFromRekap(Request $request, $id)
  {
      $rekap = DB::table('u_rekap_kekurangan')->where('id', $id)->first();
      if (!$rekap) {
          return back()->with('error', 'Rekap tidak ditemukan.');
      }
      
      $nidn = $request->input('nidn');
      if (!$nidn) {
          return back()->with('error', 'NIDN harus diisi.');
      }
      
      $nominal = (float) $request->input('nominal', 0);
      
      $excludeNidns = [];
      if (!empty($rekap->exclude_nidns)) {
          $excludeNidns = explode(',', $rekap->exclude_nidns);
          $excludeNidns = array_map('trim', $excludeNidns);
      }
      
      if (!in_array($nidn, $excludeNidns)) {
          $excludeNidns[] = $nidn;
          
          $newCount = max(0, (int) $rekap->pegawai - 1);
          $newTotal = max(0, (float) $rekap->total_nominal - abs($nominal));
          
          DB::table('u_rekap_kekurangan')->where('id', $id)->update([
              'exclude_nidns' => implode(',', array_filter($excludeNidns)),
              'pegawai' => (string) $newCount,
              'total_nominal' => $newTotal,
              'updated_at' => now(),
          ]);
      }
      
      return back()->with('success', "NIDN {$nidn} berhasil dikeluarkan dari rekap.");
  }

  public function updateRiwayat(Request $request)
  {
      $id = $request->input('id');
      $nominal = $request->input('nominal'); // ini nominal bersih
      $uraian = $request->input('uraian');
      $nomor = $request->input('nomor');
      $tanggal = $request->input('tanggal');

      if (!$id) {
          return response()->json(['success' => false, 'message' => 'ID Riwayat tidak valid']);
      }

      $riwayat = DB::table('t_kekurangan')->where('id', $id)->first();
      if (!$riwayat) {
          return response()->json(['success' => false, 'message' => 'Data riwayat tidak ditemukan']);
      }

      $nominalBersih = (float) str_replace(',', '', trim((string) $nominal));
      if ($nominalBersih < 0) {
          return response()->json(['success' => false, 'message' => 'Nominal tidak boleh kurang dari 0']);
      }

      // Ambil transaksi untuk hitung ulang pajak/kotor
      $transaksi = DB::table('s_transaksi_2')
          ->where(function ($q) use ($riwayat) {
              $q->where('NIDN', $riwayat->nidn)->orWhere('NUPTK', $riwayat->nidn);
          })
          ->where('Tahun_Versi', $riwayat->tahun)
          ->first();

      $tarif = 0.0;
      if ($transaksi) {
          $jenisKey = trim((string) ($transaksi->Jenis ?? ''));
          if (strcasecmp($jenisKey, 'PNS') === 0 || str_starts_with(strtoupper($jenisKey), 'PNS') || (str_contains(strtoupper($jenisKey), 'PNS') && !str_contains(strtoupper($jenisKey), 'NON'))) {
              $jenisKey = 'PNS';
          } else {
              $jenisKey = 'NON PNS';
          }
          $parts = explode('_', $riwayat->jenis_pembayaran);
          $bulan = isset($parts[1]) ? (int)$parts[1] : 0;
          $gol = trim((string) ($transaksi->{'Gol' . $bulan} ?? ''));
          $tarifMap = $this->loadTarifPajakMap();
          $tarif = (float) (($tarifMap[$jenisKey][$gol] ?? 0) ?: 0);
      }

      $nominalKotor = $nominalBersih;
      if ($tarif < 1 && $tarif >= 0) {
          $nominalKotor = $nominalBersih / (1 - $tarif);
      }
      $totalPajak = $nominalKotor * $tarif;

      DB::table('t_kekurangan')->where('id', $id)->update([
          'selisih' => round($nominalKotor, 2),
          'keterangan' => $uraian,
          'kode_bayar_k' => $nomor,
          'tgl_bayar_k' => $tanggal,
          'updated_at' => now()
      ]);

      return response()->json(['success' => true, 'message' => 'Data riwayat berhasil diupdate']);
  }
}
