<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$jenis = DB::table('r_proses_cair')->select('jenis')->distinct()->pluck('jenis')->toArray();
print_r($jenis);
