<?php

namespace App\Http\Controllers;

use App\Models\ADosen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterDosenController extends Controller
{
    /**
     * Display a listing of a_dosen (server-side DataTables).
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $draw = (int) $request->get('draw', 0);
            $start = (int) $request->get('start', 0);
            $length = (int) $request->get('length', 10);

            $searchValue = (string) $request->input('search.value', '');

            $order = $request->get('order', []);
            $orderColIndex = $order[0]['column'] ?? null;
            $orderDir = $order[0]['dir'] ?? 'asc';

            $columnsMap = [
                0 => 'nidn',
                1 => 'nuptk',
                2 => 'kode_pts',
                3 => 'nama_pts',
                4 => 'nama_dosen',
                5 => 'aktif',
                6 => 'wilayah',
                7 => 'tanggal_update',
            ];
            $orderColumn = $columnsMap[$orderColIndex] ?? 'id';

            $baseQuery = DB::table('a_dosen');

            $recordsTotal = (int) DB::table('a_dosen')->count();

            if (trim($searchValue) !== '') {
                $baseQuery->where(function ($q) use ($searchValue) {
                    $q->where('nidn', 'like', "%{$searchValue}%")
                        ->orWhere('nuptk', 'like', "%{$searchValue}%")
                        ->orWhere('kode_pts', 'like', "%{$searchValue}%")
                        ->orWhere('nama_pts', 'like', "%{$searchValue}%")
                        ->orWhere('nama_dosen', 'like', "%{$searchValue}%")
                        ->orWhere('wilayah', 'like', "%{$searchValue}%")
                        ->orWhere('aktif', 'like', "%{$searchValue}%");
                });
            }

            $recordsFiltered = (int) $baseQuery->count();

            if (in_array($orderColumn, array_values($columnsMap), true)) {
                $baseQuery->orderBy($orderColumn, $orderDir);
            } else {
                $baseQuery->orderBy('id', 'desc');
            }

            if ($length !== -1) {
                $baseQuery->offset($start)->limit($length);
            }

            $rows = $baseQuery->get();

            $data = $rows->map(function ($r) {
                $editBtn = '<button class="sptjm-icon-btn sptjm-btn-edit edit-dosen" data-id="' . $r->id . '" title="Edit">'
                    . '<i class="bx bx-edit"></i></button>';

                $resetBtn = '<form method="POST" action="/admin/master-dosen/' . $r->id . '/reset-password" class="d-inline reset-form" style="display:inline">'
                    . csrf_field()
                    . '<button type="button" class="sptjm-icon-btn sptjm-btn-reset reset-password" title="Reset Password">'
                    . '<i class="bx bx-key"></i></button></form>';

                $deleteBtn = '<form method="POST" action="/admin/master-dosen/' . $r->id . '" class="d-inline delete-form" style="display:inline">'
                    . csrf_field()
                    . method_field('DELETE')
                    . '<button type="button" class="sptjm-icon-btn sptjm-btn-delete delete-dosen" title="Hapus">'
                    . '<i class="bx bx-trash"></i></button></form>';

                return [
                    'id' => $r->id,
                    'nidn' => $r->nidn,
                    'nuptk' => $r->nuptk,
                    'kode_pts' => $r->kode_pts,
                    'nama_pts' => $r->nama_pts,
                    'nama_dosen' => $r->nama_dosen,
                    'aktif' => $r->aktif,
                    'wilayah' => $r->wilayah,
                    'tanggal_update' => $r->tanggal_update,
                    'aksi' => '<div class="d-flex justify-content-center gap-1">' . $editBtn . $resetBtn . $deleteBtn . '</div>',
                ];
            });

            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $data,
            ]);
        }

        $rows = DB::table('a_dosen')->orderByDesc('id')->limit(100)->get();

        $ptsOptions = DB::table('a_pts')
            ->select('kode_pts', 'nama_pts', 'alamat_pt')
            ->whereNotNull('kode_pts')
            ->orderBy('kode_pts')
            ->get();

        $picEmails = DB::table('users')
            ->select('email')
            ->where('role', 'pic')
            ->whereNotNull('email')
            ->orderBy('email')
            ->pluck('email');

        return view('admin.master-dosen', [
            'rows' => $rows,
            'ptsOptions' => $ptsOptions,
            'picEmails' => $picEmails,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nidn' => 'nullable|string|max:100',
            'nuptk' => 'nullable|string|max:100',
            'kode_pts' => 'nullable|string|max:100',
            'nama_pts' => 'nullable|string|max:250',
            'nama_dosen' => 'nullable|string|max:100',
            'alamat_pt' => 'nullable|string|max:250',
            'wilayah' => 'nullable|string|max:50',
            'aktif' => 'nullable|integer|in:0,1',
            'dokumen' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
        ]);

        // Determine password: use provided password if present, otherwise NIDN if available, else NUPTK.
        $nidnVal = trim((string) ($validated['nidn'] ?? ''));
        $nuptkVal = trim((string) ($validated['nuptk'] ?? ''));
        $passwordToStore = null;
        if (!empty($validated['password'] ?? null)) {
            $passwordToStore = $validated['password'];
        } elseif ($nidnVal !== '') {
            $passwordToStore = $nidnVal;
        } elseif ($nuptkVal !== '') {
            $passwordToStore = $nuptkVal;
        }

        $id = DB::table('a_dosen')->insertGetId([
            'nidn' => $validated['nidn'] ?? null,
            'nuptk' => $validated['nuptk'] ?? null,
            'kode_pts' => $validated['kode_pts'] ?? null,
            'nama_pts' => $validated['nama_pts'] ?? null,
            'nama_dosen' => $validated['nama_dosen'] ?? null,
            'alamat_pt' => $validated['alamat_pt'] ?? null,
            'password' => $passwordToStore,
            'aktif' => $validated['aktif'] ?? null,
            'wilayah' => $validated['wilayah'] ?? null,
            'dokumen' => $validated['dokumen'] ?? null,
            'tanggal_update' => now(),
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Data tersimpan.', 'id' => $id]);
        }

        return redirect()->route('admin.master-dosen.index')->with('success', 'Data tersimpan.');
    }

    public function edit($id)
    {
        $row = ADosen::query()->find($id);
        if (!$row) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
        }

        return response()->json($row);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nidn' => 'nullable|string|max:100',
            'nuptk' => 'nullable|string|max:100',
            'kode_pts' => 'nullable|string|max:100',
            'nama_pts' => 'nullable|string|max:250',
            'nama_dosen' => 'nullable|string|max:100',
            'alamat_pt' => 'nullable|string|max:250',
            'wilayah' => 'nullable|string|max:50',
            'aktif' => 'nullable|integer|in:0,1',
            'dokumen' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:6|max:255',
        ]);

        $update = [
            'nidn' => $validated['nidn'] ?? null,
            'nuptk' => $validated['nuptk'] ?? null,
            'kode_pts' => $validated['kode_pts'] ?? null,
            'nama_pts' => $validated['nama_pts'] ?? null,
            'nama_dosen' => $validated['nama_dosen'] ?? null,
            'alamat_pt' => $validated['alamat_pt'] ?? null,
            'aktif' => $validated['aktif'] ?? null,
            'wilayah' => $validated['wilayah'] ?? null,
            'dokumen' => $validated['dokumen'] ?? null,
            'tanggal_update' => now(),
        ];

        if (!empty($validated['password'] ?? null)) {
            $update['password'] = $validated['password'];
        }

        DB::table('a_dosen')->where('id', $id)->update($update);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Data diperbarui.']);
        }

        return redirect()->route('admin.master-dosen.index')->with('success', 'Data diperbarui.');
    }

    public function destroy(Request $request, $id)
    {
        DB::table('a_dosen')->where('id', $id)->delete();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Data dihapus.']);
        }

        return redirect()->route('admin.master-dosen.index')->with('success', 'Data dihapus.');
    }

    /**
     * Reset password dosen menjadi NIDN (jika ada) atau NUPTK.
     * Catatan: aplikasi ini menggunakan password plaintext untuk login dosen.
     */
    public function resetPassword(Request $request, $id)
    {
        $row = ADosen::query()->find($id);
        if (!$row) {
            return response()->json(['success' => false, 'message' => 'Data dosen tidak ditemukan.'], 404);
        }

        $nidn = trim((string) ($row->nidn ?? ''));
        $nuptk = trim((string) ($row->nuptk ?? ''));

        $newPassword = $nidn !== '' ? $nidn : $nuptk;
        if ($newPassword === '') {
            return response()->json(['success' => false, 'message' => 'NIDN dan NUPTK kosong, tidak bisa reset password.'], 422);
        }

        DB::table('a_dosen')->where('id', $row->id)->update([
            'password' => $newPassword,
            'tanggal_update' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil direset (NIDN jika ada, jika tidak NUPTK).',
        ]);
    }
}
