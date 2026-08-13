<?php

namespace App\Http\Controllers;

use App\Models\PengaturanUsulan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PengaturanUsulanController extends Controller
{
  public function index()
  {
    // Auto-disable any active pengaturan whose tanggal_selesai is in the past
    try {
      PengaturanUsulan::where('status', 'Aktifkan')
        ->whereDate('tanggal_selesai', '<', \Carbon\Carbon::today())
        ->update(['status' => 'Nonaktifkan']);
    } catch (\Exception $e) {
      // silently ignore update errors; page will still load
    }

    // $pengaturanUsulan = PengaturanUsulan::orderBy('tahun', 'desc')
    //   ->orderBy('pencairan_ke', 'desc')
    //   ->get();
    // // Ambil semua nilai pencairan_ke yang sudah dipakai
    // $existingPencairan = PengaturanUsulan::pluck('pencairan_ke')->toArray();
    // $pengaturan = PengaturanUsulan::latest()->first();
    // return view('admin.pengaturan-usulan', compact('pengaturanUsulan', 'existingPencairan', 'pengaturan'));

     $pengaturanUsulan = PengaturanUsulan::orderBy('tahun', 'desc')
        ->orderBy('pencairan_ke', 'desc')
        ->get();

    // Bedakan pencairan yang sudah dipakai per jenis usulan per tahun
    $existingSptjmCollection = PengaturanUsulan::where('jenis_usulan', 'SPTJM')
      ->get()
      ->groupBy('tahun')
      ->map(function ($group) {
        return $group->pluck('pencairan_ke')->toArray();
      });

    $existingTukinCollection = PengaturanUsulan::where('jenis_usulan', 'TUKIN')
      ->get()
      ->groupBy('tahun')
      ->map(function ($group) {
        return $group->pluck('pencairan_ke')->toArray();
      });

    // Convert collections to plain arrays for JSON encoding in the view
    $existingSptjm = $existingSptjmCollection->toArray();
    $existingTukin = $existingTukinCollection->toArray();

    // Compute overall max pencairan untuk SPTJM across all years
    $flatSptjm = [];
    foreach ($existingSptjm as $arr) {
      $flatSptjm = array_merge($flatSptjm, $arr);
    }
    $maxSptjm = !empty($flatSptjm) ? max($flatSptjm) : 0;

    // Konfigurasi kode cair aktif (JSON) disimpan di storage/app/kode_cair_config.json
    $configAktif = null;
    try {
      if (Storage::disk('local')->exists('kode_cair_config.json')) {
        $raw = Storage::disk('local')->get('kode_cair_config.json');
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
          $configAktif = $decoded;
        }
      }
    } catch (\Throwable $e) {
      $configAktif = null;
    }

    // Ambil daftar tahun versi dinamis dari Pengaturan Versi Admin (ActiveYears + database s_transaksi_2)
    $activeYears = \App\Helpers\ActiveYears::load();
    $dbYears = \Illuminate\Support\Facades\DB::table('s_transaksi_2')->distinct()->pluck('Tahun_Versi')->map(fn($y) => (int)$y)->toArray();
    $mergedYears = array_unique(array_merge([2023, (int)date('Y'), (int)session('tahun')], $activeYears, $dbYears));
    sort($mergedYears);
    $listTahun = array_values(array_filter($mergedYears, fn($y) => $y >= 2021));

    return view('admin.pengaturan-usulan', compact(
      'pengaturanUsulan',
      'existingSptjm',
      'existingTukin',
      'maxSptjm',
      'configAktif',
      'listTahun'
    ));
  }

  public function store(Request $request)
  {
    $request->validate([
      'jenis_usulan' => 'required|string',
      'tahun' => 'required',
      'bulan' => 'required',
      'pencairan_ke' => 'required|integer',
      'tanggal_mulai' => 'required|date',
      'tanggal_selesai' => 'required|date',
      'status' => 'required',
    ]);

    // Jika user memilih untuk mengaktifkan, pastikan aturan: maksimal 2 aktif
    // dan hanya diperbolehkan kombinasi 1 SPTJM + 1 TUKIN
    if (trim($request->status) === 'Aktifkan') {
      $active = PengaturanUsulan::where('status', 'Aktifkan')->get();

      if ($active->count() >= 2) {
        return redirect()
          ->back()
          ->withInput()
          ->with('activate-error', 'Tidak dapat mengaktifkan lebih dari 2 usulan. Hanya kombinasi 1 SPTJM dan 1 TUKIN diperbolehkan.');
      }

      if ($active->count() === 1) {
        $existing = $active->first();
        if ($existing->jenis_usulan === $request->jenis_usulan) {
          return redirect()
            ->back()
            ->withInput()
            ->with('activate-error', 'Tidak boleh mengaktifkan dua usulan dengan jenis yang sama. Hanya 1 SPTJM dan 1 TUKIN yang boleh aktif secara bersamaan.');
        }
      }
    }

    PengaturanUsulan::create($request->all());

    return redirect()
      ->route('admin.pengaturan-usulan')
      ->with('add-success', 'Pengaturan usulan berhasil ditambahkan.');
  }

  public function update(Request $request, $id)
  {
    // Validasi input jika diperlukan
    $request->validate([
      'tanggal_mulai' => 'required|date',
      'tanggal_selesai' => 'required|date',
      'status' => 'required|string',
    ]);

    // Cari data berdasarkan ID
    $data = PengaturanUsulan::findOrFail($id);

    // Jika user mengubah status menjadi Aktifkan, jalankan validasi aturan
    if (trim($request->status) === 'Aktifkan') {
      $active = PengaturanUsulan::where('status', 'Aktifkan')
        ->where('id', '<>', $id)
        ->get();

      if ($active->count() >= 2) {
        return redirect()
          ->back()
          ->withInput()
          ->with('activate-error', 'Tidak dapat mengaktifkan lebih dari 2 usulan. Hanya kombinasi 1 SPTJM dan 1 TUKIN diperbolehkan.');
      }

      if ($active->count() === 1) {
        $existing = $active->first();
        // gunakan jenis_usulan dari request jika tersedia, fallback ke data yang diedit
        $incomingJenis = $request->jenis_usulan ?? $data->jenis_usulan;
        if ($existing->jenis_usulan === $incomingJenis) {
          return redirect()
            ->back()
            ->withInput()
            ->with('activate-error', 'Tidak boleh mengaktifkan dua usulan dengan jenis yang sama. Hanya 1 SPTJM dan 1 TUKIN yang boleh aktif secara bersamaan.');
        }
      }
    }

    // Update data
    $data->update([
      'tanggal_mulai' => $request->tanggal_mulai,
      'tanggal_selesai' => $request->tanggal_selesai,
      'status' => $request->status,
    ]);

    // Redirect kembali dengan pesan sukses
    return redirect()
      ->back()
      ->with('edit-success', 'Pengaturan usulan berhasil diperbarui.');
  }

  /**
   * Tandai satu baris pengaturan sebagai sumber kode cair aktif (Ceklis).
   * Hanya satu konfigurasi yang disimpan di m_kode_cair_aktif.
   */
  public function ceklist(\Illuminate\Http\Request $request, $id)
  {
    $data = PengaturanUsulan::findOrFail($id);

    // Simpan konfigurasi JSON per jenis usulan di storage/app/kode_cair_config.json
    $payloadEntry = [
      'pencairan_ke' => (int) $data->pencairan_ke,
      'id' => (int) $data->id,
      'tahun' => $data->tahun,
      'bulan' => $data->bulan,
    ];

    try {
      $existing = [];
      if (Storage::disk('local')->exists('kode_cair_config.json')) {
        $raw = Storage::disk('local')->get('kode_cair_config.json');
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
          $existing = $decoded;
        }
      }

      // Set / overwrite the entry for this jenis_usulan
      $existing[$data->jenis_usulan] = $payloadEntry;

      Storage::disk('local')->put('kode_cair_config.json', json_encode($existing));
    } catch (\Throwable $e) {
      // ignore file write errors
    }

    if ($request->ajax() || $request->wantsJson()) {
      return response()->json(['success' => true]);
    }

    return redirect()->back();
  }

  /**
   * Hapus konfigurasi kode cair aktif (Non-Ceklis).
   */
  public function nonceklist(Request $request)
  {
    $jenis = $request->input('jenis');
    try {
      if (Storage::disk('local')->exists('kode_cair_config.json')) {
        $raw = Storage::disk('local')->get('kode_cair_config.json');
        $decoded = json_decode($raw, true);
        if (is_array($decoded) && $jenis) {
          if (isset($decoded[$jenis])) {
            unset($decoded[$jenis]);
          }
          if (empty($decoded)) {
            Storage::disk('local')->delete('kode_cair_config.json');
          } else {
            Storage::disk('local')->put('kode_cair_config.json', json_encode($decoded));
          }
        }
      }
    } catch (\Throwable $e) {
      // abaikan error hapus file
    }

    if ($request->ajax() || $request->wantsJson()) {
      return response()->json(['success' => true]);
    }

    return redirect()
      ->back()
      ->with('edit-success', 'Konfigurasi kode cair berhasil dinonaktifkan untuk jenis ' . ($jenis ?? ''));
  }
}
