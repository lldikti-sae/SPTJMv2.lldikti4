<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rows = Illuminate\Support\Facades\DB::table('s_transaksi_2')
    ->where('Bersih_Selisih', 'like', '2174117%')
    ->orWhere('Kotor_Selisih', 'like', '2174117%')
    ->get(['NIDN', 'Bersih_Selisih', 'Kotor_Selisih', 'Pajak_Selisih']);
echo json_encode($rows, JSON_PRETTY_PRINT);
