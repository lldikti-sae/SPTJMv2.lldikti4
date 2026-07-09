<?php
echo DB::table('s_transaksi_2')
    ->where('Aktif', '1')
    ->where('Tahun_Versi', 2026)
    ->where('Eligible_span', 'YA')
    ->where('Jenis', 'PNS')
    ->where(function ($q) {
        $bulanPendek = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
        foreach ($bulanPendek as $bln) {
            $q->orWhere($bln, 1);
        }
    })->count();
echo PHP_EOL;
