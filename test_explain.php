<?php
\ = '2026';
\ = app(\App\Http\Controllers\KekuranganBayarController::class)->getPivotSubquery(\);
\ = \DB::table('s_transaksi_2 as k')
    ->joinSub(\, 'ku', function (\) {
        \->on(\DB::raw("COALESCE(NULLIF(k.NIDN, ''), k.NUPTK)"), '=', 'ku.nidn');
    })
    ->where('k.Tahun_Versi', \)
    ->whereRaw('(ku.bersih + 0) < 0')->toSql();
\ = \DB::select('EXPLAIN ' . \, [\, \]);
print_r(\);
