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
      $allowedTables = ['p_sister_genap', 'p_sister_genap', 'p_sister_ganjil'];

      if (in_array($table, $allowedTables)) {
        // No need to fetch data here, as the view does not render it and it consumes massive memory.
        // Data is only used when exporting.
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
    set_time_limit(0);
    ini_set('memory_limit', '2048M');
    //ambil dari req->query
    $sisternas = request()->query('sisternas');
    $name = [
      'p_sister_genap' => 'sister genap tl',
      'p_sister_ganjil' => 'sister ganjil',
      'p_sister_genap' => 'sister genap bj'
    ];

    $file_name = "Data-" . $name[$sisternas] . "-" . Carbon::now()->format('Ymd-His');
    $export = Excel::download(new DataSisterExport($sisternas), $file_name . '.xlsx');
    return $export;
  }
}
