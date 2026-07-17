<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Dosen\MonitoringPembayaranDosenController;

// Find a dosen in s_transaksi_2 to use
$transaksi = DB::table('s_transaksi_2')->first();
if (!$transaksi) {
    die("No transactions found in s_transaksi_2\n");
}

$nidn = $transaksi->NIDN ?: $transaksi->NUPTK;
echo "Testing NIDN: " . $nidn . "\n";

// Find corresponding dosen in s_dosen or whatever guard model is used
// Let's check what model the guard 'dosen' uses.
// In auth.php, guard 'dosen' uses a provider. Let's find a record in s_dosen or table 'a_dosen'
// Let's print auth configuration to see
$modelClass = config('auth.providers.dosens.model') ?: config('auth.providers.dosen.model') ?: 'App\\Models\\Dosen';
echo "Dosen model class: " . $modelClass . "\n";

$dosenUser = null;
try {
    $dosenUser = $modelClass::where('nidn', $nidn)->orWhere('nuptk', $nidn)->first();
} catch (\Throwable $e) {
    echo "Error querying model: " . $e->getMessage() . "\n";
}

if (!$dosenUser) {
    // Try to get any dosen
    try {
        $dosenUser = $modelClass::first();
        if ($dosenUser) {
            $nidn = $dosenUser->nidn ?: $dosenUser->nuptk;
            echo "Fallback to first dosen user: " . $nidn . "\n";
        }
    } catch (\Throwable $e) {}
}

if (!$dosenUser) {
    // Mock user
    echo "Mocking dosen user\n";
    $dosenUser = new \stdClass();
    $dosenUser->nidn = $nidn;
}

Auth::guard('dosen')->setUser($dosenUser);

$request = Illuminate\Http\Request::create('/dosen/monitoring-pembayaran/data', 'POST', [
    'nidn' => $nidn,
    'start_year' => '2020',
    'end_year' => '2026',
    'tahun_versi' => $transaksi->tahun_versi ?? $transaksi->Tahun_Versi ?? '2024',
    'jenis_tunjangan' => 'sptjm'
]);

try {
    $controller = new MonitoringPembayaranDosenController();
    $response = $controller->data($request);
    echo "Status Code: " . $response->getStatusCode() . "\n";
    echo "Response Content: " . $response->getContent() . "\n";
} catch (\Throwable $e) {
    echo "EXCEPTION THROWN: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
