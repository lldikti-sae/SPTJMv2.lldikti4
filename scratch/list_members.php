<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$pts = DB::table('a_pts')->where('aktif', 1)->limit(5)->get();
print_r("PTS:\n");
print_r($pts);

$dosen = DB::table('a_dosen')->where('aktif', 1)->limit(5)->get();
print_r("Dosen:\n");
print_r($dosen);
