<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$res = DB::table('s_transaksi_2')->where('Jabatan12', 'like', '%prof%')->orWhere('Jabatan12', 'like', '%guru%')->select('NIDN', 'Jabatan12')->limit(5)->get()->toArray();
print_r($res);
