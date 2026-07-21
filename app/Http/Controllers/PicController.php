<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PicController extends Controller
{
  public function index()
  {
    // prefer year stored in session (consistent with other pages), fallback to current year
    $year = session('tahun', Carbon::now()->year);
    $pemegangWilayah = Auth::user()->email;
    $jumlahDosen = DB::table('s_transaksi_2')
      ->where('pemegang_wilayah', $pemegangWilayah)
      ->where('tahun_versi', $year)
      ->count();
    $jumlahDosenPNSAktif = DB::table('s_transaksi_2')
      ->where('pemegang_wilayah', $pemegangWilayah)
      ->where('jenis', 'PNS')
      ->where('aktif', 1)
      ->where('Tahun_Versi', $year)
      ->count();

    $jumlahDosenPNSTidakAktif = DB::table('s_transaksi_2')
      ->where('pemegang_wilayah', $pemegangWilayah)
      ->where('jenis', 'PNS')
      ->where(function($q) { $q->where('aktif', '!=', 1)->orWhereNull('aktif'); })
      ->where('Tahun_Versi', $year)
      ->count();

    $jumlahDosenPNS = DB::table('s_transaksi_2')
      ->where('pemegang_wilayah', $pemegangWilayah)
      ->where('jenis', 'PNS')
      ->where('tahun_versi', $year)
      ->count();
    $jumlahDosenNonPNSAktif = DB::table('s_transaksi_2')
      ->where('pemegang_wilayah', $pemegangWilayah)
      ->where('jenis', 'NON PNS')
      ->where('Tahun_Versi', $year)
      ->where('aktif', 1)
      ->count();

    $jumlahDosenNonPNSTidakAktif = DB::table('s_transaksi_2')
      ->where('jenis', 'NON PNS')
      ->where('Tahun_Versi', $year)
      ->where(function($q) { $q->where('aktif', '!=', 1)->orWhereNull('aktif'); })
      ->where('pemegang_wilayah', $pemegangWilayah)
      ->count();

    $jumlahDosenNonPNS = DB::table('s_transaksi_2')
      ->where('pemegang_wilayah', $pemegangWilayah)
      ->where('jenis', 'NON PNS')
      ->where('Tahun_Versi', $year)
      ->count();

    // Hitung jumlah complain pending untuk alert PIC
    $tahunVersi = (int) session('tahun');
    $allowedNidn = DB::table('s_transaksi_2 as t')
        ->selectRaw('t.NIDN as nidn')
        ->whereNotNull('t.NIDN')
        ->where('t.NIDN', '<>', '')
        ->whereRaw('TRIM(t.Pemegang_Wilayah) = ?', [$pemegangWilayah])
        ->when($tahunVersi > 0, function ($q) use ($tahunVersi) {
            $q->where('t.Tahun_Versi', '=', $tahunVersi);
        })
        ->groupBy('t.NIDN');

    $allowedNuptk = DB::table('s_transaksi_2 as t')
        ->selectRaw('t.NUPTK as nuptk')
        ->whereNotNull('t.NUPTK')
        ->where('t.NUPTK', '<>', '')
        ->whereRaw('TRIM(t.Pemegang_Wilayah) = ?', [$pemegangWilayah])
        ->when($tahunVersi > 0, function ($q) use ($tahunVersi) {
            $q->where('t.Tahun_Versi', '=', $tahunVersi);
        })
        ->groupBy('t.NUPTK');

    $pendingCountQuery = DB::table('i_complain as c')
        ->leftJoinSub($allowedNidn, 'an', function ($join) {
            $join->on('an.nidn', '=', 'c.nidn');
        })
        ->leftJoinSub($allowedNuptk, 'au', function ($join) {
            $join->on('au.nuptk', '=', 'c.nuptk');
        })
        ->where(function ($q) {
            $q->whereNotNull('an.nidn')->orWhereNotNull('au.nuptk');
        })
        ->where(function ($q) {
            $q->whereIn('c.pelapor_tipe', ['pts', 'dosen'])
              ->orWhere(function ($subq) {
                  $subq->where('c.pelapor_tipe', 'admin')
                       ->whereIn('c.jenis_pengajuan', ['Surat Keterangan', 'Surat SKPP'])
                       ->where('c.status', '!=', 'open');
              });
        })
        ->whereIn('c.status', ['open', 'menunggu_konfirmasi']);
    
    if ($tahunVersi > 0) {
        $startOfYear = \Carbon\Carbon::create($tahunVersi, 1, 1, 0, 0, 0);
        $startOfNextYear = \Carbon\Carbon::create($tahunVersi + 1, 1, 1, 0, 0, 0);
        $pendingCountQuery->where('c.created_at', '>=', $startOfYear)
            ->where('c.created_at', '<', $startOfNextYear);
    }
    
    $pendingComplainCount = (int) $pendingCountQuery->distinct('c.id')->count('c.id');

    return view(
      'pic.dashboard',
      compact(
        'jumlahDosen',
        'jumlahDosenPNSAktif',
        'jumlahDosenPNSTidakAktif',
        'jumlahDosenPNS',
        'jumlahDosenNonPNSAktif',
        'jumlahDosenNonPNSTidakAktif',
        'jumlahDosenNonPNS',
        'pendingComplainCount'
      )
    );
  }
}
