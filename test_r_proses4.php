<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$row = DB::table('r_proses_cair')->whereNotNull('no_sp2d')->where('no_sp2d', '!=', '')->first();
if ($row) {
    echo "Found! no_sp2d: " . $row->no_sp2d . "\n";
} else {
    echo "Not found\n";
}
