<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$count = DB::table('s_transaksi_2')
    ->where('NIDN', '-')
    ->orWhere('NUPTK', '-')
    ->count();

echo "Rows with hyphen: $count\n";
