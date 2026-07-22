<?php require 'vendor/autoload.php'; $app = require_once 'bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); 
$t = 2021;
echo "Year $t\n";
echo 'Total: ' . DB::table('s_transaksi_2')->where('tahun_versi', $t)->count() . "\n";
echo 'Aktif=1: ' . DB::table('s_transaksi_2')->where('tahun_versi', $t)->where('Aktif', '1')->count() . "\n";
echo "PNS Aktif: " . DB::table('s_transaksi_2')->where('tahun_versi', $t)->where('jenis', 'PNS')->where('Aktif', '1')->count() . "\n";
echo "PNS Tidak Aktif: " . DB::table('s_transaksi_2')->where('tahun_versi', $t)->where('jenis', 'PNS')->where('aktif', '0')->count() . "\n";
echo "NON PNS Aktif: " . DB::table('s_transaksi_2')->where('tahun_versi', $t)->where('jenis', 'NON PNS')->where('Aktif', '1')->count() . "\n";
echo "NON PNS Tidak Aktif: " . DB::table('s_transaksi_2')->where('tahun_versi', $t)->where('jenis', 'NON PNS')->where('aktif', '0')->count() . "\n";
