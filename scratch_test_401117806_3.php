<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$nidn = '401117806';
$row = \Illuminate\Support\Facades\DB::table('s_transaksi_2')->where('NIDN', $nidn)->first();

print_r($row);
