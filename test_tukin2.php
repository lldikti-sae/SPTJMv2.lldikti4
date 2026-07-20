<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$row = DB::table('s_tunjangan_kinerja')->whereNotNull('KP')->where('KP', '!=', '')->first();
print_r($row);
