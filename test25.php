<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$row = Illuminate\Support\Facades\DB::table('s_transaksi_2')->where('NIDN', '10036806')->first();
echo "JENIS: " . ($row->Jenis ?? 'NULL') . "\n";
