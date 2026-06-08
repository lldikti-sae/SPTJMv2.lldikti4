<?php
$t1 = microtime(true);
session()->put('tahun', '2024');
app('App\Http\Controllers\KekuranganBayarController')->index();
echo 'Total time: ' . ((microtime(true) - $t1) * 1000) . ' ms' . PHP_EOL;
