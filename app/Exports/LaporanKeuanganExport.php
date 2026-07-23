<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromGenerator;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class LaporanKeuanganExport implements FromGenerator, WithHeadings, WithTitle, WithEvents
{
  protected $kode_pt;
  protected $tahun;
  protected $nidn;

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

  public function __construct($kode_pt, $tahun = null, $nidn = null)
  {
    $this->kode_pt = $kode_pt;
    $this->tahun = $tahun ?: date('Y');
    $this->nidn = $nidn;
  }

  public function generator(): \Generator
  {
    // Use s_transaksi_2 as the canonical source for export
    $select = [
      DB::raw('`t`.`NIDN` AS nidn'),
      DB::raw('`t`.`NUPTK` AS nuptk'),
      DB::raw('`t`.`Nama` AS nama'),
      DB::raw('`t`.`Jenis` AS jenis'),
      DB::raw('`t`.`Aktif` AS aktif'),
      DB::raw('`t`.`Eligible_span` AS eligible_span'),
      DB::raw('`t`.`Bank` AS bank'),
      DB::raw('`t`.`Kode_PT` AS kode_pt'),
      DB::raw('`t`.`PTS` AS pts'),
      DB::raw('(COALESCE(`t`.`Gaji1`,0)+COALESCE(`t`.`Gaji2`,0)+COALESCE(`t`.`Gaji3`,0)+COALESCE(`t`.`Gaji4`,0)+COALESCE(`t`.`Gaji5`,0)+COALESCE(`t`.`Gaji6`,0)+COALESCE(`t`.`Gaji7`,0)+COALESCE(`t`.`Gaji8`,0)+COALESCE(`t`.`Gaji9`,0)+COALESCE(`t`.`Gaji10`,0)+COALESCE(`t`.`Gaji11`,0)+COALESCE(`t`.`Gaji12`,0)) AS total_gaji'),
    ];

    // add monthly columns: KodeUsulan1..12, TPD1..12, TKGB1..12 + bersihTPD/TKGB (aktual)
    for ($i = 1; $i <= 12; $i++) {
      $select[] = DB::raw("`t`.`KodeUsulan{$i}` AS kodeusulan{$i}");
      $select[] = DB::raw("`t`.`TPD{$i}` AS tpd{$i}");
      $select[] = DB::raw("`t`.`TKGB{$i}` AS tkgb{$i}");
      $select[] = DB::raw("`t`.`bersihTPD{$i}` AS bersihtpd{$i}");
      $select[] = DB::raw("`t`.`bersihTKGB{$i}` AS bersihtkgb{$i}");

      // Fields needed to compute selisih like /admin/kekurangan-bayar/cek
      $select[] = DB::raw("`t`.`Jabatan{$i}` AS jabatan{$i}");
      $select[] = DB::raw("`t`.`Gaji{$i}` AS gaji{$i}");
      $select[] = DB::raw("`t`.`No_sp2d_{$i}` AS no_sp2d_{$i}");
      $select[] = DB::raw("`t`.`Tgl_sp2d_{$i}` AS tgl_sp2d_{$i}");
    }

    $query = DB::table('s_transaksi_2 as t')
      ->select($select)
      ->where('Tahun_Versi', $this->tahun);

    if (!empty($this->kode_pt)) {
      $query->whereRaw('TRIM(`t`.`Kode_PT`) = ?', [$this->kode_pt]);
    }

    if ($this->nidn) {
      $nidn = $this->nidn;
      $query->where(function ($q) use ($nidn) {
        $q->where('t.NIDN', 'like', "%{$nidn}%")
          ->orWhere('t.NUPTK', 'like', "%{$nidn}%");
      });
    }

    Log::debug('LaporanKeuanganExport: params', [
      'kode_pt' => $this->kode_pt,
      'nidn' => $this->nidn,
      'tahun_session' => session('tahun'),
    ]);

    // Execute using cursor via generator for memory efficiency

    // Prepare accumulators for footer totals
    $totalGajiPerMonth = array_fill(0, 12, 0.0);
    $totalTpdPerMonth = array_fill(0, 12, 0.0);
    $totalTkgbPerMonth = array_fill(0, 12, 0.0);
    $grandTotalGaji = 0.0;
    $grandTotalTPD = 0.0;
    $grandTotalTKGB = 0.0;
    $grandSelisihTPD = 0.0;
    $grandSelisihTKGB = 0.0;

    // Use cursor() to keep memory usage low for large exports
    foreach ($query->cursor() as $d) {
      $row = [
        $d->nidn,
        $d->nuptk ?? '-',
        $d->nama,
        $d->jenis,
        $d->jabatan12 ?? '',
        $d->aktif ? 'Aktif' : 'Tidak Aktif',
        $d->eligible_span,
        $d->bank,
        $d->kode_pt,
        $d->pts,
      ];

      $totalGaji = (float) ($d->total_gaji ?? 0);
      $totalTPD = 0.0;
      $totalTKGB = 0.0;
      $selisihTPD = 0.0;
      $selisihTKGB = 0.0;

      for ($i = 1; $i <= 12; $i++) {
        $kode = $d->{"kodeusulan{$i}"} ?? '-';
        $tpd = $this->parseMoney($d->{"tpd{$i}"} ?? 0);
        $tkgb = $this->parseMoney($d->{"tkgb{$i}"} ?? 0);

        // Monthly Gaji: base salary from DB (Gaji1..12)
        $gajiShown = $this->parseMoney($d->{"gaji{$i}"} ?? 0);

        $row[] = $gajiShown;
        $row[] = $kode;
        $row[] = $tpd;
        $row[] = $tkgb;

        $totalGaji += $gajiShown;
        $totalTPD += $tpd;
        $totalTKGB += $tkgb;

        $totalGajiPerMonth[$i - 1] += $gajiShown;
        $totalTpdPerMonth[$i - 1] += $tpd;
        $totalTkgbPerMonth[$i - 1] += $tkgb;
        $grandTotalGaji += $gajiShown;
        $grandTotalTPD += $tpd;
        $grandTotalTKGB += $tkgb;

        $gaji = $this->parseMoney($d->{"gaji{$i}"} ?? 0);
        if ($tpd == 0 && $tkgb == 0 && $gaji == 0) {
          continue;
        }
        $jabatan = $d->{"jabatan{$i}"} ?? ($d->jabatan12 ?? '');
        $kenaTkgb = $this->isGuruBesarAtauProfesor($jabatan);
        [$aktTpd, $aktTkgb] = $this->splitAktualKotorFromGaji($gaji, $kenaTkgb);

        $selisihTPD += ($tpd - $aktTpd);
        $selisihTKGB += ($tkgb - $aktTkgb);
      }

      $grandSelisihTPD += $selisihTPD;
      $grandSelisihTKGB += $selisihTKGB;

      $finalRow = array_merge($row, [
        (float) $totalGaji,
        (float) $totalTPD,
        (float) $totalTKGB,
        (float) $selisihTPD,
        (float) $selisihTKGB,
        (float) $totalGaji,
        (float) $totalTPD,
        (float) $totalTKGB,
      ]);

      // Debug log for exported selisih values
      try {
        Log::debug('LaporanKeuanganExport: row_selisih', [
          'nidn' => $d->nidn ?? null,
          'nuptk' => $d->nuptk ?? null,
          'selisihTPD' => (float) $selisihTPD,
          'selisihTKGB' => (float) $selisihTKGB,
          'final_row_length' => count($finalRow),
        ]);
      } catch (\Exception $e) {
        // swallow logging errors to avoid breaking export
      }

      yield $finalRow;
    }

    // Build footer row matching headings count (66 columns)
    $footer = [];
    // First 10 columns: label + blanks to match merged header
    $footer[] = 'Jumlah';
    for ($i = 1; $i < 10; $i++) {
      $footer[] = '';
    }

    // Per-month totals: Gaji (TPD+TKGB), KC placeholder, TPD, TKGB
    for ($i = 0; $i < 12; $i++) {
      $footer[] = (float) $totalGajiPerMonth[$i];
      $footer[] = '-';
      $footer[] = (float) $totalTpdPerMonth[$i];
      $footer[] = (float) $totalTkgbPerMonth[$i];
    }

    // Overall totals and selisih
    $footer[] = (float) $grandTotalGaji;
    $footer[] = (float) $grandTotalTPD;
    $footer[] = (float) $grandTotalTKGB;
    $footer[] = (float) $grandSelisihTPD;
    $footer[] = (float) $grandSelisihTKGB;
    $footer[] = (float) $grandTotalGaji;
    $footer[] = (float) $grandTotalTPD;
    $footer[] = (float) $grandTotalTKGB;

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
    $base = [
      'NIDN',
      'NUPTK',
      'Nama',
      'Jenis',
      'Jabatan',
      'Status',
      'Eligible Span',
      'Nama Bank',
      'Kode PT',
      'Nama Perguruan Tinggi',
    ];

    $bulanHeaders = [];
    foreach ($this->bulanNames as $bulan) {
      $bulanHeaders[] = 'Gaji';
      $bulanHeaders[] = 'KC';
      $bulanHeaders[] = 'TPD';
      $bulanHeaders[] = 'TKGB';
    }

    $akhir = [
      'Jumlah Gaji',
      'Jumlah TPD',
      'Jumlah TKGB',
      'Selisih TPD',
      'Selisih TKGB',
      'Total Gaji',
      'Total TPD',
      'Total TKGB',
    ];

    return array_merge($base, $bulanHeaders, $akhir);
  }

  public function title(): string
  {
    return 'Laporan Keuangan';
  }

  public function registerEvents(): array
  {
    return [
      AfterSheet::class => function (AfterSheet $event) {
        $sheet = $event->sheet->getDelegate();

        // Insert a blank top row so we can place a title above the headings
        $sheet->insertNewRowBefore(1, 1);

        // Merge header sesuai kolom (now includes NUPTK)
        $sheet->mergeCells('A1:J1')->setCellValue('A1', 'Identitas Dosen');

        $startCol = 11; // Kolom K (after adding NUPTK)
        foreach ($this->bulanNames as $i => $bulan) {
          $start = Coordinate::stringFromColumnIndex($startCol + $i * 4);
          $end = Coordinate::stringFromColumnIndex($startCol + $i * 4 + 3);
          $sheet->mergeCells("{$start}1:{$end}1")->setCellValue("{$start}1", $bulan);
        }

        // Merge untuk Jumlah, Selisih, Total
        $jumlahStart = $startCol + 12 * 4;
        $jumlahEnd = $jumlahStart + 2;
        $selisihEnd = $jumlahEnd + 2;
        $totalEnd = $selisihEnd + 3;

        $sheet
          ->mergeCells(
            Coordinate::stringFromColumnIndex($jumlahStart) . '1:' . Coordinate::stringFromColumnIndex($jumlahEnd) . '1'
          )
          ->setCellValue(Coordinate::stringFromColumnIndex($jumlahStart) . '1', 'Jumlah');

        $sheet
          ->mergeCells(
            Coordinate::stringFromColumnIndex($jumlahEnd + 1) .
              '1:' .
              Coordinate::stringFromColumnIndex($selisihEnd) .
              '1'
          )
          ->setCellValue(Coordinate::stringFromColumnIndex($jumlahEnd + 1) . '1', 'Selisih Bayar');

        $sheet
          ->mergeCells(
            Coordinate::stringFromColumnIndex($selisihEnd + 1) .
              '1:' .
              Coordinate::stringFromColumnIndex($totalEnd) .
              '1'
          )
          ->setCellValue(Coordinate::stringFromColumnIndex($selisihEnd + 1) . '1', 'Total');

        // Header detail baris ke-2 (headings are written by WithHeadings; ensure they exist)
        $headers = $this->headings();
        foreach ($headers as $i => $text) {
          $col = Coordinate::stringFromColumnIndex($i + 1);
          // write/overwrite the heading at row 2 to ensure consistency
          $sheet->setCellValue("{$col}2", $text);
        }

        // Styling header
        $lastCol = Coordinate::stringFromColumnIndex(count($headers));
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

        // Ensure Selisih columns (Selisih TPD, Selisih TKGB) have numeric 0 instead of blank cells
        try {
          $highestRow = $sheet->getHighestRow();
          // calculate 1-based column indexes: Selisih TPD = 62, Selisih TKGB = 63
          $selisihTpdCol = 62;
          $selisihTkgbCol = 63;

          for ($r = 3; $r <= $highestRow; $r++) {
            $valTpd = $sheet->getCellByColumnAndRow($selisihTpdCol, $r)->getValue();
            $valTkgb = $sheet->getCellByColumnAndRow($selisihTkgbCol, $r)->getValue();

            if ($valTpd === null || $valTpd === '') {
              $sheet->setCellValueByColumnAndRow($selisihTpdCol, $r, 0);
            }
            if ($valTkgb === null || $valTkgb === '') {
              $sheet->setCellValueByColumnAndRow($selisihTkgbCol, $r, 0);
            }
          }

          // Merge first 10 columns on the footer row and set label 'Jumlah'
          try {
            $footerRow = $sheet->getHighestRow();
            $sheet->mergeCells("A{$footerRow}:J{$footerRow}")->setCellValue("A{$footerRow}", 'Jumlah');
            // align and bold the merged footer label
            $sheet->getStyle("A{$footerRow}:J{$footerRow}")
              ->getAlignment()
              ->setHorizontal(Alignment::HORIZONTAL_CENTER)
              ->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle("A{$footerRow}:J{$footerRow}")->getFont()->setBold(true);
          } catch (\Exception $e) {
            // ignore merge errors
          }
        } catch (\Exception $e) {
          // swallow to avoid breaking export
        }
      },
    ];
  }
}
