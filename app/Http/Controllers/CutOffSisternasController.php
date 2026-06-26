<?php

namespace App\Http\Controllers;

use App\Helpers\ErrorAlias;
use App\Imports\DataSisterImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\HeadingRowImport;
use App\Exports\CutoffSisternasExport;
use Yajra\DataTables\Facades\DataTables;

class CutOffSisternasController extends Controller
{
  public function index(Request $request)
  {
    if ($request->ajax()) {
      $table = $request->query('sisternas');

      if (!$table) {
        return response()->json([
          "draw" => intval($request->get('draw')),
          "recordsTotal" => 0,
          "recordsFiltered" => 0,
          "data" => []
        ]);
      }

      $allowedTables = ['n_sister_genap_bj', 'o_sister_genap_tl', 'p_sister_ganjil_tl'];

      if (in_array($table, $allowedTables)) {
        $query = DB::table($table);

        return DataTables::of($query)
          ->addIndexColumn()
          ->addColumn('aksi', function ($row) {
            if (auth()->check() && auth()->user()->role === 'pic') {
                return '<span class="text-muted">-</span>';
            }
            return '<button class="btn btn-icon btn-sm btn-warning edit-btn">
                                <span class="tf-icons bx bx-edit"></span>
                            </button>';
          })
          ->rawColumns(['aksi'])
          ->make(true);
      }

      return response()->json([
        "error" => "Tabel tidak valid."
      ], 400);
    }

    // kalau bukan request ajax, kembalikan view
    return view('admin.cutoff-sisternas');
  }


  public function update(Request $request)
  {
    $request->validate([
      // some lecturers only have one identifier; accept either nidn or nuptk
      'nidn' => 'nullable|string|required_without:nuptk',
      'sisternas' => 'required|in:n_sister_genap_bj,o_sister_genap_tl,p_sister_ganjil_tl',
      // allow other fields to be nullable so editing rows with blank values won't fail validation
      'nuptk' => 'nullable|string|required_without:nidn',
      'no_sertifikat' => 'nullable|string',
      'nama_dosen' => 'nullable|string',
      'kode_pt' => 'nullable|string',
      'pt' => 'nullable|string',
      'prodi' => 'nullable|string',
      'kesimpulan_bkd' => 'nullable|in:M,TM',
      'kewajiban_khusus' => 'nullable|in:Memenuhi,Tugas Belajar,Tidak Memenuhi',
      'kesimpulan' => 'nullable|in:Memenuhi,Tidak Memenuhi',
      'kd' => 'nullable|numeric',
      'kp' => 'nullable|numeric',
      'potongan_periodik' => 'nullable|numeric',
    ]);

    $table = $request->input('sisternas');

    // normalize empty strings to null for text inputs
    $norm = function ($v) {
      if ($v === null) return null;
      $s = is_string($v) ? trim($v) : $v;
      if (is_string($s) && $s === '') return null;
      return $s;
    };

    $nidn = $norm($request->input('nidn'));
    $nuptk = $norm($request->input('nuptk'));

    $updatePayload = [
      'nuptk' => $norm($request->input('nuptk')),
      'no_sertifikat' => $norm($request->input('no_sertifikat')),
      'nama_dosen' => $norm($request->input('nama_dosen')),
      'kode_pt' => $norm($request->input('kode_pt')),
      'pt' => $norm($request->input('pt')),
      'prodi' => $norm($request->input('prodi')),
      'kesimpulan_bkd' => $norm($request->input('kesimpulan_bkd')),
      'kewajiban_khusus' => $norm($request->input('kewajiban_khusus')),
      'kesimpulan' => $norm($request->input('kesimpulan')),
      'kd' => is_numeric($request->input('kd')) ? (float) $request->input('kd') : null,
      'kp' => is_numeric($request->input('kp')) ? (float) $request->input('kp') : null,
      'potongan_periodik' => is_numeric($request->input('potongan_periodik')) ? (float) $request->input('potongan_periodik') : null,
    ];

    // prioritized update to avoid accidentally matching null/blank identifiers
    $affected = 0;
    if ($nidn !== null) {
      $affected = DB::table($table)
        ->where('nidn', $nidn)
        ->update($updatePayload);
    }

    if ($affected === 0 && $nuptk !== null) {
      $affected = DB::table($table)
        ->where('nuptk', $nuptk)
        ->update($updatePayload);
    }

    // last-resort fallback for legacy data where nidn/nuptk might be swapped
    if ($affected === 0 && ($nidn !== null || $nuptk !== null)) {
      DB::table($table)
        ->where(function ($q) use ($nidn, $nuptk) {
          if ($nidn !== null) {
            $q->orWhere('nidn', $nidn)->orWhere('nuptk', $nidn);
          }
          if ($nuptk !== null) {
            $q->orWhere('nidn', $nuptk)->orWhere('nuptk', $nuptk);
          }
        })
        ->update($updatePayload);
    }

    return response()->json(['success' => true, 'message' => 'Data berhasil diperbarui!']);
  }


  //upload
  public function upload(Request $request)
  {
    @ini_set('max_execution_time', '0');
    @ini_set('memory_limit', '-1');
    @set_time_limit(0);

    $request->validate([
      'dokumen' => 'required',
      'table' => 'required|in:n_sister_genap_bj,o_sister_genap_tl,p_sister_ganjil_tl',
    ]);
    $file = $request->file(key: 'dokumen');
    $table = $request->input('table');
    try {
      // Manual CSV parsing (mirip dengan migrasi) to avoid PhpSpreadsheet timeouts
      $path = $file->getRealPath();
      $handle = fopen($path, 'r');
      if (!$handle) {
        return response()->json(['success' => false, 'message' => 'Tidak bisa membaca file CSV.'], 422);
      }

      // Read first line to detect delimiter and header
      $firstLine = fgets($handle);
      if ($firstLine === false) {
        fclose($handle);
        return response()->json(['success' => false, 'message' => 'Header CSV tidak terbaca.'], 422);
      }
      $firstLine = preg_replace('/^\xEF\xBB\xBF/', '', $firstLine);
      $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';
      $header = str_getcsv($firstLine, $delimiter);
      $header = array_map(function ($h) {
        $h = preg_replace('/^\xEF\xBB\xBF/', '', (string)$h);
        return trim($h, " \t\n\r\0\x0B\"'");
      }, $header);

      // Normalize header to lowercase underscore form
      $normalized = array_map(function ($h) {
        return strtolower(str_replace(' ', '_', $h));
      }, $header);

      $expected = [
        'nidn','nuptk','no_sertifikat','nama_dosen','kode_pt','pt','prodi',
        'kesimpulan_bkd','kewajiban_khusus','kesimpulan','kd','kp','potongan_periodik'
      ];

      $missing = array_values(array_diff($expected, $normalized));
      $extra = array_values(array_diff($normalized, $expected));
      if (!empty($missing) || !empty($extra) || count($expected) !== count($normalized)) {
        fclose($handle);
        return response()->json([
          'success' => false,
          'headerMismatch' => true,
          'message' => 'Kolom CSV tidak sesuai dengan tabel.',
          'expectedColumns' => $expected,
          'missingColumns' => $missing,
          'extraColumns' => $extra,
          'expectedCount' => count($expected),
          'foundCount' => count($normalized),
        ], 422);
      }

      // Helper to parse numeric/percent values
      $parseDecimal = function ($value) {
        if ($value === '' || $value === null) return null;
        if (is_string($value) && str_contains($value, '%')) {
          return floatval(str_replace('%', '', $value)) / 100;
        }
        return is_numeric($value) ? (float)$value : null;
      };

      $batch = [];
      $inserted = 0;
      $chunkSize = 500;

      while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
        // map row to header
        $mapped = [];
        foreach ($normalized as $i => $col) {
          $mapped[$col] = isset($row[$i]) ? trim($row[$i]) : null;
        }

        $data = [
          'nidn' => $mapped['nidn'] ?? null,
          'nuptk' => $mapped['nuptk'] ?? null,
          'no_sertifikat' => $mapped['no_sertifikat'] ?? null,
          'nama_dosen' => $mapped['nama_dosen'] ?? null,
          'kode_pt' => $mapped['kode_pt'] ?? null,
          'pt' => $mapped['pt'] ?? null,
          'prodi' => $mapped['prodi'] ?? null,
          'kesimpulan_bkd' => $mapped['kesimpulan_bkd'] ?? null,
          'kewajiban_khusus' => $mapped['kewajiban_khusus'] ?? null,
          'kesimpulan' => $mapped['kesimpulan'] ?? null,
          'kd' => $parseDecimal($mapped['kd'] ?? null),
          'kp' => $parseDecimal($mapped['kp'] ?? null),
          'potongan_periodik' => $parseDecimal($mapped['potongan_periodik'] ?? null),
        ];

        // skip rows without nidn
        if (empty($data['nidn'])) {
          continue;
        }

        $batch[] = $data;

        if (count($batch) >= $chunkSize) {
          $res = DB::table($table)->insertOrIgnore($batch);
          if (is_int($res)) {
            $inserted += $res;
          } else {
            $inserted += count($batch);
          }
          $batch = [];
        }
      }

      if (!empty($batch)) {
        $res = DB::table($table)->insertOrIgnore($batch);
        if (is_int($res)) {
          $inserted += $res;
        } else {
          $inserted += count($batch);
        }
      }

      fclose($handle);

      return response()->json([
        'success' => true,
        'message' => 'Data Tersimpan!',
        'imported' => $inserted,
      ]);

    } catch (\Throwable $e) {
      $alias = ErrorAlias::fromThrowable($e, 'ADM-CUTOFF');
      Log::error('CutOffSisternasController@upload error', [
        'alias' => $alias['code'],
        'table' => $table ?? null,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);
      return response()->json([
        'success' => false,
        'message' => 'Kesalahan saat mengupload data. (Kode: ' . $alias['code'] . ')',
        'code' => $alias['code'],
        'detail' => 'Kode: ' . $alias['code'],
      ], 500);
    }
  }

  public function clear($table)
  {
    $allowedTables = ['n_sister_genap_bj', 'o_sister_genap_tl', 'p_sister_ganjil_tl'];

    if (!in_array($table, $allowedTables)) {
      return response()->json(['success' => false, 'message' => 'Tabel tidak valid.']);
    }

    DB::table($table)->truncate();

    return response()->json(['success' => true, 'message' => 'Data berhasil dihapus!']);
  }

  public function create(Request $request)
  {
    $request->validate([
      'sisternas' => 'required|in:n_sister_genap_bj,o_sister_genap_tl,p_sister_ganjil_tl',
      'nidn' => 'required|string',
      'nuptk' => 'required|string',
      'no_sertifikat' => 'required|string',
      'nama_dosen' => 'required|string',
      'kode_pt' => 'required|string',
      'pt' => 'required|string',
      'prodi' => 'required|string',
      'kesimpulan_bkd' => 'required|in:M,TM',
      'kewajiban_khusus' => 'required|in:Memenuhi,Tugas Belajar,Tidak Memenuhi',
      'kesimpulan' => 'required|in:Memenuhi,Tidak Memenuhi',
      'kd' => 'required|numeric',
      'kp' => 'required|numeric',
      'potongan_periodik' => 'required|numeric',
    ]);

    $table = $request->input('sisternas');

    DB::table($table)->updateOrInsert(
      ['nidn' => $request->input('nidn')],
      [
        'nuptk' => $request->input('nuptk'),
        'no_sertifikat' => $request->input('no_sertifikat'),
        'nama_dosen' => $request->input('nama_dosen'),
        'kode_pt' => $request->input('kode_pt'),
        'pt' => $request->input('pt'),
        'prodi' => $request->input('prodi'),
        'kesimpulan_bkd' => $request->input('kesimpulan_bkd'),
        'kewajiban_khusus' => $request->input('kewajiban_khusus'),
        'kesimpulan' => $request->input('kesimpulan'),
        'kd' => is_numeric($request->input('kd')) ? (float) $request->input('kd') : null,
        'kp' => is_numeric($request->input('kp')) ? (float) $request->input('kp') : null,
        'potongan_periodik' => is_numeric($request->input('potongan_periodik')) ? (float) $request->input('potongan_periodik') : null,
      ]
    );

    return response()->json(['success' => true, 'message' => 'Data berhasil ditambahkan!']);
  }

  /**
   * Export selected sisternas table as CSV for backup
   */
  public function export(Request $request)
  {
    set_time_limit(0);
    ini_set('memory_limit', '2048M');
    $table = $request->query('table');
    $allowedTables = ['n_sister_genap_bj', 'o_sister_genap_tl', 'p_sister_ganjil_tl'];

    if (!$table || !in_array($table, $allowedTables)) {
      return redirect()->back()->with('error', 'Tabel tidak valid untuk export.');
    }

    $filename = "cutoff_{$table}_backup_" . date('Ymd_His') . ".ods";

    return Excel::download(new CutoffSisternasExport($table), $filename, \Maatwebsite\Excel\Excel::ODS);
  }
}