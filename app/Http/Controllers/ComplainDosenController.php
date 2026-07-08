<?php

namespace App\Http\Controllers;

use App\Helpers\ErrorAlias;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ComplainDosenController extends Controller
{
    public function index(Request $request)
    {
        $dosen = Auth::guard('dosen')->user();
        if (!$dosen) {
            abort(403);
        }

        if ($request->ajax()) {
            $draw = (int) $request->input('draw', 0);
            try {
                $start = (int) $request->input('start', 0);
                $length = (int) $request->input('length', 10);
                $searchValue = (string) $request->input('search.value', '');

                $columnsMap = [
                    0 => 'id',
                    1 => 'judul',
                    2 => 'pic',
                    3 => 'status',
                    4 => 'created_at',
                ];

                $order = $request->input('order', []);
                $orderColIndex = (int) data_get($order, '0.column', 0);
                $orderDir = (string) data_get($order, '0.dir', 'desc');
                $orderDir = in_array($orderDir, ['asc', 'desc'], true) ? $orderDir : 'desc';
                $orderColumn = $columnsMap[$orderColIndex] ?? 'id';

                $tahunVersi = (int) session('tahun');
                $picSubquery = DB::table('s_transaksi_2 as t')
                    ->selectRaw('MAX(TRIM(t.Pemegang_Wilayah))')
                    ->where(function ($w) {
                        $w->whereColumn('t.NIDN', 'c.nidn')
                            ->orWhereColumn('t.NUPTK', 'c.nuptk');
                    });
                if ($tahunVersi > 0) {
                    $picSubquery->where('t.Tahun_Versi', '=', $tahunVersi);
                }

                $baseQuery = DB::table('i_complain as c')
                    ->where('pelapor_tipe', 'dosen')
                    ->where('dosen_id', $dosen->id)
                    ->select(['c.*'])
                    ->selectSub($picSubquery, 'pic');

                $recordsTotal = (int) DB::table('i_complain')
                    ->where('pelapor_tipe', 'dosen')
                    ->where('dosen_id', $dosen->id)
                    ->count();

                if (trim($searchValue) !== '') {
                    $baseQuery->where(function ($q) use ($searchValue) {
                        $q->where('judul', 'like', "%{$searchValue}%")
                            ->orWhere('pesan', 'like', "%{$searchValue}%")
                            ->orWhere('status', 'like', "%{$searchValue}%")
                            ->orWhere('admin_balasan', 'like', "%{$searchValue}%");
                    });
                }

                $recordsFiltered = (int) $baseQuery->count();

                $baseQuery->orderBy($orderColumn, $orderDir)->orderByDesc('id');

                if ($length !== -1) {
                    $baseQuery->offset($start)->limit($length);
                }

                $rows = $baseQuery->get();

                $data = $rows->map(function ($r) {
                    $status = (string) ($r->status ?? 'open');
                    $badge = 'bg-label-secondary';
                    if ($status === 'open') $badge = 'bg-label-danger';
                    if ($status === 'setuju') $badge = 'bg-label-success';
                    if ($status === 'tolak') $badge = 'bg-label-warning';

                    $detailBtn = '<button type="button" class="sptjm-icon-btn sptjm-btn-view view-complain" data-id="' . $r->id . '" title="Detail"><i class="bx bx-show"></i></button>';

                    // format created_at to WIB
                    try {
                        $createdAt = $r->created_at ? Carbon::parse($r->created_at)->setTimezone('Asia/Jakarta')->format('d-m-Y H:i') : '-';
                    } catch (\Throwable $e) {
                        $createdAt = $r->created_at ?? '-';
                    }

                    return [
                        'id' => $r->id,
                        'judul' => $r->judul,
                        'pic' => $r->pic ?: '-',
                        'status' => '<span class="badge ' . $badge . '">' . strtoupper(str_replace('_', ' ', $status)) . '</span>',
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
                // Avoid DataTables Ajax error popup when table is missing or query fails
                $alias = ErrorAlias::fromThrowable($e, 'DOSEN-COMPLAIN-LIST');
                Log::error('ComplainDosenController@index ajax exception', [
                    'alias' => $alias['code'],
                    'dosen_id' => $dosen->id ?? null,
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

        return view('dosen.complain');
    }

    public function store(Request $request)
    {
        $dosen = Auth::guard('dosen')->user();
        if (!$dosen) {
            abort(403);
        }

        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'pesan' => 'required|string',
            'lampiran' => 'nullable|file|max:5120',
        ]);

        try {
            $uploadedPath = null;
            if ($request->hasFile('lampiran')) {
                $file = $request->file('lampiran');
                if ($file) {
                    $original = (string) $file->getClientOriginalName();
                    $safeOriginal = preg_replace('/[^A-Za-z0-9._-]/', '_', $original) ?: 'file';
                    $filename = 'complain_' . Str::uuid()->toString() . '_' . $safeOriginal;
                    $uploadedPath = $file->storeAs('File_Complain', $filename, 'public');
                }
            }

            $id = DB::table('i_complain')->insertGetId([
                'pelapor_tipe' => 'dosen',
                'pts_id' => null,
                'dosen_id' => $dosen->id,
                'kode_pts' => $dosen->kode_pts ?? null,
                'nidn' => $dosen->nidn ?? null,
                'nuptk' => $dosen->nuptk ?? null,
                'judul' => $validated['judul'],
                'pesan' => $validated['pesan'],
                'lampiran' => $uploadedPath,
                'status' => 'open',
                'admin_balasan' => null,
                'handled_by' => null,
                'handled_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json(['success' => true, 'message' => 'Complain berhasil dikirim.', 'id' => $id]);
        } catch (\Throwable $e) {
            if (!empty($uploadedPath)) {
                try {
                    Storage::disk('public')->delete($uploadedPath);
                } catch (\Throwable $ignored) {
                }
            }

            $alias = ErrorAlias::fromThrowable($e, 'DOSEN-COMPLAIN');
            Log::error('ComplainDosenController@store exception', [
                'alias' => $alias['code'],
                'dosen_id' => $dosen->id ?? null,
                'kode_pts' => $dosen->kode_pts ?? null,
                'nidn' => $dosen->nidn ?? null,
                'nuptk' => $dosen->nuptk ?? null,
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

    public function show($id)
    {
        $dosen = Auth::guard('dosen')->user();
        if (!$dosen) {
            abort(403);
        }

        try {
            $row = DB::table('i_complain')
                ->where('id', $id)
                ->where('pelapor_tipe', 'dosen')
                ->where('dosen_id', $dosen->id)
                ->first();

            if (!$row) {
                return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
            }

            return response()->json(['success' => true, 'data' => $row]);
        } catch (\Throwable $e) {
            $alias = ErrorAlias::fromThrowable($e, 'DOSEN-COMPLAIN-DETAIL');
            Log::error('ComplainDosenController@show exception', [
                'alias' => $alias['code'],
                'dosen_id' => $dosen->id ?? null,
                'complain_id' => $id,
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
}
