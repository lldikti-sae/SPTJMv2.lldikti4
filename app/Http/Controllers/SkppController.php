<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SkppController extends Controller
{
    /**
     * Display the SKPP list page.
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 15);
        $allowedPerPage = [15, 25, 50, 100];
        if (!in_array($perPage, $allowedPerPage, true)) {
            $perPage = 15;
        }

        $search = trim((string) $request->input('search', ''));

        $query = DB::table('i_complain')
            ->where('pelapor_tipe', 'admin')
            ->whereIn('jenis_pengajuan', ['Surat Keterangan', 'Surat SKPP'])
            ->orderByDesc('created_at');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nidn', 'like', "%{$search}%")
                  ->orWhere('nuptk', 'like', "%{$search}%")
                  ->orWhere('pesan', 'like', "%{$search}%");
            });
        }

        $skppList = $query->paginate($perPage)->appends($request->query());

        return view('admin.skpp', compact('skppList'));
    }

    /**
     * Search dosen by NIDN/NUPTK for the SKPP modal.
     */
    public function searchDosen(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
        ]);

        $identifier = trim((string) $request->input('identifier'));
        $tahun = (string) session('tahun');

        // Gunakan bulan aktif dari session
        $bulanSession = (int) session('bulan') ?: 12;
        $bulanSession = max(1, min(12, $bulanSession));

        $masa_kerja = "NULLIF(Tahun{$bulanSession}, '')";
        $golongan   = "NULLIF(Gol{$bulanSession}, '')";
        $jabatan    = "NULLIF(Jabatan{$bulanSession}, '')";

        $dosen = DB::table('s_transaksi_2')
            ->select(
                's_transaksi_2.NIDN',
                's_transaksi_2.NUPTK',
                's_transaksi_2.Nama',
                's_transaksi_2.Jenis',
                's_transaksi_2.Kode_PT',
                's_transaksi_2.PTS',
                's_transaksi_2.Aktif',
                DB::raw("$jabatan AS jabatan"),
                DB::raw("$golongan AS gol")
            )
            ->where('s_transaksi_2.Tahun_Versi', $tahun)
            ->where(function ($q) use ($identifier) {
                $q->where('s_transaksi_2.NIDN', $identifier)
                  ->orWhere('s_transaksi_2.NUPTK', $identifier);
            })
            ->first();

        if (!$dosen) {
            return response()->json(['found' => false, 'message' => 'Dosen tidak ditemukan.']);
        }

        // Gabungkan jabatan dan status
        $jabatanDisplay = $dosen->jabatan ?? '-';
        $statusDisplay = (in_array($dosen->Aktif, ['1', 1, 'YA', 'Ya', 'ya', 'Y'], true)) ? 'Aktif' : 'Tidak Aktif';
        $jabatanStatus = $jabatanDisplay . ' - ' . $statusDisplay;

        return response()->json([
            'found' => true,
            'data' => [
                'nidn' => $dosen->NIDN,
                'nuptk' => $dosen->NUPTK,
                'nama' => $dosen->Nama,
                'jabatan_status' => $jabatanStatus,
                'kode_pt' => $dosen->Kode_PT,
                'pts' => $dosen->PTS,
            ],
        ]);
    }

    /**
     * Get available years for a dosen (years that have records in s_transaksi_2).
     */
    public function getTahunDosen(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
        ]);

        $identifier = trim((string) $request->input('identifier'));

        $tahunList = DB::table('s_transaksi_2')
            ->where(function ($q) use ($identifier) {
                $q->where('NIDN', $identifier)
                  ->orWhere('NUPTK', $identifier);
            })
            ->distinct()
            ->orderBy('Tahun_Versi', 'desc')
            ->pluck('Tahun_Versi')
            ->all();

        return response()->json(['tahun' => $tahunList]);
    }

    /**
     * Get detail bulan belum diusulkan for a dosen in a specific year.
     */
    public function getDetailBulan(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
        ]);

        $identifier = trim((string) $request->input('identifier'));

        $bulanIndonesia = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
            4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        // Ambil semua data transaksi dosen dari berbagai tahun
        $rows = DB::table('s_transaksi_2')
            ->where(function ($q) use ($identifier) {
                $q->where('NIDN', $identifier)
                  ->orWhere('NUPTK', $identifier);
            })
            ->select(
                'Tahun_Versi',
                'KodeUsulan1', 'KodeUsulan2', 'KodeUsulan3', 'KodeUsulan4',
                'KodeUsulan5', 'KodeUsulan6', 'KodeUsulan7', 'KodeUsulan8',
                'KodeUsulan9', 'KodeUsulan10', 'KodeUsulan11', 'KodeUsulan12'
            )
            ->orderBy('Tahun_Versi', 'asc')
            ->get();

        if ($rows->isEmpty()) {
            return response()->json(['found' => false, 'message' => 'Data tidak ditemukan.']);
        }

        $currentYear = (int) Carbon::now()->format('Y');
        $currentMonth = (int) Carbon::now()->format('n');

        $bulanKosong = [];
        
        foreach ($rows as $row) {
            $tahunVersi = (int) $row->Tahun_Versi;
            
            // Jangan cek tahun yang akan datang
            if ($tahunVersi > $currentYear) {
                continue;
            }
            
            for ($i = 1; $i <= 12; $i++) {
                // Untuk tahun ini, hanya cek bulan yang sudah berjalan dan sebelumnya
                if ($tahunVersi === $currentYear && $i > $currentMonth) {
                    continue; 
                }
                
                $kolom = 'KodeUsulan' . $i;
                $nilai = $row->{$kolom} ?? null;
                if ($nilai === null || $nilai === '') {
                    $bulanKosong[] = [
                        'tahun' => $tahunVersi,
                        'kode' => 'KodeUsulan' . $i,
                        'bulan' => $bulanIndonesia[$i],
                        'status' => 'Belum Diusulkan',
                    ];
                }
            }
        }

        return response()->json([
            'found' => true,
            'bulan_kosong' => $bulanKosong,
            'total_kosong' => count($bulanKosong),
        ]);
    }

    /**
     * Get default preview data for SKPP Form.
     */
    public function getPreviewData(Request $request)
    {
        $request->validate([
            'nidn' => 'nullable|string',
            'nuptk' => 'nullable|string',
            'tahun' => 'required|string',
        ]);

        $nidn = $request->nidn;
        $nuptk = $request->nuptk;
        $tahun = $request->tahun;

        $dosen = DB::table('s_transaksi_2')
            ->where('Tahun_Versi', $tahun)
            ->where(function ($q) use ($nidn, $nuptk) {
                if ($nidn) $q->where('NIDN', $nidn);
                if ($nuptk) $q->orWhere('NUPTK', $nuptk);
            })
            ->first();

        if (!$dosen) {
            $dosen = DB::table('s_transaksi_2')
                ->where(function ($q) use ($nidn, $nuptk) {
                    if ($nidn) $q->where('NIDN', $nidn);
                    if ($nuptk) $q->orWhere('NUPTK', $nuptk);
                })
                ->orderBy('Tahun_Versi', 'desc')
                ->first();
        }

        $tpd_kotor = 0;
        $tpd_pajak = 0;
        $tpd_bersih = 0;
        $bulan_terakhir = 1;

        if ($dosen) {
            for ($i = 12; $i >= 1; $i--) {
                $tpd_val = $dosen->{'TPD' . $i} ?? 0;
                if ($tpd_val > 0) {
                    $tpd_kotor = $tpd_val;
                    $tpd_pajak = $dosen->{'nilaiPajakTPD' . $i} ?? 0;
                    $tpd_bersih = $dosen->{'bersihTPD' . $i} ?? 0;
                    $bulan_terakhir = $i;
                    break;
                }
            }
        }

        $bulanIndonesia = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
            4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return response()->json([
            'tpd_kotor' => $tpd_kotor,
            'tpd_pajak' => $tpd_pajak,
            'tpd_bersih' => $tpd_bersih,
            'bulan_terakhir_nama' => $bulanIndonesia[$bulan_terakhir] ?? '',
            'nomor_skpp' => date('Y') . '/LL4/PR/2026',
        ]);
    }

    /**
     * Store a new SKPP record.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nidn' => 'nullable|string',
            'nuptk' => 'nullable|string',
            'nama' => 'required|string',
            'jabatan_status' => 'nullable|string',
            'kode_pt' => 'nullable|string',
            'pts' => 'nullable|string',
            'tahun' => 'required|string',
            'bulan_belum_usulan' => 'nullable|string',
            'jenis_surat' => 'required|string|in:Surat Keterangan,Surat SKPP',
            'nomor_skpp' => 'nullable|string',
            'nomor_surat_pts' => 'nullable|string',
            'tanggal_surat_pts' => 'nullable|string',
            'nomor_surat_lolos_butuh' => 'nullable|string',
            'tanggal_surat_lolos_butuh' => 'nullable|string',
            'tpd_kotor' => 'nullable|numeric',
            'tpd_pajak' => 'nullable|numeric',
            'tpd_bersih' => 'nullable|numeric',
            'terhitung_bulan' => 'nullable|string',
        ]);

        $pesanJson = json_encode([
            'nama' => $request->nama,
            'pts' => $request->pts,
            'jabatan_status' => $request->jabatan_status,
            'tahun' => $request->tahun,
            'bulan_belum_usulan' => $request->bulan_belum_usulan,
            'nomor_skpp' => $request->nomor_skpp,
            'nomor_surat_pts' => $request->nomor_surat_pts,
            'tanggal_surat_pts' => $request->tanggal_surat_pts,
            'nomor_surat_lolos_butuh' => $request->nomor_surat_lolos_butuh,
            'tanggal_surat_lolos_butuh' => $request->tanggal_surat_lolos_butuh,
            'tpd_kotor' => $request->tpd_kotor,
            'tpd_pajak' => $request->tpd_pajak,
            'tpd_bersih' => $request->tpd_bersih,
            'terhitung_bulan' => $request->terhitung_bulan,
        ]);

        DB::table('i_complain')->insert([
            'pelapor_tipe' => 'admin',
            'kode_pts' => $request->kode_pt,
            'nidn' => $request->nidn,
            'nuptk' => $request->nuptk,
            'judul' => 'Pengajuan ' . $request->jenis_surat,
            'pesan' => $pesanJson,
            'jenis_pengajuan' => $request->jenis_surat,
            'status' => 'open',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'SKPP berhasil dibuat.']);
    }

    /**
     * Cetak Surat SKPP / Keterangan.
     */
    public function cetak($id)
    {
        $skpp = DB::table('i_complain')->where('id', $id)->first();
        if (!$skpp) {
            return redirect()->back()->with('error', 'Data SKPP tidak ditemukan.');
        }

        $detail = json_decode($skpp->pesan, true) ?? [];
        $tahun = $detail['tahun'] ?? date('Y');
        $nidn = $skpp->nidn;
        $nuptk = $skpp->nuptk;

        // Ambil data dosen dari s_transaksi_2
        $dosen = DB::table('s_transaksi_2')
            ->where('Tahun_Versi', $tahun)
            ->where(function ($q) use ($nidn, $nuptk) {
                if ($nidn) $q->where('NIDN', $nidn);
                if ($nuptk) $q->orWhere('NUPTK', $nuptk);
            })
            ->first();

        if (!$dosen) {
            // Coba ambil dari tahun apa saja jika tidak ketemu di tahun yang dipilih
            $dosen = DB::table('s_transaksi_2')
                ->where(function ($q) use ($nidn, $nuptk) {
                    if ($nidn) $q->where('NIDN', $nidn);
                    if ($nuptk) $q->orWhere('NUPTK', $nuptk);
                })
                ->orderBy('Tahun_Versi', 'desc')
                ->first();
        }

        if (!$dosen) {
            return redirect()->back()->with('error', 'Data Dosen tidak ditemukan di transaksi.');
        }

        // Cari bulan terakhir yang TPD/TKGB-nya ada isinya untuk mendapatkan besaran jika kosong di JSON
        $tpd_kotor = $detail['tpd_kotor'] ?? 0;
        $tpd_pajak = $detail['tpd_pajak'] ?? 0;
        $tpd_bersih = $detail['tpd_bersih'] ?? 0;
        $bulan_terakhir_nama = $detail['terhitung_bulan'] ?? '';

        if (empty($tpd_kotor)) {
            $bulan_terakhir = 1;
            for ($i = 12; $i >= 1; $i--) {
                $tpd_val = $dosen->{'TPD' . $i} ?? 0;
                if ($tpd_val > 0) {
                    $tpd_kotor = $tpd_val;
                    $tpd_pajak = $dosen->{'nilaiPajakTPD' . $i} ?? 0;
                    $tpd_bersih = $dosen->{'bersihTPD' . $i} ?? 0;
                    $bulan_terakhir = $i;
                    break;
                }
            }
            $bulanIndonesia = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
                4 => 'April', 5 => 'Mei', 6 => 'Juni',
                7 => 'Juli', 8 => 'Agustus', 9 => 'September',
                10 => 'Oktober', 11 => 'November', 12 => 'Desember',
            ];
            $bulan_terakhir_nama = $bulanIndonesia[$bulan_terakhir] ?? '';
        }

        $data = [
            'skpp' => $skpp,
            'dosen' => $dosen,
            'detail' => $detail,
            'bulan_terakhir_nama' => $bulan_terakhir_nama,
            'tpd_kotor' => $tpd_kotor,
            'tpd_pajak' => $tpd_pajak,
            'tpd_bersih' => $tpd_bersih,
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.cetak-skpp', $data);
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('SKPP_' . ($dosen->Nama ?? 'Dosen') . '.pdf');
    }

    /**
     * Hapus SKPP.
     */
    public function destroy($id)
    {
        $skpp = DB::table('i_complain')->where('id', $id)->first();
        
        if (!$skpp) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
        }

        DB::table('i_complain')->where('id', $id)->delete();

        return response()->json(['success' => true, 'message' => 'Surat berhasil dihapus.']);
    }
}
