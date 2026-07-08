<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NonaktifkanDosenPtsController extends Controller
{
  public function index()
  {
    // Ambil kode_pts dari user yang login via guard pts
    $kodePts = Auth::guard('pts')->user()->kode_pts;

    // Tentukan tahun versi (dari session jika ada, jika tidak gunakan tahun sekarang)
    $tahun = session('tahun') ?? date('Y');
    $bulan = (int) session('bulan');
    if ($bulan < 1 || $bulan > 12) $bulan = 12;
    $jabatanCol = "Jabatan{$bulan}";

    // Ambil data dosen dari tabel s_transaksi_2 berdasarkan Kode_PT dan Tahun_Versi
    // Alias kolom agar sesuai dengan property yang digunakan di view
    $dosenList = DB::table('s_transaksi_2')
      ->select([
        'NIDN as nidn',
        'NUPTK as nuptk',
        'Nama as nama',
        DB::raw("{$jabatanCol} as jabatan"),
        'Aktif as aktif',
        'Tahun_Versi as tahun_versi',
      ])
      ->where('Kode_PT', $kodePts)
      ->where('Tahun_Versi', $tahun)
      ->orderBy('Nama')
      ->get();

    // Kirim data ke view
    return view('pts.nonaktifkan-dosen', compact('dosenList'));
  }

  /**
   * Return JSON data for DataTables (server-side)
   */
  public function data(Request $request)
  {
    $kodePts = Auth::guard('pts')->user()->kode_pts;
    $tahun = session('tahun') ?? date('Y');
    $bulan = (int) session('bulan');
    if ($bulan < 1 || $bulan > 12) $bulan = 12;
    $jabatanCol = "Jabatan{$bulan}";

    $query = DB::table('s_transaksi_2')
      ->where('Kode_PT', $kodePts)
      ->where('Tahun_Versi', $tahun);

    $recordsTotal = $query->count();

    // Global search: support NIDN or NUPTK
    $search = $request->input('search.value');
    if (!empty($search)) {
      $query = $query->where(function ($q) use ($search) {
        $q->where('NIDN', 'like', "%{$search}%")
          ->orWhere('NUPTK', 'like', "%{$search}%")
          ->orWhere('Nama', 'like', "%{$search}%");
      });
    }

    $recordsFiltered = $query->count();

    // Ordering
    $orderColIndex = $request->input('order.0.column');
    $orderDir = $request->input('order.0.dir', 'asc');
    $columns = [null, 'NIDN', 'NUPTK', 'Nama', $jabatanCol, 'Aktif'];
    if (isset($columns[$orderColIndex]) && $columns[$orderColIndex]) {
      $query = $query->orderBy($columns[$orderColIndex], $orderDir);
    } else {
      $query = $query->orderBy('Nama');
    }

    // Pagination
    $start = intval($request->input('start', 0));
    $length = intval($request->input('length', 10));
    $data = $query->offset($start)->limit($length)->get([
      'NIDN as nidn',
      'NUPTK as nuptk',
      'Nama as nama',
      DB::raw("{$jabatanCol} as jabatan"),
      'Aktif as aktif',
    ]);

    // Prepare rows
    $rows = [];
    foreach ($data as $row) {
      $isActive = ($row->aktif === '1' || $row->aktif === 1 || $row->aktif === '1');
      $statusLabel = $isActive ? '<span class="badge bg-label-primary">Aktif</span>' : '<span class="badge bg-label-danger">Tidak Aktif</span>';

      // Determine action: if active -> show 'Nonaktifkan', else show 'Aktifkan'
      $actionType = $isActive ? 'deactivate' : 'activate';
      $btnClass = $isActive ? 'sptjm-btn-delete' : 'sptjm-btn-reset';
      // Use icon-only buttons (Boxicons) and include title/aria-label for accessibility
      if ($isActive) {
        $icon = '<span class="tf-icons bx bx-power-off"></span>';
        $title = 'Nonaktifkan';
      } else {
        $icon = '<span class="tf-icons bx bx-check-circle"></span>';
        $title = 'Aktifkan';
      }
      // Use identifier: prefer NIDN, fallback to NUPTK
      $identifier = !empty($row->nidn) ? $row->nidn : ($row->nuptk ?? '');
      $actionBtn = '<button type="button" class="sptjm-icon-btn ' . $btnClass . ' btn-toggle" title="' . $title . '" aria-label="' . $title . '" data-identifier="' . e($identifier) . '" data-action="' . $actionType . '">' . $icon . '</button>';

      $rows[] = [
        'nidn' => $row->nidn,
        'nuptk' => $row->nuptk ?? '-',
        'nama' => $row->nama,
        'jabatan' => $row->jabatan ?? '-',
        'aktif' => $statusLabel,
        'actions' => $actionBtn,
      ];
    }

    return response()->json([
      'draw' => intval($request->input('draw')),
      'recordsTotal' => $recordsTotal,
      'recordsFiltered' => $recordsFiltered,
      'data' => $rows,
    ]);
  }

  /**
   * Toggle Aktif (0/1) for a dosen identified by NIDN
   */
  public function toggle(Request $request)
  {
    $request->validate([
      'identifier' => 'required|string',
    ]);

    $identifier = $request->input('identifier');
    $kodePts = Auth::guard('pts')->user()->kode_pts;
    $tahun = session('tahun') ?? date('Y');

    // Support lookup by NIDN or NUPTK
    $record = DB::table('s_transaksi_2')
      ->where(function ($q) use ($identifier) {
        $q->where('NIDN', $identifier)
          ->orWhere('NUPTK', $identifier);
      })
      ->where('Kode_PT', $kodePts)
      ->where('Tahun_Versi', $tahun)
      ->first();

    if (!$record) {
      return response()->json(['success' => false, 'message' => 'Data dosen tidak ditemukan.'], 404);
    }

    $current = isset($record->Aktif) ? (string)$record->Aktif : '0';
    $new = ($current === '1' || $current === '1') ? '0' : '1';

    DB::table('s_transaksi_2')
      ->where(function ($q) use ($identifier) {
        $q->where('NIDN', $identifier)
          ->orWhere('NUPTK', $identifier);
      })
      ->where('Kode_PT', $kodePts)
      ->where('Tahun_Versi', $tahun)
      ->update(['Aktif' => $new]);

    return response()->json(['success' => true, 'aktif' => $new]);
  }
}
