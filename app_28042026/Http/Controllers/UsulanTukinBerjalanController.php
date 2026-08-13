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
		$dosenListNonPNS = collect();

		if ($bulan) {
			try {
				$bulan = (int) $bulan;

			$namaBulan = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
			$bulanTeks = $namaBulan[$bulan] ?? '';

			// Tentukan sumber BKD berdasarkan bulan
			if (in_array($bulan, [1, 2])) {
				$joinTable = ['table' => 'o_sister_genap_tl as b', 'kode_pt' => 'b.kode_pt'];
			} elseif (in_array($bulan, [3, 4, 5, 6, 7, 8])) {
				$joinTable = ['table' => 'p_sister_ganjil as b', 'kode_pt' => 'b.kode_pt'];
			} else {
				$joinTable = ['table' => 'n_sister_genap_bj as b', 'kode_pt' => 'b.kode_pt'];
			}

			$dosenList = DB::table('s_transaksi_2 as d')
				->leftJoin($joinTable['table'], function ($join) {
					$join->on('d.NIDN', '=', 'b.nidn')
						->orOn('d.NUPTK', '=', 'b.nuptk');
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
					DB::raw('b.kesimpulan_bkd as kesimpulan_bkd'),
					DB::raw('b.kd as kd'),
					DB::raw('b.kp as kp'),
					DB::raw('b.potongan_periodik as pp'),
					DB::raw('b.nuptk as nuptk'),
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
				// 	session()->flash('info', "$removed dosen sudah diusulkan pada periode $bulanTeks $tahun, sehingga tidak ditampilkan.");
				// }
			}

			// Split untuk tampilan 2 tabel (PNS vs NON PNS)
			$dosenListPNS = $dosenList
				->filter(function ($row) {
					$jenis = isset($row->jenis) ? trim((string) $row->jenis) : '';
					return strtoupper($jenis) === 'PNS';
				})
				->values();

			$dosenListNonPNS = $dosenList
				->filter(function ($row) {
					$jenis = isset($row->jenis) ? trim((string) $row->jenis) : '';
					return strtoupper($jenis) !== 'PNS';
				})
				->values();

			Log::debug('usulanTukin Berjalan index loaded', [
				'kode_pts' => $kodePts,
				'bulan' => $bulan,
				'bulan_teks' => $bulanTeks,
				'tahun' => $tahun,
				'count_total' => $dosenList->count(),
				'count_pns' => $dosenListPNS->count(),
				'count_non_pns' => $dosenListNonPNS->count(),
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
				$dosenListNonPNS = collect();
				return view('pts.usulan-tukin-berjalan', compact('dosenList', 'dosenListPNS', 'dosenListNonPNS', 'bulan'))
					->with('internal_error', $alias['message']);
			}
		}

		return view('pts.usulan-tukin-berjalan', compact('dosenList', 'dosenListPNS', 'dosenListNonPNS', 'bulan'));
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
			$joinTable = ['table' => 'o_sister_genap_tl as b'];
		} elseif (in_array($bulan, [3, 4, 5, 6, 7, 8])) {
			$joinTable = ['table' => 'p_sister_ganjil as b'];
		} else {
			$joinTable = ['table' => 'n_sister_genap_bj as b'];
		}

		// Ambil list dosen sesuai tampilan index
		$dosenList = DB::table('s_transaksi_2 as d')
			->leftJoin($joinTable['table'], function ($join) {
				$join->on('d.NIDN', '=', 'b.nidn')
					->orOn('d.NUPTK', '=', 'b.nuptk');
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
				DB::raw('b.kesimpulan_bkd as kesimpulan_bkd'),
				DB::raw('b.kd as kd'),
				DB::raw('b.kp as kp'),
				DB::raw('b.potongan_periodik as pp'),
				DB::raw('b.nuptk as nuptk')
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
			$joinTable = ['table' => 'o_sister_genap_tl as b', 'kode_pt' => 'b.kode_pt'];
		} elseif (in_array($bulan, [3, 4, 5, 6, 7, 8])) {
			$joinTable = ['table' => 'p_sister_ganjil as b', 'kode_pt' => 'b.kode_pt'];
		} else {
			$joinTable = ['table' => 'n_sister_genap_bj as b', 'kode_pt' => 'b.kode_pt'];
		}

		$dosenList = DB::table('s_transaksi_2 as d')
			->leftJoin($joinTable['table'], function ($join) {
				$join->on('d.NIDN', '=', 'b.nidn')
					->orOn('d.NUPTK', '=', 'b.nuptk');
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
				DB::raw('b.kesimpulan_bkd as kesimpulan_bkd'),
				DB::raw('b.kd as kd'),
				DB::raw('b.kp as kp'),
				DB::raw('b.potongan_periodik as pp'),
				DB::raw('b.nuptk as nuptk'),
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
			->orderBy('d.Nama')
			->get();

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

