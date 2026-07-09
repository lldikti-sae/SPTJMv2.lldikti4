<?php
$q = DB::table('s_transaksi_2')
    ->where('Aktif', '1')
    ->where('Tahun_Versi', 2026)
    ->where('Eligible_span', 'YA')
    ->where('Jenis', 'PNS');
echo 'Base: ' . $q->count() . PHP_EOL;

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
echo 'With Gaji: ' . $q->count() . PHP_EOL;
