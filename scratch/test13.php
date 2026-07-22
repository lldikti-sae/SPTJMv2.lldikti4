<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$res = DB::table('a_dosen')->where('nidn', '314626737')->value('nama_dosen');
echo $res;
