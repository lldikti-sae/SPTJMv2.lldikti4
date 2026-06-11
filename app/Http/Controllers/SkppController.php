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

        // Cek apakah dosen sudah memiliki SKPP / Surat Keterangan yang open atau setuju
        $existing = DB::table('i_complain')
            ->where('pelapor_tipe', 'admin')
            ->whereIn('jenis_pengajuan', ['Surat Keterangan', 'Surat SKPP'])
            ->where(function ($q) use ($identifier) {
                $q->where('nidn', $identifier)
                  ->orWhere('nuptk', $identifier);
            })
            ->whereIn('status', ['open', 'setuju'])
            ->first();

        $existing_skpp = false;
        $existing_message = '';
        if ($existing) {
            $existing_skpp = true;
            $statusStr = $existing->status === 'setuju' ? 'Selesai' : 'Proses';
            $existing_message = 'Dosen ini sudah memiliki ' . $existing->jenis_pengajuan . ' (Status: ' . $statusStr . '). Tidak dapat membuat pengajuan baru.';
        }

        return response()->json([
            'found' => true,
            'existing_skpp' => $existing_skpp,
            'existing_message' => $existing_message,
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
        $nidn = $request->input('nidn');
        $nuptk = $request->input('nuptk');

        $query = DB::table('s_transaksi_2');
        
        if (!empty($nidn)) {
            $query->where('NIDN', $nidn);
        } elseif (!empty($nuptk)) {
            $query->where('NUPTK', $nuptk);
        } else {
            return response()->json(['tahun' => []]);
        }

        $tahunList = $query->distinct()
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
        $nidn = $request->input('nidn');
        $nuptk = $request->input('nuptk');
        $tahun = $request->input('tahun');

        $bulanIndonesia = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
            4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $query = DB::table('s_transaksi_2');
        if (!empty($nidn)) {
            $query->where('NIDN', $nidn);
        } elseif (!empty($nuptk)) {
            $query->where('NUPTK', $nuptk);
        } else {
            return response()->json(['found' => false, 'message' => 'Data tidak ditemukan.']);
        }
        
        if (!empty($tahun)) {
            $query->where('Tahun_Versi', $tahun);
        }

        // Ambil semua data transaksi dosen dari berbagai tahun
        $rows = $query->select(
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
        $pangkat = '';
        $golongan = '';

        $pangkatMap = [
            'I/a' => 'Juru Muda',
            'I/b' => 'Juru Muda Tingkat I',
            'I/c' => 'Juru',
            'I/d' => 'Juru Tingkat I',
            'II/a' => 'Pengatur Muda',
            'II/b' => 'Pengatur Muda Tingkat I',
            'II/c' => 'Pengatur',
            'II/d' => 'Pengatur Tingkat I',
            'III/a' => 'Penata Muda',
            'III/b' => 'Penata Muda Tingkat I',
            'III/c' => 'Penata',
            'III/d' => 'Penata Tingkat I',
            'IV/a' => 'Pembina',
            'IV/b' => 'Pembina Tingkat I',
            'IV/c' => 'Pembina Utama Muda',
            'IV/d' => 'Pembina Utama Madya',
            'IV/e' => 'Pembina Utama',
        ];

        if ($dosen) {
            $gol = $dosen->Gol12 ?? ($dosen->Gol1 ?? '');
            
            if (!empty($gol)) {
                $pangkat = $pangkatMap[$gol] ?? '';
                $golongan = $gol;
            }

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

        $tahunSekarang = date('Y');
        $count = DB::table('i_complain')
            ->whereIn('jenis_pengajuan', ['Surat Keterangan', 'Surat SKPP'])
            ->whereYear('created_at', $tahunSekarang)
            ->count();
        $nextNumber = $count + 1;
        $nomor_skpp_auto = $nextNumber . '/LL4/PR/' . $tahunSekarang;

        return response()->json([
            'tpd_kotor' => $tpd_kotor,
            'tpd_pajak' => $tpd_pajak,
            'tpd_bersih' => $tpd_bersih,
            'bulan_terakhir_nama' => $bulanIndonesia[$bulan_terakhir] ?? '',
            'nomor_skpp' => $nomor_skpp_auto,
            'pangkat' => $pangkat,
            'golongan' => $golongan,
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
            'nama_surat_pts' => 'nullable|string',
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
            'pangkat' => 'nullable|string',
            'teks_tambahan_1' => 'nullable|string',
            'teks_tambahan_2' => 'nullable|string',
            'golongan' => 'nullable|string',
            'wilayah_lldikti' => 'nullable|string',
            'kota_lldikti' => 'nullable|string',
            'ttd_jabatan' => 'nullable|string',
            'ttd_nama' => 'nullable|string',
            'ttd_nip' => 'nullable|string',
        ]);

        // Cek kembali di sisi server agar tidak ada duplikasi jika tombol di-klik dua kali atau by-pass
        $existing = DB::table('i_complain')
            ->where('pelapor_tipe', 'admin')
            ->whereIn('jenis_pengajuan', ['Surat Keterangan', 'Surat SKPP'])
            ->where(function ($q) use ($request) {
                if ($request->nidn) $q->where('nidn', $request->nidn);
                if ($request->nuptk) $q->orWhere('nuptk', $request->nuptk);
            })
            ->whereIn('status', ['open', 'setuju'])
            ->first();

        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Gagal: Dosen ini sudah dibuatkan pengajuan sebelumnya.']);
        }

        $pesanData = [
            'nama' => $request->nama,
            'pts' => $request->pts,
            'nama_surat_pts' => $request->nama_surat_pts,
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
        ];
            $teks1 = trim($request->teks_tambahan_1);
            if (!empty($teks1) && substr($teks1, -1) !== '.') {
                $teks1 .= '.';
            }
            $pangkatPart = trim($request->pangkat . ' ' . $teks1 . ' ' . $request->teks_tambahan_2);
            $pangkatGolongan = $pangkatPart;
            if (!empty($request->golongan)) {
                $pangkatGolongan = $pangkatPart . ($pangkatPart ? ', ' : '') . $request->golongan;
            }
            $pesanData['pangkat_golongan'] = $pangkatGolongan;
            $pesanData['wilayah_lldikti'] = $request->wilayah_lldikti;
            $pesanData['kota_lldikti'] = $request->kota_lldikti;
            $pesanData['ttd_jabatan'] = $request->ttd_jabatan;
            $pesanData['ttd_nama'] = $request->ttd_nama;
            $pesanData['ttd_nip'] = $request->ttd_nip;

            $pesanJson = json_encode($pesanData);

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

        $viewName = ($skpp->jenis_pengajuan === 'Surat Keterangan') ? 'admin.cetak-surat-keterangan' : 'admin.cetak-skpp';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($viewName, $data);
        $pdf->setPaper('a4', 'portrait');

        $prefix = ($skpp->jenis_pengajuan === 'Surat Keterangan') ? 'Surat_Keterangan_SKPP_' : 'SKPP_';
        $namaDosen = str_replace([' ', '/', '\\'], '_', $dosen->Nama ?? 'Dosen');
        
        return $pdf->stream($prefix . $namaDosen . '.pdf');
    }

    /**
     * Upload PDF and mark SKPP as Selesai (setuju).
     */
    public function uploadPdf(Request $request)
    {
        $request->validate([
            'skpp_id' => 'required|integer',
            'pdf_file' => 'required|mimes:pdf|max:5120', // Max 5MB
        ]);

        $skpp = DB::table('i_complain')->where('id', $request->skpp_id)->first();
        if (!$skpp) {
            return response()->json(['success' => false, 'message' => 'Data SKPP tidak ditemukan.']);
        }

        if ($request->hasFile('pdf_file')) {
            $file = $request->file('pdf_file');
            $filename = time() . '_SKPP_' . ($skpp->nidn ?: $skpp->nuptk) . '.' . $file->getClientOriginalExtension();
            
            // Simpan ke storage/app/public/Dokumen_Histori_Dosen2
            $file->storeAs('public/Dokumen_Histori_Dosen2', $filename);

            DB::table('i_complain')->where('id', $request->skpp_id)->update([
                'lampiran' => $filename,
                'status' => 'setuju',
                'handled_by' => auth()->user() ? auth()->user()->name : 'Admin',
                'handled_at' => now(),
                'updated_at' => now(),
            ]);

            // Catat ke histori dosen
            $detail = json_decode($skpp->pesan, true) ?? [];
            DB::table('j_histori_dosen')->insert([
                'nidn' => $skpp->nidn,
                'nuptk' => $skpp->nuptk,
                'nama' => $detail['nama'] ?? '-',
                'pts' => $detail['pts'] ?? '-',
                'kode_pt' => $skpp->kode_pts,
                'aktif' => '0',
                'keterangan' => 'Penerbitan ' . $skpp->jenis_pengajuan,
                'pengguna' => auth()->user() ? auth()->user()->name : 'Admin',
                'no_dokumen_ubah' => $detail['nomor_skpp'] ?? '',
                'tgl_dokumen_ubah' => now()->format('Y-m-d'),
                'alasan_perubahan' => 'Penerbitan ' . $skpp->jenis_pengajuan . ' Selesai, Dosen dinonaktifkan',
                'dokumen' => $filename,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Buat dosen menjadi tidak aktif secara otomatis di s_transaksi_2
            DB::table('s_transaksi_2')
                ->where(function ($q) use ($skpp) {
                    if (!empty($skpp->nidn)) $q->where('NIDN', $skpp->nidn);
                    if (!empty($skpp->nuptk)) $q->orWhere('NUPTK', $skpp->nuptk);
                })
                ->update(['Aktif' => '0']);

            return response()->json(['success' => true, 'message' => 'PDF berhasil diupload, dicatat di Histori, dan dosen otomatis dinonaktifkan.']);
        }

        return response()->json(['success' => false, 'message' => 'File PDF tidak ditemukan.']);
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

        // Jika SKPP ini sudah memiliki lampiran (selesai), hapus file dan histori dosen
        if (!empty($skpp->lampiran)) {
            // Hapus dari histori dosen
            DB::table('j_histori_dosen')->where('dokumen', $skpp->lampiran)->delete();

            // Kembalikan status dosen menjadi aktif kembali
            DB::table('s_transaksi_2')
                ->where(function ($q) use ($skpp) {
                    if (!empty($skpp->nidn)) $q->where('NIDN', $skpp->nidn);
                    if (!empty($skpp->nuptk)) $q->orWhere('NUPTK', $skpp->nuptk);
                })
                ->update(['Aktif' => '1']);

            // Hapus file fisik
            $filePath = public_path('storage/Dokumen_Histori_Dosen2/' . $skpp->lampiran);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        // Hapus dari tabel pengajuan
        DB::table('i_complain')->where('id', $id)->delete();

        return response()->json(['success' => true, 'message' => 'Data pengajuan dan histori (jika ada) berhasil dihapus.']);
    }
}
