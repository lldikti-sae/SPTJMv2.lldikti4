<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$deleted = Illuminate\Support\Facades\DB::table('t_kekurangan')->where('nidn', '1002019401')->where('jenis_pembayaran', 'PEMBAYARAN_1')->delete();
echo "Deleted " . $deleted . " rows\n";
