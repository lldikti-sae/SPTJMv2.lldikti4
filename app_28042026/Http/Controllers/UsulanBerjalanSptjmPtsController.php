<?php

namespace App\Http\Controllers;

use App\Helpers\ErrorAlias;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class UsulanBerjalanSptjmPtsController extends Controller
{
  public function index(Request $request)
  {
    $kodePts = Auth::guard('pts')->user()->kode_pts;
    $bulan = $request->bulan;
    // $tahun = date('Y'); // Bisa kamu buat dinamis kalau mau input tahun juga
    $tahun = session('tahun');

    Log::debug('usulanSPTJM Berjalan index called', [
      'kode_pts' => $kodePts,
      'bulan' => $bulan,
      'tahun' => $tahun,
      'query' => $request->query(),
    ]);

    $dosenList = [];
    $dosenListPNS = collect();
    $dosenListNonPNS = collect();

    if ($bulan) {
      try {
        $bulan = (int) $bulan;

      // Konversi angka bulan ke nama bulan Indonesia
      $namaBulan = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
      ];
      $bulanTeks = $namaBulan[$bulan];
      $kodeUsulanColumn = 'd.KodeUsulan' . $bulan;

  // Tentukan sumber BKD berdasarkan bulan
      if (in_array($bulan, [1, 2])) {
        $bkdColumn = 'o.kesimpulan_bkd'; // Genap Tahun Lalu
        $joinTable = ['table' => 'o_sister_genap_tl as o', 'alias' => 'o'];
        $ptsColumn = 'o.kode_pt';
      } elseif (in_array($bulan, [3, 4, 5, 6, 7, 8])) {
        $bkdColumn = 'p.kesimpulan_bkd'; // Ganjil Tahun Lalu
        $joinTable = ['table' => 'p_sister_ganjil as p', 'alias' => 'p'];
        $ptsColumn = 'p.kode_pt';
      } else {
        $bkdColumn = 'n.kesimpulan_bkd'; // Genap Berjalan
        $joinTable = ['table' => 'n_sister_genap_bj as n', 'alias' => 'n'];
        $ptsColumn = 'n.kode_pt';
      }

      // Cek apakah PT ini sudah melakukan usulan SPTJM Berjalan pada bulan & tahun terpilih
      $sudahUsulkanBulanIni = DB::table('q_sptjm')
        ->where('kode_pts', $kodePts)
        ->where('bulan', $bulanTeks)
        ->where('tahun', $tahun)
        ->where('id_usulan', 'LIKE', 'B %')
        ->exists();

      // Jika sudah ada usulan (berdasarkan id_usulan, bulan, tahun), kosongkan tabel
      if ($sudahUsulkanBulanIni) {
        Log::info('usulanSPTJM Berjalan index blocked (already proposed)', [
          'kode_pts' => $kodePts,
          'bulan' => $bulan,
          'bulan_teks' => $bulanTeks,
          'tahun' => $tahun,
        ]);
        $dosenList = collect();
        $dosenListPNS = collect();
        $dosenListNonPNS = collect();
        return view('pts.usulan-sptjm-berjalan', compact('dosenList', 'dosenListPNS', 'dosenListNonPNS', 'bulan'))
          ->with('info', 'Sudah melakukan usulan SPTJM Berjalan pada periode ini.');
      }

      // Ambil NIDN dosen yang sudah diusulkan untuk bulan + tahun + kode_pts tersebut
      $nidnSudahDiusulkan = DB::table('q_sptjm')
        ->where('kode_pts', $kodePts)
        ->where('bulan', $bulanTeks)
        ->where('tahun', $tahun)
        ->where('id_usulan', '<>', 0)
        ->pluck('nidn')
        ->toArray();

      // Ambil dosen yang belum diusulkan di q_sptjm untuk periode ini
      $dosenList = DB::table('s_transaksi_2 as d')
        ->leftJoin($joinTable['table'], function ($join) use ($joinTable) {
          $alias = $joinTable['alias'];
          $join->on('d.nidn', '=', $alias . '.nidn')
            ->orOn('d.nuptk', '=', $alias . '.nuptk');
        })
        ->select(
          'd.nama',
          'd.nidn',
          'd.nuptk',
          'd.jenis',
          "d.gol$bulan as gol",
          "d.tahun$bulan as tahun",
          "d.jabatan$bulan as jabatan",
          'd.aktif',
          DB::raw($bkdColumn . ' as kesimpulan_bkd'),
          'd.keterangan'
        )
        ->where('d.kode_pt', $kodePts)
        ->where($ptsColumn, $kodePts)
        ->where('d.aktif', 1)
        ->where('d.tahun_versi', session('tahun'))
        // Hanya dosen yang belum punya KodeUsulan di bulan ini
        ->where(function ($query) use ($kodeUsulanColumn) {
          $query->whereNull($kodeUsulanColumn)
            ->orWhere($kodeUsulanColumn, '');
        })
        ->where(function ($q) use ($nidnSudahDiusulkan) {
          $q->whereNotIn('d.nidn', $nidnSudahDiusulkan)
            ->whereNotIn('d.nuptk', $nidnSudahDiusulkan);
        })
        // filter langsung di WHERE, tidak pakai HAVING supaya kompatibel ONLY_FULL_GROUP_BY
        ->whereNotNull(DB::raw($bkdColumn))
        // Hanya BKD = 'M'
        ->where(DB::raw("TRIM($bkdColumn)"), '=', 'M')
        ->orderBy('d.nama')
        ->get();

      $dosenListPNS = collect($dosenList)
        ->filter(function ($dosen) {
          return Str::upper(trim((string) ($dosen->jenis ?? ''))) === 'PNS';
        })
        ->values();

      $dosenListNonPNS = collect($dosenList)
        ->filter(function ($dosen) {
          return Str::upper(trim((string) ($dosen->jenis ?? ''))) !== 'PNS';
        })
        ->values();

      Log::debug('usulanSPTJM Berjalan index loaded', [
        'kode_pts' => $kodePts,
        'bulan' => $bulan,
        'bulan_teks' => $bulanTeks,
        'tahun' => $tahun,
        'count_total' => is_countable($dosenList) ? count($dosenList) : null,
        'count_pns' => $dosenListPNS->count(),
        'count_non_pns' => $dosenListNonPNS->count(),
      ]);
      } catch (\Throwable $e) {
        $alias = ErrorAlias::fromThrowable($e, 'PTS-SPTJM-BERJALAN');
        Log::error('usulanSPTJM Berjalan index failed', [
          'alias' => $alias['code'],
          'kode_pts' => $kodePts,
          'bulan' => $bulan,
          'tahun' => $tahun,
          'message' => $e->getMessage(),
          'trace' => $e->getTraceAsString(),
        ]);

        $dosenList = collect();
        $dosenListPNS = collect();
        $dosenListNonPNS = collect();
        return view('pts.usulan-sptjm-berjalan', compact('dosenList', 'dosenListPNS', 'dosenListNonPNS', 'bulan'))
          ->with('error', $alias['message']);
      }
    }


    return view('pts.usulan-sptjm-berjalan', compact('dosenList', 'dosenListPNS', 'dosenListNonPNS', 'bulan'));
  }

  public function printBerjalan(Request $request)
  {
    $user = Auth::guard('pts')->user();
    $kodePts = $user->kode_pts;
    $bulan = $request->query('bulan') ? (int) $request->query('bulan') : now()->month;
    // $tahun = date('Y');
    $tahun = session(key: 'tahun');

    Log::debug('printSPTJM Berjalan called', [
      'kode_pts' => $kodePts,
      'bulan' => $bulan,
      'tahun' => $tahun,
      'query' => $request->query(),
    ]);

    try {
    $dosenList = [];
    $namaBulan = [
      1 => 'Januari',
      2 => 'Februari',
      3 => 'Maret',
      4 => 'April',
      5 => 'Mei',
      6 => 'Juni',
      7 => 'Juli',
      8 => 'Agustus',
      9 => 'September',
      10 => 'Oktober',
      11 => 'November',
      12 => 'Desember',
    ];
    $bulanTeks = $namaBulan[$bulan];

    if (in_array($bulan, [1, 2])) {
      $bkdColumn = 'o.kesimpulan_bkd'; // Genap Tahun Lalu
      $joinTable = ['table' => 'o_sister_genap_tl as o', 'alias' => 'o'];
      $ptsColumn = 'o.kode_pt';
    } elseif (in_array($bulan, [3, 4, 5, 6, 7, 8])) {
      $bkdColumn = 'p.kesimpulan_bkd'; // Ganjil Tahun Lalu
      $joinTable = ['table' => 'p_sister_ganjil as p', 'alias' => 'p'];
      $ptsColumn = 'p.kode_pt';
    } else {
      $bkdColumn = 'n.kesimpulan_bkd'; // Genap Berjalan
      $joinTable = ['table' => 'n_sister_genap_bj as n', 'alias' => 'n'];
      $ptsColumn = 'n.kode_pt';
    }

    // Ambil NIDN dosen yang sudah diusulkan untuk bulan + tahun + kode_pts tersebut
    $nidnSudahDiusulkan = DB::table('q_sptjm')
      ->where('kode_pts', $kodePts)
      ->where('bulan', $bulanTeks)
      ->where('tahun', $tahun)
      ->where('id_usulan', '<>', 0)
      ->pluck('nidn')
      ->toArray();


    $kodeUsulanBulan = 'KodeUsulan' . $bulan;
    // Ambil nidn dosen yang sudah masuk di s_transaksi kolom kode_usulan{bulan}
    $rowsSudahMasukTransaksi = DB::table('s_transaksi_2')
      ->where('kode_pt', operator: $kodePts)
      ->where('tahun_versi', $tahun)
      ->whereNotNull($kodeUsulanBulan)
      ->select('nidn', 'nuptk')
      ->get();

    $identifierSudahMasukTransaksi = [];
    foreach ($rowsSudahMasukTransaksi as $r) {
      if (!empty($r->nidn) && $r->nidn !== '-') {
        $identifierSudahMasukTransaksi[] = $r->nidn;
      }
      if (!empty($r->nuptk) && $r->nuptk !== '-') {
        $identifierSudahMasukTransaksi[] = $r->nuptk;
      }
    }
    $identifierSudahMasukTransaksi = array_values(array_unique($identifierSudahMasukTransaksi));

    // Ambil dosen yang belum diusulkan (dari q_sptjm) dan belum masuk s_transaksi kolom kode_usulan{bulan}
    $dosenList = DB::table('s_transaksi_2 as d')
      ->leftJoin($joinTable['table'], function ($join) use ($joinTable) {
        $alias = $joinTable['alias'];
        $join->on('d.nidn', '=', $alias . '.nidn')
          ->orOn('d.nuptk', '=', $alias . '.nuptk');
      })
      ->select(
        'd.nama',
        'd.nidn',
        'd.nuptk',
        'd.gol' . $bulan . ' as gol',
        'd.tahun' . $bulan . ' as tahun',
        'd.jabatan' . $bulan . ' as jabatan',
        'd.aktif',
        'd.jenis',
        DB::raw($bkdColumn . ' as kesimpulan_bkd'),
        'd.keterangan'
      )
      ->where('d.kode_pt', $kodePts)
      ->where('d.tahun_versi', session('tahun'))
      ->where($ptsColumn, $kodePts)
      ->where('d.aktif', 1)
      ->where(function ($q) use ($nidnSudahDiusulkan) {
        $q->whereNotIn('d.nidn', $nidnSudahDiusulkan)
          ->whereNotIn('d.nuptk', $nidnSudahDiusulkan);
      })
      ->where(function ($q) use ($identifierSudahMasukTransaksi) {
        $q->whereNotIn('d.nidn', $identifierSudahMasukTransaksi)
          ->whereNotIn('d.nuptk', $identifierSudahMasukTransaksi);
      });

    // dosen pns
    $dosenListPNS = (clone $dosenList)
      ->where('d.jenis', 'PNS')
      ->whereNotNull(DB::raw($bkdColumn))
      ->where(DB::raw("TRIM($bkdColumn)"), '=', 'M')
      ->orderBy('d.nama')
      ->get();

    //dosen non pns
    $dosenListNonPNS = (clone $dosenList)
      ->where('d.jenis', 'NON PNS')
      ->whereNotNull(DB::raw($bkdColumn))
      ->where(DB::raw("TRIM($bkdColumn)"), '=', 'M')
      ->orderBy('d.nama')
      ->get();

    // dump($dosenListPNS);
    // dd($dosenListNonPNS);
    Log::debug('printSPTJM Berjalan loaded', [
      'kode_pts' => $kodePts,
      'bulan' => $bulan,
      'bulan_teks' => $bulanTeks,
      'tahun' => $tahun,
      'count_pns' => isset($dosenListPNS) ? $dosenListPNS->count() : null,
      'count_non_pns' => isset($dosenListNonPNS) ? $dosenListNonPNS->count() : null,
    ]);

    return view('pts.print-sptjm-berjalan', [
      'dosenListPNS' => $dosenListPNS,
      'dosenListNonPNS' => $dosenListNonPNS,
      'bulan' => $bulan,
      'pts' => $user->nama_pts,
      'kode_pts' => $user->kode_pts,
      'nama' => $user->nama_pimpinan ?? 'Nama Pejabat',
      'jabatan' => $user->jabatan_pimpinan ?? 'Jabatan Pejabat',
      'alamat_pts' => $user->alamat,
      'tanggal' => now()->translatedFormat('d F Y'),
    ]);
    } catch (\Throwable $e) {
      $alias = ErrorAlias::fromThrowable($e, 'PTS-SPTJM-BERJALAN');
      Log::error('printSPTJM Berjalan failed', [
        'alias' => $alias['code'],
        'kode_pts' => $kodePts,
        'bulan' => $bulan,
        'tahun' => $tahun,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);
      return redirect()
        ->route('pts.usulan-sptjm-berjalan', ['bulan' => $bulan])
        ->with('error', $alias['message']);
    }
  }

  public function uploadSPTJM(Request $request)
  {
    // dd($request->all());
    $user = Auth::guard('pts')->user();
    // Log entry for debugging when user clicks Usulkan
    Log::info('uploadSPTJM called', [
      'kode_pts' => $user->kode_pts ?? null,
      'user_id' => $user->id ?? null,
      'bulan' => $request->input('bulan'),
      'has_file' => $request->hasFile('file'),
      'file_name' => optional($request->file('file'))->getClientOriginalName(),
      'file_size' => optional($request->file('file'))->getSize(),
    ]);

    try {
      $request->validate([
        'file' => 'required|file|mimes:pdf|max:2048',
        'bulan' => 'required',
        'tahun' => 'required|numeric',
        'nidn' => 'nullable',
        'nuptk' => 'nullable',
        'nama' => 'nullable',
        'jabatan' => 'nullable',
        'kota' => 'nullable',
        'nomor_surat' => 'nullable',
    ]);
    } catch (ValidationException $ve) {
      Log::warning('uploadSPTJM validation failed', [
        'kode_pts' => $user->kode_pts ?? null,
        'errors' => $ve->errors(),
        'input' => array_filter($request->except(['file'])),
      ]);
      return redirect()->route('pts.usulan-sptjm-berjalan', ['bulan' => $request->input('bulan')])->with('error', 'Validasi gagal: ' . implode('; ', array_map(function($v){return implode(', ', $v);}, $ve->errors())));
    }

    Log::info('uploadSPTJM validation passed', ['kode_pts' => $user->kode_pts ?? null]);

    $kodePts = $user->kode_pts;
    $namaPts = $user->nama_pts;
    $alamatPts = $user->alamat_pt;

    $bulanAngka = str_pad($request->bulan, 2, '0', STR_PAD_LEFT);
  // Gunakan tahun dari session agar konsisten dengan filter tabel
  $tahun = session('tahun');
    $file = $request->file('file');

    try {
      Log::info('uploadSPTJM processing', ['kode_pts' => $kodePts, 'bulan' => $request->bulan]);

    $namaBulan = [
      1 => 'Januari',
      2 => 'Februari',
      3 => 'Maret',
      4 => 'April',
      5 => 'Mei',
      6 => 'Juni',
      7 => 'Juli',
      8 => 'Agustus',
      9 => 'September',
      10 => 'Oktober',
      11 => 'November',
      12 => 'Desember',
    ];

    $bulanTeks = $namaBulan[intval($request->bulan)];
    $tanggalSekarang = Carbon::now();

    // Cegah pengusulan ganda: jika sudah ada usulan berjalan untuk bulan & tahun ini, tolak
    $sudahUsulkanBulanIni = DB::table('q_sptjm')
      ->where('kode_pts', $kodePts)
      ->where('bulan', $bulanTeks)
      ->where('tahun', $tahun)
      ->where('id_usulan', 'LIKE', 'B %')
      ->exists();

    if ($sudahUsulkanBulanIni) {
      return redirect()
        ->route('pts.usulan-sptjm-berjalan', ['bulan' => $request->bulan])
        ->with('error', 'Sudah melakukan usulan SPTJM Berjalan pada periode ini. Pengusulan ganda tidak diperbolehkan.');
    }

    // Hitung usulan ke berapa (saat ini akan selalu 1 karena tidak boleh ganda)
    $countUsulan = DB::table('q_sptjm')
      ->where('kode_pts', $kodePts)
      ->where('bulan', $bulanTeks)
      ->where('tahun', $tahun)
      ->where('id_usulan', 'LIKE', 'B %')
      ->count();

    $noUsulan = $countUsulan + 1;

    // Simpan tanggal usulan sebagai date (YYYY-MM-DD)
    $tanggal_usulan = Carbon::now()->toDateString();

    // Format id_usulan
    $idUsulan = 'B ' . $bulanAngka . $kodePts . ' ' . $noUsulan;

    // Format nama file
    $filename =
      'B ' .
      $bulanAngka .
      $kodePts .
      ' ' .
      $noUsulan .
      '_' .
      $bulanTeks .
      '.' .
      $request->file->getClientOriginalExtension();

      // Simpan ke folder public/uploadFile_SPTJM_B
      $path = $request->file('file')->storeAs('public/uploadFile_SPTJM_B', $filename);

      Log::info('uploadSPTJM file stored', ['path' => $path, 'filename' => $filename]);

      // Simpan ke database
      DB::table('q_sptjm')->insert([
      'id_usulan' => $idUsulan,
      'kode_pts' => $kodePts,
      'nama_pts' => $namaPts,
      'bulan' => $bulanTeks,
      'tahun' => $tahun,
        'nidn' => (function () use ($request) {
          $nidn = trim((string) $request->input('nidn', ''));
          $nuptk = trim((string) $request->input('nuptk', ''));
          if ($nidn !== '' && $nidn !== '-') return $nidn;
          if ($nuptk !== '' && $nuptk !== '-') return $nuptk;
          return '-';
        })(),
        'nuptk' => (function () use ($request) {
          $nuptk = trim((string) $request->input('nuptk', ''));
          return ($nuptk !== '' && $nuptk !== '-') ? $nuptk : '-';
        })(),
        'nama' => $request->nama ?: ($user->nama_pimpinan ?? '-'),
        'jabatan' => $request->jabatan ?: ($user->jabatan_pimpinan ?? '-'),
        'kota' => $request->kota ?: ($user->kota_pt ?? $user->kota ?? '-'),
        'nomor_surat' => $request->nomor_surat ?: '-',
      'alamat_pts' => $user->alamat_pt,
      'file' => 'uploadFile_SPTJM_B/' . $filename,
      'status' => 'Usulan',
      'tanggal_usulan' => $tanggal_usulan,
      'wilayah' => $user->wilayah,
      'password' => $user->password,
      'aktif' => $user->aktif,
      'created_at' => now(),
      'updated_at' => now(),
    ]);

    Log::info('uploadSPTJM record inserted', ['id_usulan' => $idUsulan, 'kode_pts' => $kodePts]);

    // Simpan juga ke tabel s_transaksi pada kolom kode_usulan{bulan}
    $bulanNow = intval($request->bulan);
    $kolomKodeUsulan = 'KodeUsulan' . $bulanNow;

    // Ambil NIDN persis seperti yang ditampilkan di tabel (query yang sama dengan index)
    $namaBulan = [
      1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
      7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];
    $bulanTeks = $namaBulan[$bulanNow] ?? '';

    if (in_array($bulanNow, [1, 2])) {
      $bkdColumn = 'o.kesimpulan_bkd';
      $joinTable = ['table' => 'o_sister_genap_tl as o', 'alias' => 'o'];
      $ptsColumn = 'o.kode_pt';
    } elseif (in_array($bulanNow, [3, 4, 5, 6, 7, 8])) {
      $bkdColumn = 'p.kesimpulan_bkd';
      $joinTable = ['table' => 'p_sister_ganjil as p', 'alias' => 'p'];
      $ptsColumn = 'p.kode_pt';
    } else {
      $bkdColumn = 'n.kesimpulan_bkd';
      $joinTable = ['table' => 'n_sister_genap_bj as n', 'alias' => 'n'];
      $ptsColumn = 'n.kode_pt';
    }

    $nidnSudahDiusulkan = DB::table('q_sptjm')
      ->where('kode_pts', $kodePts)
      ->where('bulan', $bulanTeks)
      ->where('tahun', $tahun)
      ->where('id_usulan', '<>', 0)
      ->pluck('nidn')
      ->toArray();

    $dosenIdentifierRows = DB::table('s_transaksi_2 as d')
      ->leftJoin($joinTable['table'], function ($join) use ($joinTable) {
        $alias = $joinTable['alias'];
        $join->on('d.nidn', '=', $alias . '.nidn')
          ->orOn('d.nuptk', '=', $alias . '.nuptk');
      })
      ->select('d.nidn', 'd.nuptk', DB::raw($bkdColumn . ' as kesimpulan_bkd'))
      ->where('d.kode_pt', $kodePts)
      ->where('d.tahun_versi', $tahun)
      ->where($ptsColumn, $kodePts)
      ->where('d.aktif', 1)
      ->where(function ($q) use ($nidnSudahDiusulkan) {
        $q->whereNotIn('d.nidn', $nidnSudahDiusulkan)
          ->whereNotIn('d.nuptk', $nidnSudahDiusulkan);
      })
      ->whereNotNull(DB::raw($bkdColumn))
      // Konsisten: hanya update dosen dengan BKD = 'M'
      ->where(DB::raw("TRIM($bkdColumn)"), '=', 'M')
      ->get();

      // Batch update using identifiers (nidn and nuptk) to avoid per-row queries
      $identifiers = [];
      foreach ($dosenIdentifierRows as $row) {
        if (!empty($row->nidn) && $row->nidn !== '-') {
          $identifiers[] = trim($row->nidn);
        }
        if (!empty($row->nuptk) && $row->nuptk !== '-') {
          $identifiers[] = trim($row->nuptk);
        }
      }
      $identifiers = array_values(array_unique($identifiers));

      $batchSize = 200; // tuneable
      foreach (array_chunk($identifiers, $batchSize) as $batch) {
        DB::table('s_transaksi_2')
          ->where('kode_pt', $kodePts)
          ->where('tahun_versi', $tahun)
          ->where(function ($q) use ($batch) {
            $q->whereIn('nidn', $batch)
              ->orWhereIn('nuptk', $batch);
          })
          ->where(function ($q) use ($kolomKodeUsulan) {
            $q->whereNull($kolomKodeUsulan)
              ->orWhere($kolomKodeUsulan, '');
          })
          ->update([
            $kolomKodeUsulan => $idUsulan,
          ]);
      }
      Log::info('uploadSPTJM completed', ['id_usulan' => $idUsulan]);

      return redirect()
        ->route('pts.usulan-sptjm-berjalan')
        ->with('success', 'File SPTJM berhasil diupload dan data berhasil disimpan.');
    } catch (\Exception $e) {
      $alias = ErrorAlias::fromThrowable($e, 'PTS-SPTJM-BERJALAN');
      Log::error('uploadSPTJM failed', [
        'alias' => $alias['code'],
        'kode_pts' => $kodePts ?? null,
        'bulan' => $request->input('bulan'),
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);
      return redirect()
        ->route('pts.usulan-sptjm-berjalan')
        ->with('error', $alias['message']);
    }
  }
}
