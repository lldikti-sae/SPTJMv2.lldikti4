<?php

namespace App\Http\Controllers;

use App\Helpers\ErrorAlias;
use Illuminate\Http\Request;
use App\Models\Bank;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Constraint\IsFalse;
use Yajra\DataTables\Facades\DataTables;

class DataBankController extends Controller
{
  public function index(Request $request)
  {
    if ($request->ajax()) {
      $b_bank = Bank::query();
      return DataTables::of($b_bank)
        ->addColumn("aksi", function ($row) {
          $editBtn = '<button class="sptjm-icon-btn sptjm-btn-edit edit-bank"
                    data-id="' . $row->kode_bank . '"
                    data-bs-toggle="modal"
                    data-bs-target="#modalBankForm">
                    <i class="bx bx-edit"></i>
                </button>';

          $deleteForm = '<form action="' . route("admin.data-bank.destroy", ["kode" => $row->kode_bank]) . '"
                        method="POST" class="d-inline delete-form">
                        ' . csrf_field() . '
                        ' . method_field("DELETE") . '
                        <button type="button" class="sptjm-icon-btn sptjm-btn-delete delete-bank"
                            data-id="' . $row->kode_bank . '">
                            <i class="bx bx-trash"></i>
                        </button>
                    </form>';

          return $editBtn . ' ' . $deleteForm;
        })
        ->rawColumns(['aksi'])
        ->toJson();
    }
    // return view('admin.data-bank', compact('b_bank'));
    return view('admin.data-bank');
  }

  public function store(Request $request)
  {
    $request->validate([
      'nama_bank' => 'required|string|max:255',
      'kode_bank' => 'required|string|max:50|unique:b_bank,kode_bank'
    ]);

    try {
      Bank::create([
        'nama_bank' => $request->nama_bank,
        'kode_bank' => $request->kode_bank,
      ]);

      // return redirect()->back()->with('add-success', 'Data bank berhasil ditambahkan.');
      return response()->json(['success' => true, 'message' => 'Data bank berhasil ditambahkan.']);
    } catch (\Exception $e) {
      $alias = ErrorAlias::fromThrowable($e, 'ADM-BANK');
      Log::error('DataBankController@store failed', [
        'alias' => $alias['code'],
        'kode_bank' => (string) $request->input('kode_bank'),
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);

      return response()->json([
        'success' => false,
        'message' => 'Terjadi kesalahan saat menambahkan data bank. (Kode: ' . $alias['code'] . ')',
        'code' => $alias['code'],
      ], 500);
    }
  }

  public function edit($kode_bank)
  {
    $bank = Bank::where('kode_bank', $kode_bank)->first();

    if (!$bank) {
      return response()->json(['message' => 'Data tidak ditemukan'], 404);
    }

    return response()->json($bank);
  }

  public function update(Request $request, $kode)
  {
    try {
      // Mencari data berdasarkan kode_bank
      $bank = Bank::where('kode_bank', $kode)->firstOrFail();

      // Validasi input
      $request->validate([
        'nama_bank' => 'required|string|max:255',
        'kode_bank' => "required|string|max:50|unique:b_bank,kode_bank,$kode,kode_bank"
      ]);

      // Update data
      $bank->update([
        'nama_bank' => $request->nama_bank,
        'kode_bank' => $request->kode_bank,
      ]);

      // Redirect dengan alert sukses
      // return redirect()->back()->with('edit-success', 'Data bank berhasil diperbarui.');
      return response()->json(['success' => true, 'message' => 'Data bank berhasil diperbarui.']);
    } catch (\Exception $e) {
      $alias = ErrorAlias::fromThrowable($e, 'ADM-BANK');
      Log::error('DataBankController@update failed', [
        'alias' => $alias['code'],
        'kode' => (string) $kode,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);

      return response()->json([
        'success' => false,
        'message' => 'Terjadi kesalahan saat memperbarui data bank. (Kode: ' . $alias['code'] . ')',
        'code' => $alias['code'],
      ], 500);
    }
  }

  public function destroy($kode)
  {
    try {
      $bank = Bank::findOrFail($kode);
      $bank->delete();
      // return redirect()->back()->with('success', 'Data bank berhasil dihapus.');
      return response()->json(['success' => true, 'message' => "Data Berhasil Dihapus!"]);
    } catch (\Exception $e) {
      $alias = ErrorAlias::fromThrowable($e, 'ADM-BANK');
      Log::error('DataBankController@destroy failed', [
        'alias' => $alias['code'],
        'kode' => (string) $kode,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);

      return response()->json([
        'success' => false,
        'message' => 'Terjadi kesalahan saat menghapus data bank. (Kode: ' . $alias['code'] . ')',
        'code' => $alias['code'],
      ], 500);
    }
  }
}
