<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
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
        $m_pejabat = DB::table('m_pejabat')->get();
        $pejabat = new \stdClass();
        foreach ($m_pejabat as $p) {
            $pejabat->{"pejabat{$p->urutan}"} = $p->nama;
            $pejabat->{"nip_pejabat{$p->urutan}"} = $p->nip;
            $pejabat->{"jabatan{$p->urutan}"} = $p->jabatan;
        }

        $master_kop = DB::table('m_kop_surat')->first();

        return view('admin.skpp', compact('skppList', 'pejabat', 'm_pejabat', 'master_kop'));
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
        // Pencegahan bug jika input hanya strip (-) atau kosong
        if ($identifier === '-' || $identifier === '') {
            return response()->json([
                'found' => false,
                'message' => 'Silakan masukkan NIDN atau NUPTK yang spesifik. Tidak bisa menggunakan (-) untuk pencarian.',
                'existing_skpp' => false,
                'existing_message' => ''
            ]);
        }
        
        $tahun = (string) session('tahun');

        // Gunakan bulan aktif dari session
        $bulanSession = (int) session('bulan') ?: 12;
        $bulanSession = max(1, min(12, $bulanSession));

        $getDosenQuery = function($withTahun = true) use ($tahun, $identifier) {
            $q = DB::table('s_transaksi_2');
            if ($withTahun) {
                $q->where('s_transaksi_2.Tahun_Versi', $tahun);
            }
            $q->where(function ($sub) use ($identifier) {
                $sub->where('s_transaksi_2.NIDN', $identifier)
                  ->orWhere('s_transaksi_2.NUPTK', $identifier);
            });
            if (!$withTahun) {
                $q->orderByDesc('s_transaksi_2.Tahun_Versi');
            }
            return $q;
        };

        $dosenRaw = $getDosenQuery(true)->first();

        // Fallback 1: Cari di tahun mana saja (transaksi terbaru)
        if (!$dosenRaw) {
            $dosenRaw = $getDosenQuery(false)->first();
        }

        $dosen = null;
        if ($dosenRaw) {
            $jab = $dosenRaw->{'Jabatan' . $bulanSession} ?? null;
            if (empty($jab) || $jab === '-') {
                $jab = $dosenRaw->Jabatan12 ?? ($dosenRaw->Jabatan1 ?? null);
            }
            if (empty($jab) || $jab === '-') {
                $tkFallback = DB::table('s_tunjangan_kinerja')
                    ->where(function($q) use($dosenRaw) {
                        if (!empty($dosenRaw->NIDN)) $q->where('NIDN', $dosenRaw->NIDN);
                        if (!empty($dosenRaw->NUPTK)) $q->orWhere('NUPTK', $dosenRaw->NUPTK);
                    })
                    ->where('Tahun', $dosenRaw->Tahun_Versi)
                    ->first();
                $jab = $tkFallback ? ($tkFallback->Jabatan ?? '-') : '-';
            }

            $dosen = (object)[
                'NIDN' => $dosenRaw->NIDN,
                'NUPTK' => $dosenRaw->NUPTK,
                'Nama' => $dosenRaw->Nama,
                'Jenis' => $dosenRaw->Jenis ?? null,
                'Kode_PT' => $dosenRaw->{'Kode_PT_' . $bulanSession} ?? $dosenRaw->Kode_PT ?? null,
                'PTS' => $dosenRaw->{'Nama_PT_' . $bulanSession} ?? $dosenRaw->PTS ?? null,
                'Aktif' => $dosenRaw->Aktif ?? null,
                'Pemegang_Wilayah' => $dosenRaw->Pemegang_Wilayah ?? null,
                'jabatan' => $jab,
                'gol' => $dosenRaw->{'Gol' . $bulanSession} ?? $dosenRaw->Gol12 ?? $dosenRaw->Gol1 ?? null,
            ];
        }

        // Fallback 2: Cari di a_dosen jika dosen baru dan belum punya transaksi
        if (!$dosen) {
            $akunDosen = DB::table('a_dosen')
                ->where('nidn', $identifier)
                ->orWhere('nuptk', $identifier)
                ->first();
                
            if ($akunDosen) {
                $dosen = (object)[
                    'NIDN' => $akunDosen->nidn,
                    'NUPTK' => $akunDosen->nuptk,
                    'Nama' => $akunDosen->nama_dosen,
                    'Kode_PT' => $akunDosen->kode_pts,
                    'PTS' => $akunDosen->nama_pts,
                    'Aktif' => $akunDosen->aktif,
                    'Pemegang_Wilayah' => $akunDosen->wilayah,
                    'jabatan' => null,
                    'gol' => null,
                ];
            }
        }

        // Cek apakah dosen sudah memiliki SKPP / Surat Keterangan yang open atau setuju
        $existing = DB::table('i_complain')
            ->where('pelapor_tipe', 'admin')
            ->whereIn('jenis_pengajuan', ['Surat Keterangan', 'Surat SKPP'])
            ->where(function ($q) use ($identifier) {
                $q->where('nidn', $identifier)
                  ->orWhere('nuptk', $identifier);
            })
            ->whereIn('status', ['open', 'setuju', 'menunggu_konfirmasi'])
            ->first();

        $existing_skpp = false;
        $existing_message = '';
        if ($existing) {
            $existing_skpp = true;
            $statusStr = $existing->status === 'setuju' ? 'Selesai' : 'Proses';
            $existing_message = 'Dosen ini sudah memiliki ' . $existing->jenis_pengajuan . ' (Status: ' . $statusStr . '). Tidak dapat membuat pengajuan baru.';
        }

        if (!$dosen) {
            return response()->json([
                'found' => false, 
                'message' => 'Dosen tidak ditemukan.',
                'existing_skpp' => $existing_skpp,
                'existing_message' => $existing_message,
            ]);
        }

        // Gabungkan jabatan dan status
        $jabatanDisplay = $dosen->jabatan ?? '-';
        $statusDisplay = (in_array($dosen->Aktif, ['1', 1, 'YA', 'Ya', 'ya', 'Y'], true)) ? 'Aktif' : 'Tidak Aktif';
        $jabatanStatus = $jabatanDisplay . ' - ' . $statusDisplay;

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
                'pic' => $dosen->Pemegang_Wilayah ?? '-',
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
            ->orderBy('Tahun_Versi', 'asc')
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

        $tkgb_kotor = 0;
        $tkgb_pajak = 0;
        $tkgb_bersih = 0;
        $is_guru_besar = false;

        if ($dosen) {
            $gol = $dosen->Gol12 ?? ($dosen->Gol1 ?? '');
            
            if (!empty($gol)) {
                $pangkat = $pangkatMap[$gol] ?? '';
                $golongan = $gol;
            }

            // Deteksi apakah dosen Guru Besar/Profesor (TKGB)
            $jabatanDosen = strtolower(trim($dosen->Jabatan12 ?? ($dosen->Jabatan1 ?? '')));
            $is_guru_besar = (strpos($jabatanDosen, 'guru besar') !== false || strpos($jabatanDosen, 'profesor') !== false);

            for ($i = 12; $i >= 1; $i--) {
                $tpd_val = $dosen->{'TPD' . $i} ?? 0;
                if ($tpd_val > 0) {
                    $tpd_kotor = $tpd_val;
                    $tpd_pajak = $dosen->{'nilaiPajakTPD' . $i} ?? 0;
                    $tpd_bersih = $dosen->{'bersihTPD' . $i} ?? 0;
                    // Ambil TKGB dari bulan yang sama
                    $tkgb_kotor = $dosen->{'TKGB' . $i} ?? 0;
                    $tkgb_pajak = $dosen->{'nilaiPajakTKGB' . $i} ?? 0;
                    $tkgb_bersih = $dosen->{'bersihTKGB' . $i} ?? 0;
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
        $nomor_skpp_auto = ''; // Dikosongkan sesuai permintaan user

        return response()->json([
            'tpd_kotor' => $tpd_kotor,
            'tpd_pajak' => $tpd_pajak,
            'tpd_bersih' => $tpd_bersih,
            'tkgb_kotor' => $tkgb_kotor,
            'tkgb_pajak' => $tkgb_pajak,
            'tkgb_bersih' => $tkgb_bersih,
            'is_guru_besar' => $is_guru_besar,
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
            'nidn' => 'required|string',
            'nuptk' => 'required|string',
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
            'tkgb_kotor' => 'nullable|numeric',
            'tkgb_pajak' => 'nullable|numeric',
            'tkgb_bersih' => 'nullable|numeric',
            'is_guru_besar' => 'nullable',
            'terhitung_bulan' => 'nullable|string',
            'pangkat' => 'nullable|string',
            'teks_tambahan_1' => 'nullable|string',
            'teks_tambahan_2' => 'nullable|string',
            'golongan' => 'nullable|string',
            'wilayah_lldikti' => 'nullable|string',
            'wilayah_lldikti_custom' => 'nullable|string',
            'kota_lldikti' => 'nullable|string',
            'ttd_jabatan' => 'nullable|string',
            'ttd_nama' => 'nullable|string',
            'ttd_nip' => 'nullable|string',
        ]);

        // Cek kembali di sisi server agar tidak ada duplikasi jika tombol di-klik dua kali atau by-pass
        $existing = false;
        if (!empty($request->nidn) || !empty($request->nuptk)) {
            $existing = DB::table('i_complain')
                ->where('pelapor_tipe', 'admin')
                ->whereIn('jenis_pengajuan', ['Surat Keterangan', 'Surat SKPP'])
                ->where(function ($q) use ($request) {
                    if (!empty($request->nidn)) $q->where('nidn', $request->nidn);
                    if (!empty($request->nuptk)) $q->orWhere('nuptk', $request->nuptk);
                })
                ->whereIn('status', ['open', 'setuju', 'menunggu_konfirmasi'])
                ->first();
        }

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
            'tkgb_kotor' => $request->tkgb_kotor,
            'tkgb_pajak' => $request->tkgb_pajak,
            'tkgb_bersih' => $request->tkgb_bersih,
            'is_guru_besar' => filter_var($request->is_guru_besar, FILTER_VALIDATE_BOOLEAN),
            'terhitung_bulan' => $request->terhitung_bulan,
            'pangkat' => $request->pangkat,
            'golongan' => $request->golongan,
            'wilayah_lldikti' => $request->wilayah_lldikti,
            'wilayah_lldikti_custom' => $request->wilayah_lldikti_custom,
            'kota_lldikti' => $request->kota_lldikti,
            'ttd_jabatan' => $request->ttd_jabatan,
            'ttd_nama' => $request->ttd_nama,
            'ttd_nip' => $request->ttd_nip,
            'tanggal_cetak' => $request->tanggal_cetak,
        ];
        
        $pangkatGolongan = trim((string)$request->pangkat);
        if (!empty($request->golongan)) {
            $pangkatGolongan = $pangkatGolongan . ($pangkatGolongan ? ', ' : '') . $request->golongan;
        }
        $pesanData['pangkat_golongan'] = $pangkatGolongan;

        $pesanJson = json_encode($pesanData);

        // Auto-save new penandatangan
        if (!empty($request->ttd_nama)) {
            $pejabatExists = DB::table('m_pejabat')
                ->where('nama', $request->ttd_nama)
                ->exists();
                
            if (!$pejabatExists) {
                $maxUrutan = DB::table('m_pejabat')->max('urutan') ?? 0;
                DB::table('m_pejabat')->insert([
                    'urutan' => $maxUrutan + 1,
                    'nama' => $request->ttd_nama,
                    'nip' => $request->ttd_nip,
                    'jabatan' => $request->ttd_jabatan,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

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
     * Mengambil data SKPP untuk di-edit.
     */
    public function edit($id)
    {
        $skpp = DB::table('i_complain')->where('id', $id)->first();
        if (!$skpp || in_array($skpp->status, ['setuju'])) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan atau sudah disetujui.']);
        }

        return response()->json([
            'success' => true,
            'skpp' => $skpp,
            'detail' => json_decode($skpp->pesan, true) ?? [],
            'master_kop' => DB::table('m_kop_surat')->first()
        ]);
    }

    /**
     * Memperbarui SKPP.
     */
    public function update(Request $request, $id)
    {
        $skpp = DB::table('i_complain')->where('id', $id)->first();
        if (!$skpp || in_array($skpp->status, ['setuju'])) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan atau sudah disetujui.'], 403);
        }

        // Validasi sama seperti store
        $validator = Validator::make($request->all(), [
            'nidn' => 'required',
            'nuptk' => 'required',
            'kode_pt' => 'required',
            'jenis_surat' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()]);
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
            'tkgb_kotor' => $request->tkgb_kotor,
            'tkgb_pajak' => $request->tkgb_pajak,
            'tkgb_bersih' => $request->tkgb_bersih,
            'is_guru_besar' => filter_var($request->is_guru_besar, FILTER_VALIDATE_BOOLEAN),
            'terhitung_bulan' => $request->terhitung_bulan,
            'pangkat' => $request->pangkat,
            'golongan' => $request->golongan,
            'wilayah_lldikti' => $request->wilayah_lldikti,
            'wilayah_lldikti_custom' => $request->wilayah_lldikti_custom,
            'kota_lldikti' => $request->kota_lldikti,
            'ttd_jabatan' => $request->ttd_jabatan,
            'ttd_nama' => $request->ttd_nama,
            'ttd_nip' => $request->ttd_nip,
            'tanggal_cetak' => $request->tanggal_cetak,
        ];
        
        $pangkatGolongan = trim((string)$request->pangkat);
        if (!empty($request->golongan)) {
            $pangkatGolongan = $pangkatGolongan . ($pangkatGolongan ? ', ' : '') . $request->golongan;
        }
        $pesanData['pangkat_golongan'] = $pangkatGolongan;

        $pesanJson = json_encode($pesanData);

        // Auto-save new penandatangan
        if (!empty($request->ttd_nama)) {
            $pejabatExists = DB::table('m_pejabat')
                ->where('nama', $request->ttd_nama)
                ->exists();
                
            if (!$pejabatExists) {
                $maxUrutan = DB::table('m_pejabat')->max('urutan') ?? 0;
                DB::table('m_pejabat')->insert([
                    'urutan' => $maxUrutan + 1,
                    'nama' => $request->ttd_nama,
                    'nip' => $request->ttd_nip,
                    'jabatan' => $request->ttd_jabatan,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        DB::table('i_complain')->where('id', $id)->update([
            'kode_pts' => $request->kode_pt,
            'nidn' => $request->nidn,
            'nuptk' => $request->nuptk,
            'judul' => 'Pengajuan ' . $request->jenis_surat,
            'pesan' => $pesanJson,
            'jenis_pengajuan' => $request->jenis_surat,
            'status' => 'open', // Reset to open if it was rejected
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'SKPP berhasil diupdate.']);
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
            // Jika tetap tidak ditemukan (pembuatan manual), gunakan data dari payload JSON
            $dosen = (object) [
                'Nama' => $detail['nama'] ?? '-',
                'NIDN' => $nidn,
                'NUPTK' => $nuptk,
                'PTS' => $detail['pts'] ?? '-',
                'Gelar_Depan' => '',
                'Gelar_Belakang' => '',
            ];
        }

        // Cari bulan terakhir yang TPD/TKGB-nya ada isinya untuk mendapatkan besaran jika kosong di JSON
        $tpd_kotor = $detail['tpd_kotor'] ?? 0;
        $tpd_pajak = $detail['tpd_pajak'] ?? 0;
        $tpd_bersih = $detail['tpd_bersih'] ?? 0;
        $tkgb_kotor = $detail['tkgb_kotor'] ?? 0;
        $tkgb_pajak = $detail['tkgb_pajak'] ?? 0;
        $tkgb_bersih = $detail['tkgb_bersih'] ?? 0;
        $is_guru_besar = $detail['is_guru_besar'] ?? false;
        $bulan_terakhir_nama = $detail['terhitung_bulan'] ?? '';

        if (empty($tpd_kotor)) {
            $bulan_terakhir = 1;
            for ($i = 12; $i >= 1; $i--) {
                $tpd_val = $dosen->{'TPD' . $i} ?? 0;
                if ($tpd_val > 0) {
                    $tpd_kotor = $tpd_val;
                    $tpd_pajak = $dosen->{'nilaiPajakTPD' . $i} ?? 0;
                    $tpd_bersih = $dosen->{'bersihTPD' . $i} ?? 0;
                    $tkgb_kotor = $dosen->{'TKGB' . $i} ?? 0;
                    $tkgb_pajak = $dosen->{'nilaiPajakTKGB' . $i} ?? 0;
                    $tkgb_bersih = $dosen->{'bersihTKGB' . $i} ?? 0;
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

        // Deteksi Guru Besar/Profesor jika belum ada di detail
        if (!$is_guru_besar) {
            $jabatanDosen = strtolower(trim($dosen->Jabatan12 ?? ($dosen->Jabatan1 ?? '')));
            $is_guru_besar = (strpos($jabatanDosen, 'guru besar') !== false || strpos($jabatanDosen, 'profesor') !== false);
        }

        $data = [
            'skpp' => $skpp,
            'dosen' => $dosen,
            'detail' => $detail,
            'bulan_terakhir_nama' => $bulan_terakhir_nama,
            'tpd_kotor' => $tpd_kotor,
            'tpd_pajak' => $tpd_pajak,
            'tpd_bersih' => $tpd_bersih,
            'tkgb_kotor' => $tkgb_kotor,
            'tkgb_pajak' => $tkgb_pajak,
            'tkgb_bersih' => $tkgb_bersih,
            'is_guru_besar' => $is_guru_besar,
            'master_kop' => DB::table('m_kop_surat')->first(),
        ];

        $viewName = ($skpp->jenis_pengajuan === 'Surat Keterangan') ? 'admin.cetak-surat-keterangan' : 'admin.cetak-skpp';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($viewName, $data);
        $pdf->setPaper('a4', 'portrait');

        $prefix = ($skpp->jenis_pengajuan === 'Surat Keterangan') ? 'Surat_Keterangan_SKPP_' : 'SKPP_';
        $namaDosen = str_replace([' ', '/', '\\'], '_', $dosen->Nama ?? 'Dosen');
        $finalFilename = $prefix . $namaDosen . '.pdf';

        if (!empty($data['master_kop']->file_pdf_url) && class_exists('\setasign\Fpdi\Fpdi')) {
            $pdfBgPath = public_path($data['master_kop']->file_pdf_url);
            
            // Fallback: Jika symlink (storage:link) di laptop lain belum ada, akses langsung file aslinya
            if (!file_exists($pdfBgPath)) {
                $rawPath = str_replace('storage/', '', $data['master_kop']->file_pdf_url);
                $directStoragePath = storage_path('app/public/' . $rawPath);
                if (file_exists($directStoragePath)) {
                    $pdfBgPath = $directStoragePath;
                }
            }
            
            if (file_exists($pdfBgPath)) {
                $dompdfOutput = $pdf->output();
                $tempSkpp = tempnam(sys_get_temp_dir(), 'skpp_');
                file_put_contents($tempSkpp, $dompdfOutput);

                $fpdi = new \setasign\Fpdi\Fpdi();
                
                // Import Background Kop Surat (Page 1)
                $fpdi->setSourceFile($pdfBgPath);
                $bgTplId = $fpdi->importPage(1);
                
                // Import Generated SKPP
                $pageCount = $fpdi->setSourceFile($tempSkpp);
                
                for ($i = 1; $i <= $pageCount; $i++) {
                    $fpdi->AddPage();
                    // Gunakan template background di setiap halaman (atau bisa diatur khusus halaman 1)
                    $fpdi->useTemplate($bgTplId);
                    
                    $skppTpl = $fpdi->importPage($i);
                    $fpdi->useTemplate($skppTpl);
                }

                unlink($tempSkpp);

                return response($fpdi->Output('S'), 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . $finalFilename . '"'
                ]);
            }
        }
        
        return $pdf->stream($finalFilename);
    }

    /**
     * Upload PDF and mark SKPP as menunggu_konfirmasi.
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
            
            // Hapus file lama jika ada agar memori server tidak penuh
            $oldLampiran = $skpp->lampiran;
            if (!empty($oldLampiran)) {
                $oldPath = storage_path('app/public/Dokumen_Histori_Dosen2/' . $oldLampiran);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
                
                // Perbarui histori lama agar menunjuk ke file revisi yang baru
                // Ini mencegah terbentuknya dua baris histori ganda ketika PIC menyetujui ulang
                DB::table('j_histori_dosen')
                    ->where('dokumen', $oldLampiran)
                    ->update(['dokumen' => $filename, 'updated_at' => now()]);
            }

            // Simpan file baru ke storage/app/public/Dokumen_Histori_Dosen2
            $file->storeAs('public/Dokumen_Histori_Dosen2', $filename);

            $dosenExists = false;
            if ($skpp->nidn || $skpp->nuptk) {
                $dosenExists = DB::table('s_transaksi_2')
                    ->where(function ($q) use ($skpp) {
                        if ($skpp->nidn) $q->where('NIDN', $skpp->nidn);
                        if ($skpp->nuptk) $q->orWhere('NUPTK', $skpp->nuptk);
                    })->exists();
            }

            if (!$dosenExists) {
                // Langsung selesai dan masuk ke histori
                DB::table('i_complain')->where('id', $request->skpp_id)->update([
                    'lampiran' => $filename,
                    'status' => 'setuju',
                    'handled_by' => auth()->user() ? auth()->user()->email : 'Admin',
                    'handled_at' => now(),
                    'updated_at' => now(),
                ]);

                $detail = json_decode($skpp->pesan, true) ?? [];
                DB::table('j_histori_dosen')->insert([
                    'nidn' => $skpp->nidn,
                    'nuptk' => $skpp->nuptk,
                    'nama' => $detail['nama'] ?? '-',
                    'pts' => $detail['pts'] ?? '-',
                    'kode_pt' => $skpp->kode_pts,
                    'aktif' => '0',
                    'keterangan' => 'Penerbitan ' . $skpp->jenis_pengajuan,
                    'pengguna' => auth()->user() ? auth()->user()->email : 'Admin',
                    'no_dokumen_ubah' => $detail['nomor_skpp'] ?? '',
                    'tgl_dokumen_ubah' => now()->format('Y-m-d'),
                    'alasan_perubahan' => 'Penerbitan ' . $skpp->jenis_pengajuan . ' Selesai (Manual)',
                    'dokumen' => $filename,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return response()->json(['success' => true, 'message' => 'PDF berhasil diupload. Karena data dosen tidak ada di database, pengajuan langsung disetujui dan masuk histori.']);
            } else {
                DB::table('i_complain')->where('id', $request->skpp_id)->update([
                    'lampiran' => $filename,
                    'status' => 'menunggu_konfirmasi',
                    'handled_by' => auth()->user() ? auth()->user()->email : 'Admin',
                    'handled_at' => now(),
                    'updated_at' => now(),
                ]);

                return response()->json(['success' => true, 'message' => 'PDF berhasil diupload. Menunggu konfirmasi PIC untuk menonaktifkan dosen.']);
            }
        }

        return response()->json(['success' => false, 'message' => 'File PDF tidak ditemukan.']);
    }

    /**
     * Konfirmasi SKPP, nonaktifkan dosen dan catat histori.
     */
    public function konfirmasi($id)
    {
        $skpp = DB::table('i_complain')->where('id', $id)->first();
        
        if (!$skpp || $skpp->status !== 'menunggu_konfirmasi') {
            return response()->json(['success' => false, 'message' => 'Data tidak valid atau belum diupload.'], 400);
        }

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
            'pengguna' => auth()->user() ? auth()->user()->email : 'Admin',
            'no_dokumen_ubah' => $detail['nomor_skpp'] ?? '',
            'tgl_dokumen_ubah' => now()->format('Y-m-d'),
            'alasan_perubahan' => 'Penerbitan ' . $skpp->jenis_pengajuan . ' Selesai, Dosen dinonaktifkan',
            'dokumen' => $skpp->lampiran,
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

        // Update status i_complain menjadi setuju
        DB::table('i_complain')->where('id', $id)->update([
            'status' => 'setuju',
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Konfirmasi berhasil. Dosen dinonaktifkan dan histori dicatat.']);
    }

    /**
     * Tolak SKPP.
     */
    public function tolak(Request $request, $id)
    {
        $skpp = DB::table('i_complain')->where('id', $id)->first();
        
        if (!$skpp || $skpp->status !== 'menunggu_konfirmasi') {
            return response()->json(['success' => false, 'message' => 'Data tidak valid atau belum diupload.'], 400);
        }

        DB::table('i_complain')->where('id', $id)->update([
            'status' => 'tolak',
            'admin_balasan' => $request->input('alasan', 'Ditolak oleh Admin/PIC'),
            'handled_by' => auth()->user() ? auth()->user()->email : 'Admin',
            'handled_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'SKPP berhasil ditolak.']);
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

        // Cegah penghapusan jika status sudah selesai (setuju) - data riwayat harus tetap ada
        if ($skpp->status === 'setuju') {
            return response()->json(['success' => false, 'message' => 'Surat yang sudah berstatus Selesai tidak dapat dihapus. Data riwayat surat harus tetap tersimpan.']);
        }

        // Hapus file fisik jika ada
        if (!empty($skpp->lampiran)) {
            $filePath = storage_path('app/public/Dokumen_Histori_Dosen2/' . $skpp->lampiran);
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
            
            // Hapus data dari tabel histori
            DB::table('j_histori_dosen')->where('dokumen', $skpp->lampiran)->delete();
        }

        // Hapus dari tabel pengajuan
        DB::table('i_complain')->where('id', $id)->delete();

        return response()->json(['success' => true, 'message' => 'Data pengajuan berhasil dihapus.']);
    }
}
