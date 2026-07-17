<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$req = Illuminate\Http\Request::create('/dummy', 'GET', [
    'nidn' => '306047104',
    'start_year' => 2026,
    'end_year' => 2026,
    'tahun_versi' => 2026,
    'jenis_tunjangan' => 'sptjm'
]);
$ctrl = new App\Http\Controllers\MonitoringPembayaranController();
$res = $ctrl->data($req);
file_put_contents('scratch/test_ajax.json', $res->getContent());
echo "Done\n";
