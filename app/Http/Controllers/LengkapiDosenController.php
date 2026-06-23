<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Dosen;

class LengkapiDosenController extends Controller
{
  public function index($nidn)
  {
    $dosen = Dosen::where('nidn', $nidn)->orWhere('nuptk', $nidn)->first();

    if (!$dosen) {
      $dosen = new Dosen();
      $dosen->nidn = $nidn;
    }

    // Ambil data terbaru dari s_transaksi_2 untuk NIDN ini
    $tahunVersi = session('tahun');
    $transaksiQuery = DB::table('s_transaksi_2')
        ->where(function($q) use ($nidn) {
            $q->whereRaw('TRIM(NIDN) = ?', [$nidn])
              ->orWhereRaw('TRIM(NUPTK) = ?', [$nidn]);
        });
    if ($tahunVersi) {
        $transaksiQuery->where('Tahun_Versi', $tahunVersi);
    }
    $transaksi = $transaksiQuery->orderBy('Tahun_Versi', 'desc')->first();

    if ($transaksi) {
        $dosen->nidn = $dosen->nidn ?: trim($transaksi->NIDN ?? '');
        if (empty($dosen->nidn) || $dosen->nidn === '-') {
            $dosen->nidn = trim($transaksi->NUPTK ?? '');
        }
        $dosen->nama = $dosen->nama ?: $transaksi->Nama;
        $dosen->nik = $dosen->nik ?: ($transaksi->NIK ?? '');
        $dosen->tempat_lahir = $dosen->tempat_lahir ?: ($transaksi->TTL ?? '');
        $dosen->tanggal_lahir = $dosen->tanggal_lahir ?: ($transaksi->Tanggal_Lahir ?? '');
        $dosen->usia = $dosen->usia ?: ($transaksi->Usia ?? '');
        $dosen->kode_pt = $dosen->kode_pt ?: $transaksi->Kode_PT;
        $dosen->pts = $dosen->pts ?: $transaksi->PTS;
        $dosen->jenis = $dosen->jenis ?: $transaksi->Jenis;
        $dosen->pemegang_wilayah = $dosen->pemegang_wilayah ?: ($transaksi->Pemegang_Wilayah ?? '');
        $dosen->gaji = $dosen->gaji ?: 0; // Default if not found

        // Find the latest active month for gol, tahun, jabatan
        if (empty($dosen->gol) || empty($dosen->tahun) || empty($dosen->jabatan)) {
            for ($i = 12; $i >= 1; $i--) {
                $g = $transaksi->{'Gol'.$i} ?? null;
                $t = $transaksi->{'Tahun'.$i} ?? null;
                $j = $transaksi->{'Jabatan'.$i} ?? null;
                $gj = $transaksi->{'Gaji'.$i} ?? 0;
                
                if (!empty($g) && $g !== '-') {
                    $dosen->gol = $dosen->gol ?: $g;
                    $dosen->tahun = $dosen->tahun ?: $t;
                    $dosen->jabatan = $dosen->jabatan ?: $j;
                    $dosen->gaji = $dosen->gaji ?: $gj;
                    break;
                }
            }
        }
    }

    return view('admin.lengkapi-dosen', compact('dosen'));
  }
}
