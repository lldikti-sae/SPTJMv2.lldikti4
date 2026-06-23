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
        $data = [
            'nidn' => $request->old('nidn'),
            'bulan' => $request->old('bulan', 1),
            'result' => null,
            // Isi dropdown dari h_perubahan.status_perubahan
            'statusPerubahan' => Perubahan::query()->orderBy('status_perubahan')->pluck('status_perubahan')->all(),
        ];

        // If nidn and bulan are provided via query (after redirect), perform lookup
        $nidn = $request->query('nidn');
        $bulan = $request->query('bulan');
        if ($nidn && $bulan) {
            $lookup = $this->lookupData($nidn, (int)$bulan);
            if ($lookup['ok']) {
                $data['result'] = $lookup['data'];
                $data['nidn'] = $nidn;
                $data['bulan'] = (int)$bulan;
            } else {
                return back()->with('error', $lookup['message']);
            }
        }

        return view('admin.koreksi', $data);
    }

    public function cari(Request $request)
    {
        $request->validate([
            'nidn' => 'required|string',
            'bulan' => 'required|integer|min:1|max:12',
        ]);

    $nidn = trim($request->input('nidn'));
        $bulan = (int)$request->input('bulan');

        $lookup = $this->lookupData($nidn, $bulan);
        if (!$lookup['ok']) {
            return back()->withInput()->with('error', $lookup['message']);
        }

        return view('admin.koreksi', [
            'nidn' => $nidn,
            'bulan' => $bulan,
            'result' => $lookup['data'],
            // Isi dropdown dari h_perubahan.status_perubahan
            'statusPerubahan' => Perubahan::query()->orderBy('status_perubahan')->pluck('status_perubahan')->all(),
        ]);
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
            'gaji' => 'nullable|integer',
            'kodeusulan' => 'nullable|string',
            'kodecair' => 'nullable|string',
            'tpd' => 'nullable|integer',
            'tkgb' => 'nullable|integer',
            'tpd_sel' => 'nullable|integer',
            'tkgb_sel' => 'nullable|integer',
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
        // Tabel tetap tanpa suffix tahun, filter berdasarkan Tahun_Versi dari sesi login
        $table = 's_transaksi_2';
        $tahunVersi = session('tahun');
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
        /*
        if (array_key_exists('tpd_sel', $payload)) {
            $update['JmlTPD_Selisih'] = (int)$payload['tpd_sel'];
        }
        if (array_key_exists('tkgb_sel', $payload)) {
            $update['JmlTKGB_Selisih'] = (int)$payload['tkgb_sel'];
        }
        */

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
            'redirect' => route('admin.koreksi', ['nidn' => $nidn, 'bulan' => $bulan]),
        ]);
    }

    private function lookupData(string $nidn, int $bulan): array
    {
        // Tabel tetap tanpa suffix tahun + filter Tahun_Versi
        $table = 's_transaksi_2';
        $tahunVersi = session('tahun');
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
            // 'JmlTPD_Selisih as tpd_sel',
            // 'JmlTKGB_Selisih as tkgb_sel',
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
