<?php

namespace App\Http\Controllers;

use App\Helpers\ErrorAlias;
use App\Imports\DataSisterImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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

      $allowedTables = ['p_sister_genap', 'p_sister_ganjil', 'p_sister_tukin'];

      if (in_array($table, $allowedTables)) {
        if (!Schema::hasTable($table)) {
            return DataTables::of(collect([]))->make(true);
        }
        $tahun = $request->query('tahun', session('tahun') ?: date('Y'));
        $query = DB::table($table);
        if (Schema::hasColumn($table, 'tahun')) {
            $query->where('tahun', $tahun);
        }

        $bkdStatus = $request->input('bkd_status');
        if ($bkdStatus === 'M' || $bkdStatus === 'TM') {
            $query->where('kesimpulan_bkd', $bkdStatus);
        }

        $getStat = function ($tableName) use ($tahun) {
            if (!Schema::hasTable($tableName)) {
                return ['total' => 0, 'm' => 0, 'tm' => 0];
            }
            $qTotal = DB::table($tableName);
            $qM = DB::table($tableName);
            $qTm = DB::table($tableName);

            if (Schema::hasColumn($tableName, 'tahun')) {
                $qTotal->where('tahun', $tahun);
                $qM->where('tahun', $tahun);
                $qTm->where('tahun', $tahun);
            }

            return [
                'total' => $qTotal->count(),
                'm' => $qM->where('kesimpulan_bkd', 'M')->count(),
                'tm' => $qTm->where('kesimpulan_bkd', 'TM')->count(),
            ];
        };

        $statGenapTL = $getStat('p_sister_genap');
        $statGanjil = $getStat('p_sister_ganjil');
        $statGenapBJ = $getStat('p_sister_genap');

        return DataTables::of($query)
          ->addIndexColumn()
          ->addColumn('tahun_periode', function ($row) use ($table) {
            $sem = (strpos($table, 'ganjil') !== false) ? '1' : '2';
            $thn = (string)($row->tahun ?? session('tahun') ?? date('Y'));
            $thn = str_replace('-', '/', $thn);
            if (str_contains($thn, '/')) {
              return $thn;
            }
            return $thn . '/' . $sem;
          })
          ->addColumn('aksi', function ($row) {
            if (auth()->check() && auth()->user()->role === 'pic') {
                return '<span class="text-muted">-</span>';
            }
            return '<div class="d-flex justify-content-center align-items-center"><button class="sptjm-icon-btn sptjm-btn-edit edit-btn d-inline-flex align-items-center justify-content-center" style="width:30px; height:30px;"><i class="bx bx-edit"></i></button></div>';
          })
          ->with([
            'stat_ganjil' => $statGanjil,
            'stat_genap_bj'  => $statGenapBJ,
            'stat_genap_tl'  => $statGenapTL,
            'tahun_query'    => $tahun
          ])
          ->rawColumns(['aksi', 'tahun_periode'])
          ->make(true);
      }

      return response()->json([
        "error" => "Tabel tidak valid."
      ], 400);
    }

    // hitung statistik riil untuk tabel laporan monitoring (berdasarkan tahun query / session / seluruh data)
    $tahun = (string)($request->query('tahun', session('tahun') ?: date('Y')));

    $getStat = function ($tableName) use ($tahun) {
        if (!Schema::hasTable($tableName)) {
            return ['total' => 0, 'm' => 0, 'tm' => 0];
        }
        $qTotal = DB::table($tableName);
        $qM = DB::table($tableName);
        $qTm = DB::table($tableName);

        if (Schema::hasColumn($tableName, 'tahun')) {
            $qTotal->where('tahun', $tahun);
            $qM->where('tahun', $tahun);
            $qTm->where('tahun', $tahun);
        }

        return [
            'total' => $qTotal->count(),
            'm' => $qM->where('kesimpulan_bkd', 'M')->count(),
            'tm' => $qTm->where('kesimpulan_bkd', 'TM')->count(),
        ];
    };

    $statGenap = $getStat('p_sister_genap');
    $statGanjil = $getStat('p_sister_ganjil');

    // Ambil daftar tahun versi dinamis dari Pengaturan Versi Admin (ActiveYears + database s_transaksi_2)
    $activeYears = \App\Helpers\ActiveYears::load();
    $dbYears = DB::table('s_transaksi_2')->distinct()->pluck('Tahun_Versi')->map(fn($y) => (int)$y)->toArray();
    $mergedYears = array_unique(array_merge([2023, (int)date('Y'), (int)session('tahun')], $activeYears, $dbYears));
    sort($mergedYears);
    $listTahun = array_values(array_filter($mergedYears, fn($y) => $y >= 2021));

    // Ambil pemetaan aktif terbaru dari database k_data_sister (hanya tipe Ganjil/Genap baru)
    $savedMappings = [];
    if (Schema::hasTable('k_data_sister')) {
        $subQuery = DB::table('k_data_sister')
            ->select('tahun', 'periode', DB::raw('MAX(id) as max_id'))
            ->whereIn('periode', ['Ganjil', 'Genap'])
            ->groupBy('tahun', 'periode');

        $savedMappings = DB::table('k_data_sister as k')
            ->joinSub($subQuery, 'sub', function ($join) {
                $join->on('k.id', '=', 'sub.max_id');
            })
            ->select('k.tahun', 'k.periode', 'k.bulan')
            ->orderBy('k.tahun', 'desc')
            ->orderBy('k.periode', 'desc')
            ->get();
    }

    // kalau bukan request ajax, kembalikan view beserta data statistik
    return view('admin.cutoff-sisternas', compact('statGenap', 'statGanjil', 'listTahun', 'savedMappings'));
  }


  public function update(Request $request)
  {
    $request->validate([
      // some lecturers only have one identifier; accept either nidn or nuptk
      'nidn' => 'nullable|string|required_without:nuptk',
      'sisternas' => 'required|in:p_sister_genap,p_sister_ganjil',
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
    $tahun = (string)($request->input('tahun') ?: ($request->query('tahun') ?: (session('tahun') ?: date('Y'))));

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
        ->where('tahun', $tahun)
        ->update($updatePayload);
    }

    if ($affected === 0 && $nuptk !== null) {
      $affected = DB::table($table)
        ->where('nuptk', $nuptk)
        ->where('tahun', $tahun)
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
        ->where('tahun', $tahun)
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
      'table' => 'required|in:p_sister_genap,p_sister_ganjil,p_sister_tukin',
    ]);
    $file = $request->file('dokumen');
    $fileName = strtolower($file->getClientOriginalName());
    $table = $request->input('table');
    $uploadType = $request->input('upload_type', 'new');
    $jenisUsulan = $request->input('jenis_usulan', 'SPTJM');

    $periodeTukin = '';
    if (strtoupper($jenisUsulan) === 'TUKIN') {
      $periodeTukin = strpos($table, 'ganjil') !== false ? 'Ganjil' : 'Genap';
      $table = 'p_sister_tukin';
    }

    // Validasi Jika Tipe Upload Adalah UPDATE (Wajib memuat kata 'update')
    if ($uploadType === 'update' && strpos($fileName, 'update') === false) {
      return response()->json([
        'success' => false,
        'message' => 'Nama file (' . $file->getClientOriginalName() . ') tidak sesuai! Untuk menu Update Data, nama file CSV wajib memuat kata "update" (contoh: dosen_ganjil_update.csv).'
      ], 422);
    }

    // Validasi Nama File Harus Sesuai Jenis Periode
    if (strpos($table, 'ganjil') !== false) {
      if (strpos($fileName, 'ganjil') === false) {
        return response()->json([
          'success' => false,
          'message' => 'Nama file (' . $file->getClientOriginalName() . ') tidak sesuai! Untuk periode Ganjil, nama file CSV wajib memuat kata "ganjil" (contoh: dosen_ganjil_' . date('Y') . '.csv).'
        ], 422);
      }
    } else if (strpos($table, 'genap') !== false) {
      if (strpos($fileName, 'genap') === false) {
        return response()->json([
          'success' => false,
          'message' => 'Nama file (' . $file->getClientOriginalName() . ') tidak sesuai! Untuk periode Genap, nama file CSV wajib memuat kata "genap" (contoh: dosen_genap_' . date('Y') . '.csv).'
        ], 422);
      }
    } else if (strpos($table, 'tukin') !== false) {
      if (strpos($fileName, 'tukin') === false) {
        return response()->json([
          'success' => false,
          'message' => 'Nama file (' . $file->getClientOriginalName() . ') tidak sesuai! Untuk Tukin, nama file CSV wajib memuat kata "tukin" (contoh: dosen_tukin_' . date('Y') . '.csv).'
        ], 422);
      }
    }
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
      $firstLine = str_replace(["\xEF\xBB\xBF", "\u{FEFF}", "\u{200B}"], '', $firstLine);
      $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';
      $header = str_getcsv($firstLine, $delimiter);
      $header = array_map(function ($h) {
        $h = str_replace(["\xEF\xBB\xBF", "\u{FEFF}", "\u{200B}", "\r", "\n", "\t"], '', (string)$h);
        return strtolower(trim($h, " \t\n\r\0\x0B\"'"));
      }, $header);

      // Normalize header to lowercase underscore form
      $normalized = array_map(function ($h) {
        return str_replace(' ', '_', $h);
      }, $header);

      // Kolom wajib utama
      $requiredCore = ['nidn', 'nama_dosen', 'kesimpulan_bkd'];
      $missingCore = [];
      foreach ($requiredCore as $req) {
          $found = false;
          foreach ($normalized as $normCol) {
              if (str_contains($normCol, $req) || ($req === 'nidn' && str_contains($normCol, 'nuptk'))) {
                  $found = true;
                  break;
              }
          }
          if (!$found) {
              $missingCore[] = $req;
          }
      }

      if (!empty($missingCore)) {
        fclose($handle);
        return response()->json([
          'success' => false,
          'headerMismatch' => true,
          'message' => 'Kolom CSV wajib tidak ditemukan: ' . implode(', ', $missingCore),
          'missingColumns' => $missingCore,
        ], 422);
      }
      fgets($handle);

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
      $tahun = $request->input('tahun', session('tahun') ?: date('Y'));
      $updateCols = ['nuptk', 'no_sertifikat', 'nama_dosen', 'kode_pt', 'pt', 'prodi', 'kesimpulan_bkd', 'kewajiban_khusus', 'kesimpulan', 'kd', 'kp', 'potongan_periodik', 'tahun'];

      // Ambil NIDN/NUPTK yang dicentang di kolom Aksi untuk dihapus
      $rawDeleteNidns = $request->input('delete_nidn', []);
      if (!is_array($rawDeleteNidns)) {
        $rawDeleteNidns = [];
      }
      $deleteNidns = array_values(array_filter(array_map('trim', $rawDeleteNidns), function($v) {
        return $v !== '' && $v !== '—' && $v !== '-';
      }));
      $deleteSet = array_flip($deleteNidns);

      while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
        // map row to header
        $mapped = [];
        foreach ($normalized as $i => $col) {
          $mapped[$col] = isset($row[$i]) ? trim($row[$i]) : null;
        }

        $nidnVal = $mapped['nidn'] ?? $mapped['nuptk'] ?? null;
        $nuptkVal = $mapped['nuptk'] ?? null;
        if (empty($nidnVal)) {
          continue; // skip rows without both NIDN & NUPTK
        }

        // JIKA NIDN / NUPTK ini dicentang di kolom Aksi (untuk dihapus):
        // JANGAN dimasukkan ke batch upsert agar tidak menimpa/memperbarui data!
        if (isset($deleteSet[$nidnVal]) || ($nuptkVal !== null && isset($deleteSet[$nuptkVal]))) {
          continue;
        }

        $data = [
          'nidn' => $nidnVal,
          'nuptk' => $nuptkVal,
          'no_sertifikat' => $mapped['no_sertifikat'] ?? null,
          'nama_dosen' => $mapped['nama_dosen'] ?? $mapped['nama'] ?? null,
          'kode_pt' => $mapped['kode_pt'] ?? null,
          'pt' => $mapped['pt'] ?? null,
          'prodi' => $mapped['prodi'] ?? null,
          'kesimpulan_bkd' => $mapped['kesimpulan_bkd'] ?? $mapped['kesimpulan'] ?? $mapped['bkd'] ?? null,
          'kewajiban_khusus' => $mapped['kewajiban_khusus'] ?? null,
          'kesimpulan' => $mapped['kesimpulan'] ?? null,
          'kd' => $parseDecimal($mapped['kd'] ?? null),
          'kp' => $parseDecimal($mapped['kp'] ?? null),
          'potongan_periodik' => $parseDecimal($mapped['potongan_periodik'] ?? null),
          'tahun' => (string)$tahun,
        ];

        if (!empty($periodeTukin)) {
          $data['periode'] = $periodeTukin;
        }

        $batch[] = $data;

        if (count($batch) >= $chunkSize) {
          $uniqueKeys = ['nidn', 'tahun'];
          if (!empty($periodeTukin)) {
            $uniqueKeys[] = 'periode';
          }
          DB::table($table)->upsert($batch, $uniqueKeys, $updateCols);
          $inserted += count($batch);
          $batch = [];
        }
      }

      if (!empty($batch)) {
        $uniqueKeys = ['nidn', 'tahun'];
        if (!empty($periodeTukin)) {
          $uniqueKeys[] = 'periode';
        }
        DB::table($table)->upsert($batch, $uniqueKeys, $updateCols);
        $inserted += count($batch);
      }

      fclose($handle);

      // Hapus data dosen yang dicentang di kolom Aksi dari tabel Cut Off
      $deleted = 0;
      if (!empty($deleteNidns)) {
        $queryDelete = DB::table($table)->where('tahun', (string)$tahun);
        if (!empty($periodeTukin)) {
          $queryDelete->where('periode', $periodeTukin);
        }
        
        $deleted = $queryDelete
          ->where(function ($q) use ($deleteNidns) {
            $q->whereIn('nidn', $deleteNidns)
              ->orWhereIn('nuptk', $deleteNidns);
          })
          ->delete();

        Log::info('CutOffSisternas: Deleted checked NIDNs from Cut Off table', [
          'table' => $table,
          'tahun' => $tahun,
          'count' => $deleted,
          'nidns' => $deleteNidns,
        ]);
      }

      // Simpan riwayat ke History Data Sisternas (k_data_sister) secara otomatis
      try {
        $tanggalFormat = date('dmY');
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $slugName = \Illuminate\Support\Str::slug($originalName, '_');
        $finalFileName = $tanggalFormat . '_' . $slugName . '.' . $file->getClientOriginalExtension();

        \Illuminate\Support\Facades\Storage::disk('public')->putFileAs('File_Data_Sisternas2', $file, $finalFileName);

        $isGanjil = str_contains($table, 'ganjil');
        \App\Models\Sisternas::create([
          'tahun'   => (string)$tahun,
          'periode' => $isGanjil ? 'Ganjil' : 'Genap',
          'bulan'   => $isGanjil ? 'Maret - Agustus' : 'September - Februari',
          'dokumen' => $finalFileName,
          'tanggal' => date('Y-m-d'),
        ]);
      } catch (\Throwable $histErr) {
        \Illuminate\Support\Facades\Log::warning('Gagal menyimpan riwayat ke History Data Sisternas: ' . $histErr->getMessage());
      }

      $msg = 'Data Berhasil Disimpan (' . number_format($inserted, 0, ',', '.') . ' baris)';
      if ($deleted > 0) {
        $msg .= ' dan ' . $deleted . ' data dosen (TM→M) dihapus dari tabel cut off';
      }
      $msg .= '.';

      return response()->json([
        'success' => true,
        'message' => $msg,
        'imported' => $inserted,
        'deleted' => $deleted,
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
      ], 500);
    }
  }

  public function template()
  {
    $headers = [
      'Content-Type' => 'text/csv; charset=UTF-8',
      'Content-Disposition' => 'attachment; filename="template_cutoff_sisternas.csv"',
    ];

    $columns = [
      'nidn', 'nuptk', 'no_sertifikat', 'nama_dosen', 'kode_pt', 'pt', 'prodi',
      'kesimpulan_bkd', 'kewajiban_khusus', 'kesimpulan', 'kd', 'kp', 'potongan_periodik'
    ];

    $sampleRow1 = [
      '0012058001', '1234567890123456', '19001001001', 'Dr. Budi Santoso, S.T., M.T.', '041001', 'Universitas Contoh', 'Teknik Informatika',
      'M', 'Memenuhi', 'Memenuhi', '0', '0', '0'
    ];

    $sampleRow2 = [
      '0015088202', '', '19001001002', 'Ahmad Ridwan, M.Kom.', '041001', 'Universitas Contoh', 'Sistem Informasi',
      'TM', 'Tidak Memenuhi', 'Tidak Memenuhi', '0', '0', '0'
    ];

    $callback = function () use ($columns, $sampleRow1, $sampleRow2) {
      $file = fopen('php://output', 'w');
      fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM
      fputcsv($file, $columns, ';');
      fputcsv($file, $sampleRow1, ';');
      fputcsv($file, $sampleRow2, ';');
      fclose($file);
    };

    return response()->stream($callback, 200, $headers);
  }

  public function clear($table, Request $request)
  {
    $allowedTables = ['p_sister_genap', 'p_sister_ganjil', 'p_sister_tukin'];

    if (!in_array($table, $allowedTables)) {
      return response()->json(['success' => false, 'message' => 'Tabel tidak valid.']);
    }

    $tahun = (string)($request->input('tahun') ?: ($request->query('tahun') ?: (session('tahun') ?: date('Y'))));
    DB::table($table)->where('tahun', $tahun)->delete();

    if (Schema::hasTable('k_data_sister')) {
      $isGanjil = str_contains($table, 'ganjil');
      DB::table('k_data_sister')
        ->where('tahun', $tahun)
        ->where('periode', $isGanjil ? 'Ganjil' : 'Genap')
        ->delete();
    }

    return response()->json(['success' => true, 'message' => 'Data berhasil dihapus!']);
  }

  public function create(Request $request)
  {
    $request->validate([
      'sisternas' => 'required|in:p_sister_genap,p_sister_ganjil',
      'tahun' => 'nullable|integer',
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
    $tahun = (string)($request->input('tahun') ?: (session('tahun') ?: date('Y')));

    DB::table($table)->updateOrInsert(
      ['nidn' => $request->input('nidn'), 'tahun' => $tahun],
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
    $allowedTables = ['p_sister_genap', 'p_sister_ganjil', 'p_sister_tukin'];

    if (!$table || !in_array($table, $allowedTables)) {
      return redirect()->back()->with('error', 'Tabel tidak valid untuk export.');
    }

    $tahun = (string)($request->query('tahun') ?: ($request->input('tahun') ?: (session('tahun') ?: date('Y'))));
    $filename = "cutoff_{$table}_{$tahun}_backup_" . date('Ymd_His') . ".ods";

    return Excel::download(new CutoffSisternasExport($table, $tahun), $filename, \Maatwebsite\Excel\Excel::ODS);
  }

  /**
   * Cek perbandingan data CSV vs Database secara riil + validasi isi tahun/periode
   */
  public function checkDiff(Request $request)
  {
    \Illuminate\Support\Facades\Log::info('checkDiff called with:', $request->all());
    $request->validate([
      'dokumen' => 'required|file',
      'table'   => 'required|in:p_sister_genap,p_sister_ganjil,p_sister_tukin',
      'tahun'   => 'nullable|string'
    ]);

    $file = $request->file('dokumen');
    $table = $request->input('table');
    $tahun = (string)($request->input('tahun') ?: (session('tahun') ?: date('Y')));

    $handle = fopen($file->getRealPath(), 'r');
    if (!$handle) {
      return response()->json(['success' => false, 'message' => 'Gagal membaca file CSV.'], 400);
    }

    $firstLine = fgets($handle);
    if (!$firstLine) {
      fclose($handle);
      return response()->json(['success' => false, 'message' => 'File CSV kosong.'], 400);
    }

    $firstLineClean = str_replace(["\xEF\xBB\xBF", "\u{FEFF}", "\u{200B}"], '', $firstLine);
    $delimiter = (substr_count($firstLineClean, ';') > substr_count($firstLineClean, ',')) ? ';' : ',';
    rewind($handle);

    $header = fgetcsv($handle, 0, $delimiter);
    if (!$header) {
      fclose($handle);
      return response()->json(['success' => false, 'message' => 'Header CSV tidak valid.'], 400);
    }

    $cleanHeader = array_map(function($h) {
      $hClean = str_replace(["\xEF\xBB\xBF", "\u{FEFF}", "\u{200B}", "\r", "\n", "\t"], '', (string)$h);
      return strtolower(trim(str_replace(' ', '_', $hClean)));
    }, $header);

    $nidnIdx  = -1;
    $nuptkIdx = -1;
    $namaIdx  = -1;
    $bkdIdx   = -1;

    foreach ($cleanHeader as $i => $h) {
      if ($nidnIdx === -1 && str_contains($h, 'nidn')) $nidnIdx = $i;
      if ($nuptkIdx === -1 && (str_contains($h, 'nuptk') || str_contains($h, 'nik'))) $nuptkIdx = $i;
      if ($namaIdx === -1 && (str_contains($h, 'nama') || str_contains($h, 'dosen') || str_contains($h, 'sdm'))) $namaIdx = $i;
      if ($bkdIdx === -1 && (str_contains($h, 'bkd') || str_contains($h, 'kesimpulan'))) $bkdIdx = $i;
    }

    if ($nidnIdx === -1) $nidnIdx = ($nuptkIdx !== -1 ? $nuptkIdx : 0);
    if ($namaIdx === -1) $namaIdx = 2;
    if ($bkdIdx === -1) $bkdIdx = count($cleanHeader) - 1;

    $rows = [];
    $nidns = [];
    $detectedYears = [];

    $count = 0;
    while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
      if (count($data) < 2) continue;

      $nidnVal = isset($data[$nidnIdx]) ? trim($data[$nidnIdx]) : '';
      $nuptkVal = ($nuptkIdx !== -1 && isset($data[$nuptkIdx])) ? trim($data[$nuptkIdx]) : '';
      $namaVal = isset($data[$namaIdx]) ? trim($data[$namaIdx]) : '';
      $bkdVal  = isset($data[$bkdIdx]) ? strtoupper(trim($data[$bkdIdx])) : '';

      if (empty($nidnVal) && empty($nuptkVal) && empty($namaVal)) continue;

      $bkdClean = (str_contains($bkdVal, 'MEMENUHI') && !str_contains($bkdVal, 'TIDAK')) || $bkdVal === 'M' ? 'M' : 'TM';

      $keyVal = !empty($nidnVal) ? $nidnVal : $nuptkVal;
      $rows[] = [
        'nidn' => $nidnVal,
        'nuptk' => $nuptkVal,
        'nama_dosen' => $namaVal,
        'bkd_baru' => $bkdClean,
      ];

      if (!empty($keyVal)) {
        $nidns[] = $keyVal;
      }

      $count++;
      if ($count >= 300) break;
    }
    fclose($handle);

    // ── DATA COMPARISON DARI MYSQL DATABASE RIIL ──
    $existingDb = collect([]);
    if (Schema::hasTable($table) && !empty($nidns)) {
      $qDb = DB::table($table);
      if (Schema::hasColumn($table, 'tahun')) {
        $qDb->where('tahun', $tahun);
      }
      $existingDb = $qDb->where(function($subQ) use ($nidns) {
          $subQ->whereIn('nidn', $nidns)->orWhereIn('nuptk', $nidns);
        })
        ->select('nidn', 'nuptk', 'kesimpulan_bkd')
        ->get()
        ->keyBy(function($item) {
          return !empty($item->nidn) ? $item->nidn : $item->nuptk;
        });
    }

    $diffResult = [];
    $hasNew = false;
    foreach ($rows as $r) {
      $key = !empty($r['nidn']) ? $r['nidn'] : $r['nuptk'];
      $dbRecord = $existingDb->get($key);

      $bkdLama = ($dbRecord && !empty($dbRecord->kesimpulan_bkd)) ? $dbRecord->kesimpulan_bkd : '-';
      $isNewOrEmpty = (!$dbRecord || empty($dbRecord->kesimpulan_bkd) || trim($dbRecord->kesimpulan_bkd) === '-');
      
      // Hanya anggap data baru jika dosen tersebut berstatus TM di CSV (karena database hanya menyimpan dosen TM)
      if ($isNewOrEmpty && $r['bkd_baru'] === 'TM') {
          $hasNew = true;
      }
      
      $diffResult[] = [
        'nidn' => !empty($r['nidn']) ? $r['nidn'] : '—',
        'nuptk' => !empty($r['nuptk']) ? $r['nuptk'] : '—',
        'nama_dosen' => !empty($r['nama_dosen']) ? $r['nama_dosen'] : '—',
        'bkd_lama' => $bkdLama,
        'bkd_baru' => $r['bkd_baru'],
        'is_changed' => (!$isNewOrEmpty && $dbRecord->kesimpulan_bkd !== $r['bkd_baru'])
      ];
    }

    // Merupakan upload baru HANYA JIKA belum ada pemetaan di k_data_sister DAN belum ada data di tabel data
    $periodName = (str_contains($table, 'ganjil')) ? 'Ganjil' : ((str_contains($table, 'genap')) ? 'Genap' : '');
    $hasMappingInSister = DB::table('k_data_sister')
        ->where('tahun', $tahun)
        ->when(!empty($periodName), function($q) use ($periodName) {
            return $q->where('periode', $periodName);
        })->exists();
    $hasDataInTable = Schema::hasTable($table) && DB::table($table)->where('tahun', $tahun)->exists();

    $isNewUpload = !$hasMappingInSister && !$hasDataInTable;
    $changedRows = [];

    foreach ($diffResult as $item) {
      if ($item['is_changed']) {
        $changedRows[] = $item;
      }
    }

    $hasChanges = count($changedRows) > 0;

    return response()->json([
      'success' => true,
      'is_new_upload' => $isNewUpload,
      'has_changes' => $hasChanges,
      'has_new' => $hasNew,
      'data' => $isNewUpload ? array_slice($diffResult, 0, 15) : array_slice($changedRows, 0, 15),
      'total' => $isNewUpload ? count($diffResult) : count($changedRows)
    ]);
  }
}