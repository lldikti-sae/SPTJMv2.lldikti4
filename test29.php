<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rows = Illuminate\Support\Facades\DB::table('t_kekurangan')
    ->where('selisih', 'like', '2174117%')
    ->orWhere('selisih', 'like', '-2174117%')
    ->get();
echo json_encode($rows, JSON_PRETTY_PRINT);
