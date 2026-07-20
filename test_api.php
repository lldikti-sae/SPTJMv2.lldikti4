<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/admin/monitoring-pembayaran/data', 'GET', [
    'nidn' => '321066602',
    'tahun' => '2026',
    'jenis_tunjangan' => 'sptjm'
]);

$controller = $app->make(\App\Http\Controllers\MonitoringPembayaranController::class);
$response = $controller->data($request);
echo $response->getContent();
