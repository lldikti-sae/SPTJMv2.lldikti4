<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
try {
    $row = Illuminate\Support\Facades\DB::table('s_transaksi_2')->where('tahun_versi', '2023')->first();
    $nidn = $row->nidn ?? $row->NIDN;
    $request = Illuminate\Http\Request::create('/admin/monitoring-pembayaran/data', 'POST', ['nidn' => $nidn, 'start_year' => '2023', 'end_year' => '2023', 'tahun_versi' => '2023', 'jenis_tunjangan' => 'tukin']);
    $controller = new App\Http\Controllers\MonitoringPembayaranController();
    $response = $controller->data($request);
    echo "No exception. Content: " . substr($response->getContent(), 0, 100);
} catch (\Throwable $e) {
    echo "CAUGHT EXCEPTION: " . $e->getMessage() . " on line " . $e->getLine() . " of " . $e->getFile() . "\n";
}
