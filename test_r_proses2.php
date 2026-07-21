<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$nidn = '321066602';

$prosesCair = DB::table('r_proses_cair')
    ->whereRaw('FIND_IN_SET(?, nidns)', [$nidn])
    ->get();

print_r($prosesCair);
