<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/admin/usulan-sptjm/data', 'POST', [
    'pilihsptjm' => 'TUKIN Berjalan',
    'bulan' => 'All',
    'status' => 'Usulan'
]);
session()->put('tahun', '2026');

$controller = new App\Http\Controllers\UsulanSptjmController();
$response = $controller->getData($request);

echo $response->getContent();
