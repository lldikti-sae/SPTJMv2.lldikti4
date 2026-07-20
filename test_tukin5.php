<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$nidn = '10016601';
$tukinData = DB::table('s_tunjangan_kinerja')
    ->where('NIDN', $nidn)
    ->where('Tahun', '2025')
    ->get();
echo "Found: " . $tukinData->count() . "\n";
foreach($tukinData as $t) echo $t->Bulan . "\n";
