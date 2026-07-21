<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$res = DB::select('SHOW COLUMNS FROM a_dosen');
$cols = array_column((array)$res, 'Field');
print_r($cols);
