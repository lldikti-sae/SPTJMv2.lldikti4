<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rows = Illuminate\Support\Facades\DB::table('t_kekurangan')->where('nidn', '1002019401')->get();
echo json_encode($rows, JSON_PRETTY_PRINT);
