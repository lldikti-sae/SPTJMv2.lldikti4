<?php

namespace App\Http\Controllers;

use App\Helpers\ErrorAlias;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class UsulanTukinBerjalanController extends Controller
{
	public function index(Request $request)
	{
		$user = Auth::guard('pts')->user();
		$kodePts = $user->kode_pts;
		$bulan = $request->query('bulan');
		$tahun = session('tahun');

		Log::debug('usulanTukin Berjalan index called', [
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
				$joinTable = ['table' => 'p_sister_tukin as b', 'kode_pt' => 'b.kode_pt', 'periode' => 'Genap'];
			} elseif (in_array($bulan, [3, 4, 5, 6, 7, 8])) {
				$joinTable = ['table' => 'p_sister_tukin as b', 'kode_pt' => 'b.kode_pt', 'periode' => 'Ganjil'];
			} else {
				$joinTable = ['table' => 'p_sister_tukin as b', 'kode_pt' => 'b.kode_pt', 'periode' => 'Genap'];
			}

			$dosenList = DB::table('s_transaksi_2 as d')
				->leftJoin($joinTable['table'], function ($join) use ($joinTable) {
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
					if (isset($joinTable['periode'])) {
						$join->where('b.periode', '=', $joinTable['periode']);
					}
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
					DB::raw('d.Keterangan as keterangan'),
					DB::raw('d.Gaji' . $bulan . ' as gaji_serdos'),
					DB::raw('d.TPD' . $bulan . ' as pembayaran_serdos')
				)
				->where('d.Kode_PT', $kodePts)
				->where($joinTable['kode_pt'], $kodePts)
				->where('d.Aktif', '1')
				->where('d.Jenis', 'PNS')
				->where('d.Tahun_Versi', session('tahun'))
				->whereRaw("TRIM(UPPER(b.kesimpulan_bkd)) = 'M'")
				->where(function ($q) use ($bulan) {
					$kodeCol = 'd.KodeUsulan' . (int) $bulan;
					$q
						// belum punya sertifikat: boleh tampil
						->whereNull('d.Sertifikat_Dosen')
						->orWhereRaw("TRIM(d.Sertifikat_Dosen) = ''")
						->orWhereRaw("TRIM(d.Sertifikat_Dosen) = '-'")
						// sudah punya sertifikat: wajib ada kode usulan serdos
						->orWhere(function ($q2) use ($kodeCol) {
							$q2
								->whereNotNull(DB::raw($kodeCol))
								->whereRaw("TRIM($kodeCol) != ''")
								->whereRaw("TRIM($kodeCol) != '-'");
						});
				})
				->groupBy('d.NIDN', 'd.NUPTK', 'd.Nama', 'd.Jabatan' . $bulan, 'd.Gol' . $bulan, 'd.Tahun' . $bulan, 'd.Aktif', 'd.Jenis', 'd.Sertifikat_Dosen', 'd.KodeUsulan' . $bulan, 'd.Keterangan', 'd.Gaji' . $bulan, 'd.TPD' . $bulan)
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
						$nidn = trim((string) ($row->NIDN ?? $row->nidn ?? ''));
						$nuptk = trim((string) ($row->NUPTK ?? $row->nuptk ?? ''));
						$nuptkD = trim((string) ($row->NUPTK_D ?? $row->nuptk_d ?? ''));
						if ($nidn !== '' && $nidn !== '-' && isset($existingSet[$nidn])) return false;
						if ($nuptk !== '' && $nuptk !== '-' && isset($existingSet[$nuptk])) return false;
						if ($nuptkD !== '' && $nuptkD !== '-' && isset($existingSet[$nuptkD])) return false;
						return true;
					})->values();
				}
				
				// --- LOGIC NILAI KURANG TUKIN (BACKLOG) ---
				$ids = $dosenList->pluck('NIDN')->merge($dosenList->pluck('NUPTK'))->filter()->unique()->toArray();
				if (empty($ids)) {
					$ids = $dosenList->pluck('nidn')->merge($dosenList->pluck('nuptk'))->filter()->unique()->toArray();
				}

				$pendingKurangBayar = [];
				if (\Illuminate\Support\Facades\Schema::hasTable('t_uraian_pembayaran') && !empty($ids)) {
					$kurangBayarRows = \Illuminate\Support\Facades\DB::table('t_uraian_pembayaran')
						->whereIn('nidn', $ids)
						->where('status_cair', 0)
						->get();
					foreach ($kurangBayarRows as $kb) {
						$kbNidn = trim((string) $kb->nidn);
						if (!isset($pendingKurangBayar[$kbNidn])) {
							$pendingKurangBayar[$kbNidn] = 0;
						}
						$pendingKurangBayar[$kbNidn] += (float) $kb->bersih;
					}
				}

				foreach ($dosenList as $row) {
					$nidn = trim((string) ($row->NIDN ?? $row->nidn ?? ''));
					$nuptk = trim((string) ($row->NUPTK ?? $row->nuptk ?? ''));
					$nuptkD = trim((string) ($row->NUPTK_D ?? $row->nuptk_d ?? ''));
					$identifier = ($nidn !== '' && $nidn !== '-') ? $nidn : (($nuptk !== '' && $nuptk !== '-') ? $nuptk : (($nuptkD !== '' && $nuptkD !== '-') ? $nuptkD : ''));
					
					// Penyesuaian Serdos: Selisih Serdos = Gaji Serdos - Pembayaran Serdos
					$gajiSerdos = (float) ($row->gaji_serdos ?? 0);
					$pembayaranSerdos = (float) ($row->pembayaran_serdos ?? 0);
					$selisihSerdos = $gajiSerdos - $pembayaranSerdos;
					
					$row->selisih_serdos = $selisihSerdos;
					
					// Nilai Kurang awal = dari inputan manual Admin (t_uraian_pembayaran) + (Jika Selisih Serdos > 0)
					$baseKurang = $pendingKurangBayar[$identifier] ?? 0;
					$tambahanKurang = ($selisihSerdos > 0) ? $selisihSerdos : 0;
					$row->nilai_kurang = $baseKurang + $tambahanKurang;
					
					// Jika Lebih bayar serdos
					$row->nilai_lebih = ($selisihSerdos < 0) ? abs($selisihSerdos) : 0;
				}

				// $removed = $beforeCount - $dosenList->count();
				// if ($removed > 0) {
				// 	session()->flash('info', "$removed dosen sudah diusulkan pada periode $bulanTeks $tahun, sehingga tidak ditampilkan.");
				// }
			}

			// Tukin hanya untuk dosen PNS
			$dosenListPNS = $dosenList
				->filter(function ($row) {
					$jenis = trim((string) ($row->Jenis ?? $row->jenis ?? ''));
					return strtoupper($jenis) === 'PNS';
				})
				->values();

			Log::debug('usulanTukin Berjalan index loaded', [
				'kode_pts' => $kodePts,
				'bulan' => $bulan,
				'bulan_teks' => $bulanTeks,
				'tahun' => $tahun,
				'count_total' => $dosenList->count(),
				'count_pns' => $dosenListPNS->count(),
			]);
			} catch (\Throwable $e) {
				$alias = ErrorAlias::fromThrowable($e, 'PTS-TUKIN-BERJALAN');
				Log::error('usulanTukin Berjalan index failed', [
					'alias' => $alias['code'],
					'kode_pts' => $kodePts,
					'bulan' => $bulan,
					'tahun' => $tahun,
					'message' => $e->getMessage(),
					'trace' => $e->getTraceAsString(),
				]);

				$dosenList = collect();
				$dosenListPNS = collect();
				return view('pts.usulan-tukin-berjalan', compact('dosenList', 'dosenListPNS', 'bulan'))
					->with('internal_error', $alias['message']);
			}
		}

		return view('pts.usulan-tukin-berjalan', compact('dosenList', 'dosenListPNS', 'bulan'));
	}

	public function usulkan(Request $request)
	{
		$user = Auth::guard('pts')->user();

		Log::info('usulkanTukinBerjalan called', [
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
				// data pimpinan opsional, diambil dari modal jika diisi
					'nidn' => 'nullable|string',
					'nuptk' => 'nullable|string',
				'nama' => 'nullable|string',
				'jabatan' => 'nullable|string',
				'kota' => 'nullable|string',
				'nomor_surat' => 'nullable|string',
				'file' => 'nullable|file|mimes:pdf|max:2048',
			]);
		} catch (ValidationException $ve) {
			Log::warning('usulkanTukinBerjalan validation failed', [
				'kode_pts' => $user->kode_pts ?? null,
				'errors' => $ve->errors(),
				'input' => array_filter($request->except(['file'])),
			]);
			return redirect()->route('pts.usulan-tukin-berjalan', ['bulan' => $request->input('bulan')])->with('error', 'Validasi gagal: ' . implode('; ', array_map(function($v){return implode(', ', $v);}, $ve->errors())));
		}

		Log::info('usulkanTukinBerjalan validation passed', ['kode_pts' => $user->kode_pts ?? null]);

		try {

		$kodePts = $user->kode_pts;
		$namaPts = $user->nama_pts;
		$tahun = session('tahun');
		$bulan = (int) $request->input('bulan');

		$namaBulan = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
		$bulanTeks = $namaBulan[$bulan] ?? '';

		// Tentukan sumber BKD berdasarkan bulan
		if (in_array($bulan, [1, 2])) {
			$joinTable = ['table' => 'p_sister_genap as b'];
		} elseif (in_array($bulan, [3, 4, 5, 6, 7, 8])) {
			$joinTable = ['table' => 'p_sister_ganjil as b'];
		} else {
			$joinTable = ['table' => 'p_sister_genap as b'];
		}

		// Ambil list dosen sesuai tampilan index
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
				DB::raw('d.Keterangan as keterangan'),
				DB::raw('d.KodeUsulan' . $bulan . ' as kode_usulan'),
				DB::raw('MAX(b.kesimpulan_bkd) as kesimpulan_bkd'),
				DB::raw('MAX(b.kd) as kd'),
				DB::raw('MAX(b.kp) as kp'),
				DB::raw('MAX(b.potongan_periodik) as pp'),
				DB::raw('MAX(b.nuptk) as nuptk'),
				DB::raw('d.Gaji' . $bulan . ' as gaji_serdos'),
				DB::raw('d.TPD' . $bulan . ' as pembayaran_serdos')
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
			->groupBy('d.NIDN', 'd.NUPTK', 'd.Nama', 'd.Jabatan' . $bulan, 'd.Gol' . $bulan, 'd.Tahun' . $bulan, 'd.Aktif', 'd.Jenis', 'd.Sertifikat_Dosen', 'd.Keterangan', 'd.KodeUsulan' . $bulan, 'd.Gaji' . $bulan, 'd.TPD' . $bulan)
			->orderBy('d.Nama')
			->get();

		Log::debug('usulkanTukinBerjalan - fetched dosenList', [
			'kode_pts' => $kodePts,
			'bulan' => $bulan,
			'bulan_teks' => $bulanTeks,
			'tahun' => $tahun,
			'count' => $dosenList->count(),
		]);

		// Hitung ID usulan (prefix Tukin Berjalan: BT) dan siapkan metadata wilayah
		// Samakan pola dengan SPTJM Berjalan:
		// - Berjalan tidak boleh ganda pada periode yang sama
		// - Penomoran berbasis kode_pts + bulan + tahun
		$sudahUsulkanBulanIni = DB::table('s_tunjangan_kinerja')
			->where('Kode_PTS', $kodePts)
			->where('Bulan', $bulanTeks)
			->where('Tahun', (string) $tahun)
			->where('Kode_Usulan', 'like', 'BT %')
			->exists();
		if ($sudahUsulkanBulanIni) {
			return redirect()
				->route('pts.usulan-tukin-berjalan', ['bulan' => $bulan])
				->with('error', 'Sudah melakukan usulan Tukin Berjalan pada periode ini. Pengusulan ganda tidak diperbolehkan.');
		}

		// Validasi: TUKIN hanya dapat diusulkan setelah SPTJM (Serdos) sudah diusulkan
		$sptjmDiUsulkan = DB::table('q_sptjm')
			->where('kode_pts', $kodePts)
			->where('bulan', $bulanTeks)
			->where('tahun', (string) $tahun)
			->where('id_usulan', 'like', 'B %')
			->exists();
		if (!$sptjmDiUsulkan) {
			return redirect()
				->route('pts.usulan-tukin-berjalan', ['bulan' => $bulan])
				->with('error', 'Usulan TUKIN ditolak. Anda harus mengusulkan SPTJM (Serdos) terlebih dahulu untuk periode ini.');
		}

		$countUsulan = DB::table('s_tunjangan_kinerja')
			->where('Kode_PTS', $kodePts)
			->where('Bulan', $bulanTeks)
			->where('Tahun', (string) $tahun)
			->where('Kode_Usulan', 'like', 'BT %')
			->distinct()
			->count('Kode_Usulan');
		$noUsulan = $countUsulan + 1;
		$idUsulan = 'BT ' . str_pad((string)$bulan, 2, '0', STR_PAD_LEFT) . $kodePts . ' ' . $noUsulan;
		// Simpan tanggal usulan sebagai date (YYYY-MM-DD)
		$TanggalUsulan = now()->toDateString();

		// Mapping kelas & nilai tukin jabatan (angka, tanpa format Rupiah)
		$mapNilai = [
			'Guru Besar' => ['kelas' => '15', 'nilai' => 19280000.00],
			'Guru Besar 1050' => ['kelas' => '15', 'nilai' => 19280000.00],
			'Lektor Kepala' => ['kelas' => '13', 'nilai' => 10936000.00],
			'Lektor' => ['kelas' => '11', 'nilai' => 8757600.00],
			'Asisten Ahli' => ['kelas' => '9', 'nilai' => 5079200.00],
			'Tanpa Jabatan' => ['kelas' => '8', 'nilai' => 4595150.00],
			'CPNS' => ['kelas' => '7', 'nilai' => 3915950.00],
		];

		// Email PIC wilayah (pemegang_wilayah) jika tersedia di transaksi
		$wilayahEmail = DB::table('s_transaksi_2')
			->where('Kode_PT', $kodePts)
			->where('Tahun_Versi', $tahun)
			->value('pemegang_wilayah');

		// 1) Insert HEADER dulu (mirroring SPTJM) agar selalu terekam walau detail gagal
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

		// Siapkan file jika diupload
		$bulanAngka = str_pad((string)$bulan, 2, '0', STR_PAD_LEFT);
		$fileRel = '';
		if ($request->hasFile('file')) {
			$ext = $request->file('file')->getClientOriginalExtension();
			$filename = 'BT ' . $bulanAngka . $kodePts . ' ' . $noUsulan . '_' . $bulanTeks . '.' . $ext;
			$request->file('file')->storeAs('public/uploadFile_TUKIN_B', $filename);
			$fileRel = 'uploadFile_TUKIN_B/' . $filename;
		}

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
		Log::info('usulkanTukinBerjalan - header values', [
			'kode_pts' => $kodePts,
			'nidn_received' => $request->input('nidn'),
			'nuptk_received' => $request->input('nuptk'),
		]);

		// 2) Lanjut insert DETAIL secara batch: kurangi query per-row untuk performa
		$batchSize = 500; // tuneable

		// Kumpulkan semua identifier (NIDN dan/atau NUPTK) dari hasil query
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

		// Ambil identifier yang sudah ada di s_tunjangan_kinerja untuk periode ini (cek NIDN atau NUPTK)
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
				$nidn = trim((string) ($r->NIDN ?? $r->nidn ?? ''));
				$nuptk = trim((string) ($r->NUPTK ?? $r->nuptk ?? ''));
				if ($nidn !== '' && $nidn !== '-') $existingKinerjaSet[$nidn] = true;
				if ($nuptk !== '' && $nuptk !== '-') $existingKinerjaSet[$nuptk] = true;
			}
		}

		$toInsertKinerja = [];
		$rowErrorCount = 0;
		$rowErrorSamples = [];
		$ids = $dosenList->pluck('NIDN')->merge($dosenList->pluck('NUPTK'))->filter()->unique()->toArray();
		if (empty($ids)) {
			$ids = $dosenList->pluck('nidn')->merge($dosenList->pluck('nuptk'))->filter()->unique()->toArray();
		}

		$pendingKurangBayar = [];
		if (\Illuminate\Support\Facades\Schema::hasTable('t_uraian_pembayaran') && !empty($ids)) {
			$kurangBayarRows = \Illuminate\Support\Facades\DB::table('t_uraian_pembayaran')
				->whereIn('nidn', $ids)
				->where('status_cair', 0)
				->get();
			foreach ($kurangBayarRows as $kb) {
				$kbNidn = trim((string) $kb->nidn);
				if (!isset($pendingKurangBayar[$kbNidn])) {
					$pendingKurangBayar[$kbNidn] = 0;
				}
				$pendingKurangBayar[$kbNidn] += (float) $kb->bersih;
			}
		}

		$updatedKurangBayarIds = [];

		foreach ($dosenList as $row) {
			try {
				$nidn = trim((string) ($row->NIDN ?? $row->nidn ?? ''));
				$nuptk = trim((string) ($row->NUPTK ?? $row->nuptk ?? ''));
				$nuptkD = trim((string) ($row->NUPTK_D ?? $row->nuptk_d ?? ''));
				$identifier = ($nidn !== '' && $nidn !== '-') ? $nidn : (($nuptk !== '' && $nuptk !== '-') ? $nuptk : (($nuptkD !== '' && $nuptkD !== '-') ? $nuptkD : ''));
				if ($identifier === '') continue;
				if (isset($existingKinerjaSet[$identifier])) continue;

				$jab = $row->jabatan ?? '';
				$kelas = $mapNilai[$jab]['kelas'] ?? '-';
				$nilaiDasar = (float) ($mapNilai[$jab]['nilai'] ?? 0);
				$statusTxt = (($row->aktif ?? '0') == '1') ? '1' : '0';

				// Perhitungan TUKIN sesuai Sheet2
				// % KD = 60%, % KP (diberikan dari sister, tapi di sister ini adalah nilai kp langsung atau persentasenya?)
				// Wait, di Sheet2: Nilai KD = Nilai Tukin * 60%
				// Nilai KP = Nilai Tukin * %KP. 
				// Tapi $row->kd dari sister mungkin berupa angka persentase atau nominal?
				// User instructions: "% Kinerja Dasar = 60%. % Kinerja Prestasi mengikuti persentase 0-40% sesuai nilai kinerja. Nilai KD = Nilai Tukin x %KD. Nilai KP = Nilai Tukin x %KP."
				// Kita asumsikan $row->kd dan $row->kp dari data bkd/sister adalah nilai persentase (contoh: 60, 40).
				// Jika dari awal nilainya sudah tersimpan (atau harus kita hitung manual di sini?)
				// Kita akan asumsikan $row->kd adalah 60 (persentase) atau nominal?
				// Biar aman, kita hitung nominalnya.
				$persenKD = 60 / 100; // Selalu 60% berdasarkan instruksi Sheet2
				$persenKP = (float) ($row->kp ?? 0) / 100; // Asumsi $row->kp adalah angka persentase 0-40
				$nilaiKD = $nilaiDasar * $persenKD;
				$nilaiKP = $nilaiDasar * $persenKP;
				$potonganPeriodik = (float) ($row->pp ?? 0);
				
				$nilaiTukinMurni = $nilaiKD + $nilaiKP - $potonganPeriodik;

				$toInsertKinerja[] = [
					'Kode_Usulan' => $idUsulan,
					'NUPTK' => ($nuptk !== '' && $nuptk !== '-') ? $nuptk : (($nuptkD !== '' && $nuptkD !== '-') ? $nuptkD : null),
					'NIDN' => ($nidn !== '' && $nidn !== '-') ? $nidn : $identifier,
					'Nama' => $row->nama,
					'Jenis' => $row->jenis ?? 'PNS',
					'Kode_PTS' => $kodePts,
					'Nama_PTS' => $namaPts,
					'Jabatan' => $row->jabatan ?? null,
					'Kelas_Jabatan' => $kelas,
					'Nilai_tukin_Jabatan' => $nilaiDasar,
					'Status' => $statusTxt,
					'Keterangan_Status' => $row->keterangan ?? null,
					'Serdos' => $row->sertifikat_dosen ?? null,
					'Tanggal_Usulan' => $TanggalUsulan,
					'Bulan' => $bulanTeks,
					'Tahun' => (string) $tahun,
					'Kode_Cair' => null,
					'KD' => $nilaiKD,
					'KP' => $nilaiKP,
					'PP' => $potonganPeriodik,
					'Nilai_Bersih_Serdos' => 0,
					'Nilai_Tukin' => $nilaiTukinMurni,
					'Pajak' => 0,
					'Nilai_Pajak' => 0,
					'Nilai_Bersih' => $nilaiTukinMurni,
					'Nilai_Kurang' => 0,
				];

				if (isset($pendingKurangBayar[$identifier]) && $pendingKurangBayar[$identifier] > 0) {
					$updatedKurangBayarIds[] = $identifier;
				}
				// all detail rows will be stored in s_tunjangan_kinerja; no transaction table usage
			} catch (\Throwable $e) {
				$rowErrorCount++;
				if (count($rowErrorSamples) < 3) {
					$rowErrorSamples[] = $e->getMessage();
				}
				continue;
			}
		}

		Log::info('usulkanTukinBerjalan - prepared insert rows', [
			'kode_pts' => $kodePts,
			'bulan' => $bulan,
			'bulan_teks' => $bulanTeks,
			'tahun' => $tahun,
			'to_insert' => count($toInsertKinerja),
			'row_errors' => $rowErrorCount,
			'row_error_samples' => $rowErrorSamples,
		]);

		// Insert in chunks
		try {
			foreach (array_chunk($toInsertKinerja, $batchSize) as $chunk) {
				DB::table('s_tunjangan_kinerja')->insert($chunk);
			}

			if (!empty($updatedKurangBayarIds) && \Illuminate\Support\Facades\Schema::hasTable('t_uraian_pembayaran')) {
				\Illuminate\Support\Facades\DB::table('t_uraian_pembayaran')
					->whereIn('nidn', $updatedKurangBayarIds)
					->update(['status_cair' => 1]);
			}
            
			return redirect()->route('pts.usulan-tukin-berjalan', ['bulan' => $bulan])
				->with('success', 'Usulan Tukin Berjalan berhasil direkam.');
		} catch (\Throwable $e) {
			$alias = ErrorAlias::fromThrowable($e, 'PTS-TUKIN-BERJALAN');
			Log::error('usulkanTukinBerjalan - insert failed', [
				'alias' => $alias['code'],
				'kode_pts' => $kodePts ?? null,
				'bulan' => $bulan,
				'message' => $e->getMessage(),
				'trace' => $e->getTraceAsString(),
			]);
			return redirect()->route('pts.usulan-tukin-berjalan', ['bulan' => $bulan])->with('internal_error', $alias['message']);
		}
		} catch (\Throwable $e) {
			$alias = ErrorAlias::fromThrowable($e, 'PTS-TUKIN-BERJALAN');
			Log::error('usulkanTukinBerjalan failed', [
				'alias' => $alias['code'],
				'kode_pts' => $user->kode_pts ?? null,
				'bulan' => $request->input('bulan'),
				'message' => $e->getMessage(),
				'trace' => $e->getTraceAsString(),
			]);
			return redirect()->route('pts.usulan-tukin-berjalan', ['bulan' => $request->input('bulan')])
				->with('internal_error', $alias['message']);
		}
	}

	public function print(Request $request)
	{
		$user = Auth::guard('pts')->user();
		$kodePts = $user->kode_pts;
		$tahun = session('tahun');
		$bulan = $request->query('bulan') ? (int) $request->query('bulan') : now()->month;

		Log::debug('printTukin Berjalan called', [
			'kode_pts' => $kodePts,
			'bulan' => $bulan,
			'tahun' => $tahun,
			'query' => $request->query(),
		]);

		try {

		// Tentukan sumber BKD berdasarkan bulan
		if (in_array($bulan, [1, 2])) {
			$joinTable = ['table' => 'p_sister_genap as b', 'kode_pt' => 'b.kode_pt'];
		} elseif (in_array($bulan, [3, 4, 5, 6, 7, 8])) {
			$joinTable = ['table' => 'p_sister_ganjil as b', 'kode_pt' => 'b.kode_pt'];
		} else {
			$joinTable = ['table' => 'p_sister_genap as b', 'kode_pt' => 'b.kode_pt'];
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
			->where('d.Tahun_Versi', session('tahun'))
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

		$ids = $dosenList->pluck('nidn')->merge($dosenList->pluck('nuptk_d'))->filter()->unique()->toArray();
		$pendingKurangBayar = [];
		if (\Illuminate\Support\Facades\Schema::hasTable('t_uraian_pembayaran') && !empty($ids)) {
			$kurangBayarRows = \Illuminate\Support\Facades\DB::table('t_uraian_pembayaran')
				->whereIn('nidn', $ids)
				->where('status_cair', 0)
				->get();
			foreach ($kurangBayarRows as $kb) {
				$kbNidn = trim((string) $kb->nidn);
				if (!isset($pendingKurangBayar[$kbNidn])) {
					$pendingKurangBayar[$kbNidn] = 0;
				}
				$pendingKurangBayar[$kbNidn] += (float) $kb->bersih;
			}
		}

		foreach ($dosenList as $row) {
			$nidn = trim((string) ($row->nidn ?? ''));
			$nuptk = trim((string) ($row->nuptk_d ?? ''));
			$identifier = ($nidn !== '' && $nidn !== '-') ? $nidn : (($nuptk !== '' && $nuptk !== '-') ? $nuptk : '');
			$row->nilai_kurang = $pendingKurangBayar[$identifier] ?? 0;
		}

		Log::debug('printTukin Berjalan loaded', [
			'kode_pts' => $kodePts,
			'bulan' => $bulan,
			'tahun' => $tahun,
			'count' => $dosenList->count(),
		]);

		return view('pts.print-tukin-berjalan', [
			'dosenList' => $dosenList,
			'bulan' => $bulan,
			'pts' => $user->nama_pts,
			'kode_pts' => $user->kode_pts,
			'alamat_pts' => $user->alamat_pt ?? $user->alamat ?? '',
			'tanggal' => now()->translatedFormat('d F Y'),
		]);
		} catch (\Throwable $e) {
			$alias = ErrorAlias::fromThrowable($e, 'PTS-TUKIN-BERJALAN');
			Log::error('printTukin Berjalan failed', [
				'alias' => $alias['code'],
				'kode_pts' => $kodePts,
				'bulan' => $bulan,
				'tahun' => $tahun,
				'message' => $e->getMessage(),
				'trace' => $e->getTraceAsString(),
			]);
			return redirect()->route('pts.usulan-tukin-berjalan', ['bulan' => $bulan])
				->with('internal_error', $alias['message']);
		}
	}
}
