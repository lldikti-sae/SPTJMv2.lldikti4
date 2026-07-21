<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/admin/monitoring-pembayaran/data', 'GET', [
    'nidn' => '10016601',
    'tahun' => '2025',
    'jenis_tunjangan' => 'tukin'
]);
$app->instance('request', $request);

$controller = $app->make(\App\Http\Controllers\MonitoringPembayaranController::class);
$response = $controller->data($request);
$data = json_decode($response->getContent(), true);

print_r([
    'gajiBulanan' => $data['gajiBulanan'][0], // Nominal Tukin
    'tukinDasar' => $data['tukinDasar'][0], // KD
    'tukinPrestasi' => $data['tukinPrestasi'][0], // KP
    'bersihTpd' => $data['bersihTpd'][0] // Bersih TPD dari SPTJM
]);
