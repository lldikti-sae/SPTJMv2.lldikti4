<?php

namespace App\Http\Controllers;

use App\Helpers\ErrorAlias;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SinkronisasiController extends Controller
{
    private function wilayahScope(): ?string
    {
        try {
            $user = Auth::user();
            if (!$user) return null;
            if (method_exists($user, 'isPIC') && $user->isPIC()) {
                $email = trim((string) ($user->email ?? ''));
                return $email !== '' ? $email : null;
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }

    private function applyWilayahFilter($query)
    {
        $wilayah = $this->wilayahScope();
        if ($wilayah) {
            $query->where('pemegang_wilayah', $wilayah);
        }
        return $query;
    }

    private function parseIndoDatePreferDMY(string $value): ?Carbon
    {
        $tmtTrim = trim($value);

        if ($tmtTrim === '') {
            return null;
        }

        // Prefer day-first parsing for dd/mm/YYYY or dd-mm-YYYY to avoid US-style parsing
        if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $tmtTrim)) {
            try {
                return Carbon::createFromFormat('d/m/Y', $tmtTrim);
            } catch (\Exception $e) {
                // fallthrough
            }
        }

        if (preg_match('/^\d{1,2}-\d{1,2}-\d{4}$/', $tmtTrim)) {
            try {
                return Carbon::createFromFormat('d-m-Y', $tmtTrim);
            } catch (\Exception $e) {
                // fallthrough
            }
        }

        // Fallback to flexible parse, then explicit formats
        try {
            return Carbon::parse($tmtTrim);
        } catch (\Exception $e) {
            $formats = ['d/m/Y', 'd-m-Y', 'Y-m-d', 'Y/m/d', 'm/Y', 'm-Y'];
            foreach ($formats as $fmt) {
                try {
                    return Carbon::createFromFormat($fmt, $tmtTrim);
                } catch (\Exception $ex) {
                    // continue
                }
            }
        }

        return null;
    }

    private function normalizeSerdosJabatan(?string $jabatan): ?string
    {
        $text = strtolower(trim((string) $jabatan));
        if ($text === '') {
            return null;
        }

        // Map variations into canonical keys used in c_grade_serdos
        if (strpos($text, 'guru besar 1050') !== false) {
            return 'Guru Besar 1050';
        }
        if (strpos($text, 'guru besar') !== false || strpos($text, 'profesor') !== false) {
            return 'Guru Besar';
        }
        if (strpos($text, 'lektor kepala') !== false) {
            return 'Lektor Kepala';
        }
        if (strpos($text, 'lektor') !== false) {
            return 'Lektor';
        }
        if (strpos($text, 'asisten ahli') !== false) {
            return 'Asisten Ahli';
        }

        return null;
    }

    private function resolveGolonganFromGradeSerdos(?string $jabatanKey, int $masaKerja): ?string
    {
        if (!$jabatanKey) {
            return null;
        }

        // Pure DB mapping (range-based: masa_kerja_bawah..masa_kerja_atas)
        try {
            $gol = DB::table('c_grade_serdos')
                ->where('jabatan', $jabatanKey)
                ->where('masa_kerja_bawah', '<=', $masaKerja)
                ->where('masa_kerja_atas', '>=', $masaKerja)
                ->orderByDesc('masa_kerja_bawah')
                ->orderBy('masa_kerja_atas')
                ->value('golongan');
            return $gol ?: null;
        } catch (\Throwable $e) {
            // If DB errors occur, keep behavior safe (no hardcoded fallback)
            return null;
        }
    }

    private function isGuruBesarAtauProfesor($jabatan): bool
    {
        $text = strtolower(trim((string) $jabatan));
        if ($text === '') {
            return false;
        }
        return strpos($text, 'guru besar') !== false || strpos($text, 'profesor') !== false;
    }

    private function roleScopePrefix(): string
    {
        try {
            $user = Auth::user();
            if ($user) {
                if (method_exists($user, 'isPIC') && $user->isPIC()) {
                    return 'PIC';
                }
                if (isset($user->role) && strtolower((string)$user->role) === 'admin') {
                    return 'ADM';
                }
            }
        } catch (\Throwable $e) {
            // fallback
        }
        return 'SYN';
    }

    private function aliasScope(string $suffix): string
    {
        return $this->roleScopePrefix() . '-' . $suffix;
    }

    public function index()
    {
        return view('admin.sinkronisasi');
    }

    /**
     * Search Golongan / Masa Kerja data for given NIDN or NUPTK.
     * Returns JSON with main fields and arrays for Jabatan/Gol/Tahun per month (1..12).
     */
    public function golmasaSearch(Request $request)
    {
        $request->validate([
            'NIDN' => 'required|string',
        ]);

        $nidn = trim($request->input('NIDN'));
        try {
            $tahunVersi = session('tahun') ?? Carbon::now()->year;

            $wilayah = $this->wilayahScope();

            // Prefer active Tahun_Versi to keep UI consistent.
            $rowQ = DB::table('s_transaksi_2')
                ->whereRaw('(TRIM(NIDN) = ? OR TRIM(NUPTK) = ?)', [$nidn, $nidn])
                ->where('Tahun_Versi', $tahunVersi);
            if ($wilayah) {
                $rowQ->where('pemegang_wilayah', $wilayah);
            }
            $row = $rowQ->first();

            // Fallback: if no row for active year, use latest available year for this identifier.
            if (!$row) {
                $rowQ2 = DB::table('s_transaksi_2')
                    ->whereRaw('(TRIM(NIDN) = ? OR TRIM(NUPTK) = ?)', [$nidn, $nidn]);
                if ($wilayah) {
                    $rowQ2->where('pemegang_wilayah', $wilayah);
                }
                $row = $rowQ2->orderByDesc('Tahun_Versi')->first();
                if ($row && isset($row->Tahun_Versi)) {
                    $tahunVersi = (int) $row->Tahun_Versi;
                }
            }

            if (!$row) {
                return response()->json([ 'status' => 'not_found', 'message' => 'Data tidak ditemukan.' ], 404);
            }

            // TMT fields may be empty on some Tahun_Versi rows; try to fetch them from another year.
            $tmtRow = null;
            $tryFetchTmtRow = function () use ($nidn, $wilayah, &$tmtRow) {
                if ($tmtRow) return;
                $tmtQ = DB::table('s_transaksi_2')
                    ->whereRaw('(TRIM(NIDN) = ? OR TRIM(NUPTK) = ?)', [$nidn, $nidn])
                    ->when($wilayah, function ($q) use ($wilayah) {
                        return $q->where('pemegang_wilayah', $wilayah);
                    })
                    ->where(function ($q) {
                        $q->whereNotNull('TMT_JAD_Pertama')
                          ->where('TMT_JAD_Pertama', '!=', '');
                    })
                    ->orWhere(function ($q) use ($nidn) {
                        // re-apply identifier in this OR group to avoid widening results
                        $q->whereRaw('(TRIM(NIDN) = ? OR TRIM(NUPTK) = ?)', [$nidn, $nidn])
                          ->whereNotNull('TMT_Inpassing_Akhir')
                          ->where('TMT_Inpassing_Akhir', '!=', '');
                    })
                    ->orderByDesc('Tahun_Versi')
                    ;
                $tmtRow = $tmtQ->first();
            };

            // build arrays for 1..12
            $jabatan = [];
            $gol = [];
            $masa = [];
            for ($i = 1; $i <= 12; $i++) {
                $jabField = 'Jabatan' . $i;
                $golField = 'Gol' . $i;
                $tahunField = 'Tahun' . $i;
                $jabatan[] = isset($row->{$jabField}) ? $row->{$jabField} : null;
                $gol[] = isset($row->{$golField}) ? $row->{$golField} : null;
                $masa[] = isset($row->{$tahunField}) ? $row->{$tahunField} : null;
            }

            // Preview expected values (no DB update)
            $expectedMasaByIndex = array_fill(0, 12, null);
            $expectedGolByIndex = array_fill(0, 12, null);
            $masaMismatch = array_fill(0, 12, false);
            $golMismatch = array_fill(0, 12, false);

            // Prefer the same fallback as sync (TMT_JAD_Pertama, else TMT_Inpassing_Akhir)
            $tmtPertamaRaw = $row->TMT_JAD_Pertama ?? ($row->TMT_Inpassing_Akhir ?? null);
            if ($tmtPertamaRaw !== null && trim((string) $tmtPertamaRaw) === '') {
                $tmtPertamaRaw = null;
            }
            if (!$tmtPertamaRaw) {
                $tryFetchTmtRow();
                if ($tmtRow) {
                    $tmtPertamaRaw = $tmtRow->TMT_JAD_Pertama ?? ($tmtRow->TMT_Inpassing_Akhir ?? null);
                }
            }
            $tmtPertama = $tmtPertamaRaw ? $this->parseIndoDatePreferDMY((string) $tmtPertamaRaw) : null;
            if ($tmtPertama) {
                $startMonth = (int) $tmtPertama->month;
                $startYear = (int) $tmtPertama->year;
                for ($m = 1; $m <= 12; $m++) {
                    $years = (int) $tahunVersi - $startYear;
                    if ($m < $startMonth) {
                        $years -= 1;
                    }
                    if ($years < 0) $years = 0;
                    $expectedMasaByIndex[$m - 1] = (string) $years;
                }
            }

            $tmtAkhirRaw = $row->TMT_JAD_Akhir ?? null;
            if ($tmtAkhirRaw !== null && trim((string) $tmtAkhirRaw) === '') {
                $tmtAkhirRaw = null;
            }
            if (!$tmtAkhirRaw) {
                // fetch another year row that has TMT_JAD_Akhir
                $tmtAkhirRow = DB::table('s_transaksi_2')
                    ->whereRaw('(TRIM(NIDN) = ? OR TRIM(NUPTK) = ?)', [$nidn, $nidn])
                    ->when($wilayah, function ($q) use ($wilayah) {
                        return $q->where('pemegang_wilayah', $wilayah);
                    })
                    ->whereNotNull('TMT_JAD_Akhir')
                    ->where('TMT_JAD_Akhir', '!=', '')
                    ->orderByDesc('Tahun_Versi')
                    ->first();
                if ($tmtAkhirRow) {
                    $tmtAkhirRaw = $tmtAkhirRow->TMT_JAD_Akhir ?? null;
                }
            }
            $tmtAkhir = $tmtAkhirRaw ? $this->parseIndoDatePreferDMY((string) $tmtAkhirRaw) : null;
            if ($tmtAkhir) {
                $akhirMonth = (int) $tmtAkhir->month;
                $akhirYear = (int) $tmtAkhir->year;
                for ($m = 1; $m <= 12; $m++) {
                    $masaAkhir = (int) $tahunVersi - $akhirYear;
                    if ($m < $akhirMonth) {
                        $masaAkhir -= 1;
                    }
                    if ($masaAkhir < 0) $masaAkhir = 0;

                    $jabField = 'Jabatan' . $m;
                    $jabatanKey = $this->normalizeSerdosJabatan($row->{$jabField} ?? null);
                    $expectedGolByIndex[$m - 1] = $this->resolveGolonganFromGradeSerdos($jabatanKey, $masaAkhir);
                }
            }

            for ($i = 0; $i < 12; $i++) {
                $currentMasa = trim((string) ($masa[$i] ?? ''));
                $expectedMasa = $expectedMasaByIndex[$i];
                if ($expectedMasa !== null) {
                    $masaMismatch[$i] = $currentMasa !== trim((string) $expectedMasa);
                }

                $currentGol = trim((string) ($gol[$i] ?? ''));
                $expectedGol = $expectedGolByIndex[$i];
                if ($expectedGol !== null) {
                    $golMismatch[$i] = $currentGol !== trim((string) $expectedGol);
                }
            }

            $data = [
                'NIDN' => $row->NIDN ?? null,
                'NUPTK' => $row->NUPTK ?? null,
                'Nama' => $row->Nama ?? null,
                'TMT_Pertama' => $tmtPertamaRaw,
                'TMT_Akhir' => $tmtAkhirRaw,
                'jabatan' => $jabatan,
                'gol' => $gol,
                'masa' => $masa,
                'expected' => [
                    'masa' => $expectedMasaByIndex,
                    'gol' => $expectedGolByIndex,
                ],
                'mismatch' => [
                    'masa' => $masaMismatch,
                    'gol' => $golMismatch,
                ],
            ];

            return response()->json(['status' => 'success', 'row' => $data]);

        } catch (\Throwable $e) {
            $alias = ErrorAlias::fromThrowable($e, $this->aliasScope('SINKRONISASI-GOLMASA-SEARCH'));
            Log::error('SinkronisasiController golmasaSearch failed', [
                'alias' => $alias['code'],
                'nidn' => $nidn,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => $alias['message'],
                'code' => $alias['code'],
            ], 500);
        }
    }

    /**
     * Synchronize masa kerja values (Tahun1..Tahun12) for a given NIDN/NUPTK.
     * Masa kerja is computed from TMT_JAD_Pertama to current date (years, adjusted by month).
     */
    public function syncGolmasa(Request $request)
    {
        $request->validate([
            'NIDN' => 'required|string',
            'update_masa' => 'nullable',
            'update_gol' => 'nullable',
        ]);

        $identifier = trim($request->input('NIDN'));
        try {
            $tahunVersi = session('tahun') ?? Carbon::now()->year;

            // Prefer active Tahun_Versi so sync updates the same row users are viewing.
            $row = DB::table('s_transaksi_2')
                ->whereRaw('(TRIM(NIDN) = ? OR TRIM(NUPTK) = ?)', [$identifier, $identifier])
                ->where('Tahun_Versi', $tahunVersi)
                ->first();

            // Fallback: if no row for active year, use latest year for this identifier.
            if (!$row) {
                $row = DB::table('s_transaksi_2')
                    ->whereRaw('(TRIM(NIDN) = ? OR TRIM(NUPTK) = ?)', [$identifier, $identifier])
                    ->orderByDesc('Tahun_Versi')
                    ->first();
                if ($row && isset($row->Tahun_Versi)) {
                    $tahunVersi = (int) $row->Tahun_Versi;
                }
            }

            if (!$row) {
                return response()->json(['status' => 'not_found', 'message' => 'Data tidak ditemukan.'], 404);
            }

            $updateMasaRequested = $request->has('update_masa')
                ? filter_var($request->input('update_masa'), FILTER_VALIDATE_BOOLEAN)
                : true;
            $updateGolRequested = $request->has('update_gol')
                ? filter_var($request->input('update_gol'), FILTER_VALIDATE_BOOLEAN)
                : true;

            $tmtPertamaRaw = $row->TMT_JAD_Pertama ?? $row->TMT_Inpassing_Akhir ?? null;
            if ($tmtPertamaRaw !== null && trim((string) $tmtPertamaRaw) === '') {
                $tmtPertamaRaw = null;
            }
            if (!$tmtPertamaRaw) {
                $tmtRow = DB::table('s_transaksi_2')
                    ->whereRaw('(TRIM(NIDN) = ? OR TRIM(NUPTK) = ?)', [$identifier, $identifier])
                    ->where(function ($q) {
                        $q->whereNotNull('TMT_JAD_Pertama')
                          ->where('TMT_JAD_Pertama', '!=', '');
                    })
                    ->orWhere(function ($q) use ($identifier) {
                        $q->whereRaw('(TRIM(NIDN) = ? OR TRIM(NUPTK) = ?)', [$identifier, $identifier])
                          ->whereNotNull('TMT_Inpassing_Akhir')
                          ->where('TMT_Inpassing_Akhir', '!=', '');
                    })
                    ->orderByDesc('Tahun_Versi')
                    ->first();
                if ($tmtRow) {
                    $tmtPertamaRaw = $tmtRow->TMT_JAD_Pertama ?? ($tmtRow->TMT_Inpassing_Akhir ?? null);
                }
            }
            $start = $tmtPertamaRaw ? $this->parseIndoDatePreferDMY((string) $tmtPertamaRaw) : null;
            if ($updateMasaRequested && !$start) {
                Log::warning('syncGolmasa: TMT_JAD_Pertama/TMT_Inpassing_Akhir missing or unparseable for ' . $identifier, ['tmt' => $tmtPertamaRaw]);
            }

            $tmtAkhirRaw = $row->TMT_JAD_Akhir ?? null;
            if ($tmtAkhirRaw !== null && trim((string) $tmtAkhirRaw) === '') {
                $tmtAkhirRaw = null;
            }
            if (!$tmtAkhirRaw) {
                $tmtAkhirRow = DB::table('s_transaksi_2')
                    ->whereRaw('(TRIM(NIDN) = ? OR TRIM(NUPTK) = ?)', [$identifier, $identifier])
                    ->whereNotNull('TMT_JAD_Akhir')
                    ->where('TMT_JAD_Akhir', '!=', '')
                    ->orderByDesc('Tahun_Versi')
                    ->first();
                if ($tmtAkhirRow) {
                    $tmtAkhirRaw = $tmtAkhirRow->TMT_JAD_Akhir ?? null;
                }
            }
            $tmtAkhir = $tmtAkhirRaw ? $this->parseIndoDatePreferDMY((string) $tmtAkhirRaw) : null;
            if ($updateGolRequested && !$tmtAkhir) {
                if ($tmtAkhirRaw !== null && trim((string) $tmtAkhirRaw) !== '') {
                    Log::warning('syncGolmasa: unable to parse TMT_JAD_Akhir for ' . $identifier, ['tmt_akhir' => $tmtAkhirRaw]);
                }
            }

            $canUpdateMasa = $updateMasaRequested && (bool) $start;
            $canUpdateGol = $updateGolRequested && (bool) $tmtAkhir;

            if (!$canUpdateMasa && !$canUpdateGol) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tidak ada data yang bisa disinkronisasi. TMT Pertama dan/atau TMT Akhir tidak tersedia.',
                ], 400);
            }

            // compute values per month based on session year (Tahun_Versi)
            $tahunVersi = session('tahun') ?? Carbon::now()->year;
            $startMonth = $start ? (int) $start->month : null;
            $startYear = $start ? (int) $start->year : null;

            $akhirMonth = $tmtAkhir ? (int) $tmtAkhir->month : null;
            $akhirYear = $tmtAkhir ? (int) $tmtAkhir->year : null;

            // Guard: if there is no mismatch in the requested & available parts, block sync
            $hasMismatch = false;
            for ($m = 1; $m <= 12; $m++) {
                if ($canUpdateMasa) {
                    $expectedYears = (int) $tahunVersi - (int) $startYear;
                    if ($m < (int) $startMonth) {
                        $expectedYears -= 1;
                    }
                    if ($expectedYears < 0) $expectedYears = 0;
                    $currentYears = trim((string) ($row->{'Tahun' . $m} ?? ''));
                    if ($currentYears !== (string) $expectedYears) {
                        $hasMismatch = true;
                        break;
                    }
                }

                if ($canUpdateGol) {
                    $masaAkhir = (int) $tahunVersi - (int) $akhirYear;
                    if ($m < (int) $akhirMonth) {
                        $masaAkhir -= 1;
                    }
                    if ($masaAkhir < 0) $masaAkhir = 0;

                    $jabatanKey = $this->normalizeSerdosJabatan($row->{'Jabatan' . $m} ?? null);
                    $expectedGol = $this->resolveGolonganFromGradeSerdos($jabatanKey, $masaAkhir);
                    if ($expectedGol !== null && $expectedGol !== '') {
                        $currentGol = trim((string) ($row->{'Gol' . $m} ?? ''));
                        if ($currentGol !== trim((string) $expectedGol)) {
                            $hasMismatch = true;
                            break;
                        }
                    }
                }
            }

            if (!$hasMismatch) {
                return response()->json([
                    'status' => 'noop',
                    'message' => 'Data sudah benar. Tidak ada yang perlu disinkronisasi.',
                ], 422);
            }

            $masaByMonth = [];
            if ($canUpdateMasa) {
                $update = [];
                for ($m = 1; $m <= 12; $m++) {
                    $years = (int) $tahunVersi - (int) $startYear;
                    if ($m < (int) $startMonth) {
                        $years -= 1;
                    }
                    if ($years < 0) $years = 0;

                    $updateKey = 'Tahun' . $m;
                    $update[$updateKey] = (string) $years;
                    $masaByMonth[$m] = $years;
                }

                DB::table('s_transaksi_2')
                    ->whereRaw('(TRIM(NIDN) = ? OR TRIM(NUPTK) = ?)', [$identifier, $identifier])
                    ->update($update);
            }

            $golByMonth = [];

            if ($canUpdateGol) {
                $golUpdate = [];
                for ($m = 1; $m <= 12; $m++) {
                    $masaAkhir = (int) $tahunVersi - (int) $akhirYear;
                    if ($m < (int) $akhirMonth) {
                        $masaAkhir -= 1;
                    }
                    if ($masaAkhir < 0) $masaAkhir = 0;

                    $jabField = 'Jabatan' . $m;
                    $jabatanKey = $this->normalizeSerdosJabatan($row->{$jabField} ?? null);
                    $golongan = $this->resolveGolonganFromGradeSerdos($jabatanKey, $masaAkhir);

                    // Avoid overwriting DB value with null when jabatan can't be mapped
                    if ($golongan !== null && $golongan !== '') {
                        $golUpdate['Gol' . $m] = $golongan;
                    }
                    $golByMonth[$m] = [
                        'jabatan' => $jabatanKey,
                        'masa_kerja' => $masaAkhir,
                        'golongan' => $golongan,
                    ];
                }

                if (!empty($golUpdate)) {
                    $upd = DB::table('s_transaksi_2')
                        ->whereRaw('(TRIM(NIDN) = ? OR TRIM(NUPTK) = ?)', [$identifier, $identifier]);
                    $wilayah = $this->wilayahScope();
                    if ($wilayah) {
                        $upd->where('pemegang_wilayah', $wilayah);
                    }
                    $upd->update($golUpdate);
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => ($canUpdateMasa && $canUpdateGol)
                    ? 'Masa kerja & golongan disinkronisasi.'
                    : ($canUpdateMasa ? 'Masa kerja disinkronisasi.' : 'Golongan disinkronisasi.'),
                'masa_kerja_per_month' => $masaByMonth,
                'gol_per_month' => $golByMonth,
            ]);
        } catch (\Throwable $e) {
            $alias = ErrorAlias::fromThrowable($e, $this->aliasScope('SINKRONISASI-GOLMASA-SYNC'));
            Log::error('SinkronisasiController syncGolmasa failed', [
                'alias' => $alias['code'],
                'identifier' => $identifier,
                'tahun' => session('tahun'),
                'wilayah' => $this->wilayahScope(),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => $alias['message'],
                'code' => $alias['code'],
            ], 500);
        }
    }

    public function process(Request $request)
    {
        $request->validate([
            'NIDN' => 'required|string',
            'pencairan_ke' => 'required|integer|min:1|max:12',
        ]);

        $nidn = trim($request->input('NIDN'));
        $idx = (int) $request->input('pencairan_ke');

        $wilayah = $this->wilayahScope();

        // Gunakan tabel tetap tanpa suffix tahun, filter berdasar Tahun_Versi dari sesi login
        $table = 's_transaksi_2';
        $tahunVersi = session('tahun');
        if (!$tahunVersi) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tahun versi tidak ditemukan di sesi. Silakan login ulang.',
            ], 400);
        }

        $bulanCodes = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',7=>'Jul',8=>'Ags',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];
        $kcField = $bulanCodes[$idx] ?? null;
        if (!$kcField) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bulan tidak valid.',
            ], 400);
        }

        try {
            // 1) Check kode cair
            $kodeCairQ = DB::table($table)
                ->whereRaw('(TRIM(NIDN) = ? OR TRIM(NUPTK) = ?)', [$nidn, $nidn])
                ->where('Tahun_Versi', $tahunVersi)
                ;
            if ($wilayah) {
                $kodeCairQ->where('pemegang_wilayah', $wilayah);
            }
            $kodeCair = $kodeCairQ->value($kcField);
            if (!$kodeCair) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Kode cair untuk bulan {$kcField} tidak ditemukan untuk NIDN {$nidn}.",
                ], 404);
            }

            // 2) Get Jenis, Gol{idx}, Jabatan{idx}
            $rowInfoQ = DB::table($table)
                ->select('Jenis', DB::raw('Gol' . $idx . ' as Golongan'), DB::raw('Jabatan' . $idx . ' as Jabatan'))
                ->whereRaw('(TRIM(NIDN) = ? OR TRIM(NUPTK) = ?)', [$nidn, $nidn])
                ->where('Tahun_Versi', $tahunVersi)
                ;
            if ($wilayah) {
                $rowInfoQ->where('pemegang_wilayah', $wilayah);
            }
            $rowInfo = $rowInfoQ->first();

            $jenis = $rowInfo->Jenis ?? '';
            $golongan = $rowInfo->Golongan ?? '';
            $jabatan = $rowInfo->Jabatan ?? '';

            // 3) Get tarif pajak dari tabel d_pajak (kolom: status, akumulasi, tarif_pajak)
            $tarif = DB::table('d_pajak')
                ->where('status', $jenis)
                ->where('akumulasi', $golongan)
                ->value('tarif_pajak');
            $tarifPajak = (float) ($tarif ?? 0);

            // Jabatan Guru Besar/Profesor dikenakan pajak TKGB, selain itu pajak TKGB seharusnya 0
            $jabatanKenaPajakTKGB = $this->isGuruBesarAtauProfesor($jabatan);

            // 4) Get TPD/TKGB
            $tpdField = 'TPD' . $idx;
            $tkgbField = 'TKGB' . $idx;
            $rowNomQ = DB::table($table)
                ->select($tpdField . ' as tpd', $tkgbField . ' as tkgb')
                ->whereRaw('(TRIM(NIDN) = ? OR TRIM(NUPTK) = ?)', [$nidn, $nidn])
                ->where('Tahun_Versi', $tahunVersi)
                ;
            if ($wilayah) {
                $rowNomQ->where('pemegang_wilayah', $wilayah);
            }
            $rowNom = $rowNomQ->first();

            $tunjanganTPD = (float) ($rowNom->tpd ?? 0);
            $tunjanganTKGB = (float) ($rowNom->tkgb ?? 0);

            if ($tunjanganTPD == 0.0) {
                return response()->json([
                    'status' => 'warning',
                    'message' => "Kode cair ada tetapi nilai TPD untuk NIDN {$nidn} kosong atau nol pada bulan {$idx}.",
                ]);
            }

            // 5) Compute pajak & bersih
            $fieldPajakTPD = 'pajak' . $tpdField;           // pajakTPD{idx}
            $fieldNilaiPajakTPD = 'nilaiPajak' . $tpdField; // nilaiPajakTPD{idx}
            $fieldBersihTPD = 'bersih' . $tpdField;         // bersihTPD{idx}

            $fieldPajakTKGB = 'pajak' . $tkgbField;         // pajakTKGB{idx}
            $fieldNilaiPajakTKGB = 'nilaiPajak' . $tkgbField; // nilaiPajakTKGB{idx}
            $fieldBersihTKGB = 'bersih' . $tkgbField;       // bersihTKGB{idx}

            $pajakTPD = $tarifPajak;
            $nilaiPajakTPD = $tunjanganTPD * $pajakTPD;
            $bersihTPD = $tunjanganTPD - $nilaiPajakTPD;

            if ($jabatanKenaPajakTKGB) {
                $pajakTKGB = $tarifPajak;
                $nilaiPajakTKGB = $tunjanganTKGB * $pajakTKGB;
            } else {
                $pajakTKGB = 0.0;
                $nilaiPajakTKGB = 0.0;
            }
            $bersihTKGB = $tunjanganTKGB - $nilaiPajakTKGB;

            // 6) Ambil nilai sebelum update
            $beforeQ = DB::table($table)
                ->select([
                    $tpdField . ' as tpd',
                    $tkgbField . ' as tkgb',
                    $fieldPajakTPD,
                    $fieldNilaiPajakTPD,
                    $fieldBersihTPD,
                    $fieldPajakTKGB,
                    $fieldNilaiPajakTKGB,
                    $fieldBersihTKGB,
                ])
                ->whereRaw('(TRIM(NIDN) = ? OR TRIM(NUPTK) = ?)', [$nidn, $nidn])
                ->where('Tahun_Versi', $tahunVersi)
                ;
            if ($wilayah) {
                $beforeQ->where('pemegang_wilayah', $wilayah);
            }
            $before = $beforeQ->first();

            // 7) Update
            $updQ = DB::table($table)
                ->whereRaw('(TRIM(NIDN) = ? OR TRIM(NUPTK) = ?)', [$nidn, $nidn])
                ->where('Tahun_Versi', $tahunVersi)
                ;
            if ($wilayah) {
                $updQ->where('pemegang_wilayah', $wilayah);
            }
            $updQ->update([
                    $fieldPajakTPD => $pajakTPD,
                    $fieldNilaiPajakTPD => $nilaiPajakTPD,
                    $fieldBersihTPD => $bersihTPD,
                    $fieldPajakTKGB => $pajakTKGB,
                    $fieldNilaiPajakTKGB => $nilaiPajakTKGB,
                    $fieldBersihTKGB => $bersihTKGB,
                ]);

            // 8) Ambil nilai sesudah update
            $afterQ = DB::table($table)
                ->select([
                    $tpdField . ' as tpd',
                    $tkgbField . ' as tkgb',
                    $fieldPajakTPD,
                    $fieldNilaiPajakTPD,
                    $fieldBersihTPD,
                    $fieldPajakTKGB,
                    $fieldNilaiPajakTKGB,
                    $fieldBersihTKGB,
                ])
                ->whereRaw('(TRIM(NIDN) = ? OR TRIM(NUPTK) = ?)', [$nidn, $nidn])
                ->where('Tahun_Versi', $tahunVersi)
                ;
            if ($wilayah) {
                $afterQ->where('pemegang_wilayah', $wilayah);
            }
            $after = $afterQ->first();
            
            // 9) Susun ringkasan perubahan
            $fields = [
                'tpd' => 'TPD',
                'tkgb' => 'TKGB',
                $fieldPajakTPD => 'Pajak TPD',
                $fieldNilaiPajakTPD => 'Nilai Pajak TPD',
                $fieldBersihTPD => 'Bersih TPD',
                $fieldPajakTKGB => 'Pajak TKGB',
                $fieldNilaiPajakTKGB => 'Nilai Pajak TKGB',
                $fieldBersihTKGB => 'Bersih TKGB',
            ];

            $changes = [];
            // map before/after values into associative arrays for easier compare
            $beforeArr = (array) $before;
            $afterArr = (array) $after;

            // TPD/TKGB fields are keyed by aliases (tpd/tkgb) in select
            $compareKeys = [
                'tpd', 'tkgb',
                $fieldPajakTPD, $fieldNilaiPajakTPD, $fieldBersihTPD,
                $fieldPajakTKGB, $fieldNilaiPajakTKGB, $fieldBersihTKGB,
            ];

            foreach ($compareKeys as $key) {
                $beforeVal = array_key_exists($key, $beforeArr) ? $beforeArr[$key] : null;
                $afterVal = array_key_exists($key, $afterArr) ? $afterArr[$key] : null;
                // Normalize numeric values
                if (is_numeric($beforeVal) || is_numeric($afterVal)) {
                    $beforeVal = $beforeVal !== null ? (float) $beforeVal : null;
                    $afterVal = $afterVal !== null ? (float) $afterVal : null;
                }

                // Only include if at least one side has a value (or zero)
                $hasValue = ($beforeVal !== null) || ($afterVal !== null);
                if ($hasValue) {
                    $label = $key === 'tpd' ? 'TPD' : ($key === 'tkgb' ? 'TKGB' : ($fields[$key] ?? $key));
                    // If values are identical, show 'after' as null to render '-'
                    $displayAfter = ($beforeVal !== $afterVal) ? $afterVal : null;
                    $changes[] = [
                        'field' => $key,
                        'label' => $label,
                        'before' => $beforeVal,
                        'after' => $displayAfter,
                    ];
                }
            }

        } catch (\Throwable $e) {
            $alias = ErrorAlias::fromThrowable($e, $this->aliasScope('SINKRONISASI-PROCESS'));
            Log::error('SinkronisasiController process failed', [
                'alias' => $alias['code'],
                'nidn' => $nidn,
                'idx' => $idx,
                'tahun' => session('tahun'),
                'wilayah' => $this->wilayahScope(),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => $alias['message'],
                'code' => $alias['code'],
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => "Sinkronisasi selesai untuk NIDN atau NUPTK {$nidn}.",
            'changes' => $changes,
            'before' => $beforeArr,
            'after' => $afterArr,
        ]);
    }

    /**
     * Cek pajak yang tidak sesuai untuk semua bulan aktif pada satu NIDN.
     */
    public function checkMismatch(Request $request)
    {
        @set_time_limit(0);
        @ini_set('max_execution_time', '0');

        $table = 's_transaksi_2';
        $tahunVersi = session('tahun');
        if (!$tahunVersi) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tahun versi tidak ditemukan di sesi. Silakan login ulang.',
            ], 400);
        }

        $months = [
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

        $bulanCodes = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',7=>'Jul',8=>'Ags',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];

        try {
            $mismatches = [];

            $wilayah = $this->wilayahScope();

            // Cache seluruh tarif pajak di memori untuk menghindari query berulang
            $pajakRows = DB::table('d_pajak')
                ->select('status', 'akumulasi', 'tarif_pajak')
                ->get();
            $tarifMap = [];
            foreach ($pajakRows as $p) {
                // Normalisasi ke lowercase + trim agar tidak sensitif huruf besar/kecil
                $status = strtolower(trim((string) ($p->status ?? '')));
                $akum = strtolower(trim((string) ($p->akumulasi ?? '')));
                if (!isset($tarifMap[$status])) {
                    $tarifMap[$status] = [];
                }
                $tarifMap[$status][$akum] = (float) ($p->tarif_pajak ?? 0);
            }

            $rowsQ = DB::table($table)
                ->where('Tahun_Versi', $tahunVersi);
            if ($wilayah) {
                $rowsQ->where('pemegang_wilayah', $wilayah);
            }
            $rows = $rowsQ->get();

            // Helper selisih dengan toleransi kecil
            // Helper selisih dengan toleransi kecil
            $diff = function ($a, $b) {
                return abs((float)$a - (float)$b) > 0.01;
            };

            foreach ($rows as $row) {
                $nidnOnly = trim((string) ($row->NIDN ?? ''));
                $nuptkOnly = trim((string) ($row->NUPTK ?? ''));
                $identifier = $nidnOnly !== '' ? $nidnOnly : $nuptkOnly;
                if ($identifier === '') {
                    continue;
                }

                // Extract possible name/kode_pt/pemegang_wilayah fields with fallbacks
                $namaVal = trim((string)($row->Nama ?? $row->nama ?? $row->Nama_Pegawai ?? $row->nama_dosen ?? ''));
                $kodePtVal = trim((string)($row->Kode_PT ?? $row->kode_pt ?? $row->Kode_PTS ?? $row->kode_pts ?? ''));
                $pemegangWilayahVal = trim((string)($row->Pemegang_Wilayah ?? $row->pemegang_wilayah ?? ''));

                foreach ($months as $idx => $label) {
                    $kcField = $bulanCodes[$idx] ?? null;
                    if (!$kcField) {
                        continue;
                    }

                    // Lewati bulan tanpa kode cair
                    $kodeCair = $row->{$kcField} ?? null;
                    if (!$kodeCair) {
                        continue;
                    }

                    $tpdField = 'TPD' . $idx;
                    $tkgbField = 'TKGB' . $idx;

                    $tpd = (float) ($row->{$tpdField} ?? 0);
                    $tkgb = (float) ($row->{$tkgbField} ?? 0);

                    // Jika keduanya nol, lewati agar tabel fokus pada data signifikan
                    if ($tpd == 0.0 && $tkgb == 0.0) {
                        continue;
                    }

                    $golField = 'Gol' . $idx;
                    $golongan = $row->{$golField} ?? '';
                    $jabatanField = 'Jabatan' . $idx;
                    $jabatan = $row->{$jabatanField} ?? '';
                    $jenis = $row->Jenis ?? '';

                    $tarifPajak = 0.0;
                    // Kunci juga dinormalisasi ke lowercase + trim
                    $jenisKey = strtolower(trim((string) $jenis));
                    $golKey = strtolower(trim((string) $golongan));
                    if (isset($tarifMap[$jenisKey][$golKey])) {
                        $tarifPajak = $tarifMap[$jenisKey][$golKey];
                    }

                    // Field pajak sesuai skema yang sudah digunakan di method process
                    $fieldPajakTPD = 'pajak' . $tpdField;
                    $fieldNilaiPajakTPD = 'nilaiPajak' . $tpdField;
                    $fieldBersihTPD = 'bersih' . $tpdField;

                    $fieldPajakTKGB = 'pajak' . $tkgbField;
                    $fieldNilaiPajakTKGB = 'nilaiPajak' . $tkgbField;
                    $fieldBersihTKGB = 'bersih' . $tkgbField;

                    $pajakTPD_db = (float) ($row->{$fieldPajakTPD} ?? 0);
                    $nilaiPajakTPD_db = (float) ($row->{$fieldNilaiPajakTPD} ?? 0);
                    $bersihTPD_db = (float) ($row->{$fieldBersihTPD} ?? 0);

                    $pajakTKGB_db = (float) ($row->{$fieldPajakTKGB} ?? 0);
                    $nilaiPajakTKGB_db = (float) ($row->{$fieldNilaiPajakTKGB} ?? 0);
                    $bersihTKGB_db = (float) ($row->{$fieldBersihTKGB} ?? 0);

                    // Aturan pajak berdasarkan "Pajak Seharusnya" (tarifPajak)
                    // Pajak TPD: HARUS sama dengan tarifPajak (jika tidak sama -> mismatch)
                    // Pajak TKGB: jika jabatan BUKAN Guru Besar/Profesor maka seharusnya 0, jika iya maka seharusnya = tarifPajak
                    $pajakTPD_mismatch = $diff($pajakTPD_db, $tarifPajak);

                    $tarifTKGBSeharusnya = $this->isGuruBesarAtauProfesor($jabatan) ? $tarifPajak : 0.0;
                    $pajakTKGB_mismatch = $diff($pajakTKGB_db, $tarifTKGBSeharusnya);

                    // Nilai pajak seharusnya berdasarkan tarif yang berlaku
                    $nilaiTPD_calc = $tpd * $tarifPajak;
                    $nilaiTKGB_calc = $tkgb * $tarifTKGBSeharusnya;

                    $nilaiTPD_mismatch = $diff($nilaiPajakTPD_db, $nilaiTPD_calc);
                    $nilaiTKGB_mismatch = $diff($nilaiPajakTKGB_db, $nilaiTKGB_calc);

                    // Bersih seharusnya = jumlah - nilai pajak (sesuai tarif)
                    $bersihTPD_calc = $tpd - $nilaiTPD_calc;
                    $bersihTKGB_calc = $tkgb - $nilaiTKGB_calc;

                    $bersihTPD_mismatch = $diff($bersihTPD_db, $bersihTPD_calc);
                    $bersihTKGB_mismatch = $diff($bersihTKGB_db, $bersihTKGB_calc);

                    $hasMismatch =
                        $pajakTPD_mismatch ||
                        $pajakTKGB_mismatch ||
                        $nilaiTPD_mismatch ||
                        $nilaiTKGB_mismatch ||
                        $bersihTPD_mismatch ||
                        $bersihTKGB_mismatch;

                    if ($hasMismatch) {
                        $mismatches[] = [
                            'nidn' => $nidnOnly,
                            'nuptk' => $nuptkOnly,
                            'identifier' => $identifier,
                            'nama' => $namaVal,
                            'Kode_PT' => $kodePtVal,
                            'Pemegang_Wilayah' => $pemegangWilayahVal,
                            'jenis' => $jenis,
                            'bulan_index' => $idx,
                            'bulan_label' => $label,
                            'pajak_tpd_db' => $pajakTPD_db,
                            'pajak_tkgb_db' => $pajakTKGB_db,
                            'pajak_tpd_mismatch' => $pajakTPD_mismatch,
                            'pajak_tkgb_mismatch' => $pajakTKGB_mismatch,
                            'nilai_pajak_tpd_db' => $nilaiPajakTPD_db,
                            'nilai_pajak_tpd_calc' => $nilaiTPD_calc,
                            'nilai_tpd_mismatch' => $nilaiTPD_mismatch,
                            'nilai_pajak_tkgb_db' => $nilaiPajakTKGB_db,
                            'nilai_pajak_tkgb_calc' => $nilaiTKGB_calc,
                            'nilai_tkgb_mismatch' => $nilaiTKGB_mismatch,
                            'bersih_tpd_db' => $bersihTPD_db,
                            'bersih_tpd_calc' => $bersihTPD_calc,
                            'bersih_tpd_mismatch' => $bersihTPD_mismatch,
                            'bersih_tkgb_db' => $bersihTKGB_db,
                            'bersih_tkgb_calc' => $bersihTKGB_calc,
                            'bersih_tkgb_mismatch' => $bersihTKGB_mismatch,
                        ];
                    }
                }
            }

        } catch (\Throwable $e) {
            $alias = ErrorAlias::fromThrowable($e, $this->aliasScope('SINKRONISASI-PAJAK-MISMATCH'));
            Log::error('SinkronisasiController checkMismatch failed', [
                'alias' => $alias['code'],
                'tahun' => session('tahun'),
                'wilayah' => $this->wilayahScope(),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => $alias['message'],
                'code' => $alias['code'],
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Pengecekan pajak selesai.',
            'mismatches' => $mismatches,
        ]);
    }

    /**
     * Detail pajak untuk satu NIDN, menampilkan semua bulan dan menandai mana yang tidak sesuai.
     */
    public function detailMismatch(Request $request)
    {
        @set_time_limit(0);
        @ini_set('max_execution_time', '0');

        $request->validate([
            'NIDN' => 'required|string',
        ]);

        $nidn = trim($request->input('NIDN'));

        $wilayah = $this->wilayahScope();

        $table = 's_transaksi_2';
        $tahunVersi = session('tahun');
        if (!$tahunVersi) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tahun versi tidak ditemukan di sesi. Silakan login ulang.',
            ], 400);
        }

        $months = [
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

        $bulanCodes = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',7=>'Jul',8=>'Ags',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];

        try {
            $rowQ = DB::table($table)
                ->whereRaw('(TRIM(NIDN) = ? OR TRIM(NUPTK) = ?)', [$nidn, $nidn])
                ->where('Tahun_Versi', $tahunVersi);
            if ($wilayah) {
                $rowQ->where('pemegang_wilayah', $wilayah);
            }
            $row = $rowQ->first();

            if (!$row) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Data tidak ditemukan untuk NIDN atau NUPTK {$nidn}.",
                ], 404);
            }

                // Helper selisih dengan toleransi kecil
                $diff = function ($a, $b) {
                    return abs((float)$a - (float)$b) > 0.01;
                };

            $detail = [];

            // Cache seluruh tarif pajak di memori untuk menghindari query berulang
            $pajakRows = DB::table('d_pajak')
                ->select('status', 'akumulasi', 'tarif_pajak')
                ->get();
            $tarifMap = [];
            foreach ($pajakRows as $p) {
                // Normalisasi ke lowercase + trim agar tidak sensitif huruf besar/kecil
                $status = strtolower(trim((string) ($p->status ?? '')));
                $akum = strtolower(trim((string) ($p->akumulasi ?? '')));
                if (!isset($tarifMap[$status])) {
                    $tarifMap[$status] = [];
                }
                $tarifMap[$status][$akum] = (float) ($p->tarif_pajak ?? 0);
            }

            foreach ($months as $idx => $label) {
                $kcField = $bulanCodes[$idx] ?? null;
                if (!$kcField) {
                    continue;
                }

                $kodeCair = $row->{$kcField} ?? null;

                $tpdField = 'TPD' . $idx;
                $tkgbField = 'TKGB' . $idx;

                $tpd = (float) ($row->{$tpdField} ?? 0);
                $tkgb = (float) ($row->{$tkgbField} ?? 0);

                $golField = 'Gol' . $idx;
                $golongan = $row->{$golField} ?? '';
                $jabatanField = 'Jabatan' . $idx;
                $jabatan = $row->{$jabatanField} ?? '';
                $jenis = $row->Jenis ?? '';

                $tarifPajak = 0.0;
                // Kunci juga dinormalisasi ke lowercase + trim
                $jenisKey = strtolower(trim((string) $jenis));
                $golKey = strtolower(trim((string) $golongan));
                if (isset($tarifMap[$jenisKey][$golKey])) {
                    $tarifPajak = $tarifMap[$jenisKey][$golKey];
                }

                $fieldPajakTPD = 'pajak' . $tpdField;
                $fieldNilaiPajakTPD = 'nilaiPajak' . $tpdField;
                $fieldBersihTPD = 'bersih' . $tpdField;

                $fieldPajakTKGB = 'pajak' . $tkgbField;
                $fieldNilaiPajakTKGB = 'nilaiPajak' . $tkgbField;
                $fieldBersihTKGB = 'bersih' . $tkgbField;

                $pajakTPD_db = (float) ($row->{$fieldPajakTPD} ?? 0);
                $nilaiPajakTPD_db = (float) ($row->{$fieldNilaiPajakTPD} ?? 0);
                $bersihTPD_db = (float) ($row->{$fieldBersihTPD} ?? 0);

                $pajakTKGB_db = (float) ($row->{$fieldPajakTKGB} ?? 0);
                $nilaiPajakTKGB_db = (float) ($row->{$fieldNilaiPajakTKGB} ?? 0);
                $bersihTKGB_db = (float) ($row->{$fieldBersihTKGB} ?? 0);

                // Aturan pajak berdasarkan "Pajak Seharusnya" (tarifPajak)
                // Pajak TPD: HARUS sama dengan tarifPajak (jika tidak sama -> mismatch)
                // Pajak TKGB: jika jabatan BUKAN Guru Besar/Profesor maka seharusnya 0, jika iya maka seharusnya = tarifPajak
                $pajakTPD_mismatch = $diff($pajakTPD_db, $tarifPajak);

                $tarifTKGBSeharusnya = $this->isGuruBesarAtauProfesor($jabatan) ? $tarifPajak : 0.0;
                $pajakTKGB_mismatch = $diff($pajakTKGB_db, $tarifTKGBSeharusnya);

                // Nilai pajak seharusnya berdasarkan tarif yang berlaku
                $nilaiTPD_calc = $tpd * $tarifPajak;
                $nilaiTKGB_calc = $tkgb * $tarifTKGBSeharusnya;

                $nilaiTPD_mismatch = $diff($nilaiPajakTPD_db, $nilaiTPD_calc);
                $nilaiTKGB_mismatch = $diff($nilaiPajakTKGB_db, $nilaiTKGB_calc);

                // Bersih seharusnya = jumlah - nilai pajak (sesuai tarif)
                $bersihTPD_calc = $tpd - $nilaiTPD_calc;
                $bersihTKGB_calc = $tkgb - $nilaiTKGB_calc;

                $bersihTPD_mismatch = $diff($bersihTPD_db, $bersihTPD_calc);
                $bersihTKGB_mismatch = $diff($bersihTKGB_db, $bersihTKGB_calc);

                $hasMismatch =
                    $pajakTPD_mismatch ||
                    $pajakTKGB_mismatch ||
                    $nilaiTPD_mismatch ||
                    $nilaiTKGB_mismatch ||
                    $bersihTPD_mismatch ||
                    $bersihTKGB_mismatch;

                $detail[] = [
                    'bulan_index' => $idx,
                    'bulan_label' => $label,
                    'jenis' => $jenis,
                    'tarif_pajak' => $tarifPajak,
                    'tarif_pajak_tkgb_seharusnya' => $tarifTKGBSeharusnya,
                    'jabatan' => $jabatan,
                    'golongan' => $golongan,
                    'kode_cair' => $kodeCair,
                    'tpd' => $tpd,
                    'tkgb' => $tkgb,
                    'pajak_tpd_db' => $pajakTPD_db,
                    'pajak_tkgb_db' => $pajakTKGB_db,
                    'pajak_tpd_mismatch' => $pajakTPD_mismatch,
                    'pajak_tkgb_mismatch' => $pajakTKGB_mismatch,
                    'nilai_pajak_tpd_db' => $nilaiPajakTPD_db,
                    'nilai_pajak_tpd_calc' => $nilaiTPD_calc,
                    'nilai_tpd_mismatch' => $nilaiTPD_mismatch,
                    'nilai_pajak_tkgb_db' => $nilaiPajakTKGB_db,
                    'nilai_pajak_tkgb_calc' => $nilaiTKGB_calc,
                    'nilai_tkgb_mismatch' => $nilaiTKGB_mismatch,
                    'bersih_tpd_db' => $bersihTPD_db,
                    'bersih_tpd_calc' => $bersihTPD_calc,
                    'bersih_tpd_mismatch' => $bersihTPD_mismatch,
                    'bersih_tkgb_db' => $bersihTKGB_db,
                    'bersih_tkgb_calc' => $bersihTKGB_calc,
                    'bersih_tkgb_mismatch' => $bersihTKGB_mismatch,
                    'is_mismatch' => $hasMismatch,
                ];
            }

        } catch (\Throwable $e) {
            $alias = ErrorAlias::fromThrowable($e, $this->aliasScope('SINKRONISASI-PAJAK-DETAIL'));
            Log::error('SinkronisasiController detailMismatch failed', [
                'alias' => $alias['code'],
                'nidn' => $nidn,
                'tahun' => session('tahun'),
                'wilayah' => $this->wilayahScope(),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => $alias['message'],
                'code' => $alias['code'],
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Detail pajak berhasil diambil.',
            'rows' => $detail,
        ]);
    }

    /**
     * Sinkronisasi pajak ke semua bulan untuk satu NIDN.
     */
    public function syncAll(Request $request)
    {
        @set_time_limit(0);
        @ini_set('max_execution_time', '0');

        $table = 's_transaksi_2';
        $tahunVersi = session('tahun');
        if (!$tahunVersi) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tahun versi tidak ditemukan di sesi. Silakan login ulang.',
            ], 400);
        }

        $months = [
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

        $bulanCodes = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',7=>'Jul',8=>'Ags',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];

        $diff = function ($a, $b) {
            return abs((float) $a - (float) $b) > 0.01;
        };

        $wilayah = $this->wilayahScope();

        try {
            // Cache seluruh tarif pajak di memori untuk menghindari query berulang
            $pajakRows = DB::table('d_pajak')
                ->select('status', 'akumulasi', 'tarif_pajak')
                ->get();
            $tarifMap = [];
            foreach ($pajakRows as $p) {
                // Normalisasi ke lowercase + trim agar tidak sensitif huruf besar/kecil
                $status = strtolower(trim((string) ($p->status ?? '')));
                $akum = strtolower(trim((string) ($p->akumulasi ?? '')));
                if (!isset($tarifMap[$status])) {
                    $tarifMap[$status] = [];
                }
                $tarifMap[$status][$akum] = (float) ($p->tarif_pajak ?? 0);
            }

            $totalUpdatedRows = 0;

            // Proses sinkronisasi pajak dalam bentuk chunk agar efisien untuk data besar
            $rowsQ = DB::table($table)
                ->where('Tahun_Versi', $tahunVersi);
            if ($wilayah) {
                $rowsQ->where('pemegang_wilayah', $wilayah);
            }

            $rowsQ
                ->orderBy('No')
                ->chunkById(1000, function ($rows) use (&$totalUpdatedRows, $tarifMap, $months, $bulanCodes, $diff, $table) {
                    foreach ($rows as $row) {
                        $updateData = [];

                        foreach ($months as $idx => $label) {
                            $kcField = $bulanCodes[$idx] ?? null;
                            if (!$kcField) {
                                continue;
                            }

                            $kodeCair = $row->{$kcField} ?? null;
                            if (!$kodeCair) {
                                continue;
                            }

                            $tpdField = 'TPD' . $idx;
                            $tkgbField = 'TKGB' . $idx;

                            $tpd = (float) ($row->{$tpdField} ?? 0);
                            $tkgb = (float) ($row->{$tkgbField} ?? 0);

                            if ($tpd == 0.0 && $tkgb == 0.0) {
                                continue;
                            }

                            $golField = 'Gol' . $idx;
                            $golongan = $row->{$golField} ?? '';
                            $jabatanField = 'Jabatan' . $idx;
                            $jabatan = $row->{$jabatanField} ?? '';
                            $jenis = $row->Jenis ?? '';

                            $tarifPajak = 0.0;
                            // Kunci juga dinormalisasi ke lowercase + trim
                            $jenisKey = strtolower(trim((string) $jenis));
                            $golKey = strtolower(trim((string) $golongan));
                            if (isset($tarifMap[$jenisKey][$golKey])) {
                                $tarifPajak = $tarifMap[$jenisKey][$golKey];
                            }

                            $fieldPajakTPD = 'pajak' . $tpdField;
                            $fieldNilaiPajakTPD = 'nilaiPajak' . $tpdField;
                            $fieldBersihTPD = 'bersih' . $tpdField;

                            $fieldPajakTKGB = 'pajak' . $tkgbField;
                            $fieldNilaiPajakTKGB = 'nilaiPajak' . $tkgbField;
                            $fieldBersihTKGB = 'bersih' . $tkgbField;

                            // Baca nilai pajak/nilai pajak/bersih yang sudah ada di DB
                            $pajakTPD_db = (float) ($row->{$fieldPajakTPD} ?? 0);
                            $nilaiPajakTPD_db = (float) ($row->{$fieldNilaiPajakTPD} ?? 0);
                            $bersihTPD_db = (float) ($row->{$fieldBersihTPD} ?? 0);

                            $pajakTKGB_db = (float) ($row->{$fieldPajakTKGB} ?? 0);
                            $nilaiPajakTKGB_db = (float) ($row->{$fieldNilaiPajakTKGB} ?? 0);
                            $bersihTKGB_db = (float) ($row->{$fieldBersihTKGB} ?? 0);

                            // Aturan pajak berdasarkan "Pajak Seharusnya" (tarifPajak)
                            // Pajak TPD: HARUS sama dengan tarifPajak (jika tidak sama -> mismatch)
                            // Pajak TKGB: jika jabatan BUKAN Guru Besar/Profesor maka seharusnya 0, jika iya maka seharusnya = tarifPajak
                            $pajakTPD_mismatch = $diff($pajakTPD_db, $tarifPajak);

                            $tarifTKGBSeharusnya = $this->isGuruBesarAtauProfesor($jabatan) ? $tarifPajak : 0.0;
                            $pajakTKGB_mismatch = $diff($pajakTKGB_db, $tarifTKGBSeharusnya);

                            // Nilai pajak seharusnya berdasarkan tarif yang berlaku
                            $nilaiTPD_calc = $tpd * $tarifPajak;
                            $nilaiTKGB_calc = $tkgb * $tarifTKGBSeharusnya;

                            $nilaiTPD_mismatch = $diff($nilaiPajakTPD_db, $nilaiTPD_calc);
                            $nilaiTKGB_mismatch = $diff($nilaiPajakTKGB_db, $nilaiTKGB_calc);

                            // Bersih seharusnya = jumlah - nilai pajak (sesuai tarif)
                            $bersihTPD_calc = $tpd - $nilaiTPD_calc;
                            $bersihTKGB_calc = $tkgb - $nilaiTKGB_calc;

                            $bersihTPD_mismatch = $diff($bersihTPD_db, $bersihTPD_calc);
                            $bersihTKGB_mismatch = $diff($bersihTKGB_db, $bersihTKGB_calc);

                            $hasMismatch =
                                $pajakTPD_mismatch ||
                                $pajakTKGB_mismatch ||
                                $nilaiTPD_mismatch ||
                                $nilaiTKGB_mismatch ||
                                $bersihTPD_mismatch ||
                                $bersihTKGB_mismatch;

                            // Hanya lakukan sinkronisasi untuk bulan yang memang mismatch (ditandai merah di UI)
                            if (!$hasMismatch) {
                                continue;
                            }

                            $pajakTPD = $tarifPajak;
                            $nilaiPajakTPD = $tpd * $pajakTPD;
                            $bersihTPD = $tpd - $nilaiPajakTPD;

                            $pajakTKGB = $tarifTKGBSeharusnya;
                            $nilaiPajakTKGB = $tkgb * $pajakTKGB;
                            $bersihTKGB = $tkgb - $nilaiPajakTKGB;

                            $updateData[$fieldPajakTPD] = $pajakTPD;
                            $updateData[$fieldNilaiPajakTPD] = $nilaiPajakTPD;
                            $updateData[$fieldBersihTPD] = $bersihTPD;

                            $updateData[$fieldPajakTKGB] = $pajakTKGB;
                            $updateData[$fieldNilaiPajakTKGB] = $nilaiPajakTKGB;
                            $updateData[$fieldBersihTKGB] = $bersihTKGB;
                        }

                        if (!empty($updateData)) {
                            // Gunakan primary key `No` untuk update yang lebih cepat
                            DB::table($table)
                                ->where('No', $row->No)
                                ->update($updateData);
                            $totalUpdatedRows++;
                        }
                    }
                }, 'No');

        } catch (\Throwable $e) {
            $alias = ErrorAlias::fromThrowable($e, $this->aliasScope('SINKRONISASI-PAJAK-SYNCALL'));
            Log::error('SinkronisasiController syncAll failed', [
                'alias' => $alias['code'],
                'tahun' => session('tahun'),
                'wilayah' => $this->wilayahScope(),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => $alias['message'],
                'code' => $alias['code'],
            ], 500);
        }

        if (empty($totalUpdatedRows)) {
            return response()->json([
                'status' => 'warning',
                'message' => 'Tidak ada data pajak yang perlu diperbarui untuk semua bulan dan semua NIDN atau NUPTK.',
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Sinkronisasi pajak ke semua bulan berhasil untuk semua NIDN atau NUPTK pada tahun versi aktif. Total baris diperbarui: ' . $totalUpdatedRows . '.',
        ]);
    }

    /**
     * Sinkronisasi pajak ke semua bulan untuk satu NIDN saja (hanya pada bulan yang pajaknya tidak sesuai).
     */
    public function syncPajakNidnAllMonths(Request $request)
    {
        $request->validate([
            'NIDN' => 'required|string',
        ]);

        @set_time_limit(0);
        @ini_set('max_execution_time', '0');

        $table = 's_transaksi_2';
        $tahunVersi = session('tahun');
        if (!$tahunVersi) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tahun versi tidak ditemukan di sesi. Silakan login ulang.',
            ], 400);
        }

        $nidn = trim($request->input('NIDN'));
        $wilayah = $this->wilayahScope();

        $months = [
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

        $bulanCodes = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',7=>'Jul',8=>'Ags',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];

        $diff = function ($a, $b) {
            return abs((float)$a - (float)$b) > 0.01;
        };

        try {
            // Cache tarif pajak di memori
            $pajakRows = DB::table('d_pajak')
                ->select('status', 'akumulasi', 'tarif_pajak')
                ->get();

            $tarifMap = [];
            foreach ($pajakRows as $p) {
                $status = strtolower(trim((string) ($p->status ?? '')));
                $akum = strtolower(trim((string) ($p->akumulasi ?? '')));
                if (!isset($tarifMap[$status])) {
                    $tarifMap[$status] = [];
                }
                $tarifMap[$status][$akum] = (float) ($p->tarif_pajak ?? 0);
            }

            $rowQ = DB::table($table)
                ->where('Tahun_Versi', $tahunVersi)
                ->whereRaw('(TRIM(NIDN) = ? OR TRIM(NUPTK) = ?)', [$nidn, $nidn]);
            if ($wilayah) {
                $rowQ->where('pemegang_wilayah', $wilayah);
            }
            $row = $rowQ->first();

            if (!$row) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Data tidak ditemukan untuk NIDN atau NUPTK {$nidn} pada tahun versi aktif.",
                ], 404);
            }

            $updateData = [];

            foreach ($months as $idx => $label) {
                $kcField = $bulanCodes[$idx] ?? null;
                if (!$kcField) {
                    continue;
                }

                $kodeCair = $row->{$kcField} ?? null;
                if (!$kodeCair) {
                    continue;
                }

                $tpdField = 'TPD' . $idx;
                $tkgbField = 'TKGB' . $idx;

                $tpd = (float) ($row->{$tpdField} ?? 0);
                $tkgb = (float) ($row->{$tkgbField} ?? 0);

                if ($tpd == 0.0 && $tkgb == 0.0) {
                    continue;
                }

                $golField = 'Gol' . $idx;
                $jabatanField = 'Jabatan' . $idx;

                $golongan = $row->{$golField} ?? '';
                $jabatan = $row->{$jabatanField} ?? '';
                $jenis = $row->Jenis ?? '';

                $tarifPajak = 0.0;
                $jenisKey = strtolower(trim((string) $jenis));
                $golKey = strtolower(trim((string) $golongan));
                if (isset($tarifMap[$jenisKey][$golKey])) {
                    $tarifPajak = $tarifMap[$jenisKey][$golKey];
                }

                $fieldPajakTPD = 'pajak' . $tpdField;
                $fieldNilaiPajakTPD = 'nilaiPajak' . $tpdField;
                $fieldBersihTPD = 'bersih' . $tpdField;

                $fieldPajakTKGB = 'pajak' . $tkgbField;
                $fieldNilaiPajakTKGB = 'nilaiPajak' . $tkgbField;
                $fieldBersihTKGB = 'bersih' . $tkgbField;

                $pajakTPD_db = (float) ($row->{$fieldPajakTPD} ?? 0);
                $nilaiPajakTPD_db = (float) ($row->{$fieldNilaiPajakTPD} ?? 0);
                $bersihTPD_db = (float) ($row->{$fieldBersihTPD} ?? 0);

                $pajakTKGB_db = (float) ($row->{$fieldPajakTKGB} ?? 0);
                $nilaiPajakTKGB_db = (float) ($row->{$fieldNilaiPajakTKGB} ?? 0);
                $bersihTKGB_db = (float) ($row->{$fieldBersihTKGB} ?? 0);

                $pajakTPD_mismatch = $diff($pajakTPD_db, $tarifPajak);

                $tarifTKGBSeharusnya = $this->isGuruBesarAtauProfesor($jabatan) ? $tarifPajak : 0.0;
                $pajakTKGB_mismatch = $diff($pajakTKGB_db, $tarifTKGBSeharusnya);

                $nilaiTPD_calc = $tpd * $tarifPajak;
                $nilaiTKGB_calc = $tkgb * $tarifTKGBSeharusnya;

                $nilaiTPD_mismatch = $diff($nilaiPajakTPD_db, $nilaiTPD_calc);
                $nilaiTKGB_mismatch = $diff($nilaiPajakTKGB_db, $nilaiTKGB_calc);

                $bersihTPD_calc = $tpd - $nilaiTPD_calc;
                $bersihTKGB_calc = $tkgb - $nilaiTKGB_calc;

                $bersihTPD_mismatch = $diff($bersihTPD_db, $bersihTPD_calc);
                $bersihTKGB_mismatch = $diff($bersihTKGB_db, $bersihTKGB_calc);

                $hasMismatch =
                    $pajakTPD_mismatch ||
                    $pajakTKGB_mismatch ||
                    $nilaiTPD_mismatch ||
                    $nilaiTKGB_mismatch ||
                    $bersihTPD_mismatch ||
                    $bersihTKGB_mismatch;

                if (!$hasMismatch) {
                    continue;
                }

                $pajakTPD = $tarifPajak;
                $nilaiPajakTPD = $tpd * $pajakTPD;
                $bersihTPD = $tpd - $nilaiPajakTPD;

                $pajakTKGB = $tarifTKGBSeharusnya;
                $nilaiPajakTKGB = $tkgb * $pajakTKGB;
                $bersihTKGB = $tkgb - $nilaiPajakTKGB;

                $updateData[$fieldPajakTPD] = $pajakTPD;
                $updateData[$fieldNilaiPajakTPD] = $nilaiPajakTPD;
                $updateData[$fieldBersihTPD] = $bersihTPD;

                $updateData[$fieldPajakTKGB] = $pajakTKGB;
                $updateData[$fieldNilaiPajakTKGB] = $nilaiPajakTKGB;
                $updateData[$fieldBersihTKGB] = $bersihTKGB;
            }

            if (empty($updateData)) {
                return response()->json([
                    'status' => 'warning',
                    'message' => "Tidak ada data pajak yang perlu diperbarui untuk NIDN {$nidn} pada tahun versi aktif.",
                ]);
            }

            $updQ = DB::table($table)
                ->where('Tahun_Versi', $tahunVersi)
                ->whereRaw('(TRIM(NIDN) = ? OR TRIM(NUPTK) = ?)', [$nidn, $nidn]);
            if ($wilayah) {
                $updQ->where('pemegang_wilayah', $wilayah);
            }
            $updQ->update($updateData);

        } catch (\Throwable $e) {
            $alias = ErrorAlias::fromThrowable($e, $this->aliasScope('SINKRONISASI-PAJAK-SYNCONE'));
            Log::error('SinkronisasiController syncPajakNidnAllMonths failed', [
                'alias' => $alias['code'],
                'nidn' => $nidn,
                'tahun' => session('tahun'),
                'wilayah' => $this->wilayahScope(),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => $alias['message'],
                'code' => $alias['code'],
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => "Sinkronisasi pajak berhasil untuk NIDN atau NUPTK {$nidn} pada semua bulan yang pajaknya tidak sesuai.",
        ]);
    }

    /**
     * Sinkronisasi Gaji1-12 berdasarkan Gol1-12 dan Tahun1-12 menggunakan referensi c_grade
     * untuk semua NIDN pada tahun versi aktif.
     */
    public function syncGajiAll(Request $request)
    {
        @set_time_limit(0);
        @ini_set('max_execution_time', '0');

        $table = 's_transaksi_2';
        $tahunVersi = session('tahun');
        if (!$tahunVersi) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tahun versi tidak ditemukan di sesi. Silakan login ulang.',
            ], 400);
        }

        $wilayah = $this->wilayahScope();

        try {
            $bulanCodes = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',7=>'Jul',8=>'Ags',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];

            // Ambil seluruh grade gaji ke dalam memori: key = [gol][masa_kerja]
            $gradeRows = DB::table('c_grade')
                ->select('gol', 'masa_kerja', 'nominal')
                ->get();

            $gradeMap = [];
            foreach ($gradeRows as $g) {
                $gol = strtoupper(trim((string) ($g->gol ?? '')));
                $mkRaw = $g->masa_kerja;
                // Hanya lewati jika gol kosong atau masa_kerja benar-benar NULL
                if ($gol === '' || $mkRaw === null) {
                    continue;
                }
                // masa_kerja boleh 0 dan jika >32 diperlakukan sebagai 32
                $mk = (int) $mkRaw;
                if ($mk > 32) {
                    $mk = 32;
                }
                if (!isset($gradeMap[$gol])) {
                    $gradeMap[$gol] = [];
                }
                $gradeMap[$gol][$mk] = (float) ($g->nominal ?? 0);
            }

            // Jika tidak ada referensi grade, hentikan lebih awal
            if (empty($gradeMap)) {
                return response()->json([
                    'status' => 'warning',
                    'message' => 'Tidak ada data referensi gaji pada tabel c_grade.',
                ]);
            }

            $months = range(1, 12);
            // total baris (record) yang di-update dan set NIDN yang ter-update
            $totalUpdatedRows = 0;
            $updatedIdentifierSet = [];

            // Proses dalam bentuk chunk agar hemat memori & cepat untuk ~30rb baris
            $rowsQ = DB::table($table)
                ->where('Tahun_Versi', $tahunVersi);
            if ($wilayah) {
                $rowsQ->where('pemegang_wilayah', $wilayah);
            }
            $rowsQ
                ->orderBy('No')
                ->chunkById(1000, function ($rows) use (&$totalUpdatedRows, &$updatedIdentifierSet, $gradeMap, $months, $bulanCodes, $table) {
                    foreach ($rows as $row) {
                        $nidnRow = trim((string) ($row->NIDN ?? ''));
                        $nuptkRow = trim((string) ($row->NUPTK ?? ''));
                        $identifier = $nidnRow !== '' ? $nidnRow : $nuptkRow;
                        if ($identifier === '') {
                            continue;
                        }

                        $updateData = [];

                        foreach ($months as $idx) {
                            // Aturan KodeUsulan/Kode Cair:
                            // - Jika hanya memiliki KodeUsulan{idx} TANPA kode cair (Jan-Des) => TIDAK boleh disinkron gajinya.
                            // - Jika memiliki KodeUsulan{idx} DAN kode cair ATAU tidak memiliki keduanya => boleh disinkron.
                            $kodeUsulanField = 'KodeUsulan' . $idx;
                            $kcField = $bulanCodes[$idx] ?? null;

                            $kodeUsulanRaw = $row->{$kodeUsulanField} ?? null;
                            $kodeUsulan = $kodeUsulanRaw !== null ? trim((string) $kodeUsulanRaw) : null;
                            if ($kodeUsulan === '') {
                                $kodeUsulan = null;
                            }

                            $kodeCairRaw = $kcField ? ($row->{$kcField} ?? null) : null;
                            $kodeCair = $kodeCairRaw !== null ? trim((string) $kodeCairRaw) : null;
                            if ($kodeCair === '') {
                                $kodeCair = null;
                            }

                            // Hanya ada kode usulan tanpa kode cair -> lewati (tidak boleh sinkron gaji)
                            if ($kodeUsulan && !$kodeCair) {
                                continue;
                            }

                            $golField = 'Gol' . $idx;
                            $tahunField = 'Tahun' . $idx;
                            $gajiField = 'Gaji' . $idx;
                            $jabatanField = 'Jabatan' . $idx;

                            $golVal = strtoupper(trim((string) ($row->{$golField} ?? '')));
                            $mkRaw = $row->{$tahunField} ?? null;

                            if ($golVal === '' || $mkRaw === null || $mkRaw === '') {
                                continue;
                            }

                            // Normalisasi masa kerja ke integer (maksimal 32)
                            $masaKerja = (int) $mkRaw;
                            if ($masaKerja > 32) {
                                $masaKerja = 32;
                            }
                            if (!isset($gradeMap[$golVal][$masaKerja])) {
                                // Tidak ada referensi untuk kombinasi ini, lewati
                                continue;
                            }

                            $nominal = (float) $gradeMap[$golVal][$masaKerja];

                            // Jika jabatan Guru Besar / Profesor, gaji seharusnya = 3x nominal
                            $jabatan = $row->{$jabatanField} ?? '';
                            if ($this->isGuruBesarAtauProfesor($jabatan)) {
                                $nominal *= 3;
                            }
                            $currentGaji = (float) ($row->{$gajiField} ?? 0);

                            // Hanya update jika berbeda untuk mengurangi operasi tulis
                            if (abs($currentGaji - $nominal) > 0.01) {
                                $updateData[$gajiField] = $nominal;
                            }
                        }

                        if (!empty($updateData)) {
                            // Gunakan primary key `No` untuk update yang lebih cepat dan spesifik
                            DB::table($table)
                                ->where('No', $row->No)
                                ->update($updateData);
                            $totalUpdatedRows++;
                            $updatedIdentifierSet[$identifier] = true;
                        }
                    }
                }, 'No');

        } catch (\Throwable $e) {
            $alias = ErrorAlias::fromThrowable($e, $this->aliasScope('SINKRONISASI-GAJI-SYNCALL'));
            Log::error('SinkronisasiController syncGajiAll failed', [
                'alias' => $alias['code'],
                'tahun' => session('tahun'),
                'wilayah' => $this->wilayahScope(),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => $alias['message'],
                'code' => $alias['code'],
            ], 500);
        }

        $totalUpdatedIdentifier = count($updatedIdentifierSet);

        if (empty($totalUpdatedIdentifier)) {
            return response()->json([
                'status' => 'warning',
                'message' => 'Tidak ada data gaji yang perlu diperbarui untuk semua NIDN atau NUPTK pada tahun versi aktif.',
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Sinkronisasi Gaji1-12 berhasil untuk semua NIDN atau NUPTK pada tahun versi aktif. Total data diperbarui: ' . $totalUpdatedIdentifier . ' (total baris diperbarui: ' . $totalUpdatedRows . ').',
        ]);
    }
    
    /**
     * Cek gaji (Gaji1-12) yang tidak sesuai dengan referensi c_grade untuk semua NIDN.
     */
    public function checkGajiMismatch(Request $request)
    {
        @set_time_limit(0);
        @ini_set('max_execution_time', '0');
        $table = 's_transaksi_2';
        $tahunVersi = session('tahun');
        if (!$tahunVersi) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tahun versi tidak ditemukan di sesi. Silakan login ulang.',
            ], 400);
        }

        try {
            // Ambil referensi gaji dari c_grade
            $gradeRows = DB::table('c_grade')
                ->select('gol', 'masa_kerja', 'nominal')
                ->get();

            $gradeMap = [];
            foreach ($gradeRows as $g) {
                $gol = strtoupper(trim((string) ($g->gol ?? '')));
                $mkRaw = $g->masa_kerja;
                if ($gol === '' || $mkRaw === null) {
                    continue;
                }
                // masa_kerja boleh 0 dan jika >32 diperlakukan sebagai 32
                $mk = (int) $mkRaw;
                if ($mk > 32) {
                    $mk = 32;
                }
                if (!isset($gradeMap[$gol])) {
                    $gradeMap[$gol] = [];
                }
                $gradeMap[$gol][$mk] = (float) ($g->nominal ?? 0);
            }

            if (empty($gradeMap)) {
                return response()->json([
                    'status' => 'warning',
                    'message' => 'Tidak ada data referensi gaji pada tabel c_grade.',
                    'mismatches' => [],
                ]);
            }

            $months = range(1, 12);
            $bulanCodes = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',7=>'Jul',8=>'Ags',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];
            $mismatches = [];

            $wilayah = $this->wilayahScope();

            $rowsQ = DB::table($table)
                ->where('Tahun_Versi', $tahunVersi);
            if ($wilayah) {
                $rowsQ->where('pemegang_wilayah', $wilayah);
            }

            $rowsQ
                ->orderBy('No')
                ->chunkById(1000, function ($rows) use (&$mismatches, $gradeMap, $months, $bulanCodes) {
                    foreach ($rows as $row) {
                        $nidnOnly = trim((string) ($row->NIDN ?? ''));
                        $nuptkOnly = trim((string) ($row->NUPTK ?? ''));
                        $identifier = $nidnOnly !== '' ? $nidnOnly : $nuptkOnly;
                        if ($identifier === '') {
                            continue;
                        }

                        foreach ($months as $idx) {
                            // Terapkan aturan KodeUsulan/Kode Cair seperti pada sinkron gaji:
                            // jika hanya memiliki KodeUsulan{idx} TANPA kode cair, abaikan dari pengecekan mismatch
                            $kodeUsulanField = 'KodeUsulan' . $idx;
                            $kcField = $bulanCodes[$idx] ?? null;

                            $kodeUsulanRaw = $row->{$kodeUsulanField} ?? null;
                            $kodeUsulan = $kodeUsulanRaw !== null ? trim((string) $kodeUsulanRaw) : null;
                            if ($kodeUsulan === '') {
                                $kodeUsulan = null;
                            }

                            $kodeCairRaw = $kcField ? ($row->{$kcField} ?? null) : null;
                            $kodeCair = $kodeCairRaw !== null ? trim((string) $kodeCairRaw) : null;
                            if ($kodeCair === '') {
                                $kodeCair = null;
                            }

                            if ($kodeUsulan && !$kodeCair) {
                                // Kode usulan tersedia tetapi belum ada kode cair: bukan objek sinkronisasi gaji
                                continue;
                            }

                            $golField = 'Gol' . $idx;
                            $tahunField = 'Tahun' . $idx;
                            $gajiField = 'Gaji' . $idx;
                            $jabatanField = 'Jabatan' . $idx;

                            $golVal = strtoupper(trim((string) ($row->{$golField} ?? '')));
                            $mkRaw = $row->{$tahunField} ?? null;

                            if ($golVal === '' || $mkRaw === null || $mkRaw === '') {
                                continue;
                            }

                            $masaKerja = (int) $mkRaw;
                            if ($masaKerja > 32) {
                                $masaKerja = 32;
                            }
                            if (!isset($gradeMap[$golVal][$masaKerja])) {
                                // Tidak ada referensi untuk kombinasi ini, lewati dari daftar mismatch
                                continue;
                            }

                            $expected = (float) $gradeMap[$golVal][$masaKerja];

                            // Jika jabatan Guru Besar / Profesor, gaji seharusnya = 3x nominal
                            $jabatan = $row->{$jabatanField} ?? '';
                            if ($this->isGuruBesarAtauProfesor($jabatan)) {
                                $expected *= 3;
                            }
                            $gajiDb = (float) ($row->{$gajiField} ?? 0);

                            if (abs($gajiDb - $expected) > 0.01) {
                                // column names in DB may vary in casing; provide fallbacks
                                $namaVal = $row->Nama ?? $row->nama ?? $row->Nama_Pegawai ?? $row->nama_dosen ?? '';
                                $kodePtVal = $row->Kode_PT ?? $row->kode_pt ?? $row->Kode_PTS ?? $row->kode_pts ?? '';
                                $pemegangWilayahVal = $row->Pemegang_Wilayah ?? $row->pemegang_wilayah ?? '';

                                $mismatches[] = [
                                    'nidn' => $nidnOnly,
                                    'nuptk' => $nuptkOnly,
                                    'identifier' => $identifier,
                                    'bulan_index' => $idx,
                                    'nama' => $namaVal,
                                    'Kode_PT' => $kodePtVal,
                                    'Pemegang_Wilayah' => $pemegangWilayahVal,
                                ];
                            }
                        }
                    }
                }, 'No');

        } catch (\Throwable $e) {
            $alias = ErrorAlias::fromThrowable($e, $this->aliasScope('SINKRONISASI-GAJI-MISMATCH'));
            Log::error('SinkronisasiController checkGajiMismatch failed', [
                'alias' => $alias['code'],
                'tahun' => session('tahun'),
                'wilayah' => $this->wilayahScope(),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => $alias['message'],
                'code' => $alias['code'],
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Pengecekan gaji selesai.',
            'mismatches' => $mismatches,
        ]);
    }

    /**
     * Detail gaji tidak sesuai untuk satu NIDN (per bulan), dibandingkan dengan referensi c_grade.
     */
    public function detailGajiMismatch(Request $request)
    {
        @set_time_limit(0);
        @ini_set('max_execution_time', '0');

        $request->validate([
            'NIDN' => 'required|string',
        ]);

        $nidn = trim($request->input('NIDN'));
        $wilayah = $this->wilayahScope();

        $table = 's_transaksi_2';
        $tahunVersi = session('tahun');
        if (!$tahunVersi) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tahun versi tidak ditemukan di sesi. Silakan login ulang.',
            ], 400);
        }

        $months = [
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

        try {
            $rowQ = DB::table($table)
                ->whereRaw('(TRIM(NIDN) = ? OR TRIM(NUPTK) = ?)', [$nidn, $nidn])
                ->where('Tahun_Versi', $tahunVersi);
            if ($wilayah) {
                $rowQ->where('pemegang_wilayah', $wilayah);
            }
            $row = $rowQ->first();

            if (!$row) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Data tidak ditemukan untuk NIDN atau NUPTK {$nidn}.",
                ], 404);
            }

            // Ambil referensi gaji dari c_grade
            $gradeRows = DB::table('c_grade')
                ->select('gol', 'masa_kerja', 'nominal')
                ->get();

            $gradeMap = [];
            foreach ($gradeRows as $g) {
                $gol = strtoupper(trim((string) ($g->gol ?? '')));
                $mkRaw = $g->masa_kerja;
                if ($gol === '' || $mkRaw === null) {
                    continue;
                }
                // masa_kerja boleh 0 dan jika >32 diperlakukan sebagai 32
                $mk = (int) $mkRaw;
                if ($mk > 32) {
                    $mk = 32;
                }
                if (!isset($gradeMap[$gol])) {
                    $gradeMap[$gol] = [];
                }
                $gradeMap[$gol][$mk] = (float) ($g->nominal ?? 0);
            }

            $detail = [];
            $monthIndexes = range(1, 12);
            $bulanCodes = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',7=>'Jul',8=>'Ags',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];

            foreach ($monthIndexes as $idx) {
                $golField = 'Gol' . $idx;
                $tahunField = 'Tahun' . $idx;
                $gajiField = 'Gaji' . $idx;
                $jabatanField = 'Jabatan' . $idx;

                $kodeUsulanField = 'KodeUsulan' . $idx;
                $kcField = $bulanCodes[$idx] ?? null;

                $kodeUsulanRaw = $row->{$kodeUsulanField} ?? null;
                $kodeUsulan = $kodeUsulanRaw !== null ? trim((string) $kodeUsulanRaw) : null;
                if ($kodeUsulan === '') {
                    $kodeUsulan = null;
                }

                $kodeCairRaw = $kcField ? ($row->{$kcField} ?? null) : null;
                $kodeCair = $kodeCairRaw !== null ? trim((string) $kodeCairRaw) : null;
                if ($kodeCair === '') {
                    $kodeCair = null;
                }

                // Kasus khusus: hanya punya kode usulan tanpa kode cair
                if ($kodeUsulan && !$kodeCair) {
                    $jabatanOnly = $row->{$jabatanField} ?? '';
                    // Attempt to read Gol and Tahun (masa kerja) even when kode cair absent
                    $golValOnly = $row->{$golField} ?? '';
                    $mkOnly = $row->{$tahunField} ?? null;
                    $detail[] = [
                        'bulan_index' => $idx,
                        'bulan_label' => $months[$idx] ?? $idx,
                        // tampilkan nilai kode usulan asli di kolom Kode Usulan
                        'kode_usulan' => $kodeUsulan,
                        'jabatan' => $jabatanOnly,
                        'gol' => $golValOnly,
                        'masa_kerja' => $mkOnly,
                        'gaji_db' => null,
                        'gaji_expected' => null,
                        'selisih' => null,
                        'is_mismatch' => false,
                        'can_sync' => false,
                    ];
                    continue;
                }

                $golVal = strtoupper(trim((string) ($row->{$golField} ?? '')));
                $mkRaw = $row->{$tahunField} ?? null;
                $gajiDb = (float) ($row->{$gajiField} ?? 0);

                // Jika tidak ada golongan atau masa kerja, lewati baris ini
                if ($golVal === '' || $mkRaw === null || $mkRaw === '') {
                    continue;
                }

                $masaKerja = (int) $mkRaw;
                if ($masaKerja > 32) {
                    $masaKerja = 32;
                }
                $expected = null;
                if (isset($gradeMap[$golVal][$masaKerja])) {
                    $expected = (float) $gradeMap[$golVal][$masaKerja];
                }

                // Ambil jabatan untuk ditampilkan; jika kosong tetap string kosong
                $jabatan = $row->{$jabatanField} ?? '';

                // Jika jabatan Guru Besar / Profesor, gaji seharusnya = 3x nominal
                if ($expected !== null && $this->isGuruBesarAtauProfesor($jabatan)) {
                    $expected *= 3;
                }

                $isMismatch = false;
                $selisih = null;
                if ($expected !== null) {
                    $selisih = $gajiDb - $expected;
                    $isMismatch = (abs($selisih) > 0.01);
                }

                $detail[] = [
                    'bulan_index' => $idx,
                    'bulan_label' => $months[$idx] ?? $idx,
                    'kode_usulan' => $kodeUsulan,
                    'jabatan' => $jabatan,
                    'gol' => $golVal,
                    'masa_kerja' => $masaKerja,
                    'gaji_db' => $gajiDb,
                    'gaji_expected' => $expected,
                    'selisih' => $selisih,
                    'is_mismatch' => $isMismatch,
                    'can_sync' => $expected !== null,
                ];
            }

        } catch (\Throwable $e) {
            $alias = ErrorAlias::fromThrowable($e, $this->aliasScope('SINKRONISASI-GAJI-DETAIL'));
            Log::error('SinkronisasiController detailGajiMismatch failed', [
                'alias' => $alias['code'],
                'nidn' => $nidn,
                'tahun' => session('tahun'),
                'wilayah' => $this->wilayahScope(),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => $alias['message'],
                'code' => $alias['code'],
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Detail gaji berhasil diambil.',
            'rows' => $detail,
        ]);
    }

    /**
     * Sinkronisasi gaji untuk satu NIDN dan satu bulan (Gaji{bulan}) berdasarkan Gol{bulan}, Tahun{bulan} dan c_grade.
     */
    public function syncGajiSingle(Request $request)
    {
        $request->validate([
            'NIDN' => 'required|string',
            'bulan' => 'required|integer|min:1|max:12',
        ]);

        $nidn = trim($request->input('NIDN'));
        $idx = (int) $request->input('bulan');

        $table = 's_transaksi_2';
        $tahunVersi = session('tahun');
        if (!$tahunVersi) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tahun versi tidak ditemukan di sesi. Silakan login ulang.',
            ], 400);
        }

        $wilayah = $this->wilayahScope();

        try {
            $rowQ = DB::table($table)
                ->whereRaw('(TRIM(NIDN) = ? OR TRIM(NUPTK) = ?)', [$nidn, $nidn])
                ->where('Tahun_Versi', $tahunVersi);
            if ($wilayah) {
                $rowQ->where('pemegang_wilayah', $wilayah);
            }
            $row = $rowQ->first();

            if (!$row) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Data tidak ditemukan untuk NIDN atau NUPTK {$nidn}.",
                ], 404);
            }

            $golField = 'Gol' . $idx;
            $tahunField = 'Tahun' . $idx;
            $gajiField = 'Gaji' . $idx;
            $jabatanField = 'Jabatan' . $idx;

            // Aturan KodeUsulan/Kode Cair untuk sinkron per-bulan
            $bulanCodes = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',7=>'Jul',8=>'Ags',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];
            $kodeUsulanField = 'KodeUsulan' . $idx;
            $kcField = $bulanCodes[$idx] ?? null;

            $kodeUsulanRaw = $row->{$kodeUsulanField} ?? null;
            $kodeUsulan = $kodeUsulanRaw !== null ? trim((string) $kodeUsulanRaw) : null;
            if ($kodeUsulan === '') {
                $kodeUsulan = null;
            }

            $kodeCairRaw = $kcField ? ($row->{$kcField} ?? null) : null;
            $kodeCair = $kodeCairRaw !== null ? trim((string) $kodeCairRaw) : null;
            if ($kodeCair === '') {
                $kodeCair = null;
            }

            if ($kodeUsulan && !$kodeCair) {
                return response()->json([
                    'status' => 'warning',
                    'message' => "Tidak dapat sinkronisasi gaji untuk NIDN {$nidn} pada bulan {$idx} karena hanya memiliki kode usulan tanpa kode cair.",
                ]);
            }

            $golVal = strtoupper(trim((string) ($row->{$golField} ?? '')));
            $mkRaw = $row->{$tahunField} ?? null;

            if ($golVal === '' || $mkRaw === null || $mkRaw === '') {
                return response()->json([
                    'status' => 'warning',
                    'message' => "Golongan atau masa kerja untuk bulan {$idx} tidak tersedia untuk NIDN {$nidn}.",
                ]);
            }

            $masaKerja = (int) $mkRaw;
            if ($masaKerja > 32) {
                $masaKerja = 32;
            }

            $expected = DB::table('c_grade')
                ->where('gol', $golVal)
                ->where('masa_kerja', $masaKerja)
                ->value('nominal');

            if ($expected === null) {
                return response()->json([
                    'status' => 'warning',
                    'message' => "Referensi gaji pada c_grade untuk gol {$golVal} dan masa kerja {$masaKerja} tahun tidak ditemukan.",
                ]);
            }
            $expected = (float) $expected;

            // Jika jabatan Guru Besar / Profesor, gaji seharusnya = 3x nominal
            $jabatan = $row->{$jabatanField} ?? '';
            if ($this->isGuruBesarAtauProfesor($jabatan)) {
                $expected *= 3;
            }
            $currentGaji = (float) ($row->{$gajiField} ?? 0);

            if (abs($currentGaji - $expected) <= 0.01) {
                return response()->json([
                    'status' => 'warning',
                    'message' => "Gaji untuk NIDN {$nidn} pada bulan {$idx} sudah sesuai dengan referensi.",
                ]);
            }

            $updQ = DB::table($table)
                ->whereRaw('(TRIM(NIDN) = ? OR TRIM(NUPTK) = ?)', [$nidn, $nidn])
                ->where('Tahun_Versi', $tahunVersi);
            if ($wilayah) {
                $updQ->where('pemegang_wilayah', $wilayah);
            }
            $updQ->update([
                $gajiField => $expected,
            ]);

        } catch (\Throwable $e) {
            $alias = ErrorAlias::fromThrowable($e, $this->aliasScope('SINKRONISASI-GAJI-SINGLE'));
            Log::error('SinkronisasiController syncGajiSingle failed', [
                'alias' => $alias['code'],
                'nidn' => $nidn,
                'bulan' => $idx,
                'tahun' => session('tahun'),
                'wilayah' => $this->wilayahScope(),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => $alias['message'],
                'code' => $alias['code'],
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => "Sinkronisasi gaji berhasil untuk NIDN atau NUPTK {$nidn} pada bulan {$idx}.",
        ]);
    }

    /**
     * Sinkronisasi gaji 1 NIDN untuk semua bulan (Gaji1-12) berdasarkan Gol1-12, Tahun1-12, c_grade,
     * dan aturan KodeUsulan/Kode Cair (hanya update bulan yang memenuhi aturan dan masih tidak sesuai).
     */
    public function syncGajiNidnAllMonths(Request $request)
    {
        $request->validate([
            'NIDN' => 'required|string',
        ]);

        $nidn = trim($request->input('NIDN'));
        $wilayah = $this->wilayahScope();

        @set_time_limit(0);
        @ini_set('max_execution_time', '0');

        $table = 's_transaksi_2';
        $tahunVersi = session('tahun');
        if (!$tahunVersi) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tahun versi tidak ditemukan di sesi. Silakan login ulang.',
            ], 400);
        }

        try {
            $rowQ = DB::table($table)
                ->whereRaw('(TRIM(NIDN) = ? OR TRIM(NUPTK) = ?)', [$nidn, $nidn])
                ->where('Tahun_Versi', $tahunVersi);
            if ($wilayah) {
                $rowQ->where('pemegang_wilayah', $wilayah);
            }
            $row = $rowQ->first();

            if (!$row) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Data tidak ditemukan untuk NIDN atau NUPTK {$nidn} pada tahun versi aktif.",
                ], 404);
            }

            // Siapkan referensi gaji dari c_grade (disimpan di memori)
            $gradeRows = DB::table('c_grade')
                ->select('gol', 'masa_kerja', 'nominal')
                ->get();

            $gradeMap = [];
            foreach ($gradeRows as $g) {
                $gol = strtoupper(trim((string) ($g->gol ?? '')));
                $mkRaw = $g->masa_kerja;
                if ($gol === '' || $mkRaw === null) {
                    continue;
                }
                // masa_kerja boleh 0 dan jika >32 diperlakukan sebagai 32
                $mk = (int) $mkRaw;
                if ($mk > 32) {
                    $mk = 32;
                }
                if (!isset($gradeMap[$gol])) {
                    $gradeMap[$gol] = [];
                }
                $gradeMap[$gol][$mk] = (float) ($g->nominal ?? 0);
            }

            if (empty($gradeMap)) {
                return response()->json([
                    'status' => 'warning',
                    'message' => 'Tidak ada data referensi gaji pada tabel c_grade.',
                ]);
            }

            $bulanCodes = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',7=>'Jul',8=>'Ags',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];
            $months = range(1, 12);

            $updateData = [];
            $updatedCount = 0;

            foreach ($months as $idx) {
                $golField = 'Gol' . $idx;
                $tahunField = 'Tahun' . $idx;
                $gajiField = 'Gaji' . $idx;
                $jabatanField = 'Jabatan' . $idx;

                // Aturan KodeUsulan/Kode Cair sama dengan sinkron global:
                $kodeUsulanField = 'KodeUsulan' . $idx;
                $kcField = $bulanCodes[$idx] ?? null;

                $kodeUsulanRaw = $row->{$kodeUsulanField} ?? null;
                $kodeUsulan = $kodeUsulanRaw !== null ? trim((string) $kodeUsulanRaw) : null;
                if ($kodeUsulan === '') {
                    $kodeUsulan = null;
                }

                $kodeCairRaw = $kcField ? ($row->{$kcField} ?? null) : null;
                $kodeCair = $kodeCairRaw !== null ? trim((string) $kodeCairRaw) : null;
                if ($kodeCair === '') {
                    $kodeCair = null;
                }

                // Jika tidak ada kode cair, JANGAN disinkron gaji (apapun kondisi kode usulan)
                if (!$kodeCair) {
                    continue;
                }

                $golVal = strtoupper(trim((string) ($row->{$golField} ?? '')));
                $mkRaw = $row->{$tahunField} ?? null;

                if ($golVal === '' || $mkRaw === null || $mkRaw === '') {
                    continue;
                }

                $masaKerja = (int) $mkRaw;
                if ($masaKerja > 32) {
                    $masaKerja = 32;
                }
                if (!isset($gradeMap[$golVal][$masaKerja])) {
                    // Tidak ada referensi untuk kombinasi ini, lewati bulan ini
                    continue;
                }

                $expected = (float) $gradeMap[$golVal][$masaKerja];

                // Jika jabatan Guru Besar / Profesor, gaji seharusnya = 3x nominal
                $jabatan = $row->{$jabatanField} ?? '';
                if ($this->isGuruBesarAtauProfesor($jabatan)) {
                    $expected *= 3;
                }
                $currentGaji = (float) ($row->{$gajiField} ?? 0);

                if (abs($currentGaji - $expected) > 0.01) {
                    $updateData[$gajiField] = $expected;
                    $updatedCount++;
                }
            }

            if (empty($updateData)) {
                return response()->json([
                    'status' => 'warning',
                    'message' => "Tidak ada data gaji yang perlu diperbarui untuk NIDN {$nidn} pada tahun versi aktif.",
                ]);
            }

            $updQ = DB::table($table)
                ->whereRaw('(TRIM(NIDN) = ? OR TRIM(NUPTK) = ?)', [$nidn, $nidn])
                ->where('Tahun_Versi', $tahunVersi);
            if ($wilayah) {
                $updQ->where('pemegang_wilayah', $wilayah);
            }
            $updQ->update($updateData);

        } catch (\Throwable $e) {
            $alias = ErrorAlias::fromThrowable($e, $this->aliasScope('SINKRONISASI-GAJI-NIDN-ALL'));
            Log::error('SinkronisasiController syncGajiNidnAllMonths failed', [
                'alias' => $alias['code'],
                'nidn' => $nidn,
                'tahun' => session('tahun'),
                'wilayah' => $this->wilayahScope(),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => $alias['message'],
                'code' => $alias['code'],
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => "Sinkronisasi gaji berhasil untuk NIDN atau NUPTK {$nidn} pada {$updatedCount} bulan (hanya bulan yang gajinya tidak sesuai dan memenuhi aturan Kode Usulan / Kode Cair).",
        ]);
    }
}
