<?php require 'vendor/autoload.php'; $app = require_once 'bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); 
$tahun = session('tahun') ?? 2026;
echo 'PNS Aktif: ' . DB::table('s_transaksi_2')->where('jenis', 'PNS')->where('aktif', '1')->where('tahun_versi', $tahun)->count() . "\n";
echo 'PNS Non: ' . DB::table('s_transaksi_2')->where('jenis', 'PNS')->where(function($q) { $q->where('aktif', '!=', '1')->orWhereNull('aktif'); })->where('tahun_versi', $tahun)->count() . "\n";
echo 'NON PNS Aktif: ' . DB::table('s_transaksi_2')->where('jenis', 'NON PNS')->where('aktif', '1')->where('tahun_versi', $tahun)->count() . "\n";
echo 'NON PNS Non: ' . DB::table('s_transaksi_2')->where('jenis', 'NON PNS')->where(function($q) { $q->where('aktif', '!=', '1')->orWhereNull('aktif'); })->where('tahun_versi', $tahun)->count() . "\n";
var_dump(Illuminate\Support\Facades\Cache::get('admin_dashboard_2026'));
