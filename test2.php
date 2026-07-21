<?php require 'vendor/autoload.php'; $app = require_once 'bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); 
$t = session('tahun') ?? 2023;
echo 'Total: ' . DB::table('s_transaksi_2')->where('tahun_versi', $t)->count() . "\n";
echo 'Aktif=1: ' . DB::table('s_transaksi_2')->where('tahun_versi', $t)->where('Aktif', '1')->count() . "\n";
echo 'Aktif!=1: ' . DB::table('s_transaksi_2')->where('tahun_versi', $t)->where(function($q) { $q->where('Aktif', '!=', '1')->orWhereNull('Aktif'); })->count() . "\n";
echo 'Eligible=YA: ' . DB::table('s_transaksi_2')->where('tahun_versi', $t)->whereIn('Eligible_span', ['YA','Y','1','TRUE'])->count() . "\n";
