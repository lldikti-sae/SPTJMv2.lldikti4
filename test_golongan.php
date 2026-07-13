<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$gols = DB::table('e_golongan')->get();
foreach($gols as $g) {
    echo $g->Golongan . "\n";
}
