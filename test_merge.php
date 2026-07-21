<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$q = DB::table('q_sptjm')->where('tahun', 2026)->where('id_usulan', 'LIKE', 'BT%');
$dataUtama = (clone $q)->where('status', 'Usulan')->get();
$dataZero = collect();
$dataUsulan = $dataUtama->merge($dataZero);

echo json_encode([
    'count' => count($dataUsulan),
    'is_array_list' => array_is_list($dataUsulan->toArray()),
    'first' => $dataUsulan->first()
]);
