<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$ptsUsers = DB::table('users')->where('role', 'pts')->limit(5)->get();
print_r("PTS:\n");
print_r($ptsUsers);

$dosenUsers = DB::table('users')->where('role', 'dosen')->limit(5)->get();
print_r("Dosen:\n");
print_r($dosenUsers);
