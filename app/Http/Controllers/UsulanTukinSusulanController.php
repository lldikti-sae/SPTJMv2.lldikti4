<?php

namespace App\Http\Controllers;

use App\Helpers\ErrorAlias;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class UsulanTukinSusulanController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::guard('pts')->user();
        $kodePts = $user->kode_pts;
        $tahun = session('tahun');
        $bulan = $request->query('bulan'); // 1..12

        Log::debug('usulanTukin Susulan index called', [
            'kode_pts' => $kodePts,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'query' => $request->query(),
        ]);

        $dosenList = collect();
        $dosenListPNS = collect();

        if ($bulan) {
            try {
                $bulan = (int) $bulan;

            $namaBulan = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
            $bulanTeks = $namaBulan[$bulan] ?? '';

            // Tentukan sumber BKD berdasarkan bulan
            if (in_array($bulan, [1, 2])) {
                $joinTable = ['table' => 'o_sister_genap_tl as b', 'kode_pt' => 'b.kode_pt'];
            } elseif (in_array($bulan, [3, 4, 5, 6, 7, 8])) {
                $joinTable = ['table' => 'p_sister_ganjil_tl as b', 'kode_pt' => 'b.kode_pt'];
            } else {
                $joinTable = ['table' => 'n_sister_genap_bj as b', 'kode_pt' => 'b.kode_pt'];
            }

            $dosenList = DB::table('s_transaksi_2 as d')
                ->leftJoin($joinTable['table'], function ($join) {
                    $join->on(function ($on) {
                        $on->where(function ($q) {
                            $q->whereColumn('d.NIDN', '=', 'b.nidn')
                                ->whereRaw("TRIM(d.NIDN) != ''")
                                ->whereRaw("TRIM(d.NIDN) != '-'");
                        })->orWhere(function ($q) {
                            $q->whereColumn('d.NUPTK', '=', 'b.nuptk')
                                ->whereRaw("TRIM(d.NUPTK) != ''")
                                ->whereRaw("TRIM(d.NUPTK) != '-'");
                        });
                    });
                })
                ->select(
                    DB::raw('d.Nama as nama'),
                    DB::raw('d.NIDN as nidn'),
                    DB::raw('d.NUPTK as nuptk_d'),
                    DB::raw('d.Jabatan' . $bulan . ' as jabatan'),
                    DB::raw('d.Gol' . $bulan . ' as gol'),
                    DB::raw('d.Tahun' . $bulan . ' as tahun'),
                    DB::raw('d.Aktif as aktif'),
                    DB::raw('d.Jenis as jenis'),
                    DB::raw('d.Sertifikat_Dosen as sertifikat_dosen'),
                    DB::raw('d.KodeUsulan' . $bulan . ' as kode_usulan'),
                    DB::raw('MAX(b.kesimpulan_bkd) as kesimpulan_bkd'),
                    DB::raw('MAX(b.kd) as kd'),
                    DB::raw('MAX(b.kp) as kp'),
                    DB::raw('MAX(b.potongan_periodik) as pp'),
                    DB::raw('MAX(b.nuptk) as nuptk'),
                    DB::raw('d.Keterangan as keterangan')
                )
                ->where('d.Kode_PT', $kodePts)
                ->where($joinTable['kode_pt'], $kodePts)
                ->where('d.Aktif', '1')
                ->where('d.Jenis', 'PNS')
                ->where('d.Tahun_Versi', $tahun)
                ->whereRaw("TRIM(UPPER(b.kesimpulan_bkd)) = 'M'")
                ->where(function ($q) use ($bulan) {
                    $kodeCol = 'd.KodeUsulan' . (int) $bulan;
                    $q
                        ->whereNull('d.Sertifikat_Dosen')
                        ->orWhereRaw("TRIM(d.Sertifikat_Dosen) = ''")
                        ->orWhereRaw("TRIM(d.Sertifikat_Dosen) = '-'")
                        ->orWhere(function ($q2) use ($kodeCol) {
                            $q2
                                ->whereNotNull(DB::raw($kodeCol))
                                ->whereRaw("TRIM($kodeCol) != ''")
                                ->whereRaw("TRIM($kodeCol) != '-'");
                        });
                })
                ->groupBy('d.NIDN', 'd.NUPTK', 'd.Nama', 'd.Jabatan' . $bulan, 'd.Gol' . $bulan, 'd.Tahun' . $bulan, 'd.Aktif', 'd.Jenis', 'd.Sertifikat_Dosen', 'd.KodeUsulan' . $bulan, 'd.Keterangan')
                ->orderBy('d.Nama')
                ->get();

            // Filter: jika dosen sudah pernah diusulkan pada periode ini (BT/ST) maka tidak ditampilkan
            $beforeCount = $dosenList->count();
            if ($beforeCount > 0) {
                $ids = [];
                foreach ($dosenList as $row) {
                    $nidn = isset($row->nidn) ? trim((string) $row->nidn) : '';
                    $nuptk = isset($row->nuptk) ? trim((string) $row->nuptk) : '';
                    $nuptkD = isset($row->nuptk_d) ? trim((string) $row->nuptk_d) : '';
                    if ($nidn !== '' && $nidn !== '-') $ids[] = $nidn;
                    if ($nuptk !== '' && $nuptk !== '-') $ids[] = $nuptk;
                    if ($nuptkD !== '' && $nuptkD !== '-') $ids[] = $nuptkD;
                }
                $ids = array_values(array_unique($ids));

                $existingSet = [];
                if (!empty($ids)) {
                    $existingRows = DB::table('s_tunjangan_kinerja')
                        ->where('Kode_PTS', $kodePts)
                        ->where('Bulan', $bulanTeks)
                        ->where('Tahun', (string) $tahun)
                        ->where(function ($q) use ($ids) {
                            $q->whereIn('NIDN', $ids)
                                ->orWhereIn('NUPTK', $ids);
                        })
                        ->select(['NIDN', 'NUPTK'])
                        ->get();

                    foreach ($existingRows as $r) {
                        $enidn = isset($r->NIDN) ? trim((string) $r->NIDN) : '';
                        $enuptk = isset($r->NUPTK) ? trim((string) $r->NUPTK) : '';
                        if ($enidn !== '' && $enidn !== '-') $existingSet[$enidn] = true;
                        if ($enuptk !== '' && $enuptk !== '-') $existingSet[$enuptk] = true;
                    }
                }

                if (!empty($existingSet)) {
                    $dosenList = $dosenList->filter(function ($row) use ($existingSet) {
                        $nidn = isset($row->nidn) ? trim((string) $row->nidn) : '';
                        $nuptk = isset($row->nuptk) ? trim((string) $row->nuptk) : '';
                        $nuptkD = isset($row->nuptk_d) ? trim((string) $row->nuptk_d) : '';
                        if ($nidn !== '' && $nidn !== '-' && isset($existingSet[$nidn])) return false;
                        if ($nuptk !== '' && $nuptk !== '-' && isset($existingSet[$nuptk])) return false;
                        if ($nuptkD !== '' && $nuptkD !== '-' && isset($existingSet[$nuptkD])) return false;
                        return true;
                    })->values();
                }

                // $removed = $beforeCount - $dosenList->count();
                // if ($removed > 0) {
                //     session()->flash('info', "$removed dosen sudah diusulkan pada periode $bulanTeks $tahun, sehingga tidak ditampilkan.");
                // }
            }

            // Tukin hanya untuk dosen PNS
            $dosenListPNS = $dosenList
                ->filter(function ($row) {
                    $jenis = isset($row->jenis) ? trim((string) $row->jenis) : '';
                    return strtoupper($jenis) === 'PNS';
                })
                ->values();

            Log::debug('usulanTukin Susulan index loaded', [
                'kode_pts' => $kodePts,
                'bulan' => $bulan,
                'bulan_teks' => $bulanTeks,
                'tahun' => $tahun,
                'count_total' => $dosenList->count(),
                'count_pns' => $dosenListPNS->count(),
            ]);
            } catch (\Throwable $e) {
                $alias = ErrorAlias::fromThrowable($e, 'PTS-TUKIN-SUSULAN');
                Log::error('usulanTukin Susulan index failed', [
                    'alias' => $alias['code'],
                    'kode_pts' => $kodePts,
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                $dosenList = collect();
                $dosenListPNS = collect();
                return view('pts.usulan-tukin-susulan', compact('dosenList', 'dosenListPNS', 'bulan'))
                    ->with('internal_error', $alias['message']);
            }
        }

        return view('pts.usulan-tukin-susulan', compact('dosenList', 'dosenListPNS', 'bulan'));
    }

    public function usulkan(Request $request)
    {
        $user = Auth::guard('pts')->user();

        Log::info('usulkanTukinSusulan called', [
            'kode_pts' => $user->kode_pts ?? null,
            'user_id' => $user->id ?? null,
            'bulan' => $request->input('bulan'),
            'has_file' => $request->hasFile('file'),
            'file_name' => optional($request->file('file'))->getClientOriginalName(),
            'file_size' => optional($request->file('file'))->getSize(),
        ]);

        try {
            $request->validate([
                'bulan' => 'required|integer|min:1|max:12',
                'nidn' => 'nullable|string',
                'nuptk' => 'nullable|string',
                'nama' => 'nullable|string',
                'jabatan' => 'nullable|string',
                'kota' => 'nullable|string',
                'nomor_surat' => 'nullable|string',
                'file' => 'nullable|file|mimes:pdf|max:2048',
            ]);
        } catch (ValidationException $ve) {
            Log::warning('usulkanTukinSusulan validation failed', [
                'kode_pts' => $user->kode_pts ?? null,
                'errors' => $ve->errors(),
                'input' => array_filter($request->except(['file'])),
            ]);
            return redirect()->route('pts.usulan-tukin-susulan', ['bulan' => $request->input('bulan')])->with('error', 'Validasi gagal: ' . implode('; ', array_map(function($v){return implode(', ', $v);}, $ve->errors())));
        }

        Log::info('usulkanTukinSusulan validation passed', ['kode_pts' => $user->kode_pts ?? null]);

        try {

        $kodePts = $user->kode_pts;
        $namaPts = $user->nama_pts;
        $tahun = session('tahun');
        $bulan = (int) $request->input('bulan');

        $namaBulan = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
        $bulanTeks = $namaBulan[$bulan] ?? '';

        // Sumber BKD
        if (in_array($bulan, [1, 2])) {
            $joinTable = ['table' => 'o_sister_genap_tl as b'];
        } elseif (in_array($bulan, [3, 4, 5, 6, 7, 8])) {
            $joinTable = ['table' => 'p_sister_ganjil_tl as b'];
        } else {
            $joinTable = ['table' => 'n_sister_genap_bj as b'];
        }

        // Ambil list dosen persis seperti Berjalan (Aktif PNS)
        $dosenList = DB::table('s_transaksi_2 as d')
            ->leftJoin($joinTable['table'], function ($join) {
                $join->on(function ($on) {
                    $on->where(function ($q) {
                        $q->whereColumn('d.NIDN', '=', 'b.nidn')
                            ->whereRaw("TRIM(d.NIDN) != ''")
                            ->whereRaw("TRIM(d.NIDN) != '-'");
                    })->orWhere(function ($q) {
                        $q->whereColumn('d.NUPTK', '=', 'b.nuptk')
                            ->whereRaw("TRIM(d.NUPTK) != ''")
                            ->whereRaw("TRIM(d.NUPTK) != '-'");
                    });
                });
            })
            ->select(
                DB::raw('d.Nama as nama'),
                DB::raw('d.NIDN as nidn'),
                DB::raw('d.NUPTK as nuptk_d'),
                DB::raw('d.Jabatan' . $bulan . ' as jabatan'),
                DB::raw('d.Gol' . $bulan . ' as gol'),
                DB::raw('d.Tahun' . $bulan . ' as tahun'),
                DB::raw('d.Aktif as aktif'),
                DB::raw('d.Jenis as jenis'),
                DB::raw('d.Sertifikat_Dosen as sertifikat_dosen'),
                DB::raw('d.KodeUsulan' . $bulan . ' as kode_usulan'),
                DB::raw('d.Keterangan as keterangan'),
                DB::raw('MAX(b.kesimpulan_bkd) as kesimpulan_bkd'),
                DB::raw('MAX(b.kd) as kd'),
                DB::raw('MAX(b.kp) as kp'),
                DB::raw('MAX(b.potongan_periodik) as pp'),
                DB::raw('MAX(b.nuptk) as nuptk')
            )
            ->where('d.Kode_PT', $kodePts)
            ->where('b.kode_pt', $kodePts)
            ->where('d.Aktif', '1')
            ->where('d.Jenis', 'PNS')
            ->where('d.Tahun_Versi', $tahun)
            ->whereRaw("TRIM(UPPER(b.kesimpulan_bkd)) = 'M'")
            ->where(function ($q) use ($bulan) {
                $kodeCol = 'd.KodeUsulan' . (int) $bulan;
                $q
                    ->whereNull('d.Sertifikat_Dosen')
                    ->orWhereRaw("TRIM(d.Sertifikat_Dosen) = ''")
                    ->orWhereRaw("TRIM(d.Sertifikat_Dosen) = '-'")
                    ->orWhere(function ($q2) use ($kodeCol) {
                        $q2
                            ->whereNotNull(DB::raw($kodeCol))
                            ->whereRaw("TRIM($kodeCol) != ''")
                            ->whereRaw("TRIM($kodeCol) != '-'");
                    });
            })
            ->groupBy('d.NIDN', 'd.NUPTK', 'd.Nama', 'd.Jabatan' . $bulan, 'd.Gol' . $bulan, 'd.Tahun' . $bulan, 'd.Aktif', 'd.Jenis', 'd.Sertifikat_Dosen', 'd.KodeUsulan' . $bulan, 'd.Keterangan')
            ->orderBy('d.Nama')
            ->get();

        Log::debug('usulkanTukinSusulan - fetched dosenList', [
            'kode_pts' => $kodePts,
            'bulan' => $bulan,
            'bulan_teks' => $bulanTeks,
            'tahun' => $tahun,
            'count' => $dosenList->count(),
        ]);

        // Hitung ID usulan (prefix Tukin Susulan: ST) dan siapkan metadata
        // Samakan pola dengan SPTJM Susulan:
        // - Susulan boleh lebih dari 1 usulan pada periode yang sama
        // - Penomoran berbasis kode_pts + bulan + tahun
        // Referensi penomoran memakai s_tunjangan_kinerja agar percobaan gagal tidak menaikkan nomor.
        $countUsulan = DB::table('s_tunjangan_kinerja')
            ->where('Kode_PTS', $kodePts)
            ->where('Bulan', $bulanTeks)
            ->where('Tahun', (string) $tahun)
            ->where('Kode_Usulan', 'like', 'ST %')
            ->distinct()
            ->count('Kode_Usulan');
        $noUsulan = $countUsulan + 1;
        $idUsulan = 'ST ' . str_pad((string)$bulan, 2, '0', STR_PAD_LEFT) . $kodePts . ' ' . $noUsulan;
        // Simpan tanggal usulan sebagai date (YYYY-MM-DD)
        $TanggalUsulan = now()->toDateString();
        $mapNilai = [
            'Guru Besar' => ['kelas' => '15', 'nilai' => 19280000.00],
            'Lektor Kepala' => ['kelas' => '13', 'nilai' => 10936000.00],
            'Lektor' => ['kelas' => '11', 'nilai' => 8757600.00],
            'Asisten Ahli' => ['kelas' => '9', 'nilai' => 5079200.00],
            'Tanpa Jabatan' => ['kelas' => '8', 'nilai' => 4595150.00],
            'CPNS' => ['kelas' => '7', 'nilai' => 3915950.00],
        ];
        $wilayahEmail = DB::table('s_transaksi_2')
            ->where('Kode_PT', $kodePts)
            ->where('Tahun_Versi', $tahun)
            ->value('pemegang_wilayah');

        // Simpan FILE (opsional)
        $bulanAngka = str_pad((string)$bulan, 2, '0', STR_PAD_LEFT);
        $fileRel = '';
        if ($request->hasFile('file')) {
            $ext = $request->file('file')->getClientOriginalExtension();
            $filename = 'ST ' . $bulanAngka . $kodePts . ' ' . $noUsulan . '_' . $bulanTeks . '.' . $ext;
            $request->file('file')->storeAs('public/uploadFile_TUKIN_S', $filename);
            $fileRel = 'uploadFile_TUKIN_S/' . $filename;
        }

        // HEADER dulu (tanpa transaksi) agar terekam walau detail ada yang gagal
        $nidnPimpinan = (function () use ($request) {
            $nidn = trim((string) $request->input('nidn', ''));
            $nuptk = trim((string) $request->input('nuptk', ''));
            if ($nidn !== '' && $nidn !== '-') return $nidn;
            if ($nuptk !== '' && $nuptk !== '-') return $nuptk;
            return '-';
        })();
        $nuptkPimpinan = (function () use ($request) {
            $nuptk = trim((string) $request->input('nuptk', ''));
            return ($nuptk !== '' && $nuptk !== '-') ? $nuptk : '-';
        })();
        $namaPimpinan = $request->input('nama') ?: '-';
        $jabatanPimpinan = $request->input('jabatan') ?: '-';
        $kota = $request->input('kota') ?: ($user->kota_pt ?? $user->kota ?? '-');
        $nomorSurat = $request->input('nomor_surat') ?: '-';
        $alamatPts = $user->alamat_pt ?? $user->alamat ?? '-';

        DB::table('q_sptjm')->insert([
            'id_usulan' => $idUsulan,
            'kode_pts' => $kodePts,
            'nama_pts' => $namaPts,
            'bulan' => $bulanTeks,
            'tahun' => $tahun,
            'nidn' => $nidnPimpinan,
            'nuptk' => $nuptkPimpinan,
            'nama' => $namaPimpinan,
            'jabatan' => $jabatanPimpinan,
            'kota' => $kota,
            'nomor_surat' => $nomorSurat,
            'alamat_pts' => $alamatPts,
            'file' => $fileRel,
            'status' => 'Usulan',
            'tanggal_usulan' => $TanggalUsulan,
            'wilayah' => $user->wilayah,
            'password' => $user->password ?? null,
            'aktif' => $user->aktif ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Log nilai pimpinan yang diterima untuk debugging (memastikan nuptk terkirim)
        Log::info('usulkanTukinSusulan - header values', [
            'kode_pts' => $kodePts,
            'nidn_received' => $request->input('nidn'),
            'nuptk_received' => $request->input('nuptk'),
        ]);

        // DETAIL setelah header - lakukan secara batch untuk performa
        $batchSize = 500; // tuneable

        $allIdentifiers = [];
        foreach ($dosenList as $row) {
            $nidn = isset($row->nidn) ? trim((string) $row->nidn) : '';
            $nuptk = isset($row->nuptk) ? trim((string) $row->nuptk) : '';
            $nuptkD = isset($row->nuptk_d) ? trim((string) $row->nuptk_d) : '';
            if ($nidn !== '' && $nidn !== '-') $allIdentifiers[] = $nidn;
            if ($nuptk !== '' && $nuptk !== '-') $allIdentifiers[] = $nuptk;
            if ($nuptkD !== '' && $nuptkD !== '-') $allIdentifiers[] = $nuptkD;
        }
        $allIdentifiers = array_values(array_unique($allIdentifiers));

        $existingKinerjaSet = [];
        if (!empty($allIdentifiers)) {
            $existingRows = DB::table('s_tunjangan_kinerja')
                ->where('Kode_PTS', $kodePts)
                ->where('Bulan', $bulanTeks)
                ->where('Tahun', (string) $tahun)
                ->where(function ($q) use ($allIdentifiers) {
                    $q->whereIn('NIDN', $allIdentifiers)
                        ->orWhereIn('NUPTK', $allIdentifiers);
                })
                ->select(['NIDN', 'NUPTK'])
                ->get();
            foreach ($existingRows as $r) {
                $nidn = isset($r->NIDN) ? trim((string) $r->NIDN) : '';
                $nuptk = isset($r->NUPTK) ? trim((string) $r->NUPTK) : '';
                if ($nidn !== '' && $nidn !== '-') $existingKinerjaSet[$nidn] = true;
                if ($nuptk !== '' && $nuptk !== '-') $existingKinerjaSet[$nuptk] = true;
            }
        }

        $toInsertKinerja = [];
        $rowErrorCount = 0;
        $rowErrorSamples = [];
        foreach ($dosenList as $row) {
            try {
                $nidn = isset($row->nidn) ? trim((string) $row->nidn) : '';
                $nuptk = isset($row->nuptk) ? trim((string) $row->nuptk) : '';
                $nuptkD = isset($row->nuptk_d) ? trim((string) $row->nuptk_d) : '';
                $identifier = ($nidn !== '' && $nidn !== '-') ? $nidn : (($nuptk !== '' && $nuptk !== '-') ? $nuptk : (($nuptkD !== '' && $nuptkD !== '-') ? $nuptkD : ''));
                if ($identifier === '') continue;
                if (isset($existingKinerjaSet[$identifier])) continue;

                $jab = $row->jabatan ?? '';
                $kelas = $mapNilai[$jab]['kelas'] ?? '-';
                $nilai = $mapNilai[$jab]['nilai'] ?? null;
                $statusTxt = (($row->aktif ?? '0') == '1') ? '1' : '0';

                $toInsertKinerja[] = [
                    'Kode_Usulan' => $idUsulan,
                    'NUPTK' => ($nuptk !== '' && $nuptk !== '-') ? $nuptk : (($nuptkD !== '' && $nuptkD !== '-') ? $nuptkD : null),
                    // Simpan NIDN jika tersedia; jika tidak, simpan identifier (bisa NUPTK) agar tetap bisa ditelusuri.
                    'NIDN' => ($nidn !== '' && $nidn !== '-') ? $nidn : $identifier,
                    'Nama' => $row->nama,
                    'Jenis' => $row->jenis ?? 'PNS',
                    'Kode_PTS' => $kodePts,
                    'Nama_PTS' => $namaPts,
                    'Jabatan' => $row->jabatan ?? null,
                    'Kelas_Jabatan' => $kelas,
                    'Nilai_tukin_Jabatan' => $nilai,
                    'Status' => $statusTxt,
                    'Keterangan_Status' => $row->keterangan ?? null,
                    'Serdos' => $row->sertifikat_dosen ?? null,
                    'Tanggal_Usulan' => $TanggalUsulan,
                    'Bulan' => $bulanTeks,
                    'Tahun' => (string) $tahun,
                    'Kode_Cair' => null,
                    'KD' => $row->kd ?? null,
                    'KP' => $row->kp ?? null,
                    'PP' => $row->pp ?? null,
                    'Nilai_Bersih_Serdos' => null,
                    'Nilai_Tukin' => null,
                    'Pajak' => null,
                    'Nilai_Pajak' => null,
                    'Nilai_Bersih' => null,
                ];

                // no transaction rows; all detail data stored in s_tunjangan_kinerja
            } catch (\Throwable $e) {
                $rowErrorCount++;
                if (count($rowErrorSamples) < 3) {
                    $rowErrorSamples[] = $e->getMessage();
                }
                continue;
            }
        }

        Log::info('usulkanTukinSusulan - prepared insert rows', [
            'kode_pts' => $kodePts,
            'bulan' => $bulan,
            'bulan_teks' => $bulanTeks,
            'tahun' => $tahun,
            'to_insert' => count($toInsertKinerja),
            'row_errors' => $rowErrorCount,
            'row_error_samples' => $rowErrorSamples,
        ]);

        try {
            foreach (array_chunk($toInsertKinerja, $batchSize) as $chunk) {
                DB::table('s_tunjangan_kinerja')->insert($chunk);
            }

            return redirect()->route('pts.usulan-tukin-susulan', ['bulan' => $bulan])
                ->with('success', 'Usulan Tukin Susulan berhasil direkam.');
        } catch (\Throwable $e) {
            $alias = ErrorAlias::fromThrowable($e, 'PTS-TUKIN-SUSULAN');
            Log::error('usulkanTukinSusulan - insert failed', [
                'alias' => $alias['code'],
                'kode_pts' => $kodePts ?? null,
                'bulan' => $bulan,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('pts.usulan-tukin-susulan', ['bulan' => $bulan])->with('internal_error', $alias['message']);
        }

        } catch (\Throwable $e) {
            $alias = ErrorAlias::fromThrowable($e, 'PTS-TUKIN-SUSULAN');
            Log::error('usulkanTukinSusulan failed', [
                'alias' => $alias['code'],
                'kode_pts' => $user->kode_pts ?? null,
                'bulan' => $request->input('bulan'),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('pts.usulan-tukin-susulan', ['bulan' => $request->input('bulan')])
                ->with('internal_error', $alias['message']);
        }
    }

    public function print(Request $request)
    {
        $user = Auth::guard('pts')->user();
        $kodePts = $user->kode_pts;
        $tahun = session('tahun');
        $bulan = $request->query('bulan') ? (int) $request->query('bulan') : now()->month;

        Log::debug('printTukin Susulan called', [
            'kode_pts' => $kodePts,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'query' => $request->query(),
        ]);

        try {

        // Tentukan sumber BKD berdasarkan bulan
        if (in_array($bulan, [1, 2])) {
            $joinTable = ['table' => 'o_sister_genap_tl as b', 'kode_pt' => 'b.kode_pt'];
        } elseif (in_array($bulan, [3, 4, 5, 6, 7, 8])) {
            $joinTable = ['table' => 'p_sister_ganjil_tl as b', 'kode_pt' => 'b.kode_pt'];
        } else {
            $joinTable = ['table' => 'n_sister_genap_bj as b', 'kode_pt' => 'b.kode_pt'];
        }

        $dosenList = DB::table('s_transaksi_2 as d')
            ->leftJoin($joinTable['table'], function ($join) {
                $join->on(function ($on) {
                    $on->where(function ($q) {
                        $q->whereColumn('d.NIDN', '=', 'b.nidn')
                            ->whereRaw("TRIM(d.NIDN) != ''")
                            ->whereRaw("TRIM(d.NIDN) != '-'");
                    })->orWhere(function ($q) {
                        $q->whereColumn('d.NUPTK', '=', 'b.nuptk')
                            ->whereRaw("TRIM(d.NUPTK) != ''")
                            ->whereRaw("TRIM(d.NUPTK) != '-'");
                    });
                });
            })
            ->select(
                DB::raw('d.Nama as nama'),
                DB::raw('d.NIDN as nidn'),
                DB::raw('d.NUPTK as nuptk_d'),
                DB::raw('d.Jabatan' . $bulan . ' as jabatan'),
                DB::raw('d.Gol' . $bulan . ' as gol'),
                DB::raw('d.Tahun' . $bulan . ' as tahun'),
                DB::raw('d.Aktif as aktif'),
                DB::raw('d.Jenis as jenis'),
                DB::raw('d.Sertifikat_Dosen as sertifikat_dosen'),
                DB::raw('MAX(b.kesimpulan_bkd) as kesimpulan_bkd'),
                DB::raw('MAX(b.kd) as kd'),
                DB::raw('MAX(b.kp) as kp'),
                DB::raw('MAX(b.potongan_periodik) as pp'),
                DB::raw('MAX(b.nuptk) as nuptk'),
                DB::raw('d.Keterangan as keterangan')
            )
            ->where('d.Kode_PT', $kodePts)
            ->where($joinTable['kode_pt'], $kodePts)
            ->where('d.Aktif', '1')
            ->where('d.Jenis', 'PNS')
            ->where('d.Tahun_Versi', $tahun)
            ->whereRaw("TRIM(UPPER(b.kesimpulan_bkd)) = 'M'")
            ->where(function ($q) use ($bulan) {
                $kodeCol = 'd.KodeUsulan' . (int) $bulan;
                $q
                    ->whereNull('d.Sertifikat_Dosen')
                    ->orWhereRaw("TRIM(d.Sertifikat_Dosen) = ''")
                    ->orWhereRaw("TRIM(d.Sertifikat_Dosen) = '-'")
                    ->orWhere(function ($q2) use ($kodeCol) {
                        $q2
                            ->whereNotNull(DB::raw($kodeCol))
                            ->whereRaw("TRIM($kodeCol) != ''")
                            ->whereRaw("TRIM($kodeCol) != '-'");
                    });
            })
            ->groupBy('d.NIDN', 'd.NUPTK', 'd.Nama', 'd.Jabatan' . $bulan, 'd.Gol' . $bulan, 'd.Tahun' . $bulan, 'd.Aktif', 'd.Jenis', 'd.Sertifikat_Dosen', 'd.Keterangan')
            ->orderBy('d.Nama')
            ->get();

        Log::debug('printTukin Susulan loaded', [
            'kode_pts' => $kodePts,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'count' => $dosenList->count(),
        ]);

        return view('pts.print-tukin-susulan', [
            'dosenList'   => $dosenList,
            'bulan'       => $bulan,
            'pts'         => $user->nama_pts,
            'kode_pts'    => $user->kode_pts,
            'alamat_pts'  => $user->alamat_pt ?? $user->alamat ?? '',
            'tanggal'     => now()->translatedFormat('d F Y'),
        ]);

        } catch (\Throwable $e) {
            $alias = ErrorAlias::fromThrowable($e, 'PTS-TUKIN-SUSULAN');
            Log::error('printTukin Susulan failed', [
                'alias' => $alias['code'],
                'kode_pts' => $kodePts,
                'bulan' => $bulan,
                'tahun' => $tahun,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('pts.usulan-tukin-susulan', ['bulan' => $bulan])
                ->with('internal_error', $alias['message']);
        }
    }
}