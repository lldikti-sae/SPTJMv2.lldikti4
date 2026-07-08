<?php

namespace App\Http\Controllers;

use App\Helpers\ErrorAlias;
use Illuminate\Http\Request;
use App\Models\Grade;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class DataGradeController extends Controller
{
  public function index(Request $request)
  {

    if ($request->ajax()) {
      $c_grade = Grade::where('kode', '!=', 0)
        ->orderByRaw('CAST(kode AS UNSIGNED) ASC');

      return DataTables::of($c_grade)
        ->addColumn('aksi', function ($row) {
          $editBtn = '<button class="sptjm-icon-btn sptjm-btn-edit edit-grade" data-id="' . $row->kode . '"><i class="bx bx-edit"></i></button>';

          $deleteForm = '<form action="' . route('admin.data-grade.destroy', ['kode' => $row->kode]) . '" method="POST" class="d-inline delete-form">'
            . csrf_field()
            . method_field('DELETE')
            . '<button type="button" class="sptjm-icon-btn sptjm-btn-delete delete-grade" data-id="' . $row->kode . '"><i class="bx bx-trash"></i></button>'
            . '</form>';

          return $editBtn . ' ' . $deleteForm;
        })
        ->rawColumns(['aksi'])
        ->toJson();
    }
    // return view('admin.data-grade', compact('c_grade'));
    return view('admin.data-grade');
  }

  public function store(Request $request)
  {
    $request->validate([
      'kode' => 'required|string|max:50|unique:c_grade,kode',
      'gol' => 'required|string|max:50',
      'masa_kerja' => 'required|integer',
      'nominal' => 'required|numeric',
    ]);
    // dd('masuk');
    try {
      Grade::create([
        'kode' => $request->kode,
        'gol' => $request->gol,
        'masa_kerja' => $request->masa_kerja,
        'nominal' => $request->nominal,
      ]);

      return response()->json(['success' => true, 'message' => 'Data grade berhasil ditambahkan.']);
    } catch (\Exception $e) {
      $alias = ErrorAlias::fromThrowable($e, 'ADM-GRADE');
      Log::error('DataGradeController@store failed', [
        'alias' => $alias['code'],
        'kode' => (string) $request->input('kode'),
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);

      return response()->json([
        'success' => false,
        'message' => 'Terjadi kesalahan saat menambahkan data grade. (Kode: ' . $alias['code'] . ')',
        'code' => $alias['code'],
      ], 500);
    }
  }

  public function edit($kode)
  {
    $grade = Grade::findOrFail($kode);
    return response()->json($grade);
  }

  public function update(Request $request, $kode)
  {
    $request->validate([
      'kode' => 'required|integer',
      'gol' => 'nullable|string|max:5',
      'masa_kerja' => 'nullable|integer',
      'nominal' => 'nullable|integer',
    ]);
    try {
      $grade = Grade::findOrFail($kode);
      $grade->update([
        'kode' => $request->kode,
        'gol' => $request->gol,
        'masa_kerja' => $request->masa_kerja,
        'nominal' => $request->nominal,
      ]);
      return response()->json(['success' => true, 'message' => 'Data grade berhasil diperbarui.']);
    } catch (\Exception $e) {
      $alias = ErrorAlias::fromThrowable($e, 'ADM-GRADE');
      Log::error('DataGradeController@update failed', [
        'alias' => $alias['code'],
        'kode' => (string) $kode,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);

      return response()->json([
        'success' => false,
        'message' => 'Terjadi kesalahan saat memperbarui data grade. (Kode: ' . $alias['code'] . ')',
        'code' => $alias['code'],
      ], 500);
    }
  }

  public function destroy($kode)
  {
    try {
      $grade = Grade::findOrFail($kode);
      $grade->delete();
      return response()->json(['success' => true, 'message' => 'Data grade berhasil dihapus.']);
    } catch (\Exception $e) {
      $alias = ErrorAlias::fromThrowable($e, 'ADM-GRADE');
      Log::error('DataGradeController@destroy failed', [
        'alias' => $alias['code'],
        'kode' => (string) $kode,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);

      return response()->json([
        'success' => false,
        'message' => 'Terjadi kesalahan saat menghapus data grade. (Kode: ' . $alias['code'] . ')',
        'code' => $alias['code'],
      ], 500);
    }
  }
}
