<?php
$tipe_sptjm = 'TUKIN';
$status_pegawai = 'Semua';
$pencairan_ke = 'Semua';
$bank = 'Semua';
$tunjangan = 'Semua';
$eligible_span = 'YA';
$tahun = 2026;

$query = DB::table('s_transaksi_2')
    ->select('*')
    ->where('Aktif', '1')
    ->where('Tahun_Versi', $tahun)
    ->where('Eligible_span', $eligible_span);

if ($tipe_sptjm === 'TUKIN') {
    $query->where('Jenis', 'PNS');
}

$query->where(function ($q) {
    for ($i = 1; $i <= 12; $i++) {
        if ($i === 1) {
            $q->where("TPD$i", ">", 0)
            ->orWhere("TKGB$i", ">", 0);
        } else {
            $q->orWhere("TPD$i", ">", 0)
            ->orWhere("TKGB$i", ">", 0);
        }
    }
});

if ($status_pegawai != "Semua") {
    $query->where('jenis', $status_pegawai);
}

// Ignore exclude processed for now, we know it returns 454 if we skip it

echo $query->toSql() . PHP_EOL;
echo 'Count: ' . $query->count() . PHP_EOL;
