<?php

namespace App\Http\Controllers;

use App\Helpers\ErrorAlias;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\Perubahan;

class KoreksiController extends Controller
{
    public function index(Request $request)
    {
        $tahun = session('tahun');

        $data = [
            'nidn' => $request->old('nidn', $request->query('nidn')),
            'bulan' => (int)$request->old('bulan', $request->query('bulan', 1)),
            'tahun' => $tahun,
            'result' => null,
            // Isi dropdown dari h_perubahan.status_perubahan
            'statusPerubahan' => Perubahan::query()->orderBy('status_perubahan')->pluck('status_perubahan')->all(),
        ];

        // If nidn and bulan are provided via query (after redirect or link), perform lookup
        $nidn = $data['nidn'];
        $bulan = $data['bulan'];
        if ($nidn && $bulan) {
            $lookup = $this->lookupData($nidn, (int)$bulan, $tahun);
            if ($lookup['ok']) {
                $data['result'] = $lookup['data'];
            } else {
                session()->flash('error', $lookup['message']);
            }
        }

        return view('admin.koreksi', $data);
    }

    public function cari(Request $request)
    {
        $nidn = trim((string)$request->input('nidn', $request->query('nidn')));
        $bulan = (int)$request->input('bulan', $request->query('bulan', 1));
        $tahun = session('tahun');

        if (!$nidn) {
            return redirect()->route('admin.koreksi', ['bulan' => $bulan]);
        }

        $lookup = $this->lookupData($nidn, $bulan, $tahun);

        $viewData = [
            'nidn' => $nidn,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'result' => $lookup['ok'] ? $lookup['data'] : null,
            // Isi dropdown dari h_perubahan.status_perubahan
            'statusPerubahan' => Perubahan::query()->orderBy('status_perubahan')->pluck('status_perubahan')->all(),
        ];

        if (!$lookup['ok']) {
            session()->flash('error', $lookup['message']);
        }

        return view('admin.koreksi', $viewData);
    }

    public function verifikasi(Request $request)
    {
        // Expect JSON
        $payload = $request->json()->all();

        // Basic validation
        $validator = Validator::make($payload, [
            'password' => 'required|string',
            'nidn' => 'required|string',
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|string',
            'gaji' => 'nullable|integer',
            'kodeusulan' => 'nullable|string',
            'kodecair' => 'nullable|string',
            'tkgb' => 'nullable|integer',
            'pajak_tpd' => 'nullable|integer',
            'bersih_tpd' => 'nullable|integer',
            'no_sp2d' => 'nullable|string',
            'tgl_sp2d' => 'nullable|string',
            'selisih' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $password = $payload['password'];
        $user = Auth::user();
        $isValid = false;

        if ($user) {
            $isValid = hash_equals((string) $user->password, (string) $password);
        }

        // Fallback to env password if no user or password mismatch
        // Shared admin confirmation password, defaults to 'lldikti4'
        if (!$isValid) {
            $sharedPass = (string) env('ADMIN_CONFIRM_PASSWORD', 'lldikti4');
            if ($sharedPass !== '') {
                $isValid = hash_equals($sharedPass, (string)$password);
            }
        }

        if (!$isValid) {
            return response()->json([
                'success' => false,
                'message' => 'Password tidak valid!',
            ], 401);
        }

        // Proceed update
        $nidn = trim($payload['nidn']);
        $bulan = (int)$payload['bulan'];
        $table = 's_transaksi_2';
        $tahunVersi = $payload['tahun'] ?? session('tahun');
        if (!$tahunVersi) {
            return response()->json([
                'success' => false,
                'message' => 'Tahun versi tidak ditemukan di sesi.',
            ], 400);
        }

        $KCField = $this->bulanKeCair()[$bulan] ?? null;
        if (!$KCField) {
            return response()->json([
                'success' => false,
                'message' => 'Bulan tidak valid.',
            ], 400);
        }

        $update = [];
        $update['Gaji' . $bulan] = (int)($payload['gaji'] ?? 0);
        $update['KodeUsulan' . $bulan] = $payload['kodeusulan'] ?? null;
        $update[$KCField] = $payload['kodecair'] ?? null;
        $update['TPD' . $bulan] = (int)($payload['tpd'] ?? 0);
        $update['TKGB' . $bulan] = (int)($payload['tkgb'] ?? 0);
        $update['nilaiPajakTPD' . $bulan] = (int)($payload['pajak_tpd'] ?? 0);
        $update['bersihTPD' . $bulan] = (int)($payload['bersih_tpd'] ?? 0);
        $update['No_sp2d_' . $bulan] = $payload['no_sp2d'] ?? null;
        $update['Tgl_sp2d_' . $bulan] = $payload['tgl_sp2d'] ?? null;

        try {
            $affected = DB::table($table)
                ->whereRaw('(TRIM(NIDN) = ? OR TRIM(NUPTK) = ?)', [$nidn, $nidn])
                ->where('Tahun_Versi', $tahunVersi)
                ->update($update);
            if ($affected === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ada perubahan.',
                ], 404);
            }
        } catch (\Throwable $e) {
            $alias = ErrorAlias::fromThrowable($e, 'ADM-KOREKSI-UPDATE');
            Log::error('KoreksiController@verifikasi update failed', [
                'alias' => $alias['code'],
                'nidn' => $nidn,
                'bulan' => $bulan,
                'tahun_versi' => $tahunVersi,
                'update' => $update,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => $alias['message'],
                'code' => $alias['code'],
            ], 500);
        }

        return response()->json([
            'success' => true,
            'redirect' => route('admin.koreksi', ['nidn' => $nidn, 'bulan' => $bulan, 'tahun' => $tahunVersi]),
        ]);
    }

    private function lookupData(string $nidn, int $bulan, string $tahunVersi = null): array
    {
        // Tabel tetap tanpa suffix tahun + filter Tahun_Versi
        $table = 's_transaksi_2';
        $tahunVersi = $tahunVersi ?: session('tahun');
        if (!$tahunVersi) {
            return ['ok' => false, 'message' => 'Tahun versi tidak ditemukan di sesi.'];
        }

        $golField = 'Gol' . $bulan;
        $tahunField = 'Tahun' . $bulan;
        $fields = [
            'Nama',
            "$golField as GolSelected",
            "$tahunField as TahunSelected",
            'Gaji' . $bulan . ' as gaji',
            'KodeUsulan' . $bulan . ' as kode_usulan',
            ($this->bulanKeCair()[$bulan] ?? 'Jan') . ' as kode_cair',
            'TPD' . $bulan . ' as tpd',
            'TKGB' . $bulan . ' as tkgb',
            'nilaiPajakTPD' . $bulan . ' as pajak_tpd',
            'bersihTPD' . $bulan . ' as bersih_tpd',
            'No_sp2d_' . $bulan . ' as no_sp2d',
            'Tgl_sp2d_' . $bulan . ' as tgl_sp2d',
        ];

        try {
            $row = DB::table($table)
                ->selectRaw(implode(',', $fields))
                ->whereRaw('(TRIM(NIDN) = ? OR TRIM(NUPTK) = ?)', [$nidn, $nidn])
                ->where('Tahun_Versi', $tahunVersi)
                ->first();
        } catch (\Throwable $e) {
            $alias = ErrorAlias::fromThrowable($e, 'ADM-KOREKSI-LOOKUP');
            Log::error('KoreksiController lookupData query failed', [
                'alias' => $alias['code'],
                'nidn' => $nidn,
                'bulan' => $bulan,
                'tahun_versi' => $tahunVersi,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return ['ok' => false, 'message' => $alias['message']];
        }

        if (!$row) {
            return ['ok' => false, 'message' => 'Data tidak ditemukan.'];
        }

        // Kalkulasi selisih secara dinamis berdasarkan (TPD + TKGB) - Gaji
        $kotor = ((float)$row->tpd) + ((float)$row->tkgb);
        $gaji = (float)$row->gaji;
        $row->tpd_sel = $kotor - $gaji;

        // Logika Status
        $origSp2dNo = trim((string)($row->no_sp2d ?? ''));
        $origSp2dTgl = trim((string)($row->tgl_sp2d ?? ''));
        $origHasSp2d = ($origSp2dNo !== '' && $origSp2dNo !== '-' && $origSp2dTgl !== '' && $origSp2dTgl !== '-');
        
        $hasData = ($kotor > 0 || $gaji > 0);
        $kc = trim((string)($row->kode_cair ?? ''));
        $hasKodeCair = ($kc !== '' && $kc !== '-');
        $kodeStr = trim((string)($row->kode_usulan ?? ''));
        
        $status = null;
        if (!$hasData && !$kodeStr && !$hasKodeCair) {
            $status = null;
        } elseif ($origHasSp2d) {
            if (abs($row->tpd_sel) <= 0.01) {
                $status = 'selesai';
            } elseif ($row->tpd_sel < -0.01) {
                $status = 'kurang';
            } elseif ($row->tpd_sel > 0.01) {
                $status = 'lebih';
            }
        } elseif ($hasKodeCair && !$origHasSp2d) {
            $status = 'proses';
        } elseif (!$hasKodeCair && ($hasData || $kodeStr)) {
            if ($kodeStr !== '' && $kodeStr !== '-' && $gaji == 0) {
                $status = 'kode:' . $kodeStr;
            } else {
                $status = 'usulan';
            }
        }
        $row->status = $status;

        return ['ok' => true, 'data' => $row];
    }

    private function bulanKeCair(): array
    {
        return [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'May',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Ags',
            9 => 'Sep',
            10 => 'Okt',
            11 => 'Nov',
            12 => 'Des',
        ];
    }
}
