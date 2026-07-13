<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$grades = DB::table('c_grade_serdos')->get();
foreach($grades as $g) {
    echo $g->jabatan . " | " . $g->golongan . " | " . $g->masa_kerja_bawah . " - " . $g->masa_kerja_atas . "\n";
}
