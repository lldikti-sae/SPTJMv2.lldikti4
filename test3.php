<?php require 'vendor/autoload.php'; $app = require_once 'bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); 
$years = DB::table('s_transaksi_2')->select('Tahun_Versi')->distinct()->pluck('Tahun_Versi');
foreach($years as $t) {
    echo "Year $t: Total=" . DB::table('s_transaksi_2')->where('tahun_versi', $t)->count() . "\n";
}
