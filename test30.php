<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rows = Illuminate\Support\Facades\DB::table('s_transaksi_2')
    ->where('Bersih_Selisih', '>', 2170000)
    ->where('Bersih_Selisih', '<', 2180000)
    ->orWhere('Kotor_Selisih', '>', 2170000)
    ->orWhere('Kotor_Selisih', '<', 2180000)
    ->get(['NIDN', 'Bersih_Selisih', 'Kotor_Selisih']);
    
foreach ($rows as $r) {
    if ($r->Bersih_Selisih > 2170000 && $r->Bersih_Selisih < 2180000) echo "Bersih: " . $r->NIDN . " -> " . $r->Bersih_Selisih . "\n";
    if ($r->Kotor_Selisih > 2170000 && $r->Kotor_Selisih < 2180000) echo "Kotor: " . $r->NIDN . " -> " . $r->Kotor_Selisih . "\n";
}
