<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$nidn = '401117806';
$rows = \Illuminate\Support\Facades\DB::table('t_kekurangan')->where('nidn', $nidn)->get();

if ($rows->isEmpty()) {
    echo "No records in t_kekurangan for $nidn.\n";
} else {
    foreach ($rows as $r) {
        print_r($r);
    }
}
