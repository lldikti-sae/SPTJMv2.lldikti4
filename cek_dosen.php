<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$nidn = "1009108601";
$dosen = DB::table("s_transaksi_2")->where("NIDN", $nidn)->first();

echo "Aktif: " . $dosen->Aktif . "\n";
echo "Tahun_Versi: " . $dosen->Tahun_Versi . "\n";
echo "KodeUsulan6: " . $dosen->KodeUsulan6 . "\n";

$bkd = DB::table("p_sister_ganjil")->where("nidn", $nidn)->first();
if ($bkd) {
    echo "BKD Kesimpulan: " . $bkd->kesimpulan_bkd . "\n";
} else {
    echo "Not found in p_sister_ganjil\n";
}

