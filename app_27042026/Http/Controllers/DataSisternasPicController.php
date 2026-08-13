<?php

namespace App\Http\Controllers;

use App\Exports\DataSisterExport;
use Carbon\Carbon;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use League\Csv\Reader;
use League\Csv\Exception;

class DataSisternasPicController extends Controller
{
  public function index(Request $request)
  {
    $data = [];
    $table = $request->query('sisternas');

    if ($table) {
      // Validasi agar hanya tabel yang diperbolehkan yang bisa diakses
      $allowedTables = ['n_sister_genap_bj', 'o_sister_genap_tl', 'p_sister_ganjil'];

      if (in_array($table, $allowedTables)) {
        $data = DB::table($table)->get();
      } else {
        return redirect()
          ->route('pic.data-sisternas')
          ->with('error', 'Tabel tidak valid.');
      }
    }
    return view('pic.data-sisternas', compact('data'));
  }
  public function exportData(Request $request)
  {
    //ambil dari req->query
    $sisternas = request()->query('sisternas');
    $name = [
      'o_sister_genap_tl' => 'sister genap tl',
      'p_sister_ganjil' => 'sister ganjil',
      'n_sister_genap_bj' => 'sister genap bj'
    ];

    $file_name = "Data-" . $name[$sisternas] . "-" . Carbon::now()->format('Ymd-His');
    $export = Excel::download(new DataSisterExport($sisternas), $file_name . '.xlsx');
    return $export;
  }
}
