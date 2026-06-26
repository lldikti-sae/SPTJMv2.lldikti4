<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CutoffSisternasExport implements FromQuery, WithHeadings
{
    protected $table;

    public function __construct($table)
    {
        $this->table = $table;
    }

    public function query()
    {
        return \Illuminate\Support\Facades\DB::table($this->table)->orderBy('nidn');
    }

    public function headings(): array
    {
        return [
            'nidn','nuptk','no_sertifikat','nama_dosen','kode_pt','pt','prodi',
            'kesimpulan_bkd','kewajiban_khusus','kesimpulan','kd','kp','potongan_periodik'
        ];
    }
}
