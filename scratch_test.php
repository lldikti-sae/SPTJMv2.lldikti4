<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$k = \Illuminate\Support\Facades\DB::table('t_kekurangan')->where('nidn', '10036806')->get();
foreach($k as $r) {
    echo $r->jenis_pembayaran . " -> " . $r->selisih . "\n";
}
