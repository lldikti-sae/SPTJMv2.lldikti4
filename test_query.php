<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$d = App\Models\Transaksi::where('NIDN', '2563269')->where('Tahun_Versi', 2026)->select('*', Illuminate\Support\Facades\DB::raw("NULLIF(Jabatan7, '') AS jabatan"), Illuminate\Support\Facades\DB::raw("NULLIF(Gol7, '') AS gol"))->first();
echo json_encode(['jabatan' => $d->jabatan]);
