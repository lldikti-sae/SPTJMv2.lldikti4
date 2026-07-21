<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$row = DB::table('s_tunjangan_kinerja')->where('PP', '>', 1)->first();
print_r($row);
