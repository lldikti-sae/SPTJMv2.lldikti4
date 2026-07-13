<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$d = DB::table('s_transaksi_2')->where('Nama', 'TEST DOSEN GURU BESAR 1050')->first();
echo "Tahun_Versi: " . ($d ? $d->Tahun_Versi : "N/A") . "\n";
