<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$nidn = '401117806';
$row = \Illuminate\Support\Facades\DB::table('s_transaksi_2')->where('NIDN', $nidn)->where('Tahun_Versi', '2026')->first();

if (!$row) {
    echo "No records for 2026\n";
} else {
    print_r($row);
}
