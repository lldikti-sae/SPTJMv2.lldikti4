<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DataSisterExport implements FromQuery, WithHeadings
{
  /**
   * @return \Illuminate\Support\Collection
   */
  //attribut
  private $sisternas;
  public function __construct($sisternas)
  {
    $this->sisternas = $sisternas;
  }
  public function query()
  {
    return DB::table($this->sisternas)->orderBy('NIDN');
  }

  public function headings(): array
  {
    return [
      'NIDN',
      'NUPTK',
      'No_Sertifikat',
      'Nama_Dosen',
      'Kode_PT',
      'PT',
      'Prodi',
      'Kesimpulan_BKD',
      'Kesimpulan_Khusus',
      'Kesimpulan',
      'KD',
      'KP',
      'Potongan_Periodik'
    ];
  }
}
