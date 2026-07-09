<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$data = \Illuminate\Support\Facades\DB::table('r_proses_cair')->where('pencairan_ke', 6)->whereNull('no_sp2d')->get();
$request = Illuminate\Http\Request::create('/admin/rekap-pencairan?status=Proses&pencairan_ke=6', 'GET');
app()->instance('request', $request);
$view = view('admin.rekap-pencairan', ['data' => $data, 'status' => 'Proses', 'pencairanKe' => 6, 'tipeSptjm' => 'SPTJM'])->render();
file_put_contents('view_output.html', $view);
