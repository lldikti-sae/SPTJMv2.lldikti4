<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sisternas;
use App\Helpers\ErrorAlias;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DataSisternasController extends Controller
{
  public function index()
  {
    $dataSisternas = Sisternas::orderBy('tanggal', 'desc')->get();
    return view('admin.data-sisternas', compact('dataSisternas'));
  }

  public function periode()
  {
    $dataSisternas = Sisternas::orderBy('tahun', 'desc')->orderBy('tanggal', 'asc')->get();
    $tahunList = range(2023, (int) date('Y'));

    $periodeData = $dataSisternas->map(function ($item) {
      $isGanjil = stripos($item->periode, 'ganjil') !== false ||
                  in_array(strtolower($item->periode), ['juli','agustus','september','oktober','november','desember']);
      return [
        'id'           => $item->id,
        'tahun'        => $item->tahun,
        'periode'      => $item->periode,   // bulan cut-off (e.g. "Juli")
        'bulan'        => $item->bulan,     // periode laporan (e.g. "[Sept-Des] Ganjil TL")
        'semester'     => $isGanjil ? 'ganjil' : 'genap',
        'periodeLabel' => $item->tahun . '/' . ($isGanjil ? '1' : '2'),
        'tanggal'      => $item->tanggal,
        'dokumen'      => $item->dokumen,
      ];
    })->values();

    return redirect()->route('admin.cutoff-sisternas');
  }

  public function destroy($id)
  {
    $data = Sisternas::findOrFail($id);
    if ($data->dokumen != null) {
      Storage::disk('public')->delete('File_Data_Sisternas2/' . $data->dokumen);
    }

    $data->delete();

    return redirect()
      ->route('admin.data-sisternas')
      ->with('success', 'Data berhasil dihapus.');
  }

  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'tahun' => 'required|string',
      'periode' => 'required|string',
      'bulan' => 'required|string',
      // max:20480 = 20 MB (dalam kilobytes)
      'dokumen' => 'required|file|mimes:csv,txt,pdf,doc,docx,xlsx|max:20480',
      'tanggal' => 'required|date',
    ]);

    if ($validator->fails()) {
      return response()->json([
        'status' => false,
        'message' => 'Validasi gagal, periksa kembali input Anda.',
        'errors' => $validator->errors(),
      ], 422);
    }

    try {
      $file = $request->file('dokumen');
      $tanggalFormat = date('dmY', strtotime($request->tanggal));
      $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
      $slugName = Str::slug($originalName, '_');
      $finalFileName = $tanggalFormat . '_' . $slugName . '.' . $file->getClientOriginalExtension();

      // Simpan ke storage/app/public/File_Data_Sisternas2 melalui disk "public"
      Storage::disk('public')->putFileAs('File_Data_Sisternas2', $file, $finalFileName);

      Sisternas::create([
        'tahun' => $request->tahun,
        'bulan' => $request->periode,
        'periode' => $request->bulan,
        'dokumen' => $finalFileName,
        'tanggal' => $request->tanggal,
      ]);

      return response()->json([
        'status' => true,
        'message' => 'Data berhasil tersimpan!',
      ]);
    } catch (\Throwable $e) {
      $alias = ErrorAlias::fromThrowable($e, 'ADM-SISTERNAS');
      Log::error('DataSisternasController@store failed', [
        'alias' => $alias['code'],
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);

      // Best-effort cleanup if file was uploaded but DB insert failed
      if (isset($finalFileName) && $finalFileName) {
        try {
          Storage::disk('public')->delete('File_Data_Sisternas2/' . $finalFileName);
        } catch (\Throwable $ignored) {
          // ignore
        }
      }

      return response()->json([
        'status' => false,
        'message' => $alias['message'],
        'code' => $alias['code'],
      ], 500);
    }


    // return redirect()
    //   ->route('admin.data-sisternas')
    //   ->with('success', 'Data berhasil ditambahkan.');
  }
}
