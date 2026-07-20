<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$nidn = '321066602';
$selectedYear = '2026';

$prosesCair = DB::table('r_proses_cair')
    ->where('tahun', $selectedYear)
    ->where('nidns', 'LIKE', '%' . $nidn . '%')
    ->get();

print_r($prosesCair);
