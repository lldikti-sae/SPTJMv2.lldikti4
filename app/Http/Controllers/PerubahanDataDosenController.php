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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;



class PerubahanDataDosenController extends Controller
{
  private function getDraftAddDosenByIdentifier(string $identifier): ?array
  {
    $identifier = trim((string) $identifier);
    if ($identifier === '') return null;

    $direct = session()->get('draft_add_dosen.' . $identifier);
    if (is_array($direct)) return $direct;

    // Fallback: if URL param is NUPTK, search drafts by nuptk.
    $all = session()->get('draft_add_dosen');
    if (!is_array($all) || empty($all)) return null;
    foreach ($all as $draft) {
      if (!is_array($draft)) continue;
      $nuptk = isset($draft['nuptk']) ? trim((string) $draft['nuptk']) : '';
      $nidn = isset($draft['nidn']) ? trim((string) $draft['nidn']) : '';
      // match either key
      if ($nuptk !== '' && $nuptk === $identifier) {
        return $draft;
      }
      if ($nidn !== '' && $nidn === $identifier) {
        return $draft;
      }
    }
    return null;
  }

  private function formatDmyOrEmpty($v): string
  {
    $s = trim((string) $v);
    if ($s === '') return '';
    try {
      if (strpos($s, '/') !== false) {
        return Carbon::createFromFormat('d/m/Y', $s)->format('d/m/Y');
      }
      return Carbon::parse($s)->format('d/m/Y');
    } catch (\Throwable $e) {
      $alias = ErrorAlias::fromThrowable($e, 'PERUBAHAN-DOSEN');
      Log::error(__METHOD__ . ' failed', [
        'alias' => $alias['code'],
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);
      return $s;
    }
  }

  private function ensureTransaksiExistsForNewMode(string $identifierParam, int $year, Request $request): ?Transaksi
  {
    $identifierParam = trim((string) $identifierParam);
    if ($identifierParam === '') return null;

    $existing = Transaksi::where(function ($q) use ($identifierParam) {
        $q->where('NIDN', $identifierParam)
          ->orWhere('NUPTK', $identifierParam);
      })
      ->where('Tahun_Versi', $year)
      ->first();
    if ($existing) return $existing;

    $draft = $this->getDraftAddDosenByIdentifier($identifierParam);
    if (!is_array($draft)) {
      $draft = [];
    }

    $nidn = trim((string) ($draft['nidn'] ?? ''));
    $nuptk = isset($draft['nuptk']) ? trim((string) $draft['nuptk']) : '';
    if ($nidn === '' && $nuptk === '') {
      // Fallback only when no draft identifiers are available.
      $nidn = $identifierParam;
    }
    if ($nuptk === '' && $identifierParam !== '' && $identifierParam !== $nidn) {
      // If URL param is NUPTK, allow it to fill NUPTK.
      $nuptk = $identifierParam;
    }

    $kodePt = $this->norm($request->input('kode_pt', $draft['kode_pt'] ?? ''));
    $ptsName = '';
    if ($kodePt !== '') {
      $ptsName = (string) (DB::table('a_pts')->where('kode_pts', $kodePt)->value('nama_pts') ?? '');
    }
    if ($ptsName === '') {
      $ptsName = (string) ($request->input('pts', $draft['pts'] ?? ''));
    }

    $base = [
      'Tahun_Versi' => $year,
      'NIDN' => $nidn !== '' ? $nidn : null,
      'NUPTK' => $nuptk !== '' ? $nuptk : null,
      'NIK' => (string) $request->input('nik', $draft['nik'] ?? ''),
      'Nama' => (string) $request->input('nama', $draft['nama'] ?? ''),
      'Kode_PT' => $kodePt,
      'PTS' => $ptsName,
      'Sertifikat_Dosen' => (string) $request->input('sertifikat_dosen', $draft['sertifikat_dosen'] ?? ''),
      'Tahun_Lulus' => (string) $request->input('tahun_lulus', $draft['tahun_lulus'] ?? ''),
      'Pemegang_Wilayah' => (string) $request->input('pemegang_wilayah', $draft['pemegang_wilayah'] ?? ''),
      'Jenis' => (string) $request->input('jenis', $draft['jenis'] ?? ''),
      'Usia' => (string) ($draft['usia'] ?? ''),
      'TTL' => (string) ($draft['ttl'] ?? ''),
      'Tanggal_Lahir' => $this->formatDmyOrEmpty($draft['tanggal_lahir'] ?? ''),
      'No_Rekening' => (string) $request->input('no_rekening', $draft['no_rekening'] ?? ''),
      'Bank' => (string) $request->input('bank', $draft['bank'] ?? ''),
      'Nama_Rekening' => (string) $request->input('nama_rekening', $draft['nama_rekening'] ?? ''),
      'Nama_Penerima' => (string) $request->input('nama_penerima', $draft['nama_penerima'] ?? ''),
      'NPWP' => (string) $request->input('npwp', $draft['npwp'] ?? ''),
      'Eligible_span' => (string) $request->input('eligible_span', $draft['eligible_span'] ?? ''),
      'Aktif' => (string) $request->input('Aktif', $draft['aktif'] ?? '0'),
      'Inpassing' => (string) $request->input('inpassing', $draft['inpassing'] ?? ''),
      'TMT_JAD_Pertama' => (string) $request->input('tmt_jad_pertama', $draft['tmt_jad_pertama'] ?? ''),
      'TMT_JAD_Akhir' => (string) $request->input('tmt_jad_akhir', $draft['tmt_jad_akhir'] ?? ''),
      'TMT_Inpassing_Akhir' => (string) $request->input('tmt_inpassing_akhir', $draft['tmt_inpassing_akhir'] ?? ''),
    ];

    for ($i = 1; $i <= 12; $i++) {
        $base["KodeUsulan$i"] = 'Draf Baru';
        $base["Gaji$i"] = 0;
    }

    try {
      Transaksi::create($base);
    } catch (\Throwable $e) {
      $alias = ErrorAlias::fromThrowable($e, 'PERUBAHAN-DOSEN');
      Log::error(__METHOD__ . ' failed', [
        'alias' => $alias['code'],
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);
      return null;
    }

    return Transaksi::where(function ($q) use ($identifierParam, $nidn, $nuptk) {
        // Search by param, then by resolved identifiers
        $q->where('NIDN', $identifierParam)
          ->orWhere('NUPTK', $identifierParam)
          ->orWhere('NIDN', $nidn);
        if ($nuptk !== '') {
          $q->orWhere('NUPTK', $nuptk);
        }
      })
      ->where('Tahun_Versi', $year)
      ->first();
  }

  private function norm($v): string
  {
    return trim((string) $v);
  }

  private function normDate($v): string
  {
    $s = trim((string) $v);
    if ($s === '') return '';
    try {
      if (strpos($s, '/') !== false) {
        return Carbon::createFromFormat('d/m/Y', $s)->format('Y-m-d');
      }
      return Carbon::parse($s)->format('Y-m-d');
    } catch (\Throwable $e) {
      $alias = ErrorAlias::fromThrowable($e, 'PERUBAHAN-DOSEN');
      Log::error(__METHOD__ . ' failed', [
        'alias' => $alias['code'],
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);
      return $s;
    }
  }

  /**
   * Normalize a list of request date fields into Y-m-d or null.
   * Modifies the Request by merging converted values.
   */
  private function normalizeRequestDates(Request $request, array $keys): void
  {
    foreach ($keys as $k) {
      if (!$request->has($k)) continue;
      $raw = (string) $request->input($k);
      $raw = trim($raw);
      if ($raw === '') {
        $request->merge([$k => null]);
        continue;
      }
      $converted = $this->normDate($raw);
      // if normDate returns same as input but input was not Y-m-d, try parse fallback
      if ($converted === $raw) {
        try {
          $converted = Carbon::parse($raw)->format('Y-m-d');
        } catch (\Throwable $e) {
          $alias = ErrorAlias::fromThrowable($e, 'PERUBAHAN-DOSEN');
          Log::error(__METHOD__ . ' date parse fallback failed', [
            'alias' => $alias['code'],
            'raw' => $raw,
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
          ]);
        }
      }
      $request->merge([$k => $converted === '' ? null : $converted]);
    }
  }

  private function isBerdasarkanInpassing($v): bool
  {
    $s = strtolower(trim((string) $v));
    if ($s === '') return false;
    return $s === 'berdasarkan inpassing' || $s === 'inpassing' || str_contains($s, 'berdasarkan');
  }

  private function isGuruBesarAtauProfesor($jabatan): bool
  {
    $text = strtolower(trim((string) $jabatan));
    if ($text === '') {
      return false;
    }
    return strpos($text, 'guru besar') !== false || strpos($text, 'profesor') !== false;
  }

  private function lookupGajiFromCGrade($gol, $masaKerja, $jabatan = null): ?int
  {
    $golVal = strtoupper(trim((string) $gol));
    if ($golVal === '') {
      return null;
    }

    $masaKerjaInt = is_numeric($masaKerja) ? (int) $masaKerja : 0;
    if ($masaKerjaInt > 32) {
      $masaKerjaInt = 32;
    }
    if ($masaKerjaInt < 0) {
      $masaKerjaInt = 0;
    }

    // 1) Coba exact match dulu
    $nominal = DB::table('c_grade')
      ->where('gol', $golVal)
      ->where('masa_kerja', $masaKerjaInt)
      ->value('nominal');

    // 2) Jika tidak ditemukan, fallback ke masa_kerja tertinggi yang tersedia (prioritas: <= requested, lalu max global per gol)
    if ($nominal === null) {
      $mkFallback = DB::table('c_grade')
        ->where('gol', $golVal)
        ->whereNotNull('masa_kerja')
        ->where('masa_kerja', '<=', $masaKerjaInt)
        ->max('masa_kerja');

      if ($mkFallback === null) {
        $mkFallback = DB::table('c_grade')
          ->where('gol', $golVal)
          ->whereNotNull('masa_kerja')
          ->max('masa_kerja');
      }

      if ($mkFallback !== null) {
        $nominal = DB::table('c_grade')
          ->where('gol', $golVal)
          ->where('masa_kerja', (int) $mkFallback)
          ->value('nominal');
      }
    }

    if ($nominal === null) {
      return null;
    }

    $value = (float) $nominal;
    // Samakan aturan dengan referensi pada halaman admin/sinkronisasi tab gaji
    if ($this->isGuruBesarAtauProfesor($jabatan)) {
      $value *= 3;
    }

    return (int) round($value);
  }

  private function parseYearMonth($v): ?array
  {
    $s = trim((string) $v);
    if ($s === '') return null;
    try {
      $dt = null;
      if (strpos($s, '/') !== false) {
        $dt = Carbon::createFromFormat('d/m/Y', $s);
      } else {
        $dt = Carbon::parse($s);
      }
      $y = (int) $dt->format('Y');
      $m = (int) $dt->format('n');
      if ($y <= 0 || $m < 1 || $m > 12) return null;
      return [$y, $m];
    } catch (\Throwable $e) {
      $alias = ErrorAlias::fromThrowable($e, 'PERUBAHAN-DOSEN');
      Log::error(__METHOD__ . ' parseYearMonth failed', [
        'alias' => $alias['code'],
        'value' => $v,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);
      return null;
    }
  }

  private function applyMonthlyRangeUpdate(string $identifier, int $startYear, int $startMonth, string $prefix, $value): void
  {
    $startMonth = max(1, min(12, (int) $startMonth));

    // Kode cair columns (Jan-Des) live alongside KodeUsulan1-12 in s_transaksi_2.
    // Rule: if a month already has kode cair, do NOT modify KodeUsulan{month} (including re-activation nulling).
    $kodeCairCols = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
    $maxYear = (int) Transaksi::where(function ($q) use ($identifier) {
        $q->where('NIDN', $identifier)->orWhere('NUPTK', $identifier);
      })->max('Tahun_Versi');

    if ($maxYear <= 0) {
      $maxYear = (int) session('tahun');
    }
    if ($maxYear < $startYear) {
      $maxYear = $startYear;
    }

    for ($year = $startYear; $year <= $maxYear; $year++) {
      $record = Transaksi::where(function ($q) use ($identifier) {
          $q->where('NIDN', $identifier)->orWhere('NUPTK', $identifier);
        })
        ->where('Tahun_Versi', $year)
        ->first();
      if (!$record) continue;

      $months = ($year === $startYear) ? range($startMonth, 12) : range(1, 12);
      $update = [];
      foreach ($months as $m) {
        if ($prefix === 'KodeUsulan') {
          $kodeCairCol = $kodeCairCols[$m - 1] ?? null;
          if ($kodeCairCol && $this->norm($record->{$kodeCairCol} ?? '') !== '') {
            continue;
          }
        }
        $col = $prefix . $m;
        if ($this->norm($record->{$col} ?? '') !== $this->norm($value)) {
          $update[$col] = $value;
        }
      }
      if (!empty($update)) {
        Transaksi::where(function ($q) use ($identifier) {
            $q->where('NIDN', $identifier)->orWhere('NUPTK', $identifier);
          })
          ->where('Tahun_Versi', $year)
          ->update($update);
      }
    }
  }

  private function buildChangedUpdate(Transaksi $current, Request $request, array $map, array $dateKeys = []): array
  {
    $out = [];
    foreach ($map as $column => $requestKey) {
      if (!$request->has($requestKey)) {
        continue;
      }

      $incoming = $request->input($requestKey);
      $cur = $current->{$column} ?? null;

      $incomingNorm = in_array($requestKey, $dateKeys, true) ? $this->normDate($incoming) : $this->norm($incoming);
      $curNorm = in_array($requestKey, $dateKeys, true) ? $this->normDate($cur) : $this->norm($cur);

      if ($incomingNorm === $curNorm) {
        continue;
      }

      $out[$column] = $incoming;
    }
    return $out;
  }
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

          // If NIDN present: link to detail. Otherwise if NUPTK present: link to the same detail route
          // using the NUPTK value (show() will resolve by NIDN or NUPTK).
          if (!empty($row->nidn)) {
            $url = route('data-dosen.show', ['nidn' => $row->nidn]);
            return '<a href="' . $url . '" class="btn btn-icon btn-sm btn-primary"><span class="tf-icons bx bx-show"></span></a>';
          }

          if (!empty($row->nuptk)) {
            $url = route('data-dosen.show', ['nidn' => $row->nuptk]);
            return '<a href="' . $url . '" class="btn btn-icon btn-sm btn-primary" title="Lihat berdasarkan NUPTK"><span class="tf-icons bx bx-show"></span></a>';
          }

          // Jika tidak ada keduanya, tampilkan tanda strip di tengah.
          return '<div class="text-center">-</div>';
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
      'nidn' => 'required|string',
      'nuptk' => 'nullable|string',
      'nik' => 'required|string',
      'nama' => 'required|string',
      'ttl' => 'required|string',
      'tanggal_lahir' => 'required|date',
      'usia' => 'required',
      'kode_pts' => 'required|string',
      'pemegang_wilayah' => 'required|string',
      'jenis' => 'required|string',
      'sertifikat_dosen' => 'required|string',
      'tahun_lulus' => 'required',
      // Field berikut saat ini tidak ditampilkan di modal Tambah Dosen.
      // Biarkan nullable agar proses tambah dosen tetap berjalan.
      'sk_inpassing' => 'nullable|string',
      'pangkat' => 'nullable|string',
      'tmt_pangkat_golongan' => 'required|date',
      'tmt_jabatan_fungsional' => 'required|date',
      'tmt_inpassing_akhir' => 'required|date',
      'inpassing' => 'required|in:Berdasarkan Inpassing,Sesuai TMT Awal dan Akhir',
      'no_rekening' => 'required|string',
      'bank' => 'required|string',
      'nama_rekening' => 'required|string',
      'nama_penerima' => 'required|string',
      'npwp' => 'required|string',
      'eligible' => 'required|string',
      'aktif' => 'required|in:0,1',
    ]);

    $pts = Pts::where('kode_pts', $request->kode_pts)->first();
    if (!$pts) {
      return redirect()->route('admin.data-dosen')->with('error', 'Kode PTS tidak ditemukan.');
    }

    try {
      // Alur Tambah Dosen adalah 2 step:
      // 1) Simpan draft dari modal Tambah (belum insert DB)
      // 2) Redirect ke halaman ubah MK/Gol untuk mengisi Informasi Perubahan, baru insert saat submit.
      $draft = [
        'nidn' => (string) $request->nidn,
        'nik' => (string) $request->nik,
        'nama' => (string) $request->nama,
        'ttl' => (string) $request->ttl,
        'tanggal_lahir' => (string) $request->tanggal_lahir,
        'usia' => (string) $request->usia,
        'kode_pt' => (string) $request->kode_pts,
        'pts' => (string) ($pts->nama_pts ?? ''),
        'pemegang_wilayah' => (string) $request->pemegang_wilayah,
        'jenis' => (string) $request->jenis,
        'sertifikat_dosen' => (string) $request->sertifikat_dosen,
        'tahun_lulus' => (string) $request->tahun_lulus,
        'sk_inpassing' => (string) ($request->sk_inpassing ?? ''),
        'pangkat' => (string) ($request->pangkat ?? ''),

        // Mapping field modal Tambah Dosen => field yang dipakai di halaman ubah MK/Gol
        // agar otomatis terisi saat redirect mode=new
        'tmt_jad_pertama' => (string) $request->tmt_pangkat_golongan,
        'tmt_jad_akhir' => (string) $request->tmt_jabatan_fungsional,
        'tmt_inpassing_akhir' => (string) $request->tmt_inpassing_akhir,
        'inpassing' => (string) $request->inpassing,

        // Tetap simpan field asli jika sewaktu-waktu dibutuhkan
        'tmt_pangkat_golongan' => (string) $request->tmt_pangkat_golongan,
        'tmt_jabatan_fungsional' => (string) $request->tmt_jabatan_fungsional,
        'no_rekening' => (string) $request->no_rekening,
        'bank' => (string) $request->bank,
        'nama_rekening' => (string) $request->nama_rekening,
        'nama_penerima' => (string) $request->nama_penerima,
        'npwp' => (string) $request->npwp,
        'eligible_span' => (string) $request->eligible,
        'aktif' => (string) $request->aktif,
        'created_at' => now()->toDateTimeString(),
      ];
    
        // include nuptk if provided
        if ($request->filled('nuptk')) {
          $draft['nuptk'] = (string) $request->nuptk;
        }
      session()->put('draft_add_dosen.' . $draft['nidn'], $draft);

      return redirect()->route('admin.edit-mk-gol', ['nidn' => $draft['nidn'], 'mode' => 'new']);
    } catch (\Throwable $e) {
      $role = Auth::guard('web')->check() ? (Auth::user()->role ?? null) : null;
      $scope = ($role === 'pic') ? 'PIC-ADD-DOSEN' : (($role === 'admin') ? 'ADM-ADD-DOSEN' : 'ADD-DOSEN');
      $alias = ErrorAlias::fromThrowable($e, $scope);
      Log::error('PerubahanDataDosenController: failed to create draft add dosen', [
        'alias' => $alias['code'],
        'role' => $role,
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
      $jumlah = Transaksi::where(function($q) use ($request) {
          $q->whereIn('NIDN', $request->nidn)
            ->orWhereIn('NUPTK', $request->nidn);
        })
        ->where('Tahun_Versi', $tahun)
        // Guard against whitespace issues in stored Kode_PT values
        ->whereRaw('TRIM(`Kode_PT`) = ?', [$kodePtsAsal])
        ->update([
          'Kode_PT' => $kodePtsTujuan,
          'PTS' => $ptsTujuan->nama_pts,
        ]);

      return response()->json([
        'status' => 'success',
        'message' => $jumlah . ' data dosen berhasil dipindahkan ke PTS tujuan.',
      ]);
    } catch (\Exception $e) {
      $role = Auth::guard('web')->check() ? (Auth::user()->role ?? null) : null;
      $scope = ($role === 'pic') ? 'PIC-SINKRON' : (($role === 'admin') ? 'ADM-SINKRON' : 'SINKRON');
      $alias = ErrorAlias::fromThrowable($e, $scope);
      Log::error('PerubahanDataDosenController@prosesSinkronisasi failed', [
        'alias' => $alias['code'],
        'role' => $role,
        'kode_pts_asal' => $kodePtsAsal,
        'kode_pts_tujuan' => $kodePtsTujuan,
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

    $jabatanList = DB::table('e_jabatan')
      ->select('jabatan')
      ->whereNotNull('jabatan')
      ->where('jabatan', '!=', '')
      ->orderBy('jabatan')
      ->pluck('jabatan');

    $jabatanList = DB::table('e_jabatan')
      ->select('jabatan')
      ->whereNotNull('jabatan')
      ->where('jabatan', '!=', '')
      ->orderBy('jabatan')
      ->pluck('jabatan');

    $jabatanList = DB::table('e_jabatan')
      ->select('jabatan')
      ->whereNotNull('jabatan')
      ->where('jabatan', '!=', '')
      ->orderBy('jabatan')
      ->pluck('jabatan');

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

    // Dropdown sources (as requested)
    $pics = User::where('role', 'pic')->orderBy('email')->pluck('email');

    $bankList = DB::table('b_bank')
      ->select('nama_bank')
      ->whereNotNull('nama_bank')
      ->where('nama_bank', '!=', '')
      ->distinct()
      ->orderBy('nama_bank')
      ->pluck('nama_bank');

    $golonganList = DB::table('c_grade')
      ->select('gol')
      ->whereNotNull('gol')
      ->where('gol', '!=', '')
      ->distinct()
      ->orderBy('gol')
      ->pluck('gol');

    $jenisList = DB::table('g_pegawai')
      ->select('jenis')
      ->whereNotNull('jenis')
      ->where('jenis', '!=', '')
      ->distinct()
      ->orderBy('jenis')
      ->pluck('jenis');

    $ptsList = DB::table('a_pts')
      ->select('kode_pts', 'nama_pts')
      ->whereNotNull('kode_pts')
      ->where('kode_pts', '!=', '')
      ->whereNotNull('nama_pts')
      ->where('nama_pts', '!=', '')
      ->orderBy('nama_pts')
      ->get();

    $statusPerubahan = DB::table('h_perubahan')
      ->orderBy('kode')
      ->pluck('status_perubahan')
      ->filter()
      ->values();

    return view('admin.view-data-dosen', [
      'dosen' => $data_dosen,
      'pics' => $pics,
      'bankList' => $bankList,
      'golonganList' => $golonganList,
      'jenisList' => $jenisList,
      'ptsList' => $ptsList,
      'statusPerubahan' => $statusPerubahan,
    ]);
  }

  /**
   * Show the perubahan-data-dosen view which contains tabs (pengaktifan / perubahan data).
   */
  public function showPerubahan(Request $request, $nidn)
  {
    $mode = (string) $request->query('mode', 'edit');

    // reuse same data gathering as show()
    $bulanSession = (int) session('bulan') ?: 12;
    $bulanSession = max(1, min(12, $bulanSession));

    $masa_kerja = "NULLIF(Tahun{$bulanSession}, '')";
    $golongan   = "NULLIF(Gol{$bulanSession}, '')";
    $jabatan    = "NULLIF(Jabatan{$bulanSession}, '')";
    $gaji       = "NULLIF(Gaji{$bulanSession}, 0)";

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

    // mode=new: allow rendering from draft even when DB row doesn't exist yet
    if (!$data_dosen) {
      if ($mode !== 'new') {
        return redirect()->back()->with('error', 'Data dosen tidak ditemukan!');
      }

      $draft = $this->getDraftAddDosenByIdentifier((string) $nidn);
      if (!$draft) {
        return redirect()->back()->with('error', 'Draft Tambah Dosen tidak ditemukan. Silakan ulangi dari tombol Tambah.');
      }

      $t = new Transaksi();
      $t->Tahun_Versi = (int) session('tahun');
      $t->NIDN = trim((string) ($draft['nidn'] ?? ''));
      $t->NUPTK = trim((string) ($draft['nuptk'] ?? ''));
      $t->NIK = (string) ($draft['nik'] ?? '');
      if ($t->NUPTK === '' && $t->NIDN === '' && trim((string) $nidn) !== '') {
        // If the route param is NUPTK (NIDN missing), show it as NUPTK.
        $t->NUPTK = trim((string) $nidn);
      }
      $t->Nama = (string) ($draft['nama'] ?? '');
      $t->Kode_PT = (string) ($draft['kode_pt'] ?? '');
      $t->PTS = (string) ($draft['pts'] ?? '');
      $t->Sertifikat_Dosen = (string) ($draft['sertifikat_dosen'] ?? '');
      $t->Tahun_Lulus = (string) ($draft['tahun_lulus'] ?? '');
      $t->Pemegang_Wilayah = (string) ($draft['pemegang_wilayah'] ?? '');
      $t->Jenis = (string) ($draft['jenis'] ?? '');
      $t->Usia = (string) ($draft['usia'] ?? '');
      $t->TTL = (string) ($draft['ttl'] ?? '');
      $t->Tanggal_Lahir = $this->formatDmyOrEmpty($draft['tanggal_lahir'] ?? '');

      $t->No_Rekening = (string) ($draft['no_rekening'] ?? '');
      $t->Bank = (string) ($draft['bank'] ?? '');
      $t->Nama_Rekening = (string) ($draft['nama_rekening'] ?? '');
      $t->Nama_Penerima = (string) ($draft['nama_penerima'] ?? '');
      $t->NPWP = (string) ($draft['npwp'] ?? '');
      $t->Eligible_span = (string) ($draft['eligible_span'] ?? '');
      $t->Aktif = (string) ($draft['aktif'] ?? '0');

      $t->TMT_JAD_Pertama = (string) ($draft['tmt_jad_pertama'] ?? '');
      $t->TMT_JAD_Akhir = (string) ($draft['tmt_jad_akhir'] ?? '');
      $t->TMT_Inpassing_Akhir = (string) ($draft['tmt_inpassing_akhir'] ?? '');
      $t->Inpassing = (string) ($draft['inpassing'] ?? '');

      // computed fields used by UI
      $t->masa_kerja = null;
      $t->gol = null;
      $t->jabatan = null;
      $t->gaji = null;
      $data_dosen = $t;
    }

    $pics = User::where('role', 'pic')->orderBy('email')->pluck('email');

    $bankList = DB::table('b_bank')
      ->select('nama_bank')
      ->whereNotNull('nama_bank')
      ->where('nama_bank', '!=', '')
      ->distinct()
      ->orderBy('nama_bank')
      ->pluck('nama_bank');

    $golonganList = DB::table('c_grade')
      ->select('gol')
      ->whereNotNull('gol')
      ->where('gol', '!=', '')
      ->distinct()
      ->orderBy('gol')
      ->pluck('gol');

    $jenisList = DB::table('g_pegawai')
      ->select('jenis')
      ->whereNotNull('jenis')
      ->where('jenis', '!=', '')
      ->distinct()
      ->orderBy('jenis')
      ->pluck('jenis');

    $ptsList = DB::table('a_pts')
      ->select('kode_pts', 'nama_pts')
      ->whereNotNull('kode_pts')
      ->where('kode_pts', '!=', '')
      ->whereNotNull('nama_pts')
      ->where('nama_pts', '!=', '')
      ->orderBy('nama_pts')
      ->get();

    $statusPerubahan = DB::table('h_perubahan')
      ->orderBy('kode')
      ->pluck('status_perubahan')
      ->filter()
      ->values();

    $jabatanList = DB::table('e_jabatan')
      ->select('jabatan')
      ->whereNotNull('jabatan')
      ->where('jabatan', '!=', '')
      ->orderBy('jabatan')
      ->pluck('jabatan');

    return view('admin.perubahan-data-dosen', [
      'dosen' => $data_dosen,
      'mode' => $mode,
      'pics' => $pics,
      'bankList' => $bankList,
      'golonganList' => $golonganList,
      'jenisList' => $jenisList,
      'ptsList' => $ptsList,
      'statusPerubahan' => $statusPerubahan,
      'jabatanList' => $jabatanList,
    ]);
  }


  public function ubahDataDosen(Request $request, $nidn)
  {

    $dateDmyOrYmd = function ($attribute, $value, $fail) {
      $s = trim((string) $value);
      if ($s === '') return;
      try {
        if (strpos($s, '/') !== false) {
          Carbon::createFromFormat('d/m/Y', $s);
        } else {
          Carbon::createFromFormat('Y-m-d', $s);
        }
      } catch (\Throwable $e) {
        $alias = ErrorAlias::fromThrowable($e, 'PERUBAHAN-DOSEN-VALIDATION');
        Log::error(__METHOD__ . ' date rule validation failed', [
          'alias' => $alias['code'],
          'attribute' => $attribute,
          'value' => $value,
          'message' => $e->getMessage(),
          'trace' => $e->getTraceAsString(),
        ]);
        $fail("Format $attribute tidak valid. Gunakan DD/MM/YYYY.");
      }
    };

    // Validasi input
    $request->validate([
      'no_dokumen_ubah' => 'required|string',
      'tgl_dokumen_ubah' => ['required', $dateDmyOrYmd],
      'alasan_perubahan' => 'required|string',
      'dokumen' => 'required|file|mimes:pdf|max:10240',
      'keterangan' => 'required|string|max:100',
      'Aktif' => 'nullable|in:0,1',
      // Detail Data Dosen: validate only when field is present (readonly / unchanged fields may be omitted)
      'nama' => 'sometimes|nullable|string',
      'kode_pt' => 'sometimes|nullable|string',
      'pts' => 'sometimes|nullable|string',
      'jenis' => 'sometimes|nullable|string',
      'sertifikat_dosen' => 'sometimes|nullable|string',
      'tahun_lulus' => 'sometimes|nullable|string',
      'tmt_jad_pertama' => ['sometimes', 'nullable', $dateDmyOrYmd],
      'tmt_jad_akhir' => ['sometimes', 'nullable', $dateDmyOrYmd],
      'tmt_inpassing_akhir' => ['sometimes', 'nullable', $dateDmyOrYmd],
      // Pengaktifan tab: before save, TMT Keaktifan is mandatory
      'tmt_keaktifan' => ['required', $dateDmyOrYmd],
      'apply_gol_by_tmt_inpassing' => 'sometimes|nullable|in:1',
      'inpassing' => 'sometimes|nullable|string',
      'jabatan' => 'sometimes|nullable|string',
      'gol' => 'sometimes|nullable|string',
      'tmt_jabatan' => ['sometimes', 'nullable', $dateDmyOrYmd],
      'tahun' => 'sometimes|nullable|numeric',
      'gaji' => 'sometimes|nullable|numeric',
      'no_rekening' => 'sometimes|nullable|string',
      'bank' => 'sometimes|nullable|string',
      'nama_rekening' => 'sometimes|nullable|string',
      'nama_penerima' => 'sometimes|nullable|string',
      'npwp' => 'sometimes|nullable|string',
      'pemegang_wilayah' => 'sometimes|nullable|string',
      'eligible_span' => 'sometimes|nullable|string',
      'tanggal_update_terakhir' => ['sometimes', 'nullable', $dateDmyOrYmd],
    ]);


    // normalize incoming date strings to Y-m-d for DB storage
    $this->normalizeRequestDates($request, [
      'tgl_dokumen_ubah', 'tanggal_update_terakhir', 'tmt_inpassing_akhir',
      'tmt_jad_pertama', 'tmt_jad_akhir', 'tmt_keaktifan', 'tmt_jabatan'
    ]);

    try {
      // Tentukan bulan mulai (Terhitung Mulai Tanggal) mendukung format dd/mm/yyyy atau yyyy-mm-dd
      $tmt = null;
      if (!empty($request->tanggal_update_terakhir)) {
        try {
          $tmt = Carbon::createFromFormat('d/m/Y', $request->tanggal_update_terakhir);
        } catch (\Exception $e) {
          $alias = ErrorAlias::fromThrowable($e, 'PERUBAHAN-DOSEN-DATE');
          Log::error(__METHOD__ . ' createFromFormat failed, falling back to parse', [
            'alias' => $alias['code'],
            'input' => $request->tanggal_update_terakhir,
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
          ]);
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
        if ($mode === 'new') {
          $data_dosen = $this->ensureTransaksiExistsForNewMode((string) $identifierParam, (int) $year, $request);
        }
        if (!$data_dosen) {
          return redirect()
            ->back()
            ->with('error', 'Data dosen tidak ditemukan!');
        }
      }

      // Conditional validation: if Jabatan is changed or mode=new then TMT Jabatan is required
      $bulanSessionForCheck = (int) session('bulan') ?: 12;
      $bulanSessionForCheck = max(1, min(12, $bulanSessionForCheck));
      $currentJabatan = (string) ($data_dosen->{"Jabatan{$bulanSessionForCheck}"} ?? '');
      $incomingJabatan = $request->has('jabatan') ? (string) $request->input('jabatan') : '';
      $jabatanChanged = ($incomingJabatan !== '' && $this->norm($incomingJabatan) !== $this->norm($currentJabatan));
      if (($mode === 'new' || $jabatanChanged) && !$request->filled('tmt_jabatan')) {
        throw \Illuminate\Validation\ValidationException::withMessages([
          'tmt_jabatan' => 'TMT Jabatan wajib diisi jika Jabatan diubah atau saat mode=new.',
        ]);
      }

      // Approval flow: simpan pengajuan ke i_complain (status=open) dan tunggu admin setuju/tolak.
      // Tidak langsung mengubah data transaksi / histori.
      $dokumenPathRel = null;
      $dokumenFilename = null;
      if ($request->hasFile('dokumen')) {
        $dokumen = $request->file('dokumen');
        $tanggalSekarang = date('Ymd');
        $originalName = pathinfo($dokumen->getClientOriginalName(), PATHINFO_FILENAME);
        $slugName = Str::slug($originalName, '_');
        $dokumenFilename = $tanggalSekarang . '_' . $slugName . '.' . $dokumen->getClientOriginalExtension();
        Storage::putFileAs('public/Dokumen_Histori_Dosen2', $dokumen, $dokumenFilename);
        $dokumenPathRel = 'Dokumen_Histori_Dosen2/' . $dokumenFilename;
      }

      // Tentukan pelapor
      $pelaporTipe = 'pts';
      $ptsId = null;
      $dosenId = null;
      $kodePts = null;
      $pengguna = null;

      if (Auth::guard('pts')->check()) {
        $pts = Auth::guard('pts')->user();
        $ptsId = $pts ? $pts->id : null;
        $kodePts = $pts ? ($pts->kode_pts ?? null) : null;
        $pengguna = 'PTS:' . ($kodePts ?? '-');
      } elseif (Auth::guard('dosen')->check()) {
        $dosenUser = Auth::guard('dosen')->user();
        $pelaporTipe = 'dosen';
        $dosenId = $dosenUser ? $dosenUser->id : null;
        $kodePts = $dosenUser ? ($dosenUser->kode_pts ?? null) : null;
        $pengguna = 'DOSEN:' . ($dosenUser->nidn ?? ($dosenUser->nuptk ?? '-'));
      } else {
        $webUser = Auth::guard('web')->user();
        $kodePts = (string) ($request->input('kode_pt', $data_dosen->Kode_PT ?? ''));
        if (trim($kodePts) !== '') {
          $ptsId = DB::table('a_pts')->where('kode_pts', $kodePts)->value('id');
        }
        $pengguna = $webUser ? ($webUser->email ?? null) : null;
      }

      $payload = [
        'jenis_pengajuan' => 'perubahan_data_dosen',
        'tab' => 'pengaktifan',
        'tahun_versi' => $year,
        'nidn' => $data_dosen->NIDN ?? null,
        'nuptk' => $data_dosen->NUPTK ?? null,
        'nama' => (string) $request->input('nama', $data_dosen->Nama ?? ''),
        'pts' => (string) $request->input('pts', $data_dosen->PTS ?? ''),
        'kode_pt' => (string) $request->input('kode_pt', $data_dosen->Kode_PT ?? ''),
        'pemegang_wilayah' => (string) $request->input('pemegang_wilayah', $data_dosen->Pemegang_Wilayah ?? ''),
        'aktif' => (string) $request->input('Aktif', $data_dosen->Aktif ?? null),
        'keterangan' => (string) $request->input('keterangan', $data_dosen->Keterangan ?? ($data_dosen->keterangan ?? '')),
        'pengguna' => $pengguna,
        'tanggal_update_terakhir' => $request->input('tanggal_update_terakhir'),
        'no_dokumen_ubah' => (string) $request->input('no_dokumen_ubah'),
        'tgl_dokumen_ubah' => $request->input('tgl_dokumen_ubah'),
        'alasan_perubahan' => (string) $request->input('alasan_perubahan'),
        'dokumen' => $dokumenFilename,
        // tambahan konteks
        'tmt_keaktifan' => $request->input('tmt_keaktifan'),

        // field perubahan bulanan (agar approval via complain sama persis)
        'gol' => (string) $request->input('gol'),
        'tahun' => (string) $request->input('tahun'),
        'jabatan' => (string) $request->input('jabatan'),
        'gaji' => $request->input('gaji'),
        'tmt_inpassing_akhir' => $request->input('tmt_inpassing_akhir'),
        'apply_gol_by_tmt_inpassing' => (string) $request->input('apply_gol_by_tmt_inpassing'),
        'tmt_jabatan' => $request->input('tmt_jabatan'),
      ];

      try {
        DB::table('i_complain')->insert([
          'pelapor_tipe' => $pelaporTipe,
          'pts_id' => $ptsId,
          'dosen_id' => $dosenId,
          'kode_pts' => $kodePts,
          'nidn' => $payload['nidn'],
          'nuptk' => $payload['nuptk'],
          'judul' => 'Pengajuan Perubahan Data Dosen',
          'pesan' => json_encode($payload, JSON_UNESCAPED_UNICODE),
          'lampiran' => $dokumenPathRel,
          'jenis_pengajuan' => 'perubahan_data_dosen',
          'status' => 'open',
          'admin_balasan' => null,
          'handled_by' => null,
          'handled_at' => null,
          'created_at' => now(),
          'updated_at' => now(),
        ]);
      } catch (\Throwable $e) {
        if (!empty($dokumenFilename)) {
          try {
            Storage::delete('public/Dokumen_Histori_Dosen2/' . $dokumenFilename);
          } catch (\Throwable $ignored) {
            $alias = ErrorAlias::fromThrowable($ignored, 'SIMPAN-PENGAJUAN-STORAGE');
            Log::error(__METHOD__ . ' failed to delete uploaded file after insert error', [
              'alias' => $alias['code'],
              'file' => $dokumenFilename,
              'message' => $ignored->getMessage(),
              'trace' => $ignored->getTraceAsString(),
            ]);
          }
        }
        $role = Auth::guard('web')->check() ? (Auth::user()->role ?? null) : null;
        $prefix = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $pelaporTipe));
        if ($prefix === '') {
          $prefix = ($role === 'pic') ? 'PIC' : (($role === 'admin') ? 'ADM' : 'REQ');
        }
        $alias = ErrorAlias::fromThrowable($e, $prefix . '-PENGAJUAN');
        Log::error('PerubahanDataDosenController i_complain insert failed', [
          'alias' => $alias['code'],
          'role' => $role,
          'pelapor_tipe' => $pelaporTipe,
          'kode_pts' => $kodePts,
          'nidn' => $payload['nidn'] ?? null,
          'nuptk' => $payload['nuptk'] ?? null,
          'message' => $e->getMessage(),
          'trace' => $e->getTraceAsString(),
        ]);
        $publicMessage = 'Pengajuan gagal dikirim. Silakan coba lagi. (Kode: ' . $alias['code'] . ')';

        if ($request->expectsJson()) {
          return response()->json(['success' => false, 'message' => $publicMessage, 'code' => $alias['code']], 500);
        }
        return redirect()->back()->with('error', $publicMessage);
      }

      if ($request->expectsJson()) {
        return response()->json(['success' => true, 'message' => 'Pengajuan perubahan berhasil dikirim dan menunggu persetujuan admin.']);
      }
      return redirect()->back()->with('success', 'Pengajuan perubahan berhasil dikirim dan menunggu persetujuan admin.');

      $oldAktif = (string)($data_dosen->Aktif ?? '');
      $newAktif = (string)($request->Aktif ?? '');

      // Update field-field Detail Data Dosen (only when values change)
      $identifier = $data_dosen->NIDN ?? $data_dosen->NUPTK ?? $identifierParam;
      $queryByIdentifier = Transaksi::where(function($q) use ($identifier) {
          $q->where('NIDN', $identifier)
            ->orWhere('NUPTK', $identifier);
        })->where('Tahun_Versi', $year);

      $dasarMap = [
        'Nama' => 'nama',
        'Kode_PT' => 'kode_pt',
        'Jenis' => 'jenis',
        'Sertifikat_Dosen' => 'sertifikat_dosen',
        'Tahun_Lulus' => 'tahun_lulus',
        'No_Rekening' => 'no_rekening',
        'Bank' => 'bank',
        'Nama_Rekening' => 'nama_rekening',
        'Nama_Penerima' => 'nama_penerima',
        'NPWP' => 'npwp',
        'Pemegang_Wilayah' => 'pemegang_wilayah',
        'Eligible_span' => 'eligible_span',
        'Aktif' => 'Aktif',
        'Keterangan' => 'keterangan',
        'TMT_JAD_Pertama' => 'tmt_jad_pertama',
        'TMT_JAD_Akhir' => 'tmt_jad_akhir',
      ];

      $dataUpdateDasar = $this->buildChangedUpdate($data_dosen, $request, $dasarMap, ['tmt_jad_pertama', 'tmt_jad_akhir']);

      // Inpassing rules:
      // - Anything other than "Berdasarkan Inpassing" is treated as Sesuai TMT Awal dan Akhir.
      // - TMT_Inpassing_Akhir can be updated independently (even if Gol is not updated).
      $incomingInpassing = $request->has('inpassing') ? $request->input('inpassing') : ($data_dosen->Inpassing ?? '');
      $incomingBased = $this->isBerdasarkanInpassing($incomingInpassing);

      $inpassingUpdate = [];
      if ($request->has('inpassing') && $this->norm($data_dosen->Inpassing ?? '') !== $this->norm($request->inpassing)) {
        $inpassingUpdate['Inpassing'] = $request->inpassing;
      }

      // Always allow updating TMT_Inpassing_Akhir when user submits it.
      // Checkbox only controls whether monthly Gol columns are propagated.
      if ($request->has('tmt_inpassing_akhir')) {
        $incomingTmt = $request->input('tmt_inpassing_akhir');
        $incomingNorm = $this->normDate($incomingTmt);
        $currentNorm = $this->normDate($data_dosen->TMT_Inpassing_Akhir ?? '');

        if ($incomingNorm !== $currentNorm) {
          $inpassingUpdate['TMT_Inpassing_Akhir'] = ($this->norm($incomingTmt) === '') ? null : $incomingTmt;
        }
      }

      if (!empty($inpassingUpdate)) {
        $dataUpdateDasar = array_merge($dataUpdateDasar, $inpassingUpdate);
      }

      // PTS name derived from Kode_PT (avoid trusting raw input)
      if ($request->has('kode_pt')) {
        $kodePt = $this->norm($request->input('kode_pt'));
        if ($kodePt !== '') {
          $ptsName = DB::table('a_pts')->where('kode_pts', $kodePt)->value('nama_pts');
          if (!empty($ptsName) && $this->norm($ptsName) !== $this->norm($data_dosen->PTS ?? '')) {
            $dataUpdateDasar['PTS'] = $ptsName;
          }
        }
      }

      if (!empty($dataUpdateDasar)) {
        $queryByIdentifier->update($dataUpdateDasar);
      }

      // Update bulanan menyesuaikan TMT:
      // - Masa Kerja (Tahun1-12) dan Biaya (Gaji1-12) mengikuti bulan aktif session pada mode=edit
      // - KodeUsulan1-12 mengikuti TMT Keaktifan (wajib) ketika status berubah
      $alasan = (string) $request->alasan_perubahan;
      $statusChanged = ($oldAktif !== '' && $newAktif !== '' && $oldAktif !== $newAktif);

      // For mode=new (Tambah Dosen / create multi-year), keep a deterministic KodeUsulan value
      // for the bulk month fills.
      $kodeUsulanToApplyNew = $alasan;
      if ($statusChanged && $oldAktif === '0' && $newAktif === '1') {
        $kodeUsulanToApplyNew = null;
      }
      
      $sebelumTmtLabel = 'Sebelum TMT';
      if ($mode === 'new') {
        $kodeUsulanToApplyNew = 'Draf Baru';
        $sebelumTmtLabel = 'Dosen Baru';
      }

      // Perubahan bulanan mengikuti session('bulan') untuk mode=edit.
      // Untuk mode=new (Tambah Dosen), tetap gunakan perilaku lama (mulai dari bulan TMT sampai Desember).
      $bulanSession = (int) session('bulan') ?: 12;
      $bulanSession = max(1, min(12, $bulanSession));

      // hitung gaji lookup (dipakai khusus untuk tidak aktif -> aktif)
      $lookupGaji = function () use ($request, $data_dosen, $bulanSession) {
        $jabatanForSalary = $request->input('jabatan', $data_dosen->{"Jabatan{$bulanSession}"} ?? ($data_dosen->Jabatan ?? ''));
        $fromRef = $this->lookupGajiFromCGrade($request->gol, $request->tahun, $jabatanForSalary);
        if ($fromRef !== null) {
          return $fromRef;
        }
        // fallback terakhir (jika referensi c_grade benar-benar kosong): gunakan gaji dari form jika ada
        return is_numeric($request->gaji) ? (int) $request->gaji : 0;
      };

      // KodeUsulan is ONLY changed when status changes:
      // - Aktif -> Tidak Aktif: set KodeUsulan (from TMT Keaktifan month onward) to alasan_perubahan
      // - Tidak Aktif -> Aktif: set KodeUsulan (from TMT Keaktifan month onward) to NULL
      // - Status unchanged: do not touch KodeUsulan
      $ymKeaktifan = $this->parseYearMonth($request->input('tmt_keaktifan'));
      if (!$ymKeaktifan) {
        return redirect()->back()->with('error', 'TMT Keaktifan wajib diisi dengan format tanggal yang valid.');
      }
      [$startYearKeaktifan, $startMonthKeaktifan] = $ymKeaktifan;

      // Tentukan nilai gaji bulanan (mulai TMT)
      $gajiToApply = is_numeric($request->gaji) ? (int) $request->gaji : 0;
      if ($statusChanged && $oldAktif === '1' && $newAktif === '0') {
        // Aktif -> Tidak Aktif: gaji menjadi 0 sesuai TMT
        $gajiToApply = 0;
      } elseif ($statusChanged && $oldAktif === '0' && $newAktif === '1') {
        // Tidak Aktif -> Aktif: gaji dihitung sesuai gol & masa kerja dan KodeUsulan NULL sesuai TMT
        $gajiToApply = $lookupGaji();
      }

      $updateBulanan = [];

      if ($mode !== 'new') {
        // Jabatan & Golongan are handled by TMT-based rules below.
        if ($request->has('tahun') && $this->norm($data_dosen->{"Tahun{$bulanSession}"} ?? '') !== $this->norm($request->tahun)) {
          $updateBulanan["Tahun{$bulanSession}"] = $request->tahun;
        }
        // Gaji pada mode=edit hanya boleh diubah manual ketika status tidak berubah.
        // Jika status berubah, gaji akan diupdate berdasarkan TMT Keaktifan (lihat applyMonthlyRangeUpdate di bawah).
        if (!$statusChanged && $request->has('gaji') && $this->norm($data_dosen->{"Gaji{$bulanSession}"} ?? '') !== $this->norm($gajiToApply)) {
          $updateBulanan["Gaji{$bulanSession}"] = $gajiToApply;
        }
      } else {
        // Jika tahun session lebih besar dari tahun TMT, maka update dimulai dari Januari.
        $startMonthSession = ($year === $tahunTmt) ? $bulanAktif : 1;

        // Tambahan: Untuk bulan-bulan sebelum TMT, set KodeUsulan menjadi 'Sebelum TMT' agar tidak masuk susulan
        for ($i = 1; $i < $startMonthSession; $i++) {
          $updateBulanan["Gaji{$i}"] = 0;
          $updateBulanan["KodeUsulan{$i}"] = $sebelumTmtLabel;
        }

        for ($i = $startMonthSession; $i <= 12; $i++) {
          $updateBulanan["Jabatan{$i}"] = $request->jabatan;
          $updateBulanan["Gol{$i}"] = $request->gol;
          $updateBulanan["Tahun{$i}"] = $request->tahun;
          $updateBulanan["Gaji{$i}"] = $gajiToApply;
          $updateBulanan["KodeUsulan{$i}"] = $kodeUsulanToApplyNew;
        }
      }
      if (!empty($updateBulanan)) {
        $queryByIdentifier->update($updateBulanan);
      }

      // Apply KodeUsulan range update only when status changes, based on TMT Keaktifan
      if ($statusChanged) {
        if ($oldAktif === '1' && $newAktif === '0') {
          $this->applyMonthlyRangeUpdate((string) $identifier, $startYearKeaktifan, $startMonthKeaktifan, 'KodeUsulan', $alasan);
        } elseif ($oldAktif === '0' && $newAktif === '1') {
          $this->applyMonthlyRangeUpdate((string) $identifier, $startYearKeaktifan, $startMonthKeaktifan, 'KodeUsulan', null);
        }

        // Update Gaji sesuai TMT Keaktifan:
        // - Aktif -> Tidak Aktif: gaji 0
        // - Tidak Aktif -> Aktif: gaji dari referensi c_grade (dengan fallback masa kerja tertinggi)
        $this->applyMonthlyRangeUpdate((string) $identifier, $startYearKeaktifan, $startMonthKeaktifan, 'Gaji', $gajiToApply);
      }

      // NEW RULES (edit mode):
      // 1) Golongan by TMT Inpassing Akhir only when checkbox is checked
      if ($mode !== 'new') {
        $applyGol = $request->input('apply_gol_by_tmt_inpassing');
        if ($applyGol === '1' && $request->filled('tmt_inpassing_akhir') && $request->filled('gol')) {
          $ym = $this->parseYearMonth($request->input('tmt_inpassing_akhir'));
          if ($ym) {
            [$startY, $startM] = $ym;
            $this->applyMonthlyRangeUpdate((string) $identifier, $startY, $startM, 'Gol', $request->gol);
          }
        }

        // 2) Jabatan by TMT Jabatan only when TMT Jabatan is filled
        if ($request->filled('tmt_jabatan') && $request->filled('jabatan')) {
          $ymJ = $this->parseYearMonth($request->input('tmt_jabatan'));
          if ($ymJ) {
            [$startYJ, $startMJ] = $ymJ;
            $this->applyMonthlyRangeUpdate((string) $identifier, $startYJ, $startMJ, 'Jabatan', $request->jabatan);
          }
        }
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
              $updateFuture["KodeUsulan{$i}"] = $kodeUsulanToApplyNew;
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
                // Tahun sama dengan tahun TMT: sebelum bulanAktif diset 'Sebelum TMT', setelahnya isi $kodeUsulanToApplyNew
                for ($i = 1; $i <= 12; $i++) {
                  if ($i < $bulanAktif) {
                    $dataBaru["KodeUsulan{$i}"] = $sebelumTmtLabel;
                    $dataBaru["Gaji{$i}"] = 0;
                  } else {
                    $dataBaru["KodeUsulan{$i}"] = $kodeUsulanToApplyNew;
                  }
                }
              } else {
                // Tahun di antara TMT dan tahun login: semua bulan 1..12 isi $nilaiKode
                for ($i = 1; $i <= 12; $i++) {
                  $dataBaru["KodeUsulan{$i}"] = $kodeUsulanToApplyNew;
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
        'nama' => $request->input('nama', $data_dosen->Nama),
        'pts' => $request->input('pts', $data_dosen->PTS),
        'kode_pt' => $request->input('kode_pt', $data_dosen->Kode_PT),
        'pemegang_wilayah' => $request->input('pemegang_wilayah', $data_dosen->Pemegang_Wilayah),
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

      if ($request->expectsJson()) {
        return response()->json(['success' => true, 'message' => 'Data dosen tersimpan!', 'nidn' => $data_dosen->NIDN]);
      }
      return redirect()->back()->with('success', 'Data dosen tersimpan!');
    } catch (\Exception $e) {
      $role = Auth::guard('web')->check() ? (Auth::user()->role ?? null) : null;
      $scope = ($role === 'pic') ? 'PIC-SIMPAN' : (($role === 'admin') ? 'ADM-SIMPAN' : 'SIMPAN');
      $alias = ErrorAlias::fromThrowable($e, $scope);
      Log::error('PerubahanDataDosenController save failed', [
        'alias' => $alias['code'],
        'role' => $role,
        'nidn' => $nidn,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);
      $publicMessage = 'Terjadi kesalahan saat menyimpan data. (Kode: ' . $alias['code'] . ')';

      if ($request->expectsJson()) {
        return response()->json(['success' => false, 'message' => $publicMessage, 'code' => $alias['code']], 500);
      }
      return redirect()->back()->with('error', $publicMessage);
      // return redirect()->route('admin.edit-data-dosen', ['nidn' => $nidn])->with('error', $e->getMessage());
    }
  }

  public function ubahMkGol(Request $request, $nidn)
  {
    // Accept DD/MM/YYYY or Y-m-d for date inputs
    $dateDmyOrYmd = function ($attribute, $value, $fail) {
      $s = trim((string) $value);
      if ($s === '') return;
      try {
        if (strpos($s, '/') !== false) {
          Carbon::createFromFormat('d/m/Y', $s);
        } else {
          Carbon::createFromFormat('Y-m-d', $s);
        }
      } catch (\Throwable $e) {
        $alias = ErrorAlias::fromThrowable($e, 'PERUBAHAN-DOSEN-VALIDATION');
        Log::error(__METHOD__ . ' date rule validation failed', [
          'alias' => $alias['code'],
          'attribute' => $attribute,
          'value' => $value,
          'message' => $e->getMessage(),
          'trace' => $e->getTraceAsString(),
        ]);
        $fail("Format $attribute tidak valid. Gunakan DD/MM/YYYY.");
      }
    };

    // Validasi minimal agar format TMT & input numerik konsisten
    $request->validate([
      'no_dokumen_ubah' => 'required|string',
      'tgl_dokumen_ubah' => ['required', $dateDmyOrYmd],
      'alasan_perubahan' => 'required|string',
      'dokumen' => 'required|file|mimes:pdf,doc,docx',
      'tanggal_update_terakhir' => ['required', $dateDmyOrYmd],
      'keterangan' => 'required|string|max:100',
      'Aktif' => 'required|in:0,1',
      'jenis' => 'required|string',
      'jabatan' => 'required|string',
      'gol' => 'required|string',
      'tahun' => 'required|numeric',
      'gaji' => 'nullable',
      'eligible_span' => 'required|string',
    ]);

    // normalize dates for DB
    $this->normalizeRequestDates($request, ['tgl_dokumen_ubah', 'tanggal_update_terakhir']);

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
      'nama' => $request->input('nama', $data_dosen->Nama),
      'pts' => $request->input('pts', $data_dosen->PTS),
      'kode_pt' => $request->input('kode_pt', $data_dosen->Kode_PT),
      'pemegang_wilayah' => $request->input('pemegang_wilayah', $data_dosen->Pemegang_Wilayah),
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

    $dateDmyOrYmd = function ($attribute, $value, $fail) {
      $s = trim((string) $value);
      if ($s === '') return;
      try {
        if (strpos($s, '/') !== false) {
          Carbon::createFromFormat('d/m/Y', $s);
        } else {
          Carbon::createFromFormat('Y-m-d', $s);
        }
      } catch (\Throwable $e) {
        $alias = ErrorAlias::fromThrowable($e, 'PERUBAHAN-DOSEN-VALIDATION');
        Log::error(__METHOD__ . ' date rule validation failed', [
          'alias' => $alias['code'],
          'attribute' => $attribute,
          'value' => $value,
          'message' => $e->getMessage(),
          'trace' => $e->getTraceAsString(),
        ]);
        $fail("Format $attribute tidak valid. Gunakan DD/MM/YYYY.");
      }
    };
    // Validasi input informasi perubahan dan data dasar dosen
    $request->validate([
      'no_dokumen_ubah' => 'required|string',
      'tgl_dokumen_ubah' => ['required', $dateDmyOrYmd],
      'alasan_perubahan' => 'required|string',
      'dokumen' => 'required|file|mimes:pdf|max:10240',
      'keterangan' => 'required|string|max:100',
      'Aktif' => 'required|in:0,1',
      // Detail Data Dosen optional (only changed fields may be submitted)
      'nama' => 'sometimes|nullable|string',
      'kode_pt' => 'sometimes|nullable|string',
      'pts' => 'sometimes|nullable|string',
      'jenis' => 'sometimes|nullable|string',
      'sertifikat_dosen' => 'sometimes|nullable|string',
      'tahun_lulus' => 'sometimes|nullable|string',
      'tmt_jad_pertama' => ['sometimes', 'nullable', $dateDmyOrYmd],
      'tmt_jad_akhir' => ['sometimes', 'nullable', $dateDmyOrYmd],
      'tmt_inpassing_akhir' => ['sometimes', 'nullable', $dateDmyOrYmd],
      'tmt_keaktifan' => ['sometimes', 'nullable', $dateDmyOrYmd],
      'apply_gol_by_tmt_inpassing' => 'sometimes|nullable|in:1',
      'inpassing' => 'sometimes|nullable|string',
      'jabatan' => 'sometimes|nullable|string',
      'gol' => 'sometimes|nullable|string',
      'tmt_jabatan' => ['sometimes', 'nullable', $dateDmyOrYmd],
      'tahun' => 'sometimes|nullable|numeric',
      'gaji' => 'sometimes|nullable|numeric',
      'no_rekening' => 'sometimes|nullable|string',
      'bank' => 'sometimes|nullable|string',
      'nama_rekening' => 'sometimes|nullable|string',
      'nama_penerima' => 'sometimes|nullable|string',
      'npwp' => 'sometimes|nullable|string',
      'pemegang_wilayah' => 'sometimes|nullable|string',
      'eligible_span' => 'sometimes|nullable|string',
      'tanggal_update_terakhir' => ['sometimes', 'nullable', $dateDmyOrYmd],
    ]);

    // normalize incoming date strings to Y-m-d for DB storage
    $this->normalizeRequestDates($request, [
      'tgl_dokumen_ubah', 'tanggal_update_terakhir', 'tmt_inpassing_akhir',
      'tmt_jad_pertama', 'tmt_jad_akhir', 'tmt_keaktifan', 'tmt_jabatan'
    ]);

    $year = session('tahun');

    $mode = (string) $request->input('mode', 'edit');

    $webUser = Auth::guard('web')->user();
    $isPic = $webUser && method_exists($webUser, 'isPIC') && $webUser->isPIC();
    $isAdmin = $webUser && method_exists($webUser, 'isAdmin') && $webUser->isAdmin();
    // PIC/Admin should behave like direct admin edit: apply changes to s_transaksi_2.
    // Non-web actors (PTS/Dosen) keep approval flow via i_complain.
    $useApprovalFlow = !(Auth::guard('web')->check() && ($isPic || $isAdmin));

    // Mengambil data dosen berdasarkan NIDN atau NUPTK
    $data_dosen = Transaksi::where(function ($q) use ($nidn) {
        $q->where('NIDN', $nidn)
          ->orWhere('NUPTK', $nidn);
      })
      ->where('Tahun_Versi', $year)->first();

    if (!$data_dosen) {
      if ($mode === 'new') {
        $data_dosen = $this->ensureTransaksiExistsForNewMode((string) $nidn, (int) $year, $request);
      }
      if (!$data_dosen) {
        return redirect()
          ->back()
          ->with('error', 'Data dosen tidak ditemukan!');
      }
    }

    // Conditional validation: if Jabatan is changed or mode=new then TMT Jabatan is required
    $bulanSessionForCheck = (int) session('bulan') ?: 12;
    $bulanSessionForCheck = max(1, min(12, $bulanSessionForCheck));
    $currentJabatan = (string) ($data_dosen->{"Jabatan{$bulanSessionForCheck}"} ?? '');
    $incomingJabatan = $request->has('jabatan') ? (string) $request->input('jabatan') : '';
    $jabatanChanged = ($incomingJabatan !== '' && $this->norm($incomingJabatan) !== $this->norm($currentJabatan));
    if (($mode === 'new' || $jabatanChanged) && !$request->filled('tmt_jabatan')) {
      throw \Illuminate\Validation\ValidationException::withMessages([
        'tmt_jabatan' => 'TMT Jabatan wajib diisi jika Jabatan diubah atau saat mode=new.',
      ]);
    }

    if ($useApprovalFlow) {
      // Approval flow: simpan pengajuan ke i_complain (status=open) dan tunggu admin setuju/tolak.
      // Tidak langsung mengubah data transaksi / histori.
      $dokumenPathRel = null;
      $dokumenFilename = null;
      if ($request->hasFile('dokumen')) {
        $dokumen = $request->file('dokumen');
        $tanggalSekarang = date('Ymd');
        $originalName = pathinfo($dokumen->getClientOriginalName(), PATHINFO_FILENAME);
        $slugName = Str::slug($originalName, '_');
        $dokumenFilename = $tanggalSekarang . '_' . $slugName . '.' . $dokumen->getClientOriginalExtension();
        Storage::putFileAs('public/Dokumen_Histori_Dosen2', $dokumen, $dokumenFilename);
        $dokumenPathRel = 'Dokumen_Histori_Dosen2/' . $dokumenFilename;
      }

      $pelaporTipe = 'pts';
      $ptsId = null;
      $dosenId = null;
      $kodePts = null;
      $pengguna = null;

      if (Auth::guard('pts')->check()) {
        $pts = Auth::guard('pts')->user();
        $ptsId = $pts ? $pts->id : null;
        $kodePts = $pts ? ($pts->kode_pts ?? null) : null;
        $pengguna = 'PTS:' . ($kodePts ?? '-');
      } elseif (Auth::guard('dosen')->check()) {
        $dosenUser = Auth::guard('dosen')->user();
        $pelaporTipe = 'dosen';
        $dosenId = $dosenUser ? $dosenUser->id : null;
        $kodePts = $dosenUser ? ($dosenUser->kode_pts ?? null) : null;
        $pengguna = 'DOSEN:' . ($dosenUser->nidn ?? ($dosenUser->nuptk ?? '-'));
      } else {
        $kodePts = (string) ($request->input('kode_pt', $data_dosen->Kode_PT ?? ''));
        if (trim($kodePts) !== '') {
          $ptsId = DB::table('a_pts')->where('kode_pts', $kodePts)->value('id');
        }
        $pengguna = $webUser ? ($webUser->email ?? null) : null;
      }

      $payload = [
        'jenis_pengajuan' => 'perubahan_data_dosen',
        'tab' => 'perubahan',
        'tahun_versi' => $year,
        'nidn' => $data_dosen->NIDN ?? null,
        'nuptk' => $data_dosen->NUPTK ?? null,
        'nama' => (string) $request->input('nama', $data_dosen->Nama ?? ''),
        'pts' => (string) $request->input('pts', $data_dosen->PTS ?? ''),
        'kode_pt' => (string) $request->input('kode_pt', $data_dosen->Kode_PT ?? ''),
        'pemegang_wilayah' => (string) $request->input('pemegang_wilayah', $data_dosen->Pemegang_Wilayah ?? ''),
        'aktif' => (string) $request->input('Aktif', $data_dosen->Aktif ?? null),
        'keterangan' => (string) $request->input('keterangan', $data_dosen->Keterangan ?? ($data_dosen->keterangan ?? '')),
        'pengguna' => $pengguna,
        'tanggal_update_terakhir' => $request->input('tanggal_update_terakhir'),
        'no_dokumen_ubah' => (string) $request->input('no_dokumen_ubah'),
        'tgl_dokumen_ubah' => $request->input('tgl_dokumen_ubah'),
        'alasan_perubahan' => (string) $request->input('alasan_perubahan'),
        'dokumen' => $dokumenFilename,

        // field perubahan bulanan (agar approval via complain sama persis)
        'tmt_keaktifan' => $request->input('tmt_keaktifan'),
        'gol' => (string) $request->input('gol'),
        'tahun' => (string) $request->input('tahun'),
        'jabatan' => (string) $request->input('jabatan'),
        'gaji' => $request->input('gaji'),
        'tmt_inpassing_akhir' => $request->input('tmt_inpassing_akhir'),
        'apply_gol_by_tmt_inpassing' => (string) $request->input('apply_gol_by_tmt_inpassing'),
        'tmt_jabatan' => $request->input('tmt_jabatan'),
      ];

      try {
        DB::table('i_complain')->insert([
          'pelapor_tipe' => $pelaporTipe,
          'pts_id' => $ptsId,
          'dosen_id' => $dosenId,
          'kode_pts' => $kodePts,
          'nidn' => $payload['nidn'],
          'nuptk' => $payload['nuptk'],
          'judul' => 'Pengajuan Perubahan Data Dosen',
          'pesan' => json_encode($payload, JSON_UNESCAPED_UNICODE),
          'lampiran' => $dokumenPathRel,
          'jenis_pengajuan' => 'perubahan_data_dosen',
          'status' => 'open',
          'admin_balasan' => null,
          'handled_by' => null,
          'handled_at' => null,
          'created_at' => now(),
          'updated_at' => now(),
        ]);
      } catch (\Throwable $e) {
        if (!empty($dokumenFilename)) {
          try {
            Storage::delete('public/Dokumen_Histori_Dosen2/' . $dokumenFilename);
          } catch (\Throwable $ignored) {
            $alias = ErrorAlias::fromThrowable($ignored, 'PENGAJUAN-STORAGE');
            Log::error(__METHOD__ . ' failed to delete uploaded file after insert error', [
              'alias' => $alias['code'],
              'file' => $dokumenFilename,
              'message' => $ignored->getMessage(),
              'trace' => $ignored->getTraceAsString(),
            ]);
          }
        }
        $role = Auth::guard('web')->check() ? (Auth::user()->role ?? null) : null;
        $prefix = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $pelaporTipe));
        if ($prefix === '') {
          $prefix = ($role === 'pic') ? 'PIC' : (($role === 'admin') ? 'ADM' : 'REQ');
        }
        $alias = ErrorAlias::fromThrowable($e, $prefix . '-PENGAJUAN');
        Log::error('PerubahanDataDosenController i_complain insert failed', [
          'alias' => $alias['code'],
          'role' => $role,
          'pelapor_tipe' => $pelaporTipe,
          'kode_pts' => $kodePts,
          'nidn' => $payload['nidn'] ?? null,
          'nuptk' => $payload['nuptk'] ?? null,
          'message' => $e->getMessage(),
          'trace' => $e->getTraceAsString(),
        ]);
        $publicMessage = 'Pengajuan gagal dikirim. Silakan coba lagi. (Kode: ' . $alias['code'] . ')';

        if ($request->expectsJson()) {
          return response()->json(['success' => false, 'message' => $publicMessage, 'code' => $alias['code']], 500);
        }
        return redirect()->back()->with('error', $publicMessage);
      }

      if ($request->expectsJson()) {
        return response()->json(['success' => true, 'message' => 'Pengajuan perubahan berhasil dikirim dan menunggu persetujuan admin.']);
      }
      return redirect()->back()->with('success', 'Pengajuan perubahan berhasil dikirim dan menunggu persetujuan admin.');
    }

    $identifierUpdate = $data_dosen->NIDN ?? $data_dosen->NUPTK ?? $nidn;
    $queryByIdentifier = Transaksi::where(function ($q) use ($identifierUpdate) {
        $q->where('NIDN', $identifierUpdate)
          ->orWhere('NUPTK', $identifierUpdate);
      })
      ->where('Tahun_Versi', $year);

    $dasarMap = [
      'Nama' => 'nama',
      'Kode_PT' => 'kode_pt',
      'Jenis' => 'jenis',
      'Sertifikat_Dosen' => 'sertifikat_dosen',
      'Tahun_Lulus' => 'tahun_lulus',
      'No_Rekening' => 'no_rekening',
      'Bank' => 'bank',
      'Nama_Rekening' => 'nama_rekening',
      'Nama_Penerima' => 'nama_penerima',
      'NPWP' => 'npwp',
      'Pemegang_Wilayah' => 'pemegang_wilayah',
      'Eligible_span' => 'eligible_span',
      'Aktif' => 'Aktif',
      'Keterangan' => 'keterangan',
      'TMT_JAD_Pertama' => 'tmt_jad_pertama',
      'TMT_JAD_Akhir' => 'tmt_jad_akhir',
    ];

    $dataUpdate = $this->buildChangedUpdate($data_dosen, $request, $dasarMap, ['tmt_jad_pertama', 'tmt_jad_akhir']);

    // Inpassing rules (same as ubahDataDosen)
    $incomingInpassing = $request->has('inpassing') ? $request->input('inpassing') : ($data_dosen->Inpassing ?? '');
    $incomingBased = $this->isBerdasarkanInpassing($incomingInpassing);

    if ($request->has('inpassing') && $this->norm($data_dosen->Inpassing ?? '') !== $this->norm($request->inpassing)) {
      $dataUpdate['Inpassing'] = $request->inpassing;
    }

    // Always update TMT_Inpassing_Akhir when it is submitted (checkbox only controls monthly Gol propagation)
    if ($request->has('tmt_inpassing_akhir')) {
      $incomingTmt = $request->input('tmt_inpassing_akhir');
      $incomingNorm = $this->normDate($incomingTmt);
      $currentNorm = $this->normDate($data_dosen->TMT_Inpassing_Akhir ?? '');

      if ($incomingNorm !== $currentNorm) {
        $dataUpdate['TMT_Inpassing_Akhir'] = ($this->norm($incomingTmt) === '') ? null : $incomingTmt;
      }
    }

    if ($request->has('kode_pt')) {
      $kodePt = $this->norm($request->input('kode_pt'));
      $oldKodePt = $this->norm($data_dosen->Kode_PT ?? '');
      if ($kodePt !== '' && $kodePt !== $oldKodePt) {
        $ptsData = DB::table('a_pts')->where('kode_pts', $kodePt)->first();
        if ($ptsData) {
          $ptsName = $ptsData->nama_pts;
          $wilayahBaru = strtolower($ptsData->wilayah);

          // Cek apakah ada usulan aktif di tahun ini
          if ($this->hasActiveUsulan($identifierUpdate, $year)) {
             return redirect()->back()->withInput()->with([
                 'requires_scheduling' => true,
                 'kode_pt_baru' => $kodePt,
                 'nama_pts_baru' => $ptsName,
                 'pemegang_wilayah_baru' => $wilayahBaru,
                 'warning' => 'Dosen ini tidak bisa dipindah PTS sekarang karena dalam proses usulan pencairan aktif.'
             ]);
          }

          // Jika tidak ada usulan aktif, langsung eksekusi perpindahan global
          $globalPtsUpdate = [
            'Kode_PT' => $kodePt,
            'PTS' => $ptsName,
            'Pemegang_Wilayah' => $wilayahBaru,
          ];
          Transaksi::where(function ($q) use ($identifierUpdate) {
            $q->where('NIDN', $identifierUpdate)
              ->orWhere('NUPTK', $identifierUpdate);
          })->update($globalPtsUpdate);

          // Hapus kunci dari $dataUpdate agar tidak di-update lagi di bawah
          if (isset($dataUpdate['Kode_PT'])) unset($dataUpdate['Kode_PT']);
          if (isset($dataUpdate['PTS'])) unset($dataUpdate['PTS']);
          if (isset($dataUpdate['Pemegang_Wilayah'])) unset($dataUpdate['Pemegang_Wilayah']);
        }
      }
    }

    // Monthly fields (session month): only update when changed
    $bulanSession = (int) session('bulan') ?: 12;
    $bulanSession = max(1, min(12, $bulanSession));
    // Jabatan & Golongan are handled by TMT-based rules below.
    if ($request->has('tahun') && $this->norm($data_dosen->{"Tahun{$bulanSession}"} ?? '') !== $this->norm($request->tahun)) {
      $dataUpdate["Tahun{$bulanSession}"] = $request->tahun;
    }
    if ($request->has('gaji') && $this->norm($data_dosen->{"Gaji{$bulanSession}"} ?? '') !== $this->norm($request->gaji)) {
      $dataUpdate["Gaji{$bulanSession}"] = $request->gaji;
    }

    if (!empty($dataUpdate)) {
      $queryByIdentifier->update($dataUpdate);
    }

    // NEW RULES (perubahan tab):
    // 1) Golongan by TMT Inpassing Akhir only when checkbox is checked
    $applyGol = $request->input('apply_gol_by_tmt_inpassing');
    if ($applyGol === '1' && $request->filled('tmt_inpassing_akhir') && $request->filled('gol')) {
      $ym = $this->parseYearMonth($request->input('tmt_inpassing_akhir'));
      if ($ym) {
        [$startY, $startM] = $ym;
        $this->applyMonthlyRangeUpdate((string) $identifierUpdate, $startY, $startM, 'Gol', $request->gol);
      }
    }

    // 2) Jabatan by TMT Jabatan only when TMT Jabatan is filled
    if ($request->filled('tmt_jabatan') && $request->filled('jabatan')) {
      $ymJ = $this->parseYearMonth($request->input('tmt_jabatan'));
      if ($ymJ) {
        [$startYJ, $startMJ] = $ymJ;
        $this->applyMonthlyRangeUpdate((string) $identifierUpdate, $startYJ, $startMJ, 'Jabatan', $request->jabatan);
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
      'nama' => $request->input('nama', $data_dosen->Nama),
      'pts' => $request->input('pts', $data_dosen->PTS),
      'kode_pt' => $request->input('kode_pt', $data_dosen->Kode_PT),
      'pemegang_wilayah' => $request->input('pemegang_wilayah', $data_dosen->Pemegang_Wilayah),
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

    // Stay on Perubahan Data Dosen page (so success modal + timed redirect can run)
    $routePrefix = $isPic ? 'pic' : 'admin';
    return redirect()
      ->route($routePrefix . '.perubahan-data-dosen.show', ['nidn' => $nidn])
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
    // dd('tes datadosentidakaktif');
    $dataDosenTidakAktif = Dosen::where('aktif', 0)->get();
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
        ->select(DB::raw('NIDN AS nidn'), DB::raw('NUPTK AS nuptk'), 'Nama', 'Kode_PT', 'PTS', 'Jenis', 'Keterangan', DB::raw($cekKodeUsulan));
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
          if (isset($row->Aktifq) && $row->Aktif == 1) {
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
                <button type="submit" class="btn btn-icon btn-sm btn-danger">
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
