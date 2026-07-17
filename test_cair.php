<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$cairs = Illuminate\Support\Facades\DB::table('s_tunjangan_kinerja')->whereNotNull('Kode_Cair')->pluck('Kode_Cair')->unique()->toArray();
$proses = Illuminate\Support\Facades\DB::table('r_proses_cair')->whereIn('no', $cairs)->get();
echo json_encode($proses);
