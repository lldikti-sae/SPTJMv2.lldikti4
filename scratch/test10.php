<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$nidn = '314626737'; // Guru Besar 1050
$req = Illuminate\Http\Request::create('/admin/monitoring-pembayaran/cari', 'POST', [
    'nidn' => $nidn,
    'start_year' => '2023',
    'end_year' => '2026',
    'tahun_versi' => '2024',
    '_token' => csrf_token()
]);
$controller = app()->make(App\Http\Controllers\MonitoringPembayaranController::class);
$response = $controller->cari($req);
if(method_exists($response, 'render')) {
    file_put_contents('scratch/rendered_view_2024.html', $response->render());
}
