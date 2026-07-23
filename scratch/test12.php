<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$res = DB::table('s_transaksi_2')->where('nidn', '314626737')->where('tahun_versi', '2024')->first();
print_r($res);
