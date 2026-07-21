<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$nidn = '1005128601';

$prosesCair = DB::table('r_proses_cair')
    ->where('tahun', '2026')
    ->where('jenis', 'TPD')
    ->whereRaw('FIND_IN_SET(?, nidns)', [$nidn])
    ->get();

foreach ($prosesCair as $c) {
    echo "Bulan: " . $c->pencairan_ke . " | SP2D: " . $c->no_sp2d . "\n";
}
