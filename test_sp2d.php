<?php
$q = DB::table('s_transaksi_2')->where('Tahun_Versi', 2026)->where('Jenis', 'PNS');
echo 'sp2d_1 not null: ' . (clone $q)->whereNotNull('No_sp2d_1')->count() . PHP_EOL;
echo 'sp2d_2 not null: ' . (clone $q)->whereNotNull('No_sp2d_2')->count() . PHP_EOL;
echo 'sp2d_3 not null: ' . (clone $q)->whereNotNull('No_sp2d_3')->count() . PHP_EOL;
