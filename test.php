<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/admin/monitoring-pembayaran/data', 'POST', ['jenis_tunjangan' => 'sptjm', 'tahun' => '2023']);
$kernel->handle($request);
$controller = app()->make('App\Http\Controllers\MonitoringPembayaranController');
$request->setRouteResolver(function() { return new Illuminate\Routing\Route('POST', '/admin/monitoring-pembayaran/data', ['uses' => 'App\Http\Controllers\MonitoringPembayaranController@data']); });
$response = $controller->data($request);
file_put_contents('response.json', $response->getContent());
