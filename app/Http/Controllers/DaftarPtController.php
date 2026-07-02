<?php

namespace App\Http\Controllers;

use App\Helpers\ErrorAlias;
use Illuminate\Http\Request;
use App\Models\Pts;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;

class DaftarPtController extends Controller
{
  public function index(Request $request)
  {
    $user = Auth::user();

    if ($request->ajax()) {
      $query = Pts::select('*');
      
      if ($user && $user->role === 'pic') {
          $query->where('wilayah', $user->email);
      }

      return DataTables::of($query)
        ->addColumn('aksi', function ($row) {
          return "<a href='" . route('admin.daftar-pt.edit', ['id' => $row->id]) . "'
                                    class='btn btn-sm btn-warning'>
                                    <i class='bx bx-edit'></i>
                                </a>
";
        })
        ->editColumn('aktif', function ($row) {
          return $row->aktif == 1 ? '<span class="badge bg-label-primary">Aktif</span>
' : '<span class="badge bg-label-danger">Tidak Aktif</span>';
        })
        ->rawColumns(['aksi', 'aktif'])
        ->make(true);
    }
    
    if ($user && $user->role === 'pic') {
        $kode_pts = Pts::where('wilayah', $user->email)->get();
    } else {
        $kode_pts = Pts::all();
    }
    
    $users = User::all();
    return view('admin.daftar-pt', compact('kode_pts', 'users'));
  }

  public function create()
  {
    $user = Auth::user();
    if ($user && $user->role === 'pic') {
        $kode_pts = Pts::where('wilayah', $user->email)->get();
    } else {
        $kode_pts = Pts::all();
    }
    $users = User::all();
    return view('admin.daftar-pt', compact('kode_pts', 'users'));
  }

  public function store(Request $request)
  {
    $request->validate([
      'kode_pts' => ['required', 'regex:/^[1-9][0-9]*$/', 'unique:a_pts,kode_pts'],
      'nama_pts' => 'required|string|max:250',
      'nama_pimpinan' => 'required|string|max:100',
      'jabatan_pimpinan' => 'required|string|max:100',
      'alamat_pt' => 'required|string|max:250',
      'wilayah' => 'required|string|max:50',
      'password' => 'required|string|max:255',
      'aktif' => 'required|integer',
      'dokumen' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
    ]);

    try {
      $user = Auth::user();
      $data_pts = new Pts();
      $data_pts->kode_pts = $request->kode_pts;
      $data_pts->nama_pts = $request->nama_pts;
      $data_pts->nama_pimpinan = $request->nama_pimpinan;
      $data_pts->jabatan_pimpinan = $request->jabatan_pimpinan;
      $data_pts->alamat_pt = $request->alamat_pt;
      
      // Jika PIC yang membuat, paksa wilayahnya sesuai dengan wilayah PIC tersebut
      if ($user && $user->role === 'pic') {
          $data_pts->wilayah = $user->email;
      } else {
          $data_pts->wilayah = $request->wilayah;
      }
      $data_pts->password = $request->password;
      $data_pts->aktif = $request->aktif;

      if ($request->hasFile('dokumen')) {
        $file = $request->file('dokumen');
        $tanggalFormat = now()->format('dmY');
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $slugName = Str::slug($originalName, '_');
        $finalFileName = "{$tanggalFormat}_{$slugName}.{$file->getClientOriginalExtension()}";

        // Simpan ke storage/app/public/Dokumen_PTS dan layani via /storage/Dokumen_PTS/
        Storage::putFileAs('public/Dokumen_PTS', $file, $finalFileName);

        // Simpan nama file ke database
        $data_pts->dokumen = $finalFileName;
      }

      $data_pts->save();

      // return redirect()
      //   ->route('admin.daftar-pt')
      //   ->with('success', 'Data PT berhasil ditambahkan.');

      return response()->json(['success' => true, 'message' => 'Data PT Berhasil Disimpan!']);
    } catch (\Exception $e) {
      $alias = ErrorAlias::fromThrowable($e, 'ADM-PTS');
      Log::error('DaftarPtController@store failed', [
        'alias' => $alias['code'],
        'kode_pts' => (string) $request->input('kode_pts'),
        'nama_pts' => (string) $request->input('nama_pts'),
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);

      return response()->json([
        'success' => false,
        'message' => 'Terjadi kesalahan saat menyimpan data PT. (Kode: ' . $alias['code'] . ')',
        'code' => $alias['code'],
      ], 500);
    }
  }

  public function edit($id)
  {
    $data_pts = Pts::findOrFail($id);
    $user = Auth::user();
    
    if ($user && $user->role === 'pic' && $data_pts->wilayah !== $user->email) {
        abort(403, 'Anda tidak memiliki akses ke Perguruan Tinggi di wilayah ini.');
    }
    
    $users = User::pluck('email');
    return view('admin.edit-pt', compact('data_pts', 'users'));
  }

  public function update(Request $request, $id)
  {
    $data_pts = Pts::findOrFail($id);
    $user = Auth::user();
    
    if ($user && $user->role === 'pic' && $data_pts->wilayah !== $user->email) {
        abort(403, 'Anda tidak memiliki akses untuk mengubah Perguruan Tinggi di wilayah ini.');
    }

    $request->validate([
      'kode_pts' => [
        'required',
        'regex:/^[1-9][0-9]*$/',
        'unique:a_pts,kode_pts,' . $id . ',id', // <- Ignore ID saat validasi unik
      ],
      'nama_pts' => 'required|string|max:250',
      'nama_pimpinan' => 'required|string|max:100',
      'jabatan_pimpinan' => 'required|string|max:100',
      'alamat_pt' => 'required|string|max:250',
      'wilayah' => 'required|string|max:50',
      'password' => 'nullable|string|max:255',
      'aktif' => 'required|integer',
      'dokumen' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
    ]);

    // Update data termasuk kode_pts
    $data_pts->kode_pts = $request->kode_pts;
    $data_pts->nama_pts = $request->nama_pts;
    $data_pts->nama_pimpinan = $request->nama_pimpinan;
    $data_pts->jabatan_pimpinan = $request->jabatan_pimpinan;
    $data_pts->alamat_pt = $request->alamat_pt;
    
    if (!($user && $user->role === 'pic')) {
        $data_pts->wilayah = $request->wilayah;
    }

    if (!empty($request->password)) {
      $data_pts->password = $request->password;
    }

    $data_pts->aktif = $request->aktif;

    if ($request->hasFile('dokumen')) {
      $file = $request->file('dokumen');
      $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
      $extension = $file->getClientOriginalExtension();
      $formattedDate = now()->format('dmY');

      // Gunakan slug untuk nama file
      $slugName = Str::slug($originalName, '_');
      $filename = "{$formattedDate}_{$slugName}.{$extension}";

      // Simpan di storage/app/public/Dokumen_PTS dan layani via /storage/Dokumen_PTS/
      Storage::putFileAs('public/Dokumen_PTS', $file, $filename);

      // Simpan nama file ke database
      $data_pts->dokumen = $filename;
    }

    $data_pts->tanggal_update = now();
    $data_pts->save();

    return redirect()
      ->route('admin.daftar-pt.edit', $id)
      ->with('edit-success', 'Data Perguruan Tinggi berhasil diperbarui!');
  }

  public function updateWilayah(Request $request)
  {
    $request->validate([
      'kode_pts' => 'required|exists:a_pts,kode_pts',
      'pemegang_wilayah_baru' => 'required|string|max:50',
    ]);

    try {
      $kode_pts = $request->kode_pts;
      $data_pts = Pts::where('kode_pts', $kode_pts)->firstOrFail();
      $wilayahBaru = $request->pemegang_wilayah_baru;

      // Update wilayah di master PT
      $data_pts->wilayah = $wilayahBaru;
      $data_pts->save();

      // Update wilayah di tabel transaksi sesuai kode PT dan Tahun_Versi (tahun sesi login)
      DB::table('s_transaksi_2')
        ->where('Kode_PT', $kode_pts)
        ->where('Tahun_Versi', session('tahun'))
        ->update(['Pemegang_wilayah' => $wilayahBaru]);

      return response()->json(['success' => true, 'message' => "Data Berhasil di Update!"]);
    } catch (\Exception $e) {
      $alias = ErrorAlias::fromThrowable($e, 'ADM-PTS');
      Log::error('DaftarPtController@updateWilayah failed', [
        'alias' => $alias['code'],
        'kode_pts' => (string) $request->input('kode_pts'),
        'pemegang_wilayah_baru' => (string) $request->input('pemegang_wilayah_baru'),
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);

      return response()->json([
        'success' => false,
        'message' => 'Terjadi kesalahan saat mengubah wilayah. (Kode: ' . $alias['code'] . ')',
        'code' => $alias['code'],
      ], 500);
    }
  }
}
