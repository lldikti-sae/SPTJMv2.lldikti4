<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/admin/monitoring-pembayaran/data', 'GET', [
    'nidn' => '1005128601',
    'tahun' => '2026',
    'jenis_tunjangan' => 'sptjm'
]);
$app->instance('request', $request);

$controller = $app->make(\App\Http\Controllers\MonitoringPembayaranController::class);
$response = $controller->data($request);
$data = json_decode($response->getContent(), true);

print_r($data['noSp2d']);
print_r($data['kotorTpd']);
