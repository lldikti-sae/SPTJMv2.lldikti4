<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CGradeSerdosController extends Controller
{
    private function findOverlappingRange(string $jabatan, int $bawah, int $atas, ?int $excludeId = null)
    {
        $query = DB::table('c_grade_serdos')
            ->where('jabatan', trim($jabatan))
            // overlap if existing.bawah <= new.atas AND existing.atas >= new.bawah
            ->where('masa_kerja_bawah', '<=', $atas)
            ->where('masa_kerja_atas', '>=', $bawah);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->orderBy('masa_kerja_bawah')->first();
    }

    /**
     * Display a listing of c_grade_serdos.
     */
    public function index()
    {
        if (request()->ajax()) {
            $draw = (int) request()->get('draw', 0);
            $start = (int) request()->get('start', 0);
            $length = (int) request()->get('length', 10);

            $searchValue = request()->input('search.value');

            // map DataTables column index to DB column name
            $order = request()->get('order', []);
            $orderColIndex = $order[0]['column'] ?? null;
            $orderDir = $order[0]['dir'] ?? 'asc';
            $columnsMap = [
                0 => 'jabatan',
                1 => 'masa_kerja_bawah',
                2 => 'masa_kerja_atas',
                3 => 'golongan'
            ];
            $orderColumn = $columnsMap[$orderColIndex] ?? 'jabatan';

            // base query
            $query = DB::table('c_grade_serdos');

            $recordsTotal = DB::table('c_grade_serdos')->count();

            // apply search filter
            if (!empty($searchValue)) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('jabatan', 'like', "%{$searchValue}%")
                        ->orWhere('golongan', 'like', "%{$searchValue}%")
                        ->orWhere('masa_kerja_bawah', 'like', "%{$searchValue}%")
                        ->orWhere('masa_kerja_atas', 'like', "%{$searchValue}%");
                });
            }

            $recordsFiltered = $query->count();

            // apply ordering
            if (in_array($orderColumn, ['jabatan', 'masa_kerja_bawah', 'masa_kerja_atas', 'golongan'])) {
                $query->orderBy($orderColumn, $orderDir);
            } else {
                $query->orderBy('jabatan')->orderBy('masa_kerja_bawah')->orderBy('masa_kerja_atas');
            }

            // apply paging
            if ($length != -1) {
                $query->offset($start)->limit($length);
            }

            $rows = $query->get();

            $data = $rows->map(function ($r) {
                $editBtn = '<button class="sptjm-icon-btn sptjm-btn-edit edit-grade" data-id="' . $r->id . '" title="Edit">'
                    . '<i class="bx bx-edit"></i></button>';
                $deleteBtn = '<form method="POST" action="/admin/grade-serdos/' . $r->id . '" class="d-inline delete-form" style="display:inline">'
                    . csrf_field()
                    . method_field('DELETE')
                    . '<button type="button" class="sptjm-icon-btn sptjm-btn-delete delete-grade" title="Hapus">'
                    . '<i class="bx bx-trash"></i></button></form>';

                return [
                    'id' => $r->id,
                    'jabatan' => $r->jabatan,
                    'masa_kerja_bawah' => $r->masa_kerja_bawah,
                    'masa_kerja_atas' => $r->masa_kerja_atas,
                    'golongan' => $r->golongan,
                    'aksi' => $editBtn . ' ' . $deleteBtn,
                ];
            });

            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $data,
            ]);
        }

        // non-AJAX: render server-side rows as a fallback
        $rows = DB::table('c_grade_serdos')
            ->orderBy('jabatan')
            ->orderBy('masa_kerja_bawah')
            ->orderBy('masa_kerja_atas')
            ->get();

        return view('admin.grade-serdos', ['rows' => $rows]);
    }

    public function store(Request $request)
    {
        Log::info('CGradeSerdosController@store called', ['input' => $request->all()]);
        $request->validate([
            'jabatan' => 'required|string',
            'masa_kerja_bawah' => 'required|integer|min:0|max:255|lte:masa_kerja_atas',
            'masa_kerja_atas' => 'required|integer|min:0|max:255|gte:masa_kerja_bawah',
            'golongan' => 'required|string',
        ]);

        $jabatan = trim((string) $request->jabatan);
        $bawah = (int) $request->masa_kerja_bawah;
        $atas = (int) $request->masa_kerja_atas;

        $overlap = $this->findOverlappingRange($jabatan, $bawah, $atas);
        if ($overlap) {
            $message = "Range masa kerja {$bawah}-{$atas} untuk jabatan '{$jabatan}' bertabrakan dengan data lain ({$overlap->masa_kerja_bawah}-{$overlap->masa_kerja_atas}).";
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }
            return back()->withErrors(['range' => $message])->withInput();
        }

        $id = DB::table('c_grade_serdos')->insertGetId([
            'jabatan' => $jabatan,
            'masa_kerja_bawah' => $bawah,
            'masa_kerja_atas' => $atas,
            'golongan' => $request->golongan,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Log::info('CGradeSerdosController@store inserted', ['id' => $id]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Data tersimpan.', 'id' => $id]);
        }

        return redirect()->route('admin.grade-serdos.index')->with('success', 'Data tersimpan.');
    }

    public function edit($id)
    {
        $row = DB::table('c_grade_serdos')->where('id', $id)->first();
        if (!$row) return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
        return response()->json($row);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'jabatan' => 'required|string',
            'masa_kerja_bawah' => 'required|integer|min:0|max:255|lte:masa_kerja_atas',
            'masa_kerja_atas' => 'required|integer|min:0|max:255|gte:masa_kerja_bawah',
            'golongan' => 'required|string',
        ]);

        $jabatan = trim((string) $request->jabatan);
        $bawah = (int) $request->masa_kerja_bawah;
        $atas = (int) $request->masa_kerja_atas;

        $overlap = $this->findOverlappingRange($jabatan, $bawah, $atas, (int) $id);
        if ($overlap) {
            $message = "Range masa kerja {$bawah}-{$atas} untuk jabatan '{$jabatan}' bertabrakan dengan data lain ({$overlap->masa_kerja_bawah}-{$overlap->masa_kerja_atas}).";
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }
            return back()->withErrors(['range' => $message])->withInput();
        }

        DB::table('c_grade_serdos')->where('id', $id)->update([
            'jabatan' => $jabatan,
            'masa_kerja_bawah' => $bawah,
            'masa_kerja_atas' => $atas,
            'golongan' => $request->golongan,
            'updated_at' => now(),
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Data diperbarui.']);
        }

        return redirect()->route('admin.grade-serdos.index')->with('success', 'Data diperbarui.');
    }

    public function destroy(Request $request, $id)
    {
        DB::table('c_grade_serdos')->where('id', $id)->delete();
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Data dihapus.']);
        }

        return redirect()->route('admin.grade-serdos.index')->with('success', 'Data dihapus.');
    }
}
