<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

$yosman = \DB::table('s_transaksi_2')->where('Nama', 'LIKE', '%yosman%')->first();
if ($yosman) {
    echo "s_transaksi_2:\n";
    echo "NIDN: " . $yosman->NIDN . " | NUPTK: " . $yosman->NUPTK . " | Nama: " . $yosman->Nama . "\n\n";

    $b_genap = \DB::table('n_sister_genap_bj')->where('nidn', $yosman->NIDN)->orWhere('nuptk', $yosman->NUPTK)->get();
    echo "n_sister_genap_bj:\n";
    foreach($b_genap as $b) {
        echo "- NIDN: " . $b->nidn . " | NUPTK: " . $b->nuptk . " | Nama: " . $b->nama . " | PT: " . $b->kode_pt . "\n";
    }

    $b_ganjil = \DB::table('p_sister_ganjil_tl')->where('nidn', $yosman->NIDN)->orWhere('nuptk', $yosman->NUPTK)->get();
    echo "\np_sister_ganjil_tl:\n";
    foreach($b_ganjil as $b) {
        echo "- NIDN: " . $b->nidn . " | NUPTK: " . $b->nuptk . " | Nama: " . $b->nama . " | PT: " . $b->kode_pt . "\n";
    }
} else {
    echo "Tidak ditemukan dr. Yosman di s_transaksi_2";
}
