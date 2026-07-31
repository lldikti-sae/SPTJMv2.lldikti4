<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KekuranganBayarExport implements FromArray, WithHeadings, WithStyles, WithColumnFormatting, ShouldAutoSize
{
    private array $rows;
    private int $rowCount;
    private array $headings;

    public function __construct(array $rows, ?array $headings = null)
    {
        $this->rows = $rows;
        $this->rowCount = count($rows);

        $this->headings = $headings ?: [
            'No',
            'NIDN',
            'NUPTK',
            'Nama',
            'Jenis',
            'Jabatan',
            'Status',
            'Bank',
            'Jan TPD', 'Jan TKGB', 'Jan TPD (Aktual)', 'Jan TKGB (Aktual)',
            'Feb TPD', 'Feb TKGB', 'Feb TPD (Aktual)', 'Feb TKGB (Aktual)',
            'Mar TPD', 'Mar TKGB', 'Mar TPD (Aktual)', 'Mar TKGB (Aktual)',
            'Apr TPD', 'Apr TKGB', 'Apr TPD (Aktual)', 'Apr TKGB (Aktual)',
            'Mei TPD', 'Mei TKGB', 'Mei TPD (Aktual)', 'Mei TKGB (Aktual)',
            'Jun TPD', 'Jun TKGB', 'Jun TPD (Aktual)', 'Jun TKGB (Aktual)',
            'Jul TPD', 'Jul TKGB', 'Jul TPD (Aktual)', 'Jul TKGB (Aktual)',
            'Ags TPD', 'Ags TKGB', 'Ags TPD (Aktual)', 'Ags TKGB (Aktual)',
            'Sep TPD', 'Sep TKGB', 'Sep TPD (Aktual)', 'Sep TKGB (Aktual)',
            'Okt TPD', 'Okt TKGB', 'Okt TPD (Aktual)', 'Okt TKGB (Aktual)',
            'Nov TPD', 'Nov TKGB', 'Nov TPD (Aktual)', 'Nov TKGB (Aktual)',
            'Des TPD', 'Des TKGB', 'Des TPD (Aktual)', 'Des TKGB (Aktual)',
            'Jumlah Kotor TPD',
            'Jumlah Kotor TKGB',
            'Nilai Pajak TPD',
            'Nilai Pajak TKGB',
            'Bersih',
            'Jumlah Kotor TPD (Aktual)',
            'Jumlah Kotor TKGB (Aktual)',
            'Nilai Pajak TPD (Aktual)',
            'Nilai Pajak TKGB (Aktual)',
            'Bersih (Aktual)',
            'Kesimpulan'
        ];
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function columnFormats(): array
    {
        $formats = [];
        for ($col = 9; $col <= 67; $col++) {
            $colLetter = $this->columnLetterFromIndex($col);
            $formats[$colLetter] = '#,##0';
        }
        return $formats;
    }

    public function styles(Worksheet $sheet)
    {
        $lastCol = $this->columnLetterFromIndex(count($this->headings));

        // Header style
        $sheet->getStyle('A1:' . $lastCol . '1')->getFont()->setBold(true);
        $sheet->getStyle('A1:' . $lastCol . '1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE5E7EB');

        // Borders for table range (header + rows)
        $lastRow = 1 + $this->rowCount;
        if ($lastRow < 1) {
            $lastRow = 1;
        }
        $range = 'A1:' . $lastCol . $lastRow;
        $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        return [];
    }

    private function columnLetterFromIndex(int $index): string
    {
        $index = max(1, $index);
        $letters = '';
        while ($index > 0) {
            $mod = ($index - 1) % 26;
            $letters = chr(65 + $mod) . $letters;
            $index = (int) floor(($index - 1) / 26);
        }
        return $letters;
    }
}
