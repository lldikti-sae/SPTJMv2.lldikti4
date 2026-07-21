<?php

namespace App\Http\Controllers;

use App\Helpers\ErrorAlias;
use Illuminate\Http\Request;
use App\Models\Dosen;
use App\Models\Pts;
use App\Models\User;
use App\Models\Jabatan;
use App\Models\Bank;
use App\Models\Grade;
use App\Models\HistoriDosen;
use App\Models\Transaksi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;



class DataDosenController extends Controller
{
  public function index(Request $request)
  {
    $tahun = session('tahun');
    // Jika request AJAX, kembalikan response untuk Yajra DataTables
    if ($request->ajax()) {
      // Query builder untuk Yajra DataTables (server-side processing)
      // Menggunakan select terarah agar hanya kolom yang dibutuhkan yang diambil
      $query = DB::table('s_transaksi_2')
        ->select([
          'NIDN as nidn',
          'NUPTK as nuptk',
          'Nama as nama',
          'Kode_PT as kode_pt',
          'PTS as pts',
          'Aktif as aktif',
          'Eligible_span as eligible_span',
        ])
        ->where('Tahun_Versi', $tahun)
        // Pastikan data aktif (Aktif='1' atau 'YA') tampil paling atas, lalu tidak aktif
        ->orderByRaw("(CASE WHEN `Aktif` IN ('1','YA','Ya','ya','Y') THEN 1 ELSE 0 END) DESC")
        ->orderBy('Nama');

      return DataTables::of($query)
        ->editColumn('nidn', function ($row) {
          return !empty($row->nidn) ? $row->nidn : '-';
        })
        ->editColumn('nuptk', function ($row) {
          return !empty($row->nuptk) ? $row->nuptk : '-';
        })
        ->editColumn('aktif', function ($row) {
          if ($row->aktif == 1) {
            return '<span class="badge bg-label-primary">Aktif</span>';
          }
          return '<span class="badge bg-label-danger">Tidak Aktif</span>';
        })
        ->addColumn('aksi', function ($row) {
          // Determine identifier: prefer NIDN, fallback to NUPTK
          $identifier = !empty($row->nidn) ? $row->nidn : (!empty($row->nuptk) ? $row->nuptk : null);
          if (!$identifier) {
            return '<div class="text-center">-</div>';
          }

          // URL targets: open perubahan-data-dosen view. Use query param to indicate which tab to open.
          $basePerubahanUrl = route('admin.perubahan-data-dosen.show', ['nidn' => $identifier]);
          $urlPengaktifan = $basePerubahanUrl; // default shows Pengaktifan
          $urlPerubahan = $basePerubahanUrl . '?open=perubahan';

          // Icon for active / inactive status
          $statusBtn = '';
          $isActive = false;
          if (isset($row->aktif)) {
            $val = $row->aktif;
            $isActive = ($val === 1 || $val === '1' || strcasecmp($val, 'YA') === 0 || strcasecmp($val, 'Y') === 0);
          }

          if ($isActive) {
            $statusBtn = '<a href="' . $urlPengaktifan . '" class="sptjm-icon-btn sptjm-btn-reset" title="Pengaktifan"><i class="bx bx-check"></i></a>';
          } else {
            $statusBtn = '<a href="' . $urlPengaktifan . '" class="sptjm-icon-btn sptjm-btn-delete" title="Tidak Aktif"><i class="bx bx-block"></i></a>';
          }

          // View button opens the regular detail view
          $urlView = route('data-dosen.show', ['nidn' => $identifier]);
          $viewBtn = '<a href="' . $urlView . '" class="sptjm-icon-btn sptjm-btn-view" title="Lihat"><span class="tf-icons bx bx-show"></span></a>';

          // Edit button opens the "Perubahan Data" tab
          $editBtn = '<a href="' . $urlPerubahan . '" class="sptjm-icon-btn sptjm-btn-edit" title="Edit"><i class="bx bx-edit-alt"></i></a>';

          return '<div class="d-flex justify-content-center gap-1">' . $viewBtn . $statusBtn . $editBtn . '</div>';
        })
        ->rawColumns(['aksi', 'aktif'])
        ->make(true);
    }

    // Non-AJAX: render view (initial page load)
    $emails = User::pluck('email');
    $jabatans = Jabatan::pluck('jabatan');
    $banks = Bank::pluck('nama_bank');
    $kodePT = Pts::pluck('kode_pts');
    return view('admin.data-dosen', compact('emails', 'jabatans', 'banks', 'kodePT'));
  }

  public function store(Request $request)
  {
    $request->validate([
      'nidn' => 'nullable|string',
      'nuptk' => 'nullable|string',
      'nik' => 'required|string',
      'nama' => 'required|string',
      'ttl' => 'required|string',
      'tanggal_lahir' => 'required|date',
      'usia' => 'required',
      'kode_pts' => 'required|string',
      'jenis' => 'required|string',
      'sertifikat_dosen' => 'required|string',
      'tahun_lulus' => 'required',
      'aktif' => 'required|in:0,1',
    ]);

    $nidn = trim((string) $request->input('nidn'));
    $nuptk = trim((string) $request->input('nuptk'));
    if ($nidn === '' && $nuptk === '') {
      throw \Illuminate\Validation\ValidationException::withMessages([
        'nidn' => 'Isi NIDN atau NUPTK (salah satu saja cukup).',
        'nuptk' => 'Isi NIDN atau NUPTK (salah satu saja cukup).',
      ]);
    }

    $identifier = $nidn !== '' ? $nidn : $nuptk;

    $pts = Pts::where('kode_pts', $request->kode_pts)->first();
    if (!$pts) {
      return redirect()->route('admin.data-dosen')->with('error', 'Kode PTS tidak ditemukan.');
    }

    try {
      // Alur Tambah Dosen adalah 2 step:
      // 1) Simpan draft dari modal Tambah (belum insert DB)
      // 2) Redirect ke halaman ubah MK/Gol untuk mengisi Informasi Perubahan, baru insert saat submit.
      $draft = [
        'nidn' => $nidn,
        'nik' => (string) $request->nik,
        'nama' => (string) $request->nama,
        'ttl' => (string) $request->ttl,
        'tanggal_lahir' => (string) $request->tanggal_lahir,
        'usia' => (string) $request->usia,
        'kode_pt' => (string) $request->kode_pts,
        'pts' => (string) ($pts->nama_pts ?? ''),
        'jenis' => (string) $request->jenis,
        'sertifikat_dosen' => (string) $request->sertifikat_dosen,
        'tahun_lulus' => (string) $request->tahun_lulus,
        'aktif' => (string) $request->aktif,
        'created_at' => now()->toDateTimeString(),
      ];
    
      // include nuptk if provided
      if ($nuptk !== '') {
        $draft['nuptk'] = $nuptk;
      }

      // Store draft keyed by the identifier used for redirect.
      session()->put('draft_add_dosen.' . $identifier, $draft);
      // Also ensure draft is accessible by both identifiers if provided
      if ($nuptk !== '') {
        session()->put('draft_add_dosen.' . $nuptk, $draft);
      }
      if ($nidn !== '') {
        session()->put('draft_add_dosen.' . $nidn, $draft);
      }

      // New flow: after completing the single-step modal, jump directly to perubahan-data-dosen
      // and prefill fields from the draft (mode=new).
      return redirect()->route('admin.perubahan-data-dosen.show', [
        'nidn' => $identifier,
        'mode' => 'new',
      ]);
    } catch (\Throwable $e) {
      $alias = ErrorAlias::fromThrowable($e, 'ADM-DOSEN');
      Log::error('DataDosenController: failed to create draft add dosen', [
        'alias' => $alias['code'],
        'nidn' => (string) ($request->nidn ?? ''),
        'nuptk' => (string) ($request->nuptk ?? ''),
        'kode_pts' => (string) ($request->kode_pts ?? ''),
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);

      return redirect()->route('admin.data-dosen')->with('error', 'Terjadi kesalahan saat memproses data. (Kode: ' . $alias['code'] . ')');
    }
  }

  public function getNamaPTS($kode)
  {
    $pts = Pts::where('kode_pts', $kode)->first();

    if ($pts) {
      return response()->json(['nama_pts' => $pts->nama_pts]);
    } else {
      return response()->json(['nama_pts' => null], 404);
    }
  }

  public function searchByKodePts(Request $request)
  {
    $request->validate([
      'kode_pts' => 'required|string',
    ]);

    $kodePts = trim((string) $request->kode_pts);

    $tahun = session('tahun');

    $query = DB::table('s_transaksi_2')
      ->selectRaw("`NIDN` AS nidn, `NUPTK` AS nuptk, `Nama` AS nama, `Kode_PT` AS kode_pt, `PTS` AS pts, `Aktif` AS aktif, `Eligible_span` AS eligible_span")
      ->where('Tahun_Versi', $tahun)
      // Guard against whitespace issues in stored Kode_PT values
      ->whereRaw('TRIM(`Kode_PT`) = ?', [$kodePts])
      ->orderBy('Nama', 'asc');

    $dosen = $query->get();

    return response()->json(['data' => $dosen]);
  }

  public function prosesSinkronisasi(Request $request)
  {
    $request->validate([
      'kode_pts_asal' => 'required|string',
      'kode_pts_tujuan' => 'required|string|different:kode_pts_asal',
      'nidn' => 'required|array',
      'nidn.*' => 'required|string',
    ]);

    $kodePtsAsal = trim((string) $request->kode_pts_asal);
    $kodePtsTujuan = trim((string) $request->kode_pts_tujuan);
    if ($kodePtsAsal === '' || $kodePtsTujuan === '') {
      return response()->json([
        'status' => 'error',
        'message' => 'Kode PTS asal dan tujuan wajib diisi.',
      ], 422);
    }
    if ($kodePtsAsal === $kodePtsTujuan) {
      return response()->json([
        'status' => 'error',
        'message' => 'Kode PTS asal dan tujuan tidak boleh sama.',
      ], 422);
    }

    $tahun = session('tahun');

    // Pastikan PTS tujuan ada
    $ptsTujuan = Pts::where('kode_pts', $kodePtsTujuan)->first();
    if (!$ptsTujuan) {
      return response()->json([
        'status' => 'error',
        'message' => 'Kode PTS tujuan tidak ditemukan.',
      ], 404);
    }

    try {
      $dosens = Transaksi::where(function($q) use ($request) {
          $q->whereIn('NIDN', $request->nidn)
            ->orWhereIn('NUPTK', $request->nidn);
        })
        ->where('Tahun_Versi', $tahun)
        // Guard against whitespace issues in stored Kode_PT values
        ->whereRaw('TRIM(`Kode_PT`) = ?', [$kodePtsAsal])
        ->get();

      $jumlahDipindah = 0;
      $jumlahGagal = 0;
      $wilayahBaru = strtolower($ptsTujuan->wilayah ?? '');

      foreach ($dosens as $dosen) {
          $identifier = $dosen->NIDN ?? $dosen->NUPTK;
          if (!$identifier) continue;

          if ($this->hasActiveUsulan($identifier, $tahun)) {
              // Tidak bisa dipindah, catat jumlah gagal
              $jumlahGagal++;
          } else {
              // Pindah langsung
              Transaksi::where(function ($q) use ($identifier) {
                $q->where('NIDN', $identifier)
                  ->orWhere('NUPTK', $identifier);
              })->update([
                  'Kode_PT' => $kodePtsTujuan,
                  'PTS' => $ptsTujuan->nama_pts,
                  'Pemegang_Wilayah' => $wilayahBaru
              ]);
              $jumlahDipindah++;
          }
      }

      $msg = [];
      if ($jumlahDipindah > 0) {
          $msg[] = "$jumlahDipindah data dosen berhasil dipindahkan ke PTS tujuan.";
      }
      if ($jumlahGagal > 0) {
          $msg[] = "$jumlahGagal data dosen tidak bisa dipindah karena proses pencairan aktif.";
      }
      
      $finalMessage = implode(' ', $msg);
      if (empty($finalMessage)) {
          $finalMessage = "Tidak ada data yang diproses.";
      }

      return response()->json([
        'status' => 'success',
        'message' => $finalMessage,
      ]);
    } catch (\Exception $e) {
      $alias = ErrorAlias::fromThrowable($e, 'ADM-SINKRON');
      Log::error('DataDosenController@prosesSinkronisasi failed', [
        'alias' => $alias['code'],
        'kode_pts_asal' => $kodePtsAsal,
        'kode_pts_tujuan' => $kodePtsTujuan,
        'nidn_count' => is_array($request->nidn ?? null) ? count($request->nidn) : null,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);

      return response()->json([
        'status' => 'error',
        'message' => 'Terjadi kesalahan saat memproses sinkronisasi. (Kode: ' . $alias['code'] . ')',
        'code' => $alias['code'],
      ], 500);
    }
  }

  public function editDataDosen(Request $request, $nidn)
  {
    // Tampilkan nilai sesuai bulan aktif di session.
    // Fallback ke 12 (Desember) bila session('bulan') kosong.
    $bulanSession = (int) session('bulan') ?: 12;
    $bulanSession = max(1, min(12, $bulanSession));

    $masa_kerja = "NULLIF(Tahun{$bulanSession}, '')";
    $golongan   = "NULLIF(Gol{$bulanSession}, '')";
    $jabatan    = "NULLIF(Jabatan{$bulanSession}, '')";
    $gaji       = "NULLIF(Gaji{$bulanSession}, 0)";
    // Support lookup by NIDN or NUPTK for edit flow
    $data_dosen = Transaksi::where(function($q) use ($nidn) {
        $q->where('NIDN', $nidn)
          ->orWhere('NUPTK', $nidn);
      })
      ->where('tahun_versi', session('tahun'))
      ->select(
        "*",
        DB::raw("$masa_kerja AS masa_kerja"),
        DB::raw("$golongan AS gol"),
        DB::raw("$jabatan AS jabatan"),
        DB::raw("$gaji AS gaji")
      )
      ->first();

    if (!$data_dosen) {
      return redirect()
        ->back()
        ->with('error', 'Data dosen tidak ditemukan!');
    }
    $banks = Bank::pluck('nama_bank');
    $jabatans = Jabatan::pluck('jabatan');
    // Ambil daftar user dengan role = 'pic' (gunakan email sebagai nilai)
    $pics = User::where('role', 'pic')->pluck('email');

    // Master list alasan/keterangan (digunakan untuk dropdown) dari h_perubahan
    $statusPerubahan = DB::table('h_perubahan')
      ->orderBy('kode')
      ->pluck('status_perubahan')
      ->filter()
      ->values();

    // mode: 'new' jika datang dari tombol Tambah (redirect setelah store),
    // default 'edit' jika datang dari tombol Ubah (Perubahan Lainnya)
    $mode = $request->query('mode', 'edit');

    return view('admin.ubah-data-dosen', compact('data_dosen', 'banks', 'jabatans', 'mode', 'pics', 'statusPerubahan'));
  }

  

  public function show($nidn)
  {
    // Gunakan bulan aktif dari session agar detail dosen menyesuaikan periode saat user mengambil data.
    // Fallback ke 12 (Desember) bila session('bulan') kosong.
    $bulanSession = (int) session('bulan') ?: 12;
    $bulanSession = max(1, min(12, $bulanSession));

    $masa_kerja = "NULLIF(Tahun{$bulanSession}, '')";
    $golongan   = "NULLIF(Gol{$bulanSession}, '')";
    $jabatan    = "NULLIF(Jabatan{$bulanSession}, '')";
    $gaji       = "NULLIF(Gaji{$bulanSession}, 0)";

    // Support lookup by NIDN or NUPTK: allow passing either identifier in the URL
    $data_dosen = Transaksi::where(function($q) use ($nidn) {
        $q->where('NIDN', $nidn)
          ->orWhere('NUPTK', $nidn);
      })
      ->where('Tahun_Versi', session('tahun'))
      ->select(
        "*",
        DB::raw("$masa_kerja AS masa_kerja"),
        DB::raw("$golongan AS gol"),
        DB::raw("$jabatan AS jabatan"),
        DB::raw("$gaji AS gaji")
      )
      ->first();

    if (!$data_dosen) {
      return redirect()->back()->with('error', 'Data dosen tidak ditemukan!');
    }

    return view('admin.view-data-dosen', ['dosen' => $data_dosen]);
  }


  public function ubahDataDosen(Request $request, $nidn)
  {

    // Validasi input
    $request->validate([
      'no_dokumen_ubah' => 'required|string',
      'tgl_dokumen_ubah' => 'required|date',
      'alasan_perubahan' => 'required|string',
      'dokumen' => 'nullable|file|mimes:pdf|max:10240',
      'tanggal_update_terakhir' => 'required|date',
      'keterangan' => 'required|string',
      'Aktif' => 'required|in:0,1',
      'kode_pt' => 'required|string',
      'pts' => 'required|string',
      'jenis' => 'required|string',
      'jabatan' => 'required|string',
      'gol' => 'required|string',
      'tahun' => 'required|numeric',
      'gaji' => 'required|numeric',
      'no_rekening' => 'required|string',
      'bank' => 'required|string',
      'nama_rekening' => 'required|string',
      'nama_penerima' => 'required|string',
      'npwp' => 'required|string',
      'pemegang_wilayah' => 'required|string',
      'eligible_span' => 'required|string',
    ]);


    try {
      // Tentukan bulan mulai (Terhitung Mulai Tanggal) mendukung format dd/mm/yyyy atau yyyy-mm-dd
      $tmt = null;
      if (!empty($request->tanggal_update_terakhir)) {
        try {
          $tmt = Carbon::createFromFormat('d/m/Y', $request->tanggal_update_terakhir);
        } catch (\Exception $e) {
          // fallback untuk format input type=date (Y-m-d) atau format lain yang bisa diparse
          $tmt = Carbon::parse($request->tanggal_update_terakhir);
        }
      } else {
        $tmt = Carbon::now();
      }
      $bulanAktif = (int) $tmt->format('n'); // 1..12
      $tahunTmt = (int) $tmt->format('Y'); // tahun dari Terhitung Mulai Tanggal

      // Tahun login (tahun_versi aktif di session)
      $year = (int) session('tahun'); //ambil tahun sekarang

      // mode dikirim dari form: 'new' jika datang dari tombol Tambah (buat data baru multi-tahun),
      // 'edit' (default) jika datang dari tombol Ubah (hanya update data yang sudah ada)
      $mode = $request->input('mode', 'edit');
      // Support identifier being either NIDN or NUPTK
      $identifierParam = $nidn;
      $data_dosen = Transaksi::where(function($q) use ($identifierParam) {
          $q->where('NIDN', $identifierParam)
            ->orWhere('NUPTK', $identifierParam);
        })
        ->where('Tahun_Versi', $year)
        ->first();

      if (!$data_dosen) {
        return redirect()
          ->back()
          ->with('error', 'Data dosen tidak ditemukan!');
      }

      $oldAktif = (string)($data_dosen->Aktif ?? '');
      $newAktif = (string)($request->Aktif ?? '');

      // Update field-field Data Dosen pada tahun versi login (card "Data Dosen")
      $dataUpdateDasar = [
        'Kode_PT' => $request->kode_pt,
        'PTS' => $request->pts,
        'Jenis' => $request->jenis,
        'No_Rekening' => $request->no_rekening,
        'Bank' => $request->bank,
        'Nama_Rekening' => $request->nama_rekening,
        'Nama_Penerima' => $request->nama_penerima,
        'NPWP' => $request->npwp,
        'Pemegang_Wilayah' => $request->pemegang_wilayah,
        'Eligible_span' => $request->eligible_span,
        'Aktif' => $request->Aktif,
        'Keterangan' => $request->keterangan,
        'Tanggal_Update_Terakhir' => now(),
      ];

      $identifier = $data_dosen->NIDN ?? $data_dosen->NUPTK;
      Transaksi::where(function($q) use ($identifier) {
          $q->where('NIDN', $identifier)
            ->orWhere('NUPTK', $identifier);
        })
        ->where('Tahun_Versi', $year)
        ->update($dataUpdateDasar);

      // Update bulanan menyesuaikan TMT:
      // - Status, Jabatan, Golongan, Masa Kerja (Tahun1-12), Biaya (Gaji1-12), dan KodeUsulan1-12
      // - Update dimulai dari bulan TMT sampai Desember untuk Tahun_Versi session
      $alasan = (string) $request->alasan_perubahan;
      $statusChanged = ($oldAktif !== '' && $newAktif !== '' && $oldAktif !== $newAktif);

      // hitung gaji lookup (dipakai khusus untuk tidak aktif -> aktif)
      $lookupGaji = function () use ($request) {
        $masaKerjaInput = $request->tahun;
        $masaKerjaInt = is_numeric($masaKerjaInput) ? (int) $masaKerjaInput : 0;
        if ($masaKerjaInt > 32) {
          $masaKerjaInt = 32;
        }
        $nominal = DB::table('c_grade')
          ->where('gol', $request->gol)
          ->where('masa_kerja', $masaKerjaInt)
          ->value('nominal');
        if (!is_null($nominal)) {
          return (int) $nominal;
        }
        // fallback: gunakan gaji yang dikirim dari form
        return is_numeric($request->gaji) ? (int) $request->gaji : 0;
      };

      // Tentukan nilai KodeUsulan yang akan di-set per bulan (mulai TMT)
      // kondisi khusus hanya berlaku jika status berubah
      $kodeUsulanToApply = $alasan;
      if ($statusChanged && $oldAktif === '0' && $newAktif === '1') {
        // Tidak Aktif -> Aktif: KodeUsulan NULL sesuai TMT
        $kodeUsulanToApply = null;
      } elseif (!$statusChanged) {
        // jika status tidak berubah, kondisi khusus tidak berlaku
        // tapi tetap ikuti perilaku umum: "Pengaktifan Kembali" => NULL
        if (strcasecmp(trim($alasan), 'Pengaktifan kembali') === 0 || strcasecmp(trim($alasan), 'Pengaktifan Kembali') === 0) {
          $kodeUsulanToApply = null;
        }
      }

      // Tentukan nilai gaji bulanan (mulai TMT)
      $gajiToApply = is_numeric($request->gaji) ? (int) $request->gaji : 0;
      if ($statusChanged && $oldAktif === '1' && $newAktif === '0') {
        // Aktif -> Tidak Aktif: gaji menjadi 0 sesuai TMT
        $gajiToApply = 0;
        $kodeUsulanToApply = $alasan;
      } elseif ($statusChanged && $oldAktif === '0' && $newAktif === '1') {
        // Tidak Aktif -> Aktif: gaji dihitung sesuai gol & masa kerja dan KodeUsulan NULL sesuai TMT
        $gajiToApply = $lookupGaji();
        $kodeUsulanToApply = null;
      }

      $updateBulanan = [];

      // Perubahan bulanan mengikuti session('bulan') untuk mode=edit.
      // Untuk mode=new (Tambah Dosen), tetap gunakan perilaku lama (mulai dari bulan TMT sampai Desember).
      $bulanSession = (int) session('bulan') ?: 12;
      $bulanSession = max(1, min(12, $bulanSession));

      if ($mode !== 'new') {
        $updateBulanan["Jabatan{$bulanSession}"] = $request->jabatan;
        $updateBulanan["Gol{$bulanSession}"] = $request->gol;
        $updateBulanan["Tahun{$bulanSession}"] = $request->tahun;
        $updateBulanan["Gaji{$bulanSession}"] = $gajiToApply;
        $updateBulanan["KodeUsulan{$bulanSession}"] = $kodeUsulanToApply;
      } else {
        // Jika tahun session lebih besar dari tahun TMT, maka update dimulai dari Januari.
        $startMonthSession = ($year === $tahunTmt) ? $bulanAktif : 1;
        
        // Tambahan: Untuk bulan-bulan sebelum TMT, set KodeUsulan menjadi 'Sebelum TMT' agar tidak masuk susulan
        for ($i = 1; $i < $startMonthSession; $i++) {
          $updateBulanan["Gaji{$i}"] = 0;
          $updateBulanan["KodeUsulan{$i}"] = 'Sebelum TMT';
        }

        for ($i = $startMonthSession; $i <= 12; $i++) {
          $updateBulanan["Jabatan{$i}"] = $request->jabatan;
          $updateBulanan["Gol{$i}"] = $request->gol;
          $updateBulanan["Tahun{$i}"] = $request->tahun;
          $updateBulanan["Gaji{$i}"] = $gajiToApply;
          $updateBulanan["KodeUsulan{$i}"] = $kodeUsulanToApply;
        }
      }
      if (!empty($updateBulanan)) {
        Transaksi::where(function($q) use ($identifier) {
            $q->where('NIDN', $identifier)
              ->orWhere('NUPTK', $identifier);
          })
          ->where('Tahun_Versi', $year)
          ->update($updateBulanan);
      }

      // Propagasi ke tahun berikutnya hanya untuk alur mode=new.
      if ($mode === 'new') {
        // Tambahan: update semua Tahun_Versi yang tersedia setelah tahun session.
        // Contoh: TMT 20/05/2025, tersedia record 2026 dan 2027 => update mulai Januari di tahun-tahun tsb.
        $futureYears = Transaksi::where(function($q) use ($identifier) {
            $q->where('NIDN', $identifier)
              ->orWhere('NUPTK', $identifier);
          })
          ->where('Tahun_Versi', '>', $year)
          ->orderBy('Tahun_Versi')
          ->pluck('Tahun_Versi')
          ->all();

        if (!empty($futureYears)) {
          foreach ($futureYears as $tahunVersiFuture) {
            $updateFuture = [];

            // Kolom dasar yang tetap diselaraskan agar status di tahun berikutnya konsisten
            $updateFuture['Aktif'] = $request->Aktif;
            $updateFuture['Keterangan'] = $request->keterangan;

            // Update kolom-kolom bulanan mulai Januari (1..12)
            for ($i = 1; $i <= 12; $i++) {
              $updateFuture["Jabatan{$i}"] = $request->jabatan;
              $updateFuture["Gol{$i}"] = $request->gol;
              $updateFuture["Tahun{$i}"] = $request->tahun;
              $updateFuture["Gaji{$i}"] = $gajiToApply;
              $updateFuture["KodeUsulan{$i}"] = $kodeUsulanToApply;
            }

            Transaksi::where(function($q) use ($identifier) {
                $q->where('NIDN', $identifier)
                  ->orWhere('NUPTK', $identifier);
              })
              ->where('Tahun_Versi', $tahunVersiFuture)
              ->update($updateFuture);
          }
        }
      }

      // Tambahan: jika mode = 'new' dan tahun TMT lebih kecil dari tahun login,
      // buat data Transaksi per tahun (tahun_versi) dari tahun TMT sampai tahun login.
      // Contoh: login 2025, TMT 30-04-2023, mode=new => dibuat record 2023, 2024, 2025 jika belum ada.
      if ($mode === 'new' && $tahunTmt <= $year) {
        for ($tahunIterasi = $tahunTmt; $tahunIterasi <= $year; $tahunIterasi++) {
          // Cek apakah data untuk tahun_versi ini sudah ada
          $transaksiTahun = Transaksi::where(function($q) use ($identifier) {
              $q->where('NIDN', $identifier)
                ->orWhere('NUPTK', $identifier);
            })
            ->where('Tahun_Versi', $tahunIterasi)
            ->first();

          if (!$transaksiTahun) {
            // Duplikasi data dari tahun login sebagai dasar
            $dataBaru = $data_dosen->replicate()->toArray();

            // Hapus primary key (misal kolom 'no') jika ada, agar create membuat baris baru
            unset($dataBaru['no']);

            // Set tahun versi sesuai iterasi
            $dataBaru['Tahun_Versi'] = $tahunIterasi;

            // Samakan field-field dasar dengan perubahan terbaru
            $dataBaru['Kode_PT'] = $request->kode_pt;
            $dataBaru['PTS'] = $request->pts;
            $dataBaru['Jenis'] = $request->jenis;
            $dataBaru['No_Rekening'] = $request->no_rekening;
            $dataBaru['Bank'] = $request->bank;
            $dataBaru['Nama_Rekening'] = $request->nama_rekening;
            $dataBaru['Nama_Penerima'] = $request->nama_penerima;
            $dataBaru['NPWP'] = $request->npwp;
            $dataBaru['Pemegang_Wilayah'] = $request->pemegang_wilayah;
            $dataBaru['Eligible_span'] = $request->eligible_span;

            // Atur KodeUsulan per bulan berdasarkan TMT dan tahun iterasi
            // - Untuk tahun sebelum tahun login, semua bulan diset dengan nilai KodeUsulan terbaru
            // - Untuk tahun TMT, bulan sebelum bulanAktif dikosongkan (NULL), mulai bulanAktif sampai Desember diisi nilai KodeUsulan terbaru
            // - Untuk tahun login (yang sudah di-update di atas), kita biarkan apa adanya
            if ($tahunIterasi < $year) {
              // Tahun di bawah tahun login
              if ($tahunIterasi === $tahunTmt) {
                // Tahun sama dengan tahun TMT: sebelum bulanAktif diset 'Sebelum TMT', setelahnya isi $kodeUsulanToApply
                for ($i = 1; $i <= 12; $i++) {
                  if ($i < $bulanAktif) {
                    $dataBaru["KodeUsulan{$i}"] = 'Sebelum TMT';
                    $dataBaru["Gaji{$i}"] = 0;
                  } else {
                    $dataBaru["KodeUsulan{$i}"] = $kodeUsulanToApply;
                  }
                }
              } else {
                // Tahun di antara TMT dan tahun login: semua bulan 1..12 isi $nilaiKode
                for ($i = 1; $i <= 12; $i++) {
                  $dataBaru["KodeUsulan{$i}"] = $kodeUsulanToApply;
                }
              }
            }

            Transaksi::create($dataBaru);
          }
        }
      }

      // Menyimpan data perubahan ke j_histori_dosen
      $dokumenPath = null;

      if ($request->hasFile('dokumen')) {
        $dokumen = $request->file('dokumen');
        $tanggalSekarang = date('Ymd');
        // Ambil nama asli tanpa ekstensi
        $originalName = pathinfo($dokumen->getClientOriginalName(), PATHINFO_FILENAME);
        // Slug nama dokumen untuk menghindari spasi dan karakter aneh
        $slugName = Str::slug($originalName, '_');
        // Gabungkan tanggal dan nama dokumen yang telah dislug
        $namaDokumenBaru = $tanggalSekarang . '_' . $slugName . '.' . $dokumen->getClientOriginalExtension();
        // Simpan file ke storage/app/public/Dokumen_Histori_Dosen2 dan layani via /storage/Dokumen_Histori_Dosen2/
        Storage::putFileAs('public/Dokumen_Histori_Dosen2', $dokumen, $namaDokumenBaru);
        // Simpan hanya nama file di database (tanpa prefix path)
        $dokumenPath = $namaDokumenBaru;
      }

      HistoriDosen::create([
        'nidn' => $data_dosen->NIDN ?? null,
        'nuptk' => $data_dosen->NUPTK ?? null,
        'nama' => $data_dosen->Nama,
        'pts' => $data_dosen->PTS,
        'kode_pt' => $data_dosen->Kode_PT,
        'pemegang_wilayah' => $data_dosen->Pemegang_Wilayah,
        'aktif' => $request->Aktif,
        'keterangan' => $request->keterangan,
        'pengguna' => auth()->user()->email,
        'tanggal_update_terakhir' => $request->tanggal_update_terakhir,
        'no_dokumen_ubah' => $request->no_dokumen_ubah,
        'tgl_dokumen_ubah' => $request->tgl_dokumen_ubah,
        'alasan_perubahan' => $request->alasan_perubahan,
        'dokumen' => $dokumenPath,
        'tanggal_update_terbaru' => now(),
      ]);

      return response()->json(['success' => true, 'message' => 'Data dosen tersimpan!', 'nidn' => $data_dosen->NIDN]);
    } catch (\Exception $e) {
      $alias = ErrorAlias::fromThrowable($e, 'ADM-DOSEN');
      Log::error('DataDosenController@ubahDataDosen failed', [
        'alias' => $alias['code'],
        'nidn' => (string) ($nidn ?? ''),
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);

      return response()->json([
        'success' => false,
        'message' => 'Terjadi kesalahan saat menyimpan data. (Kode: ' . $alias['code'] . ')',
        'code' => $alias['code'],
      ], 500);
      // return redirect()->route('admin.edit-data-dosen', ['nidn' => $nidn])->with('error', $e->getMessage());
    }
  }

  public function ubahMkGol(Request $request, $nidn)
  {
    // Validasi minimal agar format TMT & input numerik konsisten
    $request->validate([
      'no_dokumen_ubah' => 'required|string',
      'tgl_dokumen_ubah' => 'required|date',
      'alasan_perubahan' => 'required|string',
      'dokumen' => 'required|file|mimes:pdf,doc,docx',
      'tanggal_update_terakhir' => 'required|date',
      'keterangan' => 'required|string',
      'Aktif' => 'required|in:0,1',
      'jenis' => 'required|string',
      'jabatan' => 'required|string',
      'gol' => 'required|string',
      'tahun' => 'required|numeric',
      'gaji' => 'nullable',
      'eligible_span' => 'required|string',
    ]);

    // Terhitung Mulai Tanggal: ambil bulan (untuk start update) dan tahun (untuk Tahun_Versi)
    $tmt = Carbon::parse($request->tanggal_update_terakhir);
    $tmtBulan = (int) $tmt->format('n');
    $tahunVersi = (int) $tmt->format('Y');

    // Mengambil data dosen dari tabel s_transaksi_2 berdasarkan NIDN & Tahun_Versi (mengikuti tahun pada TMT)
    $data_dosen = DB::table('s_transaksi_2')
      ->where(function ($q) use ($nidn) {
        $q->where('NIDN', $nidn)
          ->orWhere('NUPTK', $nidn);
      })
      ->where('Tahun_Versi', $tahunVersi)
      ->first();

    if (!$data_dosen) {
      return redirect()
        ->back()
        ->with('error', 'Data dosen tidak ditemukan untuk Tahun Versi ' . $tahunVersi . '!');
    }

    $dataUpdate = [
      'Jenis' => $request->jenis,
      'Aktif' => $request->Aktif,
      'Eligible_span' => $request->eligible_span,
    ];

    $alasan = trim((string) $request->alasan_perubahan);

    // Jika alasan "Perubahan Golongan dan Masa Kerja":
    // - update masa kerja -> kolom Tahun1-12
    // - update golongan -> kolom Gol1-12
    // mulai dari bulan TMT sampai Desember.
    if (strcasecmp($alasan, 'Perubahan Golongan dan Masa Kerja') === 0) {
      for ($bulan = $tmtBulan; $bulan <= 12; $bulan++) {
        $dataUpdate['Gol' . $bulan] = $request->gol;
        $dataUpdate['Tahun' . $bulan] = $request->tahun;
        if (!is_null($request->gaji) && $request->gaji !== '') {
          $dataUpdate['Gaji' . $bulan] = $request->gaji;
        }
      }
    } else {
      // Default: pertahankan perilaku lama (update juga jabatan)
      for ($bulan = $tmtBulan; $bulan <= 12; $bulan++) {
        $dataUpdate['Jabatan' . $bulan] = $request->jabatan;
        $dataUpdate['Gol' . $bulan] = $request->gol;
        $dataUpdate['Tahun' . $bulan] = $request->tahun;
        $dataUpdate['Gaji' . $bulan] = $request->gaji;
      }
    }
    // dd($dataUpdate);
    // Update data dosen
    // $data_dosen->update($dataUpdate);
    $identifier = $data_dosen->NIDN ?? $data_dosen->NUPTK;
    DB::table('s_transaksi_2')
      ->where(function ($q) use ($identifier) {
        $q->where('NIDN', $identifier)
          ->orWhere('NUPTK', $identifier);
      })
      ->where('Tahun_Versi', $tahunVersi)
      ->update($dataUpdate);

    // Menyimpan data perubahan ke j_histori_dosen
    $dokumenPath = null;

    if ($request->hasFile('dokumen')) {
      $dokumen = $request->file('dokumen');
      $tanggalSekarang = date('Ymd');
      // Ambil nama asli tanpa ekstensi
      $originalName = pathinfo($dokumen->getClientOriginalName(), PATHINFO_FILENAME);
      // Slug nama dokumen untuk menghindari spasi dan karakter aneh
      $slugName = Str::slug($originalName, '_');
      // Gabungkan tanggal dan nama dokumen yang telah dislug
      $namaDokumenBaru = $tanggalSekarang . '_' . $slugName . '.' . $dokumen->getClientOriginalExtension();
      // Simpan file ke storage/app/public/Dokumen_Histori_Dosen2 dan layani via /storage/Dokumen_Histori_Dosen2/
      Storage::putFileAs('public/Dokumen_Histori_Dosen2', $dokumen, $namaDokumenBaru);
      // Simpan hanya nama file di database (tanpa prefix path)
      $dokumenPath = $namaDokumenBaru;
    }

    HistoriDosen::create([
      'nidn' => $data_dosen->NIDN ?? null,
      'nuptk' => $data_dosen->NUPTK ?? null,
      'nama' => $data_dosen->Nama,
      'pts' => $data_dosen->PTS,
      'kode_pt' => $data_dosen->Kode_PT,
      'pemegang_wilayah' => $data_dosen->Pemegang_Wilayah,
      'aktif' => $request->Aktif,
      'keterangan' => $request->keterangan,
      'pengguna' => auth()->user()->email,
      'tanggal_update_terakhir' => $request->tanggal_update_terakhir,
      'no_dokumen_ubah' => $request->no_dokumen_ubah,
      'tgl_dokumen_ubah' => $request->tgl_dokumen_ubah,
      'alasan_perubahan' => $request->alasan_perubahan,
      'dokumen' => $dokumenPath,
      'tanggal_update_terbaru' => now(),
    ]);

    // Redirect ke halaman yang benar dengan parameter nidn
    return redirect()
      ->route('admin.edit-mk-gol', ['nidn' => $nidn]) // Pastikan parameter nidn ada
      ->with('success', 'Data dosen berhasil diubah.');
  }
  public function updateData(Request $request, $nidn)
  {
    // Validasi input informasi perubahan dan data dasar dosen
    $request->validate([
      'no_dokumen_ubah' => 'required|string',
      'tgl_dokumen_ubah' => 'required|date',
      'alasan_perubahan' => 'required|string',
      'dokumen' => 'required|file|mimes:pdf,doc,docx',
      'tanggal_update_terakhir' => 'required|date',
      'keterangan' => 'required|string',
      'Aktif' => 'required|in:0,1',
      'nama' => 'required|string',
      'kode_pt' => 'required|string',
      'pts' => 'required|string',
      'no_rekening' => 'required|string',
      'bank' => 'required|string',
      'nama_rekening' => 'required|string',
      'nama_penerima' => 'required|string',
      'npwp' => 'required|string',
      'pemegang_wilayah' => 'required|string',
      'eligible_span' => 'required|string',
    ]);

    $year = session('tahun');

    // Mengambil data dosen berdasarkan NIDN atau NUPTK
    $data_dosen = Transaksi::where(function ($q) use ($nidn) {
        $q->where('NIDN', $nidn)
          ->orWhere('NUPTK', $nidn);
      })
      ->where('Tahun_Versi', $year)->first();

    if (!$data_dosen) {
      return redirect()
        ->back()
        ->with('error', 'Data dosen tidak ditemukan!');
    }

    // Mengupdate data dosen
    // $data_dosen->update();
    $dataUpdate = [
      'Nama' => $request->nama,
      'No_Rekening' => $request->no_rekening,
      'Bank' => $request->bank,
      'Nama_Rekening' => $request->nama_rekening,
      'Nama_Penerima' => $request->nama_penerima,
      'NPWP' => $request->npwp,
      'Tanggal_Update_Terakhir' => now(),
      'Keterangan' => $request->keterangan,
      'Eligible_span' => $request->eligible_span,
    ];
    // dd($request->all(), $dataUpdate);
    // dd($data_dosen->PTS);
    $identifierUpdate = $data_dosen->NIDN ?? $data_dosen->NUPTK;
    
    // Langsung ubah Kode_PT, PTS, dan Pemegang_Wilayah untuk SEMUA TAHUN
    if ($request->has('kode_pt') && !empty($request->kode_pt)) {
      $kodePt = $request->kode_pt;
      $oldKodePt = $data_dosen->Kode_PT ?? '';

      if ($kodePt !== $oldKodePt) {
          // Cek apakah ada usulan aktif di tahun ini
          if ($this->hasActiveUsulan($identifierUpdate, $year)) {
             return redirect()->back()->withInput()->with([
                 'requires_scheduling' => true,
                 'kode_pt_baru' => $kodePt,
                 'nama_pts_baru' => $request->pts,
                 'pemegang_wilayah_baru' => $request->pemegang_wilayah,
                 'warning' => 'Dosen ini tidak bisa dipindah PTS sekarang karena dalam proses usulan pencairan aktif.'
             ]);
          }

          $globalPtsUpdate = [
            'Kode_PT' => $kodePt,
            'PTS' => $request->pts,
            'Pemegang_Wilayah' => $request->pemegang_wilayah,
          ];
          Transaksi::where(function ($q) use ($identifierUpdate) {
              $q->where('NIDN', $identifierUpdate)
                ->orWhere('NUPTK', $identifierUpdate);
          })->update($globalPtsUpdate);
      }
    }

    Transaksi::where(function ($q) use ($identifierUpdate) {
        $q->where('NIDN', $identifierUpdate)
          ->orWhere('NUPTK', $identifierUpdate);
      })
      ->where('Tahun_Versi', $year)
      ->update($dataUpdate);

    // Menyimpan data perubahan ke j_histori_dosen
    $dokumenPath = null;

    if ($request->hasFile('dokumen')) {
      $dokumen = $request->file('dokumen');
      $tanggalSekarang = date('Ymd');
      // Ambil nama asli tanpa ekstensi
      $originalName = pathinfo($dokumen->getClientOriginalName(), PATHINFO_FILENAME);
      // Slug nama dokumen untuk menghindari spasi dan karakter aneh
      $slugName = Str::slug($originalName, '_');
      // Gabungkan tanggal dan nama dokumen yang telah dislug
      $namaDokumenBaru = $tanggalSekarang . '_' . $slugName . '.' . $dokumen->getClientOriginalExtension();
      // Simpan file ke storage/app/public/Dokumen_Histori_Dosen2 dan layani via /storage/Dokumen_Histori_Dosen2/
      Storage::putFileAs('public/Dokumen_Histori_Dosen2', $dokumen, $namaDokumenBaru);
      // Simpan hanya nama file di database (tanpa prefix path)
      $dokumenPath = $namaDokumenBaru;
    }

    HistoriDosen::create([
      'nidn' => $data_dosen->NIDN ?? null,
      'nuptk' => $data_dosen->NUPTK ?? null,
      'nama' => $data_dosen->Nama,
      'pts' => $data_dosen->PTS,
      'kode_pt' => $data_dosen->Kode_PT,
      'pemegang_wilayah' => $data_dosen->Pemegang_Wilayah,
      'aktif' => $request->Aktif,
      'keterangan' => $request->keterangan,
      'pengguna' => auth()->user()->email,
      'tanggal_update_terakhir' => $request->tanggal_update_terakhir,
      'no_dokumen_ubah' => $request->no_dokumen_ubah,
      'tgl_dokumen_ubah' => $request->tgl_dokumen_ubah,
      'alasan_perubahan' => $request->alasan_perubahan,
      'dokumen' => $dokumenPath,
      'tanggal_update_terbaru' => now(),
    ]);

    // Redirect ke halaman yang benar dengan parameter nidn
    return redirect()
      ->route('admin.update-data-dosen', ['nidn' => $nidn]) // Pastikan parameter nidn ada
      ->with('success', 'Data dosen berhasil diubah.');
  }

  public function getBiaya(Request $request)
  {
    $golongan = $request->input('golongan');
    $masa_kerja = $request->input('masa_kerja');

    // Jika masa kerja lebih dari 32, gunakan 32 sebagai batas maksimum
    if (is_numeric($masa_kerja)) {
      $masa_kerja_int = (int) $masa_kerja;
      if ($masa_kerja_int > 32) {
        $masa_kerja_int = 32;
      }
    } else {
      $masa_kerja_int = $masa_kerja;
    }

    $gaji = DB::table('c_grade')
      ->where('gol', $golongan)
      ->where('masa_kerja', $masa_kerja_int)
      ->value('nominal');

    return response()->json(['gaji' => $gaji ?? 'Data tidak ditemukan']);
  }

  //view data dosen tidak aktif
  public function viewDataDosenTidakAktif()
  {
    // dataDosenTidakAktif sudah tidak digunakan di view (menggunakan datatables)
    $dataDosenTidakAktif = [];
    // ambil daftar kategori keterangan untuk dropdown filter dari tabel h_perubahan
    // menggunakan field status_perubahan agar opsi selalu mengikuti master data perubahan
    $keteranganOptions = DB::table('h_perubahan')
      ->orderBy('kode')
      ->pluck('status_perubahan');
    // $query = Transaksi::select('NIDN', 'Nama', 'Kode_PT', 'PTS', 'Jenis', 'Keterangan')->get();
    // dd($query);
    return view('admin.hapus-data-dosen-tidak-aktif', compact('dataDosenTidakAktif', 'keteranganOptions'));
  }

  // server-side datatable untuk view dosen tidak aktif (menggunakan Yajra DataTables)

  public function datatableTidakAktif(Request $request)
  {

    $year = session('tahun');
    if ($request->ajax()) {
      // siapkan daftar kolom kode cair (Jan-Des) saja
      // catatan: kondisi kodeusulan dihapus, sehingga hanya kode cair yang
      // menjadi penentu apakah data boleh dihapus atau tidak
      $kodeCair = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];

      // Treat empty string as NULL so kolom yang berisi '' dianggap tidak punya kode cair
      $kodeCairExpr = array_map(function ($col) {
        return "NULLIF($col, '')";
      }, $kodeCair);
      $kodeCairStr = implode(',', $kodeCairExpr);

      // can_delete = 1 hanya jika SEMUA kode cair bernilai NULL / kosong
      $cekKodeUsulan = "CASE
                            WHEN COALESCE($kodeCairStr) IS NULL
                            THEN 1
                            ELSE 0
                        END AS can_delete";
      $aktifValues = ['1', 'YA', 'Ya', 'ya', 'Y'];
      $query = Transaksi::where(function($q) use ($aktifValues) {
            $q->where('Aktif', '0')
              ->orWhereNotIn('Eligible_span', $aktifValues)
              ->orWhereNull('Eligible_span');
        })
        ->where('Tahun_Versi', $year)
        ->select(DB::raw('NIDN AS nidn'), DB::raw('NUPTK AS nuptk'), 'Nama', 'Kode_PT', 'PTS', 'Jenis', 'Keterangan', 'Eligible_span', DB::raw($cekKodeUsulan));
      // apply keterangan filter if provided (client sends 'all' for no-filter)
      $keterangan = $request->input('keterangan', 'all');
      if (!empty($keterangan) && $keterangan !== 'all') {
        $query->where('Keterangan', 'LIKE', '%' . $keterangan . '%');
      }

      return DataTables::of($query)
        ->editColumn('nidn', function ($row) {
          return !empty($row->nidn) ? Str::mask($row->nidn, '*', 0, 0) : '-';
        })
        ->editColumn('nuptk', function ($row) {
          return $row->nuptk ?? '-';
        })
        ->editColumn('status', function ($row) {
          $val = strtoupper($row->Eligible_span ?? '');
          if (in_array($val, ['1', 'YA', 'Y'])) {
            return '<span class="badge bg-label-primary">Aktif</span>';
          }
          return '<span class="badge bg-label-danger">Tidak Aktif</span>';
        })
        ->editColumn('keterangan', function ($row) {
          return $row->Keterangan ?? '';
        })
        ->addColumn('aksi', function ($row) {
          // Hanya boleh dihapus jika tidak punya kode usulan & kode cair (can_delete == 1)
          if (!isset($row->can_delete) || (int)$row->can_delete !== 1) {
            return '';
          }

          // Ambil identifier (NIDN atau NUPTK)
          $identifier = $row->NIDN ?? $row->nidn ?? null;
          if (empty($identifier)) {
            $identifier = $row->NUPTK ?? $row->nuptk ?? null;
          }
          if (empty($identifier)) {
            return '';
          }

          $url = route('admin.data-dosen.tidak-aktif.hapus', ['id' => $identifier]);
          return '
            <form action="' . $url . '" method="POST" onsubmit="return confirm(\'Apakah Anda yakin ingin menghapus data ini?\')">
                ' . csrf_field() . '
                ' . method_field('DELETE') . '
                 <button type="submit" class="sptjm-icon-btn sptjm-btn-delete" title="Hapus">
                    <span class="tf-icons bx bx-trash"></span>
                </button>
            </form>';
        })
        ->rawColumns(['status', 'aksi'])
        ->make(true);
    }

  }
  //hapus data dosen yang tidak aktif
  public function hapusDataDosenTidakAktif($id)
  {
    $year = session('tahun');
    $aktifValues = ['1', 'YA', 'Ya', 'ya', 'Y'];
    $dosen = Transaksi::where(function ($q) use ($id) {
      $q->where('NIDN', $id)->orWhere('NUPTK', $id);
    })
      ->where(function($q) use ($aktifValues) {
          $q->where('Aktif', '0')
            ->orWhereNotIn('Eligible_span', $aktifValues)
            ->orWhereNull('Eligible_span');
      })
      ->where('Tahun_Versi', $year)
      ->first();

    if (!$dosen) {
      return redirect()->back()->with('error', 'Data dosen tidak ada!');
    }

    // Cek kode cair: jika salah satu bulan memiliki nilai, data tidak boleh dihapus
    $kodeCairCols = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
    foreach ($kodeCairCols as $col) {
      if (!empty($dosen->$col)) {
        return redirect()->back()->with('error', 'Data dosen tidak dapat dihapus karena memiliki kode cair.');
      }
    }

    $aktifValues = ['1', 'YA', 'Ya', 'ya', 'Y'];
    Transaksi::where(function ($q) use ($id) {
      $q->where('NIDN', $id)->orWhere('NUPTK', $id);
    })
      ->where(function($q) use ($aktifValues) {
          $q->where('Aktif', '0')
            ->orWhereNotIn('Eligible_span', $aktifValues)
            ->orWhereNull('Eligible_span');
      })
      ->where('Tahun_Versi', $year)
      ->delete();

    return redirect()->back()->with('success', 'Data dosen tidak aktif berhasil dihapus.');
  }
  private function hasActiveUsulan($identifier, $year) {
      $transaksi = Transaksi::where(function ($q) use ($identifier) {
              $q->where('NIDN', $identifier)
                ->orWhere('NUPTK', $identifier);
          })
          ->where('Tahun_Versi', $year)
          ->first();

      if (!$transaksi) return false;

      for ($i = 1; $i <= 12; $i++) {
          $kodeUsulan = $transaksi->{'KodeUsulan' . $i} ?? null;
          $noSp2d = $transaksi->{'No_sp2d_' . $i} ?? null;

          // Jika ada kode usulan, tapi No SP2D kosong (belum selesai)
          if (!empty($kodeUsulan) && empty($noSp2d)) {
              return true;
          }
      }
      return false;
  }
}
