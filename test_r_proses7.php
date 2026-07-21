<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$nidn = '1005128601';

$prosesCair = DB::table('r_proses_cair')
    ->whereRaw('FIND_IN_SET(?, nidns)', [$nidn])
    ->get();

if ($prosesCair->count() > 0) {
    echo "Found " . $prosesCair->count() . " records for $nidn\n";
} else {
    echo "Not found for $nidn\n";
}
