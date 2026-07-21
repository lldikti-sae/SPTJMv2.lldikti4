<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$nidn = '314626737'; // Guru Besar 1050
$req = Illuminate\Http\Request::create('/admin/monitoring-pembayaran/cari', 'POST', [
    'nidn' => $nidn,
    'start_year' => '2023',
    'end_year' => '2026',
    '_token' => csrf_token()
]);
$controller = app()->make(App\Http\Controllers\MonitoringPembayaranController::class);
$response = $controller->cari($req);
if(method_exists($response, 'render')) {
    $html = $response->render();
    if(strpos($html, 'Nominal TUKIN') !== false) {
        echo "FOUND Nominal TUKIN\n";
    } else {
        echo "NOT FOUND Nominal TUKIN\n";
    }
} else {
    echo "Redirected or Error\n";
}
