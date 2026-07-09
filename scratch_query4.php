<?php
$q = DB::table('s_transaksi_2')
    ->where('Aktif', '1')
    ->where('Tahun_Versi', 2026)
    ->where('Eligible_span', 'YA')
    ->where('Jenis', 'PNS');
$q->where(function ($query) {
    for ($i = 1; $i <= 12; $i++) {
        if ($i === 1) {
            $query->where('TPD'.$i, '>', 0)
                ->orWhere('TKGB'.$i, '>', 0);
        } else {
            $query->orWhere('TPD'.$i, '>', 0)
                ->orWhere('TKGB'.$i, '>', 0);
        }
    }
});

$allNidnsProcessed = DB::table('r_proses_cair')
        ->where('tahun', 2026)
        ->where('eligible_span', 'YA')
        ->where('tipe_sptjm', 'TUKIN')
        ->pluck('nidns')->all();

$processedArray = [];
foreach($allNidnsProcessed as $str) {
    $parts = explode(',', $str);
    foreach($parts as $p) {
        $processedArray[] = trim($p);
    }
}
$processedArray = array_unique(array_filter($processedArray));

if (!empty($processedArray)) {
    $chunks = array_chunk($processedArray, 1000);
    foreach($chunks as $chunk) {
        $q->whereNotIn('NIDN', $chunk);
        $q->whereNotIn('NUPTK', $chunk);
    }
}

echo 'Final Eligible TUKIN count: ' . $q->count() . PHP_EOL;
