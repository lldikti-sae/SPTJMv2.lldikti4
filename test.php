<?php require 'vendor/autoload.php'; $app = require_once 'bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); 
echo "Total: " . DB::table('s_transaksi_2')->where('tahun_versi', 2024)->count() . "\n";
echo "PNS Aktif: " . DB::table('s_transaksi_2')->where('tahun_versi', 2024)->where('jenis', 'PNS')->where('Aktif', '1')->count() . "\n";
echo "PNS Tidak Aktif: " . DB::table('s_transaksi_2')->where('tahun_versi', 2024)->where('jenis', 'PNS')->where(function($q) { $q->where('Aktif', '!=', '1')->orWhereNull('Aktif'); })->count() . "\n";
echo "NON PNS Aktif: " . DB::table('s_transaksi_2')->where('tahun_versi', 2024)->where('jenis', 'NON PNS')->where('Aktif', '1')->count() . "\n";
echo "NON PNS Tidak Aktif: " . DB::table('s_transaksi_2')->where('tahun_versi', 2024)->where('jenis', 'NON PNS')->where(function($q) { $q->where('Aktif', '!=', '1')->orWhereNull('Aktif'); })->count() . "\n";
