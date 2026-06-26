<?php
require __DIR__.'/vendor/autoload.php';
require __DIR__.'/bootstrap/app.php';
$app = app();
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$user = \App\Models\User::where('role', 'pic')->first();
Auth::loginUsingId($user->id);
$req = Illuminate\Http\Request::create('/pic/laporan-keuangan', 'POST', ['draw' => 1, 'start' => 0, 'length' => 10], [], [], ['HTTP_X-Requested-With' => 'XMLHttpRequest']);
$res = $app->handle($req);
if ($res->getStatusCode() !== 200) {
    echo "ERROR: " . $res->getStatusCode() . "\n";
    echo $res->getContent();
} else {
    echo "SUCCESS\n";
    echo substr($res->getContent(), 0, 500);
}
