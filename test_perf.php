<?php
require __DIR__.'/vendor/autoload.php';
require __DIR__.'/bootstrap/app.php';
$app = app();
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

function parseMoney($value) {
    if ($value === null) return 0.0;
    if (is_int($value) || is_float($value)) return (float) $value;
    $text = trim((string) $value);
    if ($text === '') return 0.0;
    $text = preg_replace('/[^0-9\-]/', '', $text);
    if ($text === '' || $text === '-') return 0.0;
    return (float) $text;
}

function isGuruBesarAtauProfesor($jabatan) {
    $text = strtolower(trim((string) $jabatan));
    if ($text === '') return false;
    return strpos($text, 'guru besar') !== false || strpos($text, 'profesor') !== false;
}

function splitAktualKotorFromGaji(float $gaji, bool $kenaTKGB) {
    if ($gaji == 0.0) return [0.0, 0.0];
    if (!$kenaTKGB) return [$gaji, 0.0];
    $tpd = $gaji / 3.0;
    $tkgb = $gaji - $tpd;
    return [$tpd, $tkgb];
}

$start = microtime(true);
$query = DB::table('s_transaksi_2 as t');
$selects = [];
for ($i = 1; $i <= 12; $i++) {
    $selects[] = DB::raw("t.`TPD{$i}` as `tpd{$i}`");
    $selects[] = DB::raw("t.`TKGB{$i}` as `tkgb{$i}`");
    $selects[] = DB::raw("t.`Jabatan{$i}` as `jabatan{$i}`");
    $selects[] = DB::raw("t.`Gaji{$i}` as `gaji{$i}`");
    $selects[] = DB::raw("t.`No_sp2d_{$i}` as `no_sp2d_{$i}`");
    $selects[] = DB::raw("t.`Tgl_sp2d_{$i}` as `tgl_sp2d_{$i}`");
}
$selects[] = DB::raw("COALESCE(t.`Jabatan12`, '-') as jabatan");
$query->select($selects);

echo "Query building done. Executing cursor...\n";
$count = 0;
foreach ($query->cursor() as $row) {
    $count++;
    for ($i = 1; $i <= 12; $i++) {
        $tpd = parseMoney($row->{"tpd{$i}"} ?? 0);
        $tkgb = parseMoney($row->{"tkgb{$i}"} ?? 0);
        $gaji = $tpd + $tkgb;

        $noSp2d = trim((string) ($row->{"no_sp2d_{$i}"} ?? ''));
        $tglSp2d = trim((string) ($row->{"tgl_sp2d_{$i}"} ?? ''));
        if ($noSp2d !== '' && $tglSp2d !== '') {
            $gajiDb = parseMoney($row->{"gaji{$i}"} ?? 0);
            $jabatan = $row->{"jabatan{$i}"} ?? ($row->Jabatan12 ?? $row->jabatan ?? '');
            $kenaTkgb = isGuruBesarAtauProfesor($jabatan);
            $res = splitAktualKotorFromGaji($gajiDb, $kenaTkgb);
        }
    }
}
$time = microtime(true) - $start;
echo "Processed $count rows in $time seconds. Peak Memory: " . (memory_get_peak_usage() / 1024 / 1024) . " MB\n";
