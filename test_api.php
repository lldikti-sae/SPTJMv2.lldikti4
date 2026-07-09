<?php
use Illuminate\Http\Request;
use App\Http\Controllers\RekapUsulanEligibleController;

session()->put('tahun', 2026);
session()->put('bulan', 12);

$request = Request::create('/admin/rekap-usulan-eligible/data', 'GET', [
    'tipe_sptjm' => 'TUKIN',
    'pencairan_ke' => 'Semua',
    'bank' => 'Semua',
    'status_pegawai' => 'Semua',
    'Eligible_span' => 'YA',
    'tunjangan' => 'Semua',
    'draw' => 1,
    'start' => 0,
    'length' => 25
]);

$controller = app()->make(RekapUsulanEligibleController::class);
$response = $controller->data($request);
$data = json_decode($response->getContent(), true);
echo 'Records Total: ' . $data['recordsTotal'] . PHP_EOL;
echo 'Records Filtered: ' . $data['recordsFiltered'] . PHP_EOL;
echo 'Data Count: ' . count($data['data']) . PHP_EOL;
