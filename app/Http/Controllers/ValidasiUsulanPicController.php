<?php

namespace App\Http\Controllers;

use App\Helpers\ErrorAlias;
use App\Models\PengaturanUsulan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Storage;

class ValidasiUsulanPicController extends Controller
{
  public function index()
  {
    return view('pic.validasi-usulan');
  }

  /**
   * Cek apakah konfigurasi kode cair (jenis usulan + pencairan_ke)
   * sudah di-set oleh admin untuk baris usulan tertentu.
   * Digunakan oleh halaman /pic/validasi-usulan sebelum masuk ke halaman validasi dosen.
   */
  public function cekKodeCair($id)
  {
    try {
      $currentYear = session('tahun');
      // $id di sini adalah nomor baris 'no' pada q_sptjm
      $row = DB::table('q_sptjm')
        ->where('no', $id)
        ->where('tahun', $currentYear)
        ->first();
      if (!$row) {
        return response()->json([
          'allowed' => false,
          'message' => 'Data usulan tidak ditemukan.',
        ]);
      }

      // Tentukan jenis usulan berdasarkan prefix id_usulan
      $idUsulanFull = str_replace('-', ' ', $row->id_usulan);
      $cleanPrefix = strtoupper(substr(str_replace(' ', '', $idUsulanFull), 0, 2));
      if (in_array($cleanPrefix, ['BT', 'TB', 'ST', 'TS'])) {
        $jenisUsulan = 'TUKIN';
      } else {
        $jenisUsulan = 'SPTJM';
      }

      $kodeCairAktif = 0;
      try {
        if (Storage::disk('local')->exists('kode_cair_config.json')) {
          $rawCfg = Storage::disk('local')->get('kode_cair_config.json');
          $cfg = json_decode($rawCfg, true);
          // Aturan: boleh validasi jika key jenis usulan ada di json.
          // SPTJM (B%, S%) hanya boleh jika ada key 'SPTJM'
          // TUKIN (BT%, ST%) hanya boleh jika ada key 'TUKIN'
          // (Tanpa validasi tahun di sisi backend.)
          if (is_array($cfg) && isset($cfg[$jenisUsulan]) && is_array($cfg[$jenisUsulan])) {
            $entry = $cfg[$jenisUsulan];
            $kodeCairAktif = (int) ($entry['pencairan_ke'] ?? 0);
          }
        }
      } catch (\Throwable $e) {
        $kodeCairAktif = 0;
      }

      if ($kodeCairAktif > 0) {
        return response()->json([
          'allowed' => true,
          'message' => 'Konfigurasi kode cair ditemukan.',
          'jenis_usulan' => $jenisUsulan,
          'pencairan_ke' => $kodeCairAktif,
        ]);
      }

      return response()->json([
        'allowed' => false,
        'message' => 'Tidak bisa validasi. Silakan meminta izin kepada admin untuk membuka kode cair.',
        'jenis_usulan' => $jenisUsulan,
      ]);
    } catch (\Throwable $e) {
      $alias = ErrorAlias::fromThrowable($e, 'PIC-KODE-CAIR');
      Log::error('ValidasiUsulanPicController cekKodeCair failed', [
        'alias' => $alias['code'],
        'id' => $id,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);

      return response()->json([
        'allowed' => false,
        'message' => $alias['message'],
        'code' => $alias['code'],
      ], 500);
    }
  }

  public function getData(Request $request)
  {
    $tipeSptjm = $request->input('pilihsptjm');
    $bulan = $request->input('bulan');
    $status = $request->input('status');
    // $currentYear = date('Y');
    $currentYear = session('tahun');

    // Ambil wilayah dari user yang login
    $emailPIC = Auth::user()->email;

    // Query dasar: data sesuai tahun dan wilayah
    $query = DB::table('q_sptjm')
      ->where('tahun', $currentYear)
      ->where('wilayah', $emailPIC);

    // Filter Tipe SPTJM/TUKIN
    if (!empty($tipeSptjm) && $tipeSptjm !== 'All') {
      // If frontend sends a SQL-like prefix (e.g. 'B%','S%','BT%','ST%'), use it with exclusions
      if (is_string($tipeSptjm) && str_ends_with($tipeSptjm, '%')) {
        // Ensure SPTJM 'B%' doesn't include TUKIN 'BT%'
        if ($tipeSptjm === 'B%') {
          $query->where('id_usulan', 'LIKE', 'B%')
            ->whereRaw('id_usulan NOT LIKE ?', ['BT%']);
        } elseif ($tipeSptjm === 'S%') {
          // Ensure SPTJM 'S%' doesn't include TUKIN 'ST%'
          $query->where('id_usulan', 'LIKE', 'S%')
            ->whereRaw('id_usulan NOT LIKE ?', ['ST%']);
        } else {
          $query->where('id_usulan', 'LIKE', $tipeSptjm);
        }
      } else {
        // Backwards-compatible mapping for textual values
        switch ($tipeSptjm) {
          case 'SPTJM Berjalan':
            $query->where('id_usulan', 'LIKE', 'B%');
            break;
          case 'SPTJM Susulan':
            $query->where('id_usulan', 'LIKE', 'S%');
            break;
          case 'TUKIN Berjalan':
            $query->where('id_usulan', 'LIKE', 'BT%');
            break;
          case 'TUKIN Susulan':
            $query->where('id_usulan', 'LIKE', 'ST%');
            break;
        }
      }
    }

    // Filter Bulan
    if ($bulan !== 'All') {
      $query->where('bulan', $bulan);
    }

    // Filter Status
    if (!empty($status) && $status !== 'All') {
      $query->where('status', $status);
    }

    // Ambil data utama
    $dataUtama = $query->get();

    // Ambil data id_usulan = 0 (khusus status = 'Tolak')
    $dataZero = collect();
    if ($status === 'Tolak') {
      $dataZero = DB::table('q_sptjm')
        ->where('id_usulan', 0)
        ->where('status', 'Tolak')
        ->where('tahun', $currentYear)
        ->where('wilayah', $emailPIC)
        ->when($bulan !== 'All', function ($q) use ($bulan) {
          return $q->where('bulan', $bulan);
        })
        ->orderByDesc('no')
        ->get();
    }

    // Gabungkan hasilnya
    $dataUsulan = $dataUtama->merge($dataZero);

    // Masking kode_pts
    // foreach ($dataUsulan as $item) {
    //   $item->kode_pts = Str::mask($item->kode_pts, '*', -2, 1);
    // }

    return response()->json([
      'success' => true,
      'data' => $dataUsulan,
    ]);
  }

  public function setujui(Request $request, $id)
  {
    try {
      $currentYear = session('tahun');
      $emailPIC = Auth::user()->email;

      $row = DB::table('q_sptjm')
        ->where('no', $id)
        ->where('tahun', $currentYear)
        ->where('wilayah', $emailPIC)
        ->first();

      if (!$row) {
        return response()->json([
          'success' => false,
          'message' => 'Data usulan tidak ditemukan.',
        ]);
      }

      if ($request->filled('id_usulan') && (string) $request->input('id_usulan') !== (string) $row->id_usulan) {
        return response()->json([
          'success' => false,
          'message' => 'ID usulan tidak sesuai.',
        ]);
      }

      DB::table('q_sptjm')
        ->where('no', $id)
        ->where('tahun', $currentYear)
        ->where('wilayah', $emailPIC)
        ->update([
          'status' => 'Validasi',
          'updated_at' => now(),
        ]);

      return response()->json(['success' => true]);
    } catch (\Throwable $e) {
      $alias = ErrorAlias::fromThrowable($e, 'PIC-VALIDASI-SETUJUI');
      Log::error('ValidasiUsulanPicController setujui failed', [
        'alias' => $alias['code'],
        'id' => $id,
        'tahun' => session('tahun'),
        'user_email' => Auth::user()->email ?? null,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);

      return response()->json([
        'success' => false,
        'message' => $alias['message'],
        'code' => $alias['code'],
      ], 500);
    }
  }

  public function tolak(Request $request, $id)
  {
    try {
      $currentYear = session('tahun');
      // $id di sini adalah identifier unik baris (mis. kolom 'no')
      // cari data persis satu baris berdasarkan 'no'
      $dataSPTJM = DB::table('q_sptjm')
        ->where('no', $id)
        ->where('tahun', $currentYear)
        ->first();

      if (!$dataSPTJM) {
        return response()->json(['success' => false, 'message' => 'Data usulan tidak ditemukan.']);
      }

      $idUsulan = $dataSPTJM->id_usulan;
      $filePath = $dataSPTJM->file;
      // Simpan alasan dan status tolak
      DB::table('q_sptjm')
        ->where('no', $id)
        ->update([
          'status' => 'Tolak',
          'alasan_penolakan' => $request->alasan,
          'id_usulan' => 0,
          'file' => 0,
          'updated_at' => now(),
        ]);

      // // // Set id_usulan jadi 0 setelah ditolak
      // DB::table('q_sptjm')
      //   ->where('id_usulan', $id)
      //   ->update([
      //     'id_usulan' => 0,
      //     'file' => 0,
      //   ]);

      // Hapus detail untuk TUKIN; untuk SPTJM, kosongkan KodeUsulan[bulan] di s_transaksi_2
      $cleanPrefix = strtoupper(substr(str_replace(' ', '', $idUsulan), 0, 2));
      if (in_array($cleanPrefix, ['BT', 'TB', 'ST', 'TS'])) {
        // TUKIN: hapus baris terkait di tabel tukin untuk bulan & tahun usulan
        $bulanUsulan = $dataSPTJM->bulan ?? null;
        DB::table('s_tunjangan_kinerja')
          ->when($idUsulan, fn($q) => $q->where('Kode_Usulan', $idUsulan))
          ->when($bulanUsulan, fn($q) => $q->where('Bulan', $bulanUsulan))
          ->when($currentYear, fn($q) => $q->where('Tahun', $currentYear))
          ->delete();
        try {
          DB::table('s_tunjangan_transaksi')->where('Kode_Usulan', $idUsulan)->delete();
        } catch (\Throwable $ignore) {}
      } else {
        // SPTJM: unset kolom KodeUsulan sesuai bulan usulan (dropdown) yang tersimpan di q_sptjm
        // Mapping nama bulan Indonesia ke nomor bulan
        $mapBulan = [
          'Januari' => 1,
          'Februari' => 2,
          'Maret' => 3,
          'April' => 4,
          'Mei' => 5,
          'Juni' => 6,
          'Juli' => 7,
          'Agustus' => 8,
          'September' => 9,
          'Oktober' => 10,
          'November' => 11,
          'Desember' => 12,
        ];

        $bulanUsulan = $dataSPTJM->bulan ?? null;
        if ($bulanUsulan && isset($mapBulan[$bulanUsulan])) {
          $index = $mapBulan[$bulanUsulan];
          $col = 'KodeUsulan' . $index;
          DB::table('s_transaksi_2')
            ->where($col, $idUsulan)
            ->update([$col => null]);
        }
      }

      //delete file jika ada
      if (Storage::disk('public')->exists($filePath)) {
        Storage::disk('public')->delete($filePath);
      }


      return response()->json(['success' => true]);
    } catch (\Exception $e) {
      $alias = ErrorAlias::fromThrowable($e, 'PIC-VALIDASI');
      Log::error('ValidasiUsulanPicController action failed', [
        'alias' => $alias['code'],
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);

      return response()->json([
        'success' => false,
        'message' => 'Terjadi kesalahan saat memproses data. (Kode: ' . $alias['code'] . ')',
        'code' => $alias['code'],
      ], 500);
    }
  }

  /**
   * Preload BKD idents dengan pagination (hindari full tabel scan + sort)
   */
  private function preloadBkdIdentsPaginated($sisternas, $kodePTS, $limit = 1000)
  {
    $exprIdent = "COALESCE(NULLIF(TRIM(b.nidn),''), NULLIF(TRIM(b.nuptk),''))";
    return DB::table("$sisternas as b")
      ->where('b.kode_pt', $kodePTS)
      ->where('b.kesimpulan_bkd', 'M')
      ->selectRaw("$exprIdent as ident")
      ->groupBy('ident')
      ->limit($limit)
      ->pluck('ident')
      ->values()
      ->all();
  }

  /**
   * Get dosen untuk SPTJM dengan BKD preload (optimized)
   */
  private function getDosenSPTJM($sisternas, $kodePTS, $namaBulan, $idUsulanVariants, $currentYear)
  {
    $mapBulan = [
      'Januari' => 1, 'Februari' => 2, 'Maret' => 3, 'April' => 4,
      'Mei' => 5, 'Juni' => 6, 'Juli' => 7, 'Agustus' => 8,
      'September' => 9, 'Oktober' => 10, 'November' => 11, 'Desember' => 12,
    ];
    $indexBulan = $mapBulan[$namaBulan] ?? null;

    // Preload BKD idents terlebih dahulu (batasi 1000 untuk hindari OOM)
    $bkdIdents = $this->preloadBkdIdentsPaginated($sisternas, $kodePTS, 1000);

    if (empty($bkdIdents)) {
      return collect();
    }

    $dosenQuery = DB::table('s_transaksi_2 as a')
      ->where('a.aktif', '1')
      ->where('a.kode_pt', $kodePTS)
      ->where('a.tahun_versi', session('tahun'))
      ->whereIn(DB::raw("COALESCE(a.NIDN, a.NUPTK)"), $bkdIdents)
      ->select(
        DB::raw('COALESCE(a.NIDN, a.NUPTK) as nidn'),
        DB::raw('a.Nama as nama'),
        DB::raw('a.Jenis as jenis'),
        DB::raw('a.Aktif as aktif'),
        DB::raw("'" . 'M' . "' as kesimpulan_bkd")
      )
      ->orderBy('a.Nama');

    if ($indexBulan) {
      $kolomKodeUsulan = 'a.KodeUsulan' . $indexBulan;
      $dosenQuery->whereIn($kolomKodeUsulan, $idUsulanVariants);
    }

    return $dosenQuery->get();
  }

  /**
   * Get dosen untuk TUKIN dengan BKD preload (optimized)
   */
  private function getDosenTUKIN($sisternas, $kodePTS, $namaBulan, $idUsulanVariants, $currentYear)
  {
    // Preload BKD idents (batasi 1000)
    $bkdIdents = $this->preloadBkdIdentsPaginated($sisternas, $kodePTS, 1000);

    if (empty($bkdIdents)) {
      return collect();
    }

    return DB::table('s_tunjangan_kinerja as tk')
      ->whereIn(DB::raw("COALESCE(NULLIF(TRIM(tk.NIDN),''), NULLIF(TRIM(tk.NUPTK),''))"), $bkdIdents)
      ->whereIn('tk.Kode_Usulan', $idUsulanVariants)
      ->where('tk.Kode_PTS', $kodePTS)
      ->where('tk.Bulan', $namaBulan)
      ->where('tk.Tahun', (string) $currentYear)
      ->distinct()
      ->select([
        DB::raw('COALESCE(NULLIF(TRIM(tk.NIDN),""), NULLIF(TRIM(tk.NUPTK),"")) as nidn'),
        DB::raw('tk.Nama as nama'),
        DB::raw('tk.Jenis as jenis'),
        DB::raw("CASE WHEN tk.Status = '1' THEN '1' ELSE '0' END as aktif"),
        DB::raw("'" . 'M' . "' as kesimpulan_bkd"),
      ])
      ->orderBy('tk.Nama')
      ->get();
  }

  public function halamanValidasiDosen($id)
  {
    $currentYear = session('tahun');
    // $id bisa berupa nomor baris 'no' atau id_usulan (ter-encode)
    $rawId = str_replace('-', ' ', $id);
    // coba ambil berdasarkan no terlebih dahulu
    $pengajuan = DB::table('q_sptjm')
      ->where('no', $id)
      ->where('tahun', $currentYear)
      ->first();

    // jika tidak ditemukan oleh no, coba gunakan id_usulan dari query param atau rawId
    if (!$pengajuan) {
      $usulanParam = request()->query('usulan') ? str_replace('-', ' ', request()->query('usulan')) : $rawId;
      $pengajuan = DB::table('q_sptjm')
        ->where('id_usulan', $usulanParam)
        ->where('tahun', $currentYear)
        ->first();
    }

    if (!$pengajuan) {
      abort(404, 'Data usulan tidak ditemukan.');
    }

    $kodePTS = $pengajuan->kode_pts;
    $namaBulan = $pengajuan->bulan;
    // siapkan varian id usulan (ada data yang memakai '-' atau spasi)
    $idUsulanRaw = (string) $pengajuan->id_usulan;
    $idUsulanSpace = str_replace('-', ' ', $idUsulanRaw);
    $idUsulanHyphen = str_replace(' ', '-', $idUsulanRaw);
    $idUsulanVariants = array_values(array_unique([$idUsulanRaw, $idUsulanSpace, $idUsulanHyphen]));
    // default value untuk dipakai di view / link
    $idUsulan = $idUsulanRaw;

    //sisternas
    if (($namaBulan == "Januari") or
      ($namaBulan == "Februari")
    ) {
      $sisternas = "o_sister_genap_tl";
    }
    if (($namaBulan == "Maret") or
      ($namaBulan == "April") or
      ($namaBulan == "Mei") or
      ($namaBulan == "Juni") or
      ($namaBulan == "Juli") or
      ($namaBulan == "Agustus")
    ) {
      $sisternas = "p_sister_ganjil_tl";
    }
    if (($namaBulan == "September") or
      ($namaBulan == "Oktober") or
      ($namaBulan == "November") or
      ($namaBulan == "Desember")
    ) {
      $sisternas = "n_sister_genap_bj";
    }

    // Ambil data dosen: beda sumber untuk SPTJM vs TUKIN
    $dosen = collect();
    $dosenPns = collect();
    $dosenNonPns = collect();
    if ($pengajuan->status === "Validasi") {
      // Gunakan prefix dari id_usulan yang sebenarnya, bukan dari parameter route
      $cleanPrefix = strtoupper(substr(str_replace(' ', '', $idUsulan), 0, 2));
      if (in_array($cleanPrefix, ['BT', 'TB', 'ST', 'TS'])) {
        $dosen = $this->getDosenTUKIN($sisternas, $kodePTS, $namaBulan, $idUsulanVariants, $currentYear);
      } else {
        $dosen = $this->getDosenSPTJM($sisternas, $kodePTS, $namaBulan, $idUsulanVariants, $currentYear);
      }

      // Sort sudah di-database, tinggal filter PNS vs non-PNS
      $dosenPns = $dosen
        ->filter(function ($r) {
          return strtoupper(trim((string) ($r->jenis ?? ''))) === 'PNS';
        })
        ->values();

      $dosenNonPns = $dosen
        ->filter(function ($r) {
          return strtoupper(trim((string) ($r->jenis ?? ''))) === 'NON PNS';
        })
        ->values();
    }

    return view('pic.validasi-data-dosen', [
      'pengajuan' => $pengajuan,
      'dosen' => $dosen,
      'dosenPns' => $dosenPns,
      'dosenNonPns' => $dosenNonPns,
    ]);
  }

  /**
   * Batch update s_transaksi_2 dengan chunking (avoid per-row update)
   */
  private function batchUpdateDosenSPTJM($dosenList, $updateData, $chunkSize = 50)
  {
    $dosenList->chunk($chunkSize)->each(function ($chunk) use ($updateData) {
      $nidnValues = $chunk->map(function ($d) {
        $nidn = isset($d->NIDN) ? trim((string) $d->NIDN) : '';
        return ($nidn !== '' && $nidn !== '-') ? $nidn : null;
      })->filter()->values()->all();

      $nuptkValues = $chunk->map(function ($d) {
        $nuptk = isset($d->NUPTK) ? trim((string) $d->NUPTK) : '';
        return ($nuptk !== '' && $nuptk !== '-') ? $nuptk : null;
      })->filter()->values()->all();

      if (!empty($nidnValues)) {
        DB::table('s_transaksi_2')
          ->whereIn('NIDN', $nidnValues)
          ->update($updateData);
      }

      if (!empty($nuptkValues)) {
        DB::table('s_transaksi_2')
          ->whereIn('NUPTK', $nuptkValues)
          ->update($updateData);
      }
    });
  }

  public function proses($id)
  {
    try {
      $currentYear = session('tahun');
      // Tentukan nomor baris 'no' yang akan diproses (prioritaskan request('no'))
      $rowNo = request('no') ?? null;

      if (!$rowNo) {
        // Jika parameter route $id tampak numerik, anggap itu adalah 'no'
        if (is_numeric($id)) {
          $rowNo = $id;
        } else {
          // Coba cari berdasarkan id_usulan (mungkin di-encode dengan -)
          $rawId = str_replace('-', ' ', $id);
          $found = DB::table('q_sptjm')
            ->where('id_usulan', $rawId)
            ->where('tahun', $currentYear)
            ->first();
          $rowNo = $found ? $found->no : null;
        }
      }

      if (!$rowNo) {
        return redirect('/pic/validasi-usulan')->with('error', 'Data usulan tidak ditemukan.');
      }

      // ambil data sptjm berdasarkan 'no' baris yang ditentukan
      $sptjm = DB::table("q_sptjm")
        ->select('id_usulan', 'Bulan', 'kode_pts', 'tanggal_usulan', 'tahun')
        ->where('no', $rowNo)
        ->where('tahun', $currentYear)
        ->first();

      $namaBulan = $sptjm->Bulan;
      // Id usulan yang akan diproses (siapkan varian '-' vs spasi)
      $idUsulanRaw = (string) $sptjm->id_usulan;
      $idUsulanSpace = str_replace('-', ' ', $idUsulanRaw);
      $idUsulanHyphen = str_replace(' ', '-', $idUsulanRaw);
      $idUsulanVariants = array_values(array_unique([$idUsulanRaw, $idUsulanSpace, $idUsulanHyphen]));
      // default value untuk logika prefix
      $idUsulan = $idUsulanRaw;
      // Tanggal usulan dipakai untuk memilih konfigurasi pencairan di m_pengaturan_usulan
      $tanggalUsulan = $sptjm->tanggal_usulan;
      $tahunUsulan = $sptjm->tahun;
      $kodePts = $sptjm->kode_pts;
      $pemegang_wilayah = Auth::user()->email;
      //sisternas
      if (($namaBulan == "Januari") or
        ($namaBulan == "Februari")
      ) {
        $sisternas = "o_sister_genap_tl";
      }
      if (($namaBulan == "Maret") or
        ($namaBulan == "April") or
        ($namaBulan == "Mei") or
        ($namaBulan == "Juni") or
        ($namaBulan == "Juli") or
        ($namaBulan == "Agustus")
      ) {
        $sisternas = "p_sister_ganjil_tl";
      }
      if (($namaBulan == "September") or
        ($namaBulan == "Oktober") or
        ($namaBulan == "November") or
        ($namaBulan == "Desember")
      ) {
        $sisternas = "n_sister_genap_bj";
      }

      //bulan angka
      $bulanAngka = [
        "Januari" => 1,
        "Februari" => 2,
        "Maret" => 3,
        "April" => 4,
        "Mei" => 5,
        "Juni" => 6,
        "Juli" => 7,
        "Agustus" => 8,
        "September" => 9,
        "Oktober" => 10,
        "November" => 11,
        "Desember" => 12
      ];

      if (!isset($bulanAngka[$namaBulan])) {
        return redirect('/pic/validasi-usulan')->with('error', 'Gagal memproses: Bulan usulan ("' . $namaBulan . '") tidak valid.');
      }


      $nmBulanSingkat = [
        "Januari" => "Jan",
        "Februari" => "Feb",
        "Maret" => "Mar",
        "April" => "Apr",
        "Mei" => "May",
        "Juni" => "Jun",
        "Juli" => "Jul",
        "Agustus" => "Ags",
        "September" => "Sep",
        "Oktober" => "Okt",
        "November" => "Nov",
        "Desember" => "Des"
      ];
      // Tentukan index bulan (1-12) dan kolom KodeUsulan yang relevan untuk usulan ini
      $bulanIndex = $bulanAngka[$namaBulan] ?? null;
      $kolomKodeUsulan = $bulanIndex ? 'a.KodeUsulan' . $bulanIndex : null;

      // Tentukan jenis usulan dari prefix id_usulan
      $idUsulanFull = str_replace('-', ' ', $sptjm->id_usulan);
      $cleanPrefix = strtoupper(substr(str_replace(' ', '', $idUsulanFull), 0, 2));
      if (in_array($cleanPrefix, ['BT', 'TB', 'ST', 'TS'])) {
        $jenisUsulan = 'TUKIN';
      } else {
        $jenisUsulan = 'SPTJM';
      }

      // Ambil kode cair (pencairan_ke) dari file JSON storage/app/kode_cair_config.json
      // Format sekarang: { "SPTJM": {"pencairan_ke":3,...}, "TUKIN": {...} }
      $kodeCairAktif = 0;
      try {
        if (Storage::disk('local')->exists('kode_cair_config.json')) {
          $rawCfg = Storage::disk('local')->get('kode_cair_config.json');
          $cfg = json_decode($rawCfg, true);
          if (is_array($cfg) && isset($cfg[$jenisUsulan])) {
            $entry = $cfg[$jenisUsulan];
            $kodeCairAktif = (int) ($entry['pencairan_ke'] ?? 0);
          }
        }
      } catch (\Throwable $e) {
        $kodeCairAktif = 0;
      }

      // Jika usulan adalah TUKIN (BT%/ST%), gunakan tabel s_tunjangan_kinerja dan update Kode_Cair
      if ($jenisUsulan === 'TUKIN') {
        // Update hanya untuk dosen yang BKD-nya 'M' (ident = NIDN atau NUPTK)
        $bkdMIdents = DB::query()->fromSub(function ($q) use ($sisternas, $kodePts) {
          $q->from("$sisternas as b")
            ->selectRaw("COALESCE(NULLIF(TRIM(b.nidn),''), NULLIF(TRIM(b.nuptk),'')) as ident")
            ->where('b.kode_pt', $kodePts)
            ->where('b.kesimpulan_bkd', 'M')
            ->groupBy('ident');
        }, 'bkd_m')->select('ident');


        DB::table('s_tunjangan_kinerja')
          ->whereIn('Kode_Usulan', $idUsulanVariants)
          ->where('Kode_PTS', $kodePts)
          ->where('Bulan', $namaBulan)
          ->where('Tahun', (string) $currentYear)
          ->where(function ($q) use ($bkdMIdents) {
            $q->whereIn('NIDN', $bkdMIdents)
              ->orWhereIn('NUPTK', $bkdMIdents);
          })
          ->update([
            'Kode_Cair' => (string) $kodeCairAktif,
          ]);

        DB::table('q_sptjm')
          ->where('no', $rowNo)
          ->where('tahun', $currentYear)
          ->update([
            'status' => 'Proses',
            'updated_at' => now(),
          ]);

        return redirect('/pic/validasi-usulan')->with('success', 'Usulan berhasil diproses.');
      }

      //ambil data yg ingin diupdate: hanya baris dengan KodeUsulan{bulan} sama persis dengan id usulan ini
      // Ambil dengan chunk untuk batch processing (hindari full load ke array)
      $dataDosen = collect();
      $offset = 0;
      $chunkSize = 100;
      $hasMore = true;

      while ($hasMore) {
        $chunk = DB::table('s_transaksi_2 as a')
          ->select(
            'a.*',
            DB::raw("a.Jabatan" . $bulanAngka[$namaBulan] . " as Jabatan"),
            DB::raw("a.Gaji" . $bulanAngka[$namaBulan] . " as Gaji"),
            DB::raw("a.Gol" . $bulanAngka[$namaBulan] . " as Gol")
          )
          ->where('a.kode_pt', $kodePts)
          ->where('a.aktif', '1')
          ->where('a.pemegang_wilayah', $pemegang_wilayah)
          ->where('a.Tahun_Versi', session('tahun'))
          ->when($kolomKodeUsulan !== null, function ($q) use ($kolomKodeUsulan, $idUsulanVariants) {
            $q->where(function ($q2) use ($kolomKodeUsulan, $idUsulanVariants) {
              foreach ($idUsulanVariants as $v) {
                $q2->orWhere($kolomKodeUsulan, $v);
              }
            });
          })
          ->offset($offset)
          ->limit($chunkSize)
          ->get();

        if ($chunk->count() === 0) {
          $hasMore = false;
        } else {
          $dataDosen = $dataDosen->merge($chunk);
          $offset += $chunkSize;
        }
      }

      // Filter BKD='M' dari tabel sisternas, tapi batasi hanya ke dosen yang sedang diproses
      // (menghindari scan/groupBy seluruh tabel sisternas yang besar)
      $idents = [];
      foreach ($dataDosen as $d) {
        $nidn = isset($d->NIDN) ? trim((string) $d->NIDN) : '';
        $nuptk = isset($d->NUPTK) ? trim((string) $d->NUPTK) : '';
        $ident = ($nidn !== '' && $nidn !== '-') ? $nidn : (($nuptk !== '' && $nuptk !== '-') ? $nuptk : '');
        if ($ident !== '') $idents[] = $ident;
      }
      $idents = array_values(array_unique($idents));

      $bkdSet = [];
      if (!empty($idents)) {
        $exprIdent = "COALESCE(NULLIF(TRIM(b.nidn),''), NULLIF(TRIM(b.nuptk),''))";
        $bkdIdents = DB::table("$sisternas as b")
          ->where('b.kode_pt', $kodePts)
          ->where('b.kesimpulan_bkd', 'M')
          ->whereIn(DB::raw($exprIdent), $idents)
          ->selectRaw("$exprIdent as ident")
          ->groupBy('ident')
          ->pluck('ident')
          ->all();
        foreach ($bkdIdents as $ident) {
          $key = trim((string) $ident);
          if ($key !== '' && $key !== '-') $bkdSet[$key] = true;
        }
      }

      if (!empty($bkdSet)) {
        $dataDosen = $dataDosen->filter(function ($d) use ($bkdSet) {
          $nidn = isset($d->NIDN) ? trim((string) $d->NIDN) : '';
          $nuptk = isset($d->NUPTK) ? trim((string) $d->NUPTK) : '';
          $ident = ($nidn !== '' && $nidn !== '-') ? $nidn : (($nuptk !== '' && $nuptk !== '-') ? $nuptk : '');
          return ($ident !== '' && isset($bkdSet[$ident]));
        })->values();
      } else {
        $dataDosen = collect();
      }

      // preload tarif pajak (hindari query per dosen)
      $pajakMap = DB::table('d_pajak')->pluck('tarif_pajak', 'akumulasi')->all();

      // Siapkan bulk update data dengan chunking (hindari per-row update)
      $updateBatch = collect();
      $bulanNum = $bulanAngka[$namaBulan];
      $bulanShort = $nmBulanSingkat[$namaBulan];

      // Pengecekan awal: Pastikan semua dosen memiliki nilai Gaji yang valid
      $invalidGajiDosen = [];
      foreach ($dataDosen as $d) {
        $rawGaji = trim((string) $d->Gaji);
        // Gaji tidak boleh kosong atau berisi huruf
        if ($rawGaji === '' || !is_numeric($rawGaji)) {
            $ident = isset($d->NIDN) && $d->NIDN !== '-' ? $d->NIDN : ($d->NUPTK ?? '');
            $invalidGajiDosen[] = $d->Nama . ($ident ? " ($ident)" : "");
        }
      }

      if (count($invalidGajiDosen) > 0) {
          $names = implode(', ', array_slice($invalidGajiDosen, 0, 3));
          if (count($invalidGajiDosen) > 3) {
              $names .= " dan " . (count($invalidGajiDosen) - 3) . " lainnya";
          }
          return redirect('/pic/validasi-usulan')->with('error', 'Gagal memproses: Ditemukan isian Gaji kosong atau tidak valid pada dosen: ' . $names . '. Harap perbaiki master data terlebih dahulu.');
      }

      foreach ($dataDosen as $d) {
        $isGuruBesar = strtolower(trim($d->Jabatan)) === 'guru besar';
        $gajiDosen = (float) $d->Gaji;
        $golKey = isset($d->Gol) ? trim((string) $d->Gol) : '';
        $pajak = (float) ($pajakMap[$golKey] ?? 0);

        if ($isGuruBesar) {
          $gajiTPD = $gajiDosen / 3;
          $gajiTKGB = $gajiTPD * 2;
        } else {
          $gajiTPD = $gajiDosen;
          $gajiTKGB = 0;
        }

        $nilaiPajakTPD = $gajiTPD * $pajak;
        $nilaiPajakTKGB = $gajiTKGB * $pajak;
        $bersihTPD = $gajiTPD - $nilaiPajakTPD;
        $bersihTKGB = $gajiTKGB - $nilaiPajakTKGB;

        // Siapkan data update kode cair & komponen lain
        $updateData = [
          "TPD{$bulanNum}" => $gajiTPD,
          "TKGB{$bulanNum}" => $gajiTKGB,
          "pajakTPD{$bulanNum}" => $pajak,
          "pajakTKGB{$bulanNum}" => $isGuruBesar ? $pajak : 0,
          "nilaiPajakTPD{$bulanNum}" => $nilaiPajakTPD,
          "nilaiPajakTKGB{$bulanNum}" => $nilaiPajakTKGB,
          "bersihTPD{$bulanNum}" => $bersihTPD,
          "bersihTKGB{$bulanNum}" => $bersihTKGB,
          "Pengguna" => $pemegang_wilayah,
          $bulanShort => $kodeCairAktif,
        ];

        $nidnKey = isset($d->NIDN) ? trim((string) $d->NIDN) : '';
        $nuptkKey = isset($d->NUPTK) ? trim((string) $d->NUPTK) : '';
        
        $updateBatch->push([
          'nidn' => $nidnKey !== '' && $nidnKey !== '-' ? $nidnKey : null,
          'nuptk' => $nuptkKey !== '' && $nuptkKey !== '-' ? $nuptkKey : null,
          'data' => $updateData,
        ]);
      }

      // Batch update dengan chunking (50 rows per batch)
      if ($updateBatch->count() > 0) {
        $updateBatch->chunk(50)->each(function ($chunk) use ($kodePts, $pemegang_wilayah) {
          foreach ($chunk as $item) {
            $query = DB::table('s_transaksi_2')
              ->where('Tahun_Versi', '=', session('tahun'))
              ->where('kode_pt', $kodePts)
              ->where('pemegang_wilayah', $pemegang_wilayah)
              ->where('aktif', '1');

            if ($item['nidn']) {
              $query->where('NIDN', $item['nidn']);
            } elseif ($item['nuptk']) {
              $query->where('NUPTK', $item['nuptk']);
            } else {
              continue;
            }

            $query->update($item['data']);
          }
        });
      }

      // Tandai hanya baris q_sptjm yang spesifik (berdasarkan 'no') sebagai Proses
      DB::table('q_sptjm')
        ->where('no', $rowNo)
        ->where('tahun', $currentYear)
        ->update([
          'status' => 'Proses',
          'updated_at' => now(),
        ]);

      // Redirect ke halaman validasi usulan dengan pesan sukses
      return redirect('/pic/validasi-usulan')->with('success', 'Usulan berhasil diproses.');
    } catch (\Exception $e) {
      $alias = ErrorAlias::fromThrowable($e, 'PIC-VALIDASI-PROSES');
      Log::error('ValidasiUsulanPicController prosesUsulan failed', [
        'alias' => $alias['code'],
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);
      return redirect('/pic/validasi-usulan')->with('error', $alias['message']);
    }
  }
}
