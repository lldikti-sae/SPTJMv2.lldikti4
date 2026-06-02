<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$rows = DB::table('t_kekurangan')->where('jenis_pembayaran', 'not like', 'PEMBAYARAN%')->limit(10)->get();
print_r($rows);
