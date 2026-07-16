<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$t = \DB::table('s_tunjangan_kinerja')->where('Tahun', '2026')->first(); 
if ($t) {
    echo "NIDN in TUKIN 2026: " . $t->NIDN . "\n";
    echo "Tahun: " . $t->Tahun . "\n";
    echo "Bulan: " . $t->Bulan . "\n";
    echo "Nilai_tukin_Jabatan: " . $t->Nilai_tukin_Jabatan . "\n";
    echo "KD: " . $t->KD . "\n";
    
    // Check if this NIDN is in s_transaksi_2 for 2026
    $trans = \DB::table('s_transaksi_2')->where('NIDN', $t->NIDN)->orWhere('NUPTK', $t->NIDN)->where('tahun_versi', '2026')->first();
    if ($trans) {
        echo "Found in s_transaksi_2 for 2026!\n";
    } else {
        echo "NOT FOUND in s_transaksi_2 for 2026!\n";
    }
}
