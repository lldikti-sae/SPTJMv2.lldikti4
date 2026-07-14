<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CutoffSisternasExport implements FromQuery, WithHeadings
{
    protected $table;
    protected $tahun;

    public function __construct($table, $tahun)
    {
        $this->table = $table;
        $this->tahun = $tahun;
    }

    public function query()
    {
        return \Illuminate\Support\Facades\DB::table($this->table)->where('tahun', $this->tahun)->orderBy('nidn');
    }

    public function headings(): array
    {
        return [
            'nidn','nuptk','no_sertifikat','nama_dosen','kode_pt','pt','prodi',
            'kesimpulan_bkd','kewajiban_khusus','kesimpulan','kd','kp','potongan_periodik'
        ];
    }
}
