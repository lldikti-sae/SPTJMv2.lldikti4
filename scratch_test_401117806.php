<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$nidn = '401117806';
$row = \Illuminate\Support\Facades\DB::table('s_transaksi_2')->where('NIDN', $nidn)->first();

if (!$row) {
    echo "NIDN $nidn not found.\n";
    exit;
}

echo "NIDN: $row->NIDN, Nama: $row->Nama\n";

for ($i = 1; $i <= 12; $i++) {
    $gaji = $row->{"Gaji$i"} ?? 0;
    $tpd = $row->{"TPD$i"} ?? 0;
    $tkgb = $row->{"TKGB$i"} ?? 0;
    $bersihTpd = $row->{"bersihTPD$i"} ?? 0;
    $bersihTkgb = $row->{"bersihTKGB$i"} ?? 0;
    
    echo "Bulan $i: Gaji=$gaji, TPD=$tpd, TKGB=$tkgb, bersihTPD=$bersihTpd, bersihTKGB=$bersihTkgb\n";
}
