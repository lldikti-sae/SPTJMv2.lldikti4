<?php require 'vendor/autoload.php'; $app = require_once 'bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); 
$t = 2026;
echo "PNS Aktif (Eligible): " . DB::table('s_transaksi_2')->where('tahun_versi', $t)->where('jenis', 'PNS')->whereIn('Eligible_span', ['YA','Y','1','TRUE'])->count() . "\n";
echo "NON PNS Aktif (Eligible): " . DB::table('s_transaksi_2')->where('tahun_versi', $t)->where('jenis', 'NON PNS')->whereIn('Eligible_span', ['YA','Y','1','TRUE'])->count() . "\n";
echo "PNS Tidak Aktif (Eligible): " . DB::table('s_transaksi_2')->where('tahun_versi', $t)->where('jenis', 'PNS')->whereIn('Eligible_span', ['TIDAK','TDK','N','0','FALSE','NO'])->count() . "\n";
echo "NON PNS Tidak Aktif (Eligible): " . DB::table('s_transaksi_2')->where('tahun_versi', $t)->where('jenis', 'NON PNS')->whereIn('Eligible_span', ['TIDAK','TDK','N','0','FALSE','NO'])->count() . "\n";
