<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$res = DB::table('s_transaksi_2')->select('Jabatan12')->distinct()->whereNotNull('Jabatan12')->pluck('Jabatan12')->toArray();
print_r($res);
