<?php

namespace App\Http\Controllers;

use App\Helpers\ErrorAlias;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class UsulanSusulanSptjmPtsController extends Controller
{
  public function index(Request $request)
  {
    $kodePts = Auth::guard('pts')->user()->kode_pts;
    $bulan = $request->bulan;
    // $tahun = date('Y'); // Bisa kamu buat dinamis kalau mau input tahun juga
    $tahun = session('tahun');

    Log::debug('usulanSPTJM Susulan index called', [
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
        $joinTable = ['table' => 'p_sister_ganjil_tl as p', 'alias' => 'p'];
        $ptsColumn = 'p.kode_pt';
      } else {
        $bkdColumn = 'n.kesimpulan_bkd'; // Genap Berjalan
        $joinTable = ['table' => 'n_sister_genap_bj as n', 'alias' => 'n'];
        $ptsColumn = 'n.kode_pt';
      }

      // Cek apakah PT ini sudah melakukan usulan SPTJM Susulan pada bulan & tahun terpilih
      // DI BULAN AKTUAL SAAT INI (Current Month).
      // Dengan mengecek tanggal_usulan, bukan dari ID usulan.
      $currentMonth = \Carbon\Carbon::now()->month;
      $sudahUsulkanBulanIni = DB::table('q_sptjm')
        ->where('kode_pts', $kodePts)
        ->where('bulan', $bulanTeks)
        ->where('tahun', $tahun)
        ->where('id_usulan', 'LIKE', 'S %')
        ->whereMonth('tanggal_usulan', $currentMonth)
        ->exists();

      if ($sudahUsulkanBulanIni) {
        Log::info('usulanSPTJM Susulan index blocked (already proposed this month)', [
          'kode_pts' => $kodePts,
          'bulan' => $bulan,
          'bulan_teks' => $bulanTeks,
          'tahun' => $tahun,
          'current_month' => $currentMonth
        ]);
        $dosenList = collect();
        $dosenListPNS = collect();
        $dosenListNonPNS = collect();
        return view('pts.usulan-sptjm-susulan', compact('dosenList', 'dosenListPNS', 'dosenListNonPNS', 'bulan'))
          ->with('info', 'Sudah melakukan usulan SPTJM Susulan di bulan ini. Anda dapat mengusulkan susulan kembali di bulan berikutnya.');
      }

      // Ambil NIDN dosen yang sudah diusulkan untuk bulan + tahun + kode_pts tersebut
      $nidnSudahDiusulkan = DB::table('q_sptjm')
        ->where('kode_pts', $kodePts)
        ->where('bulan', $bulanTeks)
        ->where('tahun', $tahun)
        ->where('id_usulan', '<>', '0')
        ->pluck('nidn')
        ->filter(function ($value) {
            return !empty(trim($value)) && trim($value) !== '-';
        })
        ->toArray();

      // Ambil dosen yang belum diusulkan di q_sptjm untuk periode ini
      $dosenList = DB::table('s_transaksi_2 as d')
        ->leftJoin($joinTable['table'], function ($join) use ($joinTable) {
          $alias = $joinTable['alias'];
          $join->on(function ($on) use ($alias) {
            $on->where(function ($q) use ($alias) {
              $q->whereColumn('d.nidn', '=', $alias . '.nidn')
                ->whereRaw("TRIM(d.nidn) != ''")
                ->whereRaw("TRIM(d.nidn) != '-'");
            })->orWhere(function ($q) use ($alias) {
              $q->whereColumn('d.nuptk', '=', $alias . '.nuptk')
                ->whereRaw("TRIM(d.nuptk) != ''")
                ->whereRaw("TRIM(d.nuptk) != '-'");
            });
          });
        })
        ->select(
          'd.nama',
          'd.nidn',
          'd.nuptk',
          'd.jenis',
          "d.gol{$bulan} as gol",
          "d.tahun{$bulan} as tahun",
          "d.jabatan{$bulan} as jabatan",
          'd.aktif',
          DB::raw("MAX($bkdColumn) as kesimpulan_bkd"),
          'd.keterangan'
        )
        ->where('d.kode_pt', $kodePts)
        ->where('d.tahun_versi', $tahun)
        ->where($ptsColumn, $kodePts)
        ->where('d.aktif', 1)
        ->where(function ($query) use ($kodeUsulanColumn) {
          $query->whereNull($kodeUsulanColumn)
            ->orWhere($kodeUsulanColumn, '');
        })
        ->where(function ($q) use ($nidnSudahDiusulkan) {
          $q->whereNotIn('d.nidn', $nidnSudahDiusulkan)
            ->whereNotIn('d.nuptk', $nidnSudahDiusulkan);
        })
        // filter langsung di WHERE agar kompatibel dengan ONLY_FULL_GROUP_BY
        ->whereNotNull(DB::raw($bkdColumn))
        // Hanya tampilkan yang BKD-nya 'M'
        ->where(DB::raw("TRIM($bkdColumn)"), '=', 'M')
        ->groupBy('d.nidn', 'd.nuptk', 'd.nama', 'd.jenis', "d.gol{$bulan}", "d.tahun{$bulan}", "d.jabatan{$bulan}", 'd.aktif', 'd.keterangan')
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

      Log::debug('usulanSPTJM Susulan index loaded', [
        'kode_pts' => $kodePts,
        'bulan' => $bulan,
        'bulan_teks' => $bulanTeks,
        'tahun' => $tahun,
        'count_total' => is_countable($dosenList) ? count($dosenList) : null,
        'count_pns' => $dosenListPNS->count(),
        'count_non_pns' => $dosenListNonPNS->count(),
      ]);
      } catch (\Throwable $e) {
        $alias = ErrorAlias::fromThrowable($e, 'PTS-SPTJM-SUSULAN');
        Log::error('usulanSPTJM Susulan index failed', [
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
        return view('pts.usulan-sptjm-susulan', compact('dosenList', 'dosenListPNS', 'dosenListNonPNS', 'bulan'))
          ->with('error', $alias['message']);
      }
    }


    return view('pts.usulan-sptjm-susulan', compact('dosenList', 'dosenListPNS', 'dosenListNonPNS', 'bulan'));
  }

  public function printSusulan(Request $request)
  {
    //kode terbaru
    $user = Auth::guard('pts')->user();
    $kodePts = $user->kode_pts;
    $bulan = $request->query('bulan') ? (int) $request->query('bulan') : now()->month;
    // $tahun = date('Y');
    $tahun = session(key: 'tahun');

    Log::debug('printSPTJM Susulan called', [
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
      $joinTable = ['table' => 'p_sister_ganjil_tl as p', 'alias' => 'p'];
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
      ->where('id_usulan', '<>', '0')
      ->pluck('nidn')
      ->filter(function ($value) {
          return !empty(trim($value)) && trim($value) !== '-';
      })
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
        $join->on(function ($on) use ($alias) {
          $on->where(function ($q) use ($alias) {
            $q->whereColumn('d.nidn', '=', $alias . '.nidn')
              ->whereRaw("TRIM(d.nidn) != ''")
              ->whereRaw("TRIM(d.nidn) != '-'");
          })->orWhere(function ($q) use ($alias) {
            $q->whereColumn('d.nuptk', '=', $alias . '.nuptk')
              ->whereRaw("TRIM(d.nuptk) != ''")
              ->whereRaw("TRIM(d.nuptk) != '-'");
          });
        });
      })
      ->select(
        'd.nama',
        'd.nidn',
        'd.nuptk',
        DB::raw('d.gol' . $bulan . ' as gol'),
        DB::raw('d.tahun' . $bulan . ' as tahun'),
        DB::raw('d.jabatan' . $bulan . ' as jabatan'),
        'd.aktif',
        'd.jenis',
        DB::raw("MAX($bkdColumn) as kesimpulan_bkd"),
        'd.keterangan'
      )
      ->where('d.kode_pt', $kodePts)
      ->where('d.tahun_versi', $tahun)
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

    // Split data berdasarkan jenis (PNS / NON PNS) agar sesuai template cetak
    $dosenListPNS = (clone $dosenList)
      ->where('d.jenis', 'PNS')
      ->whereNotNull(DB::raw($bkdColumn))
      ->where(DB::raw("TRIM($bkdColumn)"), '=', 'M')
      ->groupBy('d.nidn', 'd.nuptk', 'd.nama', 'd.jenis', 'd.gol' . $bulan, 'd.tahun' . $bulan, 'd.jabatan' . $bulan, 'd.aktif', 'd.keterangan')
      ->orderBy('d.nama')
      ->get();

    $dosenListNonPNS = (clone $dosenList)
      ->where('d.jenis', 'NON PNS')
      ->whereNotNull(DB::raw($bkdColumn))
      ->where(DB::raw("TRIM($bkdColumn)"), '=', 'M')
      ->groupBy('d.nidn', 'd.nuptk', 'd.nama', 'd.jenis', 'd.gol' . $bulan, 'd.tahun' . $bulan, 'd.jabatan' . $bulan, 'd.aktif', 'd.keterangan')
      ->orderBy('d.nama')
      ->get();

    Log::debug('printSPTJM Susulan loaded', [
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
      $alias = ErrorAlias::fromThrowable($e, 'PTS-SPTJM-SUSULAN');
      Log::error('printSPTJM Susulan failed', [
        'alias' => $alias['code'],
        'kode_pts' => $kodePts,
        'bulan' => $bulan,
        'tahun' => $tahun,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);
      return redirect()
        ->route('pts.usulan-sptjm-susulan', ['bulan' => $bulan])
        ->with('error', $alias['message']);
    }
  }

  public function uploadSPTJM(Request $request)
  {
    $user = Auth::guard('pts')->user();

    Log::info('uploadSPTJM(susulan) called', [
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
        // allow missing pimpinan settings; default on server side
        'nidn' => 'nullable',
        'nuptk' => 'nullable',
        'nama' => 'nullable',
        'jabatan' => 'nullable',
        'kota' => 'nullable',
        'nomor_surat' => 'nullable',
      ]);
    } catch (ValidationException $ve) {
      Log::warning('uploadSPTJM(susulan) validation failed', [
        'kode_pts' => $user->kode_pts ?? null,
        'errors' => $ve->errors(),
        'input' => array_filter($request->except(['file'])),
      ]);
      return redirect()->route('pts.usulan-sptjm-susulan', ['bulan' => $request->input('bulan')])->with('error', 'Validasi gagal: ' . implode('; ', array_map(function($v){return implode(', ', $v);}, $ve->errors())));
    }

    Log::info('uploadSPTJM(susulan) validation passed', ['kode_pts' => $user->kode_pts ?? null]);

    try {

    $kodePts = $user->kode_pts;
    $namaPts = $user->nama_pts;
    // $alamatPts = $user->alamat;

  $tahun = session('tahun');
  // Gunakan bulan yang DIPILIH DI DROPDOWN untuk prefix ID usulan
  // Misal: Dropdown Maret -> 03 (S 03...)
  $bulanAngka = str_pad($request->bulan, 2, '0', STR_PAD_LEFT);

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
    $bulanTeks = $namaBulan[intval($request->bulan)]; // ✅ Bulan yang DIUSULKAN tetap pakai $request

    // Hitung jumlah usulan SUSULAN (id_usulan diawali 'S ') untuk bulan dropdown yang dipilih
    // sehingga jika dropdown bulan berbeda, penomoran mulai dari 1; jika sama, bertambah +1
    $countUsulan = DB::table('q_sptjm')
      ->where('kode_pts', $kodePts)
      ->where('bulan', $bulanTeks)
      ->where('tahun', $tahun)
      ->where('id_usulan', 'LIKE', 'S %')
      ->count();

    $noUsulan = $countUsulan + 1;

    // Format tanggal
    // Simpan tanggal usulan sebagai date (YYYY-MM-DD)
    $tanggal_usulan = Carbon::now()->toDateString();

    // Format id_usulan
    $idUsulan = 'S ' . $bulanAngka . $kodePts . ' ' . $noUsulan;

    // Format nama file
    $filename =
      'S ' .
      $bulanAngka .
      $kodePts .
      ' ' .
      $noUsulan .
      '_' .
      $bulanTeks .
      '.' .
      $request->file->getClientOriginalExtension();

    // Simpan file ke storage
    $path = $request->file('file')->storeAs('public/uploadFile_SPTJM_S', $filename);

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
      'file' => 'uploadFile_SPTJM_S/' . $filename,
      'status' => 'Usulan',
      'tanggal_usulan' => $tanggal_usulan,
      'wilayah' => $user->wilayah,
      'password' => $user->password,
      'aktif' => $user->aktif,
      'created_at' => now(),
      'updated_at' => now(),
    ]);

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
      $joinTable = ['table' => 'p_sister_ganjil_tl as p', 'alias' => 'p'];
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
        $join->on(function ($on) use ($alias) {
          $on->where(function ($q) use ($alias) {
            $q->whereColumn('d.nidn', '=', $alias . '.nidn')
              ->whereRaw("TRIM(d.nidn) != ''")
              ->whereRaw("TRIM(d.nidn) != '-'");
          })->orWhere(function ($q) use ($alias) {
            $q->whereColumn('d.nuptk', '=', $alias . '.nuptk')
              ->whereRaw("TRIM(d.nuptk) != ''")
              ->whereRaw("TRIM(d.nuptk) != '-'");
          });
        });
      })
      ->select('d.nidn', 'd.nuptk', DB::raw("MAX($bkdColumn) as kesimpulan_bkd"))
      ->where('d.kode_pt', $kodePts)
      ->where('d.tahun_versi', $tahun)
      ->where($ptsColumn, $kodePts)
      ->where('d.aktif', 1)
      ->where(function ($q) use ($nidnSudahDiusulkan) {
        $q->whereNotIn('d.nidn', $nidnSudahDiusulkan)
          ->whereNotIn('d.nuptk', $nidnSudahDiusulkan);
      })
      ->whereNotNull(DB::raw($bkdColumn))
      // Pastikan hanya dosen dengan BKD = 'M' yang akan di-update KodeUsulan-nya
      ->where(DB::raw("TRIM($bkdColumn)"), '=', 'M')
      ->groupBy('d.nidn', 'd.nuptk')
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

    return redirect()
      ->route('pts.usulan-sptjm-susulan')
      ->with('success', 'File SPTJM berhasil diupload dan data berhasil disimpan.');
    } catch (\Throwable $e) {
      $alias = ErrorAlias::fromThrowable($e, 'PTS-SPTJM-SUSULAN');
      Log::error('uploadSPTJM(susulan) failed', [
        'alias' => $alias['code'],
        'kode_pts' => $user->kode_pts ?? null,
        'bulan' => $request->input('bulan'),
        'tahun' => session('tahun'),
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);
      return redirect()
        ->route('pts.usulan-sptjm-susulan', ['bulan' => $request->input('bulan')])
        ->with('error', $alias['message']);
    }
  }
}
