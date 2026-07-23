<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromGenerator;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class LaporanKeuanganPtsExport implements FromGenerator, WithHeadings, WithTitle, WithEvents, WithCustomStartCell
{
  protected $search;
  protected $kode_pts;
  protected $tahun;

  protected $bulanKeys = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'ags', 'sep', 'okt', 'nov', 'des'];
  protected $bulanNames = [
    'Januari',
    'Februari',
    'Maret',
    'April',
    'Mei',
    'Juni',
    'Juli',
    'Agustus',
    'September',
    'Oktober',
    'November',
    'Desember',
  ];

  public function __construct($kode_pts, $search = null, $tahun = null)
  {
    $this->search = $search;
    $this->kode_pts = $kode_pts;
    $this->tahun = $tahun ?: date('Y');
  }

  public function generator(): \Generator
  {
    // Map DB month columns to lowercase aliases expected by export structure
    $bulanDbMap = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Ags','Sep','Okt','Nov','Des'];

    // determine jabatan column based on session month (fallback to 12)
    $bulanIndex = (int) session('bulan');
    if ($bulanIndex < 1 || $bulanIndex > 12) $bulanIndex = 12;
    $jabatanCol = 'Jabatan' . $bulanIndex;

    $selects = [
      't.NIDN as nidn',
      't.NUPTK as nuptk',
      't.Nama as nama',
      't.Jenis as jenis',
      DB::raw("t.`{$jabatanCol}` as jabatan"),
      't.Aktif as aktif',
      't.Eligible_span as eligible_span',
      't.Bank as bank',
      't.Kode_PT as kode_pt',
      't.PTS as pts',
    ];
    foreach ($this->bulanKeys as $idx => $alias) {
      $col = $bulanDbMap[$idx];
      $selects[] = DB::raw("t.`$col` as `$alias`");
    }
    // Map KodeUsulan per bulan so we can compute "KC" and selisih bayar like admin
    for ($i = 1; $i <= 12; $i++) {
      $selects[] = DB::raw("t.`KodeUsulan{$i}` as `kodeusulan{$i}`");
    }
    for ($i = 1; $i <= 12; $i++) {
      $selects[] = DB::raw("t.`TPD{$i}` as `tpd{$i}`");
      $selects[] = DB::raw("t.`TKGB{$i}` as `tkgb{$i}`");
      $selects[] = DB::raw("t.`bersihTPD{$i}` as `bersihtpd{$i}`");
      $selects[] = DB::raw("t.`bersihTKGB{$i}` as `bersihtkgb{$i}`");

      // Fields needed to compute selisih like /admin/kekurangan-bayar/cek
      $selects[] = DB::raw("t.`Jabatan{$i}` as `jabatan{$i}`");
      $selects[] = DB::raw("t.`Gaji{$i}` as `gaji{$i}`");
      $selects[] = DB::raw("t.`No_sp2d_{$i}` as `no_sp2d_{$i}`");
      $selects[] = DB::raw("t.`Tgl_sp2d_{$i}` as `tgl_sp2d_{$i}`");
    }
    $selects[] = DB::raw('(COALESCE(t.Gaji1,0)+COALESCE(t.Gaji2,0)+COALESCE(t.Gaji3,0)+COALESCE(t.Gaji4,0)+COALESCE(t.Gaji5,0)+COALESCE(t.Gaji6,0)+COALESCE(t.Gaji7,0)+COALESCE(t.Gaji8,0)+COALESCE(t.Gaji9,0)+COALESCE(t.Gaji10,0)+COALESCE(t.Gaji11,0)+COALESCE(t.Gaji12,0)) as total_gaji');

    $query = DB::table('s_transaksi_2 as t')
      ->select($selects)
      ->where('t.Kode_PT', $this->kode_pts)
      ->where('t.Tahun_Versi', $this->tahun)
      ->orderBy('t.Nama');

    if ($this->search) {
      $query->where(function ($q) {
        $q->where('t.NIDN', 'like', "%{$this->search}%")
          ->orWhere('t.NUPTK', 'like', "%{$this->search}%")
          ->orWhere('t.Nama', 'like', "%{$this->search}%");
      });
    }

    // Execute using cursor via generator for memory efficiency
    // Prepare accumulators for footer totals
    $sumGajiPerMonth = array_fill(0, 12, 0);
    $sumTPDPerMonth = array_fill(0, 12, 0);
    $sumTKGBPerMonth = array_fill(0, 12, 0);
    $totalGajiAll = 0;
    $totalTPDAll = 0;
    $totalTKGBAll = 0;

    $sumSelisihTPDAll = 0;
    $sumSelisihTKGBAll = 0;

    foreach ($query->cursor() as $d) {
      $row = [
        $d->nidn,
        $d->nuptk ?? '',
        $d->nama,
        $d->jenis,
        $d->jabatan,
        $d->aktif ? 'Aktif' : 'Tidak Aktif',
        $d->eligible_span,
        $d->bank,
        $d->kode_pt,
        $d->pts,
      ];

      $totalGaji = (float) ($d->total_gaji ?? 0);
      $totalGajiMonthly = 0;
      $totalTPD = 0;
      $totalTKGB = 0;

      $selisihTPDPerDosen = 0;
      $selisihTKGBPerDosen = 0;

      foreach ($this->bulanKeys as $idx => $b) {
        // KC comes from kodeusulanN
        $kc = $d->{"kodeusulan" . ($idx + 1)} ?? '-';
        $month = $idx + 1;
        $tpd = $this->parseMoney($d->{"tpd{$month}"} ?? 0);
        $tkgb = $this->parseMoney($d->{"tkgb{$month}"} ?? 0);

        // Monthly Gaji: base salary from DB (Gaji1..12)
        $gajiMonth = $this->parseMoney($d->{"gaji{$month}"} ?? 0);

        $row[] = $gajiMonth;
        $row[] = $kc;
        $row[] = $tpd;
        $row[] = $tkgb;

        $totalGaji += $gajiMonth;
        $totalGajiMonthly += $gajiMonth;
        $totalTPD += $tpd;
        $totalTKGB += $tkgb;

        $sumGajiPerMonth[$idx] += $gajiMonth;
        $sumTPDPerMonth[$idx] += $tpd;
        $sumTKGBPerMonth[$idx] += $tkgb;

        $gaji = $this->parseMoney($d->{"gaji{$month}"} ?? 0);
        if ($tpd == 0 && $tkgb == 0 && $gaji == 0) {
          continue;
        }
        $jabatan = $d->{"jabatan{$month}"} ?? ($d->jabatan12 ?? $d->jabatan ?? '');
        $kenaTkgb = $this->isGuruBesarAtauProfesor($jabatan);
        [$aktTpd, $aktTkgb] = $this->splitAktualKotorFromGaji($gaji, $kenaTkgb);

        $selisihTPDPerDosen += ($tpd - $aktTpd);
        $selisihTKGBPerDosen += ($tkgb - $aktTkgb);
      }

      // Footer grand total gaji matches monthly totals (sum of tpd+tkgb), not including base gaji
      $totalGajiAll += $totalGajiMonthly;
      $totalTPDAll += $totalTPD;
      $totalTKGBAll += $totalTKGB;

      // Ensure selisih values are numeric and default to 0
      $selisihTPDPerDosen = (float) ($selisihTPDPerDosen ?? 0);
      $selisihTKGBPerDosen = (float) ($selisihTKGBPerDosen ?? 0);

      yield array_merge($row, [
        (int) round($totalGaji),
        (int) round($totalTPD),
        (int) round($totalTKGB),
        (int) round($selisihTPDPerDosen),
        (int) round($selisihTKGBPerDosen),
        (int) round($totalGaji),
        (int) round($totalTPD),
        (int) round($totalTKGB),
      ]);

      $sumSelisihTPDAll += $selisihTPDPerDosen;
      $sumSelisihTKGBAll += $selisihTKGBPerDosen;
    }

    // Footer totals row: put label in first identity column; other identity cells empty
    $footer = array_fill(0, 10, '');
    $footer[0] = 'Jumlah';

    // For each month: Gaji, KC (empty), TPD, TKGB
    for ($i = 0; $i < 12; $i++) {
      $footer[] = $sumGajiPerMonth[$i];
      $footer[] = '';
      $footer[] = $sumTPDPerMonth[$i];
      $footer[] = $sumTKGBPerMonth[$i];
    }

    // Append grand totals matching the tail columns
    // Ensure footer selisih totals are numeric (default 0)
    $sumSelisihTPDAll = (float) ($sumSelisihTPDAll ?? 0);
    $sumSelisihTKGBAll = (float) ($sumSelisihTKGBAll ?? 0);
    $footer = array_merge($footer, [
      (int) round($totalGajiAll),
      (int) round($totalTPDAll),
      (int) round($totalTKGBAll),
      (int) round($sumSelisihTPDAll),
      (int) round($sumSelisihTKGBAll),
      (int) round($totalGajiAll),
      (int) round($totalTPDAll),
      (int) round($totalTKGBAll),
    ]);

    // Append footer as final row
    yield $footer;
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

  private function parseMoney($value): float
  {
    if ($value === null) return 0.0;
    if (is_int($value) || is_float($value)) return (float) $value;
    $text = trim((string) $value);
    if ($text === '') return 0.0;
    $text = preg_replace('/[^0-9\-]/', '', $text);
    if ($text === '' || $text === '-') return 0.0;
    return (float) $text;
  }

  public function headings(): array
  {
    $base = ['NIDN', 'NUPTK', 'Nama', 'Jenis', 'Jabatan', 'Status', 'Eligible Span', 'Nama Bank', 'Kode PT', 'Nama PT'];
    $bulanHeaders = [];

    foreach ($this->bulanNames as $bulan) {
      $bulanHeaders[] = 'Gaji';
      $bulanHeaders[] = 'KC';
      $bulanHeaders[] = 'TPD';
      $bulanHeaders[] = 'TKGB';
    }

    $akhir = [
      'Gaji',
      'TPD',
      'TKGB',
      'TPD',
      'TKGB',
      'Gaji',
      'TPD',
      'TKGB',
    ];

    return array_merge($base, $bulanHeaders, $akhir);
  }

  public function title(): string
  {
    return 'Laporan Keuangan';
  }

  // Let headings start at A2 so data begins at A3
  public function startCell(): string
  {
    return 'A2';
  }

  public function registerEvents(): array
  {
    return [
      AfterSheet::class => function (AfterSheet $event) {
        $sheet = $event->sheet->getDelegate();

        // Merge Header (identity columns expanded for NUPTK)
        $sheet->mergeCells('A1:J1')->setCellValue('A1', 'Identitas Dosen');
        $startCol = 11;

        foreach ($this->bulanNames as $i => $bulan) {
          $start = Coordinate::stringFromColumnIndex($startCol + $i * 4);
          $end = Coordinate::stringFromColumnIndex($startCol + $i * 4 + 3);
          $sheet->mergeCells("{$start}1:{$end}1")->setCellValue("{$start}1", $bulan);
        }

        $jumlahStart = $startCol + 12 * 4;
        $sheet
          ->mergeCells(
            Coordinate::stringFromColumnIndex($jumlahStart) .
              '1:' .
              Coordinate::stringFromColumnIndex($jumlahStart + 2) .
              '1'
          )
          ->setCellValue(Coordinate::stringFromColumnIndex($jumlahStart) . '1', 'Jumlah');

        $sheet
          ->mergeCells(
            Coordinate::stringFromColumnIndex($jumlahStart + 3) .
              '1:' .
              Coordinate::stringFromColumnIndex($jumlahStart + 4) .
              '1'
          )
          ->setCellValue(Coordinate::stringFromColumnIndex($jumlahStart + 3) . '1', 'Selisih Bayar');

        $sheet
          ->mergeCells(
            Coordinate::stringFromColumnIndex($jumlahStart + 5) .
              '1:' .
              Coordinate::stringFromColumnIndex($jumlahStart + 7) .
              '1'
          )
          ->setCellValue(Coordinate::stringFromColumnIndex($jumlahStart + 5) . '1', 'Total');

        // Headings are rendered automatically at A2 via WithHeadings + WithCustomStartCell

        $lastCol = Coordinate::stringFromColumnIndex(count($this->headings()));
        $sheet
          ->getStyle("A1:{$lastCol}2")
          ->getFont()
          ->setBold(true);
        $sheet
          ->getStyle("A1:{$lastCol}2")
          ->getAlignment()
          ->setHorizontal(Alignment::HORIZONTAL_CENTER)
          ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->getRowDimension(2)->setRowHeight(25);
        $sheet
          ->getStyle("A1:{$lastCol}" . $sheet->getHighestRow())
          ->getBorders()
          ->getAllBorders()
          ->setBorderStyle(Border::BORDER_THIN);

        // Merge identity columns on the footer totals row (last row)
        $lastRow = $sheet->getHighestRow();
        $sheet->mergeCells('A' . $lastRow . ':J' . $lastRow);
        $sheet->getStyle('A' . $lastRow)->getFont()->setBold(true);
        $sheet->getStyle('A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
      },
    ];
  }
}
