<?php
$c = app()->make('App\Http\Controllers\KekuranganBayarController');
session()->put('tahun', '2026');
$indexData = $c->index()->getData();
echo 'Kurang Rows: ' . count($indexData['detailKurang']) . "\n";
echo 'Lebih Rows: ' . count($indexData['detailLebih']) . "\n";
$zeroKesimpulanKurang = 0;
foreach($indexData['detailKurang'] as $r) {
    if (abs($r->bersih - $r->bersih_akt) < 0.01) $zeroKesimpulanKurang++;
}
echo 'Kurang with zero kesimpulan: ' . $zeroKesimpulanKurang . "\n";
$zeroKesimpulanLebih = 0;
foreach($indexData['detailLebih'] as $r) {
    if (abs($r->bersih - $r->bersih_akt) < 0.01) $zeroKesimpulanLebih++;
}
echo 'Lebih with zero kesimpulan: ' . $zeroKesimpulanLebih . "\n";
