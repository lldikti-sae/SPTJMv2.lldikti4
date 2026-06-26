<?php

namespace App\Http\Controllers;

use App\Helpers\ErrorAlias;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\HistoriDosen;
use App\Models\Transaksi;
use App\Helpers\ComplainMessageFormatter;
use Carbon\Carbon;

class ComplainAdminController extends Controller
{
    private array $transaksiCache = [];

    private function norm($v): string
    {
        return trim((string) $v);
    }

    private function getMaxTahunVersiForIdentifier(string $identifier): int
    {
        $key = $identifier;
        if (isset($this->transaksiCache[$key]['maxYear'])) {
            return (int) $this->transaksiCache[$key]['maxYear'];
        }

        $maxYear = (int) Transaksi::where(function ($q) use ($identifier) {
                $q->where('NIDN', $identifier)->orWhere('NUPTK', $identifier);
            })->max('Tahun_Versi');

        $this->transaksiCache[$key]['maxYear'] = $maxYear;
        return $maxYear;
    }

    /**
     * @return array<int, \App\Models\Transaksi>
     */
    private function getTransaksiByYear(string $identifier, int $startYear, int $endYear): array
    {
        $key = $identifier;
        if (!isset($this->transaksiCache[$key]['loadedRange'])) {
            $this->transaksiCache[$key]['loadedRange'] = null;
            $this->transaksiCache[$key]['rowsByYear'] = [];
        }

        $loadedRange = $this->transaksiCache[$key]['loadedRange'];
        $needReload = $loadedRange === null
            || $startYear < $loadedRange[0]
            || $endYear > $loadedRange[1];

        if ($needReload) {
            $min = $loadedRange ? min((int) $loadedRange[0], $startYear) : $startYear;
            $max = $loadedRange ? max((int) $loadedRange[1], $endYear) : $endYear;

            $rows = Transaksi::where(function ($q) use ($identifier) {
                    $q->where('NIDN', $identifier)->orWhere('NUPTK', $identifier);
                })
                ->whereBetween('Tahun_Versi', [$min, $max])
                ->get();

            $map = [];
            foreach ($rows as $r) {
                $map[(int) $r->Tahun_Versi] = $r;
            }

            $this->transaksiCache[$key]['rowsByYear'] = $map;
            $this->transaksiCache[$key]['loadedRange'] = [$min, $max];
        }

        /** @var array<int, \App\Models\Transaksi> */
        return $this->transaksiCache[$key]['rowsByYear'];
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
            return $s;
        }
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
            return null;
        }
    }

    private function isGuruBesarAtauProfesor($jabatan): bool
    {
        $text = strtolower(trim((string) $jabatan));
        if ($text === '') return false;
        return strpos($text, 'guru besar') !== false || strpos($text, 'profesor') !== false;
    }

    private function lookupGajiFromCGrade($gol, $masaKerja, $jabatan = null): ?int
    {
        $golVal = strtoupper(trim((string) $gol));
        if ($golVal === '') return null;

        $masaKerjaInt = is_numeric($masaKerja) ? (int) $masaKerja : 0;
        if ($masaKerjaInt > 32) $masaKerjaInt = 32;
        if ($masaKerjaInt < 0) $masaKerjaInt = 0;

        $nominal = DB::table('c_grade')
            ->where('gol', $golVal)
            ->where('masa_kerja', $masaKerjaInt)
            ->value('nominal');

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

        if ($nominal === null) return null;

        $value = (float) $nominal;
        if ($this->isGuruBesarAtauProfesor($jabatan)) {
            $value *= 3;
        }
        return (int) round($value);
    }

    private function applyMonthlyRangeUpdate(string $identifier, int $startYear, int $startMonth, string $prefix, $value): void
    {
        $startMonth = max(1, min(12, (int) $startMonth));
        $maxYear = $this->getMaxTahunVersiForIdentifier($identifier);

        // Kode cair columns (Jan-Des) live alongside KodeUsulan1-12 in s_transaksi_2.
        // Rule: if a month already has kode cair, do NOT modify KodeUsulan{month}.
        $kodeCairCols = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];

        if ($maxYear <= 0) {
            $maxYear = $startYear;
        }
        if ($maxYear < $startYear) {
            $maxYear = $startYear;
        }

        $rowsByYear = $this->getTransaksiByYear($identifier, $startYear, $maxYear);
        foreach ($rowsByYear as $year => $record) {
            if ($year < $startYear || $year > $maxYear) continue;
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
                Transaksi::where('No', $record->No)->update($update);
                // keep in-memory cache consistent
                foreach ($update as $k => $v) {
                    $record->setAttribute($k, $v);
                }
            }
        }
    }

    private function applyPerubahanDataDosenApproval(array $payload, $row, $admin): void
    {
        $identifier = trim((string) ($payload['nidn'] ?? ($row->nidn ?? '')));
        if ($identifier === '') {
            $identifier = trim((string) ($payload['nuptk'] ?? ($row->nuptk ?? '')));
        }
        if ($identifier === '') {
            throw new \RuntimeException('Identifier dosen (NIDN/NUPTK) tidak ditemukan.');
        }

        $tahunVersi = (int) ($payload['tahun_versi'] ?? 0);
        if ($tahunVersi <= 0) {
            // fallback: use current session year if available
            $tahunVersi = (int) session('tahun');
        }
        if ($tahunVersi <= 0) {
            throw new \RuntimeException('Tahun versi pengajuan tidak valid.');
        }

        /** @var Transaksi|null $current */
        $current = Transaksi::where(function ($q) use ($identifier) {
                $q->where('NIDN', $identifier)->orWhere('NUPTK', $identifier);
            })
            ->where('Tahun_Versi', $tahunVersi)
            ->first();

        if (!$current) {
            throw new \RuntimeException('Data dosen tidak ditemukan di s_transaksi_2 untuk tahun versi tersebut.');
        }

        // Capture status change before applying updates
        $oldAktif = $this->norm($current->Aktif ?? '');
        $newAktif = array_key_exists('aktif', $payload) ? $this->norm($payload['aktif']) : $oldAktif;
        $statusChanged = ($oldAktif !== '' && $newAktif !== '' && $oldAktif !== $newAktif);

        $update = [];
        $map = [
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
            'Aktif' => 'aktif',
            'Keterangan' => 'keterangan',
            'TMT_JAD_Pertama' => 'tmt_jad_pertama',
            'TMT_JAD_Akhir' => 'tmt_jad_akhir',
            'Inpassing' => 'inpassing',
            'TMT_Inpassing_Akhir' => 'tmt_inpassing_akhir',
            'Tanggal_Update_Terakhir' => 'tanggal_update_terakhir',
        ];

        $dateKeys = ['tmt_jad_pertama', 'tmt_jad_akhir', 'tmt_inpassing_akhir', 'tanggal_update_terakhir'];

        foreach ($map as $col => $key) {
            if (!array_key_exists($key, $payload)) continue;
            $incoming = $payload[$key];
            $cur = $current->{$col} ?? null;

            $incomingNorm = in_array($key, $dateKeys, true) ? $this->normDate($incoming) : $this->norm($incoming);
            $curNorm = in_array($key, $dateKeys, true) ? $this->normDate($cur) : $this->norm($cur);
            if ($incomingNorm === $curNorm) continue;
            $update[$col] = $incoming;
        }

        // PTS name derived from Kode_PT if present
        if (array_key_exists('kode_pt', $payload)) {
            $kodePt = $this->norm($payload['kode_pt']);
            if ($kodePt !== '') {
                $ptsName = DB::table('a_pts')->where('kode_pts', $kodePt)->value('nama_pts');
                if (!empty($ptsName) && $this->norm($ptsName) !== $this->norm($current->PTS ?? '')) {
                    $update['PTS'] = $ptsName;
                }
            }
        } elseif (array_key_exists('pts', $payload)) {
            if ($this->norm($payload['pts']) !== $this->norm($current->PTS ?? '')) {
                $update['PTS'] = $payload['pts'];
            }
        }

        // Set Pengguna (audit) if provided
        $pengguna = (string) ($payload['pengguna'] ?? ($admin ? ($admin->email ?? null) : null));
        if ($pengguna !== '' && $this->norm($pengguna) !== $this->norm($current->Pengguna ?? '')) {
            $update['Pengguna'] = $pengguna;
        }

        if (!empty($update)) {
            Transaksi::where(function ($q) use ($identifier) {
                    $q->where('NIDN', $identifier)->orWhere('NUPTK', $identifier);
                })
                ->where('Tahun_Versi', $tahunVersi)
                ->update($update);
        }

        // Reload for any subsequent reads if needed
        $current = Transaksi::where(function ($q) use ($identifier) {
                $q->where('NIDN', $identifier)->orWhere('NUPTK', $identifier);
            })
            ->where('Tahun_Versi', $tahunVersi)
            ->first();

        // Monthly rule: Gol by TMT Inpassing (only when checkbox set)
        $applyGol = (string) ($payload['apply_gol_by_tmt_inpassing'] ?? '');
        if ($applyGol === '1' && !empty($payload['tmt_inpassing_akhir']) && !empty($payload['gol'])) {
            $ym = $this->parseYearMonth($payload['tmt_inpassing_akhir']);
            if ($ym) {
                [$startY, $startM] = $ym;
                $this->applyMonthlyRangeUpdate($identifier, $startY, $startM, 'Gol', $payload['gol']);
            }
        }

        // Monthly rule: Jabatan by TMT Jabatan
        if (!empty($payload['tmt_jabatan']) && !empty($payload['jabatan'])) {
            $ym = $this->parseYearMonth($payload['tmt_jabatan']);
            if ($ym) {
                [$startY, $startM] = $ym;
                $this->applyMonthlyRangeUpdate($identifier, $startY, $startM, 'Jabatan', $payload['jabatan']);
            }
        }

        // Monthly rules based on TMT Keaktifan when status changes
        if ($statusChanged) {
            if (empty($payload['tmt_keaktifan'])) {
                throw new \RuntimeException('TMT Keaktifan wajib diisi dengan format tanggal yang valid.');
            }
            $ym = $this->parseYearMonth($payload['tmt_keaktifan']);
            if (!$ym) {
                throw new \RuntimeException('TMT Keaktifan wajib diisi dengan format tanggal yang valid.');
            }

            [$startY, $startM] = $ym;
            $alasan = (string) ($payload['alasan_perubahan'] ?? '');

                if ($oldAktif === '1' && $newAktif === '0') {
                    $this->applyMonthlyRangeUpdate($identifier, $startY, $startM, 'KodeUsulan', $alasan);
                } elseif ($oldAktif === '0' && $newAktif === '1') {
                    $this->applyMonthlyRangeUpdate($identifier, $startY, $startM, 'KodeUsulan', null);
                }

                // Update Gaji range following the same change semantics
                $gajiToApply = null;
                if ($oldAktif === '1' && $newAktif === '0') {
                    $gajiToApply = 0;
                } elseif ($oldAktif === '0' && $newAktif === '1') {
                    $jabatanForSalary = (string) ($payload['jabatan'] ?? '');
                    $golForSalary = $payload['gol'] ?? null;
                    $mkForSalary = $payload['tahun'] ?? null;
                    $fromRef = $this->lookupGajiFromCGrade($golForSalary, $mkForSalary, $jabatanForSalary);
                    if ($fromRef !== null) {
                        $gajiToApply = $fromRef;
                    } elseif (isset($payload['gaji']) && is_numeric($payload['gaji'])) {
                        $gajiToApply = (int) $payload['gaji'];
                    } else {
                        $gajiToApply = 0;
                    }
                } else {
                    if (isset($payload['gaji']) && is_numeric($payload['gaji'])) {
                        $gajiToApply = (int) $payload['gaji'];
                    }
                }

            if ($gajiToApply !== null) {
                $this->applyMonthlyRangeUpdate($identifier, $startY, $startM, 'Gaji', $gajiToApply);
            }
        }
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $draw = (int) $request->input('draw', 0);
            try {
                $start = (int) $request->input('start', 0);
                $length = (int) $request->input('length', 10);
                $searchValue = (string) $request->input('search.value', '');

                $columnsMap = [
                    0 => 'id',
                    1 => 'pelapor_tipe',
                    2 => 'kode_pts',
                    3 => 'c.nidn',
                    4 => 'c.nuptk',
                    5 => 'pic',
                    6 => 'nama_pelapor',
                    7 => 'c.judul',
                    8 => 'c.status',
                    9 => 'c.handled_at',
                    10 => 'c.created_at',
                ];

                $order = $request->input('order', []);
                $orderColIndex = (int) data_get($order, '0.column', 0);
                $orderDir = (string) data_get($order, '0.dir', 'desc');
                $orderDir = in_array($orderDir, ['asc', 'desc'], true) ? $orderDir : 'desc';
                $orderColumn = $columnsMap[$orderColIndex] ?? 'id';

                $tahunVersi = (int) session('tahun');

                // Pre-aggregate PIC lookups to avoid correlated subquery per row
                $picByNidn = DB::table('s_transaksi_2 as tn')
                    ->selectRaw('tn.NIDN as nidn, MAX(TRIM(tn.Pemegang_Wilayah)) as pic_nidn')
                    ->whereNotNull('tn.NIDN')
                    ->where('tn.NIDN', '<>', '')
                    ->when($tahunVersi > 0, function ($q) use ($tahunVersi) {
                        $q->where('tn.Tahun_Versi', '=', $tahunVersi);
                    })
                    ->groupBy('tn.NIDN');

                $picByNuptk = DB::table('s_transaksi_2 as tu')
                    ->selectRaw('tu.NUPTK as nuptk, MAX(TRIM(tu.Pemegang_Wilayah)) as pic_nuptk')
                    ->whereNotNull('tu.NUPTK')
                    ->where('tu.NUPTK', '<>', '')
                    ->when($tahunVersi > 0, function ($q) use ($tahunVersi) {
                        $q->where('tu.Tahun_Versi', '=', $tahunVersi);
                    })
                    ->groupBy('tu.NUPTK');

                $baseQuery = DB::table('i_complain as c')
                    ->leftJoin('a_dosen as d', 'c.dosen_id', '=', 'd.id')
                    ->leftJoin('a_pts as p', 'c.pts_id', '=', 'p.id')
                    ->leftJoinSub($picByNidn, 'pn', function ($join) {
                        $join->on('pn.nidn', '=', 'c.nidn');
                    })
                    ->leftJoinSub($picByNuptk, 'pu', function ($join) {
                        $join->on('pu.nuptk', '=', 'c.nuptk');
                    })
                    ->select([
                        'c.id',
                        'c.pelapor_tipe',
                        'c.kode_pts',
                        'c.nidn',
                        'c.nuptk',
                        'c.judul',
                        'c.status',
                        'c.handled_at',
                        'c.created_at',
                        DB::raw('COALESCE(d.nama_dosen, p.nama_pts) as nama_pelapor'),
                        DB::raw('COALESCE(pn.pic_nidn, pu.pic_nuptk) as pic'),
                    ])
                    ->whereIn('c.pelapor_tipe', ['pts', 'dosen']);
                // Apply session year filter to list (compare created_at year)
                $tahunSession = (int) session('tahun');
                if ($tahunSession > 0) {
                    $baseQuery->whereYear('c.created_at', $tahunSession);
                }

                $recordsTotalQuery = DB::table('i_complain')->whereIn('pelapor_tipe', ['pts', 'dosen']);
                if ($tahunSession > 0) {
                    $recordsTotalQuery->whereYear('created_at', $tahunSession);
                }
                $recordsTotal = (int) $recordsTotalQuery->count();

                if (trim($searchValue) !== '') {
                    $baseQuery->where(function ($q) use ($searchValue) {
                        $q->where('c.pelapor_tipe', 'like', "%{$searchValue}%")
                            ->orWhere('c.kode_pts', 'like', "%{$searchValue}%")
                            ->orWhere('c.nidn', 'like', "%{$searchValue}%")
                            ->orWhere('c.nuptk', 'like', "%{$searchValue}%")
                            ->orWhere('d.nama_dosen', 'like', "%{$searchValue}%")
                            ->orWhere('p.nama_pts', 'like', "%{$searchValue}%")
                            ->orWhere('c.judul', 'like', "%{$searchValue}%")
                            ->orWhere('c.pesan', 'like', "%{$searchValue}%")
                            ->orWhere('c.status', 'like', "%{$searchValue}%");
                    });
                }

                // Apply status filter if provided
                $statusFilter = (string) $request->input('status', '');
                if ($statusFilter !== '') {
                    $allowed = ['open', 'setuju', 'tolak', 'menunggu_konfirmasi'];
                    if (in_array($statusFilter, $allowed, true)) {
                        $baseQuery->where('c.status', $statusFilter);
                    }
                }

                // Filtered count (avoid counting full select, and avoid PIC subqueries)
                $filteredCountQuery = DB::table('i_complain as c')
                    ->leftJoin('a_dosen as d', 'c.dosen_id', '=', 'd.id')
                    ->leftJoin('a_pts as p', 'c.pts_id', '=', 'p.id')
                    ->whereIn('c.pelapor_tipe', ['pts', 'dosen']);
                if ($tahunSession > 0) {
                    $filteredCountQuery->whereYear('c.created_at', $tahunSession);
                }
                if (trim($searchValue) !== '') {
                    $filteredCountQuery->where(function ($q) use ($searchValue) {
                        $q->where('c.pelapor_tipe', 'like', "%{$searchValue}%")
                            ->orWhere('c.kode_pts', 'like', "%{$searchValue}%")
                            ->orWhere('c.nidn', 'like', "%{$searchValue}%")
                            ->orWhere('c.nuptk', 'like', "%{$searchValue}%")
                            ->orWhere('d.nama_dosen', 'like', "%{$searchValue}%")
                            ->orWhere('p.nama_pts', 'like', "%{$searchValue}%")
                            ->orWhere('c.judul', 'like', "%{$searchValue}%")
                            ->orWhere('c.pesan', 'like', "%{$searchValue}%")
                            ->orWhere('c.status', 'like', "%{$searchValue}%");
                    });
                }
                $statusFilter = (string) $request->input('status', '');
                if ($statusFilter !== '') {
                    $allowed = ['open', 'setuju', 'tolak', 'menunggu_konfirmasi'];
                    if (in_array($statusFilter, $allowed, true)) {
                        $filteredCountQuery->where('c.status', $statusFilter);
                    }
                }
                $recordsFiltered = (int) $filteredCountQuery->distinct('c.id')->count('c.id');

                $baseQuery->orderBy($orderColumn, $orderDir)->orderByDesc('id');
                if ($length !== -1) {
                    $baseQuery->offset($start)->limit($length);
                }

                $rows = $baseQuery->get();

                $data = $rows->map(function ($r) {
                    $status = (string) ($r->status ?? 'open');
                    $badge = 'bg-label-secondary';
                    // Make 'open' orange, 'setuju' green, 'tolak' red
                    if ($status === 'open') $badge = 'bg-label-warning';
                    if ($status === 'menunggu_konfirmasi') $badge = 'bg-label-info';
                    if ($status === 'setuju') $badge = 'bg-label-success';
                    if ($status === 'tolak') $badge = 'bg-label-danger';

                    $detailBtn = '<button type="button" class="btn btn-sm btn-icon btn-primary view-complain" data-id="' . $r->id . '" title="Detail"><i class="bx bx-show"></i></button>';

                    // prepare truncated title (max 5 words)
                    $fullTitle = (string) ($r->judul ?? '');
                    $words = preg_split('/\s+/', trim($fullTitle));
                    if (is_array($words) && count($words) > 5) {
                        $shortTitle = implode(' ', array_slice($words, 0, 5)) . ' .....';
                    } else {
                        $shortTitle = $fullTitle;
                    }

                    // format dates to WIB (Asia/Jakarta)
                    $handledAt = null;
                    $createdAt = null;
                    try {
                        $handledAt = $r->handled_at ? Carbon::parse($r->handled_at)->setTimezone('Asia/Jakarta')->format('d-m-Y H:i') : '-';
                    } catch (\Throwable $e) {
                        $handledAt = $r->handled_at ?? '-';
                    }
                    try {
                        $createdAt = $r->created_at ? Carbon::parse($r->created_at)->setTimezone('Asia/Jakarta')->format('d-m-Y H:i') : '-';
                    } catch (\Throwable $e) {
                        $createdAt = $r->created_at ?? '-';
                    }

                    return [
                        'id' => $r->id,
                        'pelapor_tipe' => strtoupper($r->pelapor_tipe),
                        'kode_pts' => $r->kode_pts,
                        'nidn' => $r->nidn ?: '-',
                        'nuptk' => $r->nuptk ?: '-',
                        'pic' => $r->pic ?: '-',
                        'nama' => $r->nama_pelapor ?: '-',
                        'judul' => $shortTitle,
                        'status' => '<span class="badge ' . $badge . '">' . strtoupper(str_replace('_', ' ', $status)) . '</span>',
                        'handled_at' => $handledAt,
                        'created_at' => $createdAt,
                        'aksi' => $detailBtn,
                    ];
                });

                return response()->json([
                    'draw' => $draw,
                    'recordsTotal' => $recordsTotal,
                    'recordsFiltered' => $recordsFiltered,
                    'data' => $data,
                ]);
            } catch (\Throwable $e) {
                $alias = ErrorAlias::fromThrowable($e, 'ADM-COMPLAIN-LIST');
                Log::error('ComplainAdminController@index ajax failed', [
                    'alias' => $alias['code'],
                    'draw' => $draw,
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                return response()->json([
                    'draw' => $draw,
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                ]);
            }
        }

        return view('admin.complain');
    }

    public function show($id)
    {
        try {
            $row = DB::table('i_complain')->where('id', $id)->first();
            if (!$row) {
                return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
            }

            // If pesan is JSON, return a human-friendly summary
            try {
                $formatted = null;
                $jenis = (string) ($row->jenis_pengajuan ?? '');
                
                if ($jenis === 'perubahan_data_dosen') {
                    $formatted = ComplainMessageFormatter::formatPerubahanDataDosenHtml($row);
                } elseif (in_array($jenis, ['Surat Keterangan', 'Surat SKPP'], true)) {
                    $formatted = ComplainMessageFormatter::formatSkppHtml($row);
                }

                if (!empty($formatted)) {
                    $row->pesan_raw = $row->pesan;
                    $row->pesan = $formatted;
                }
            } catch (\Throwable $ignored) {
                // ignore formatting errors; fall back to raw pesan
            }

            return response()->json(['success' => true, 'data' => $row]);
        } catch (\Throwable $e) {
            $alias = ErrorAlias::fromThrowable($e, 'ADM-COMPLAIN-DETAIL');
            Log::error('ComplainAdminController@show failed', [
                'alias' => $alias['code'],
                'id' => $id,
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

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:open,setuju,tolak,menunggu_konfirmasi',
            'admin_balasan' => 'nullable|string',
        ]);

        $admin = Auth::guard('web')->user();
        $now = now();

        try {
            DB::beginTransaction();

            $row = DB::table('i_complain')->where('id', $id)->lockForUpdate()->first();
            if (!$row) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
            }

            $newStatus = (string) $validated['status'];

            // Jika pengajuan adalah perubahan data dosen dan disetujui, buat histori dosen.
            if ($newStatus === 'setuju' && (string) ($row->status ?? '') !== 'setuju' && (string) ($row->jenis_pengajuan ?? '') === 'perubahan_data_dosen') {
                $payload = json_decode((string) ($row->pesan ?? ''), true);
                if (!is_array($payload)) {
                    throw new \RuntimeException('Payload pengajuan perubahan data dosen tidak valid.');
                }

                // Backfill display/audit fields from current transaksi when payload intentionally omits readonly inputs.
                $identifier = trim((string) ($payload['nidn'] ?? ($row->nidn ?? '')));
                if ($identifier === '') {
                    $identifier = trim((string) ($payload['nuptk'] ?? ($row->nuptk ?? '')));
                }
                $tahunVersi = (int) ($payload['tahun_versi'] ?? 0);
                if ($tahunVersi <= 0) {
                    $tahunVersi = (int) session('tahun');
                }
                $current = null;
                if ($identifier !== '' && $tahunVersi > 0) {
                    $current = Transaksi::where(function ($q) use ($identifier) {
                            $q->where('NIDN', $identifier)->orWhere('NUPTK', $identifier);
                        })
                        ->where('Tahun_Versi', $tahunVersi)
                        ->first();
                }

                // dokumen pada histori hanya nama file; lampiran di i_complain berisi path relatif storage
                $dokumenName = null;
                if (!empty($payload['dokumen'])) {
                    $dokumenName = (string) $payload['dokumen'];
                } elseif (!empty($row->lampiran)) {
                    $dokumenName = basename((string) $row->lampiran);
                }

                // Pastikan No Dokumen pada histori terisi.
                // Sumber utama: payload pengajuan (i_complain.pesan). Fallback: nilai di s_transaksi_2
                // (yang juga tampil pada form PTS perubahan-data-dosen).
                $noDokumenUbah = trim((string) ($payload['no_dokumen_ubah'] ?? ''));
                if ($noDokumenUbah === '' && $current) {
                    $noDokumenUbah = trim((string) ($current->no_dokumen_ubah ?? ($current->No_Dokumen_Ubah ?? '')));
                }

                HistoriDosen::create([
                    'nidn' => $payload['nidn'] ?? $row->nidn ?? null,
                    'nuptk' => $payload['nuptk'] ?? $row->nuptk ?? null,
                    'nama' => $payload['nama'] ?? ($current ? ($current->Nama ?? null) : null),
                    'pts' => $payload['pts'] ?? ($current ? ($current->PTS ?? null) : null),
                    'kode_pt' => $payload['kode_pt'] ?? ($current ? ($current->Kode_PT ?? null) : null),
                    'pemegang_wilayah' => $payload['pemegang_wilayah'] ?? ($current ? ($current->Pemegang_Wilayah ?? null) : null),
                    'aktif' => $payload['aktif'] ?? ($current ? ($current->Aktif ?? null) : null),
                    'keterangan' => $payload['keterangan'] ?? null,
                    'pengguna' => $payload['pengguna'] ?? null,
                    'tanggal_update_terakhir' => $payload['tanggal_update_terakhir'] ?? null,
                    'no_dokumen_ubah' => $noDokumenUbah !== '' ? $noDokumenUbah : null,
                    'tgl_dokumen_ubah' => $payload['tgl_dokumen_ubah'] ?? null,
                    'alasan_perubahan' => $payload['alasan_perubahan'] ?? null,
                    'dokumen' => $dokumenName,
                    'tanggal_update_terbaru' => now(),
                ]);

                // Apply the approved changes to s_transaksi_2 (same canonical source used by perubahan-data-dosen).
                $this->applyPerubahanDataDosenApproval($payload, $row, $admin);
            }

            // Jika pengajuan adalah SKPP dan disetujui
            if ($newStatus === 'setuju' && in_array((string) ($row->status ?? ''), ['menunggu_konfirmasi', 'open'], true) && in_array((string) ($row->jenis_pengajuan ?? ''), ['Surat Keterangan', 'Surat SKPP'], true)) {
                $detail = json_decode($row->pesan, true) ?? [];
                // Catat histori dosen
                HistoriDosen::updateOrCreate(
                    ['dokumen' => $row->lampiran],
                    [
                        'nidn' => $row->nidn ?? null,
                        'nuptk' => $row->nuptk ?? null,
                        'nama' => $detail['nama'] ?? '-',
                        'pts' => $detail['pts'] ?? '-',
                        'kode_pt' => $row->kode_pts,
                        'aktif' => '0',
                        'keterangan' => 'Penerbitan ' . $row->jenis_pengajuan,
                        'pengguna' => $admin ? ($admin->email ?? 'Admin') : 'PIC',
                        'no_dokumen_ubah' => $detail['nomor_skpp'] ?? '',
                        'tgl_dokumen_ubah' => now()->format('Y-m-d'),
                        'alasan_perubahan' => 'Penerbitan ' . $row->jenis_pengajuan . ' Selesai, Dosen dinonaktifkan',
                        'tanggal_update_terbaru' => now(),
                    ]
                );

                // Nonaktifkan dosen di s_transaksi_2
                Transaksi::where(function ($q) use ($row) {
                    if (!empty($row->nidn)) $q->where('NIDN', $row->nidn);
                    if (!empty($row->nuptk)) $q->orWhere('NUPTK', $row->nuptk);
                })->update(['Aktif' => '0']);
            }

            $affected = DB::table('i_complain')->where('id', $id)->update([
                'status' => $newStatus,
                'admin_balasan' => $validated['admin_balasan'] ?? null,
                'handled_by' => $admin ? $admin->id : null,
                'handled_at' => $now,
                'updated_at' => $now,
            ]);

            if ($affected === 0) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Complain berhasil diperbarui.']);
        } catch (\Throwable $e) {
            try {
                DB::rollBack();
            } catch (\Throwable $ignored) {
            }
            $alias = ErrorAlias::fromThrowable($e, 'ADM-COMPLAIN');
            $adminUser = isset($admin) ? $admin : null;
            Log::error('ComplainAdminController: update complain failed', [
                'alias' => $alias['code'],
                'id' => $id ?? null,
                'admin_id' => $adminUser ? ($adminUser->id ?? null) : null,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan. (Kode: ' . $alias['code'] . ')',
                'code' => $alias['code'],
            ], 500);
        }
    }
}
