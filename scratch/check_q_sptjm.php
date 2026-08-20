<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$no = 48; // Try to get the latest r_proses_cair
$prosesCair = DB::table('r_proses_cair')->orderByDesc('no')->first();
if (!$prosesCair) die("No data");

$nidns = array_values(array_filter(array_map('trim', explode(',', (string) $prosesCair->nidns))));
echo "NIDNS: " . json_encode($nidns) . "\n";

$kodePTList = DB::table('s_tunjangan_kinerja')
  ->where(function($q) use ($nidns) {
      $q->whereIn('NIDN', $nidns)
        ->orWhereIn('NUPTK', $nidns);
  })
  ->where('Kode_Cair', (string) $prosesCair->pencairan_ke)
  ->where('Tahun', $prosesCair->tahun)
  ->distinct()
  ->pluck('Kode_PTS');
echo "Kode PT List: " . json_encode($kodePTList) . "\n";

// Test the update query
$affected = DB::table('q_sptjm')
  ->whereIn('kode_pts', $kodePTList)
  ->where('status', 'Proses')
  ->update(['status' => 'Selesai']);

echo "Affected rows in q_sptjm: " . $affected . "\n";
