<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\DataDosen;
use Carbon\Carbon;

class JadwalPindahPtsController extends Controller
{
    public function simpan(Request $request)
    {
        $request->validate([
            'nidn' => 'required',
            'kode_pt_baru' => 'required',
            'nama_pts_baru' => 'required',
            'pemegang_wilayah_baru' => 'required'
        ]);

        $identifier = $request->nidn;
        $dosen = DataDosen::where('NIDN', $identifier)->orWhere('NUPTK', $identifier)->first();
        
        if (!$dosen) {
            return redirect()->back()->with('error', 'Dosen tidak ditemukan.');
        }

        // Cek apakah sudah ada jadwal pending untuk dosen ini
        $existing = DB::table('j_jadwal_pindah_pts')
            ->where(function($q) use ($identifier) {
                $q->where('nidn', $identifier)
                  ->orWhere('nuptk', $identifier);
            })
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            // Update jadwal yang ada
            DB::table('j_jadwal_pindah_pts')
                ->where('id', $existing->id)
                ->update([
                    'kode_pt_baru' => $request->kode_pt_baru,
                    'nama_pts_baru' => $request->nama_pts_baru,
                    'pemegang_wilayah_baru' => $request->pemegang_wilayah_baru,
                    'pengguna' => auth()->user()->email ?? 'admin',
                    'updated_at' => Carbon::now()
                ]);
        } else {
            // Buat jadwal baru
            DB::table('j_jadwal_pindah_pts')->insert([
                'nidn' => $dosen->NIDN,
                'nuptk' => $dosen->NUPTK,
                'kode_pt_baru' => $request->kode_pt_baru,
                'nama_pts_baru' => $request->nama_pts_baru,
                'pemegang_wilayah_baru' => $request->pemegang_wilayah_baru,
                'status' => 'pending',
                'pengguna' => auth()->user()->email ?? 'admin',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }

        return redirect()->back()->with('success', 'Perpindahan PTS berhasil dijadwalkan dan akan otomatis dieksekusi setelah usulan pencairan selesai.');
    }
}
