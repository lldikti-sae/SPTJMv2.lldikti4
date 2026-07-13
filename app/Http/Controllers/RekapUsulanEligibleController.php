<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RekapUsulanEligibleController extends Controller
{
  private function parseProcessedIdentifiers($rows): array
  {
    $processed = [];
    foreach ($rows as $r) {
      if (!is_string($r) || trim($r) === '') continue;
      $parts = array_filter(array_map('trim', explode(',', $r)));
      foreach ($parts as $p) {
        if ($p !== '') {
          $processed[$p] = true;
        }
      }
    }
    return array_keys($processed);
  }

  private function applyExcludeProcessedToQuery($query, string $pencairan_ke, string $eligible_span, string $tipe_sptjm): void
  {
    $tahun = session('tahun');
    if (!$tahun) {
      return;
    }

    try {
      $base = DB::table('r_proses_cair')
        ->where('tahun', $tahun)
        ->where('eligible_span', $eligible_span)
        ->where('tipe_sptjm', $tipe_sptjm);

      if ($pencairan_ke !== 'Semua') {
        $base->where('pencairan_ke', $pencairan_ke);
      }

      $rows = $base->pluck('nidns')->all();
      $processed = $this->parseProcessedIdentifiers($rows);
      if (empty($processed)) {
        return;
      }

      // Chunk whereNotIn to avoid very large SQL lists
      foreach (array_chunk($processed, 1000) as $chunk) {
        $query->whereNotIn('NIDN', $chunk);
        $query->whereNotIn('NUPTK', $chunk);
      }
    } catch (\Throwable $e) {
      // ignore
    }
  }

  public function index(Request $request)
  {
    //request
    $pencairan_ke = $request->pencairan_ke;
    $bank = $request->bank;
    $status_pegawai = $request->status_pegawai;
    $eligible_span = $request->Eligible_span;
    $tunjangan = $request->tunjangan;
    $tipe_sptjm = $request->input('tipe_sptjm', 'SPTJM');
    //base query
    $query = DB::table('s_transaksi_2')
      ->select('*')
      ->where('Aktif', '1')
      ->where('Tahun_Versi', session('tahun'))
      ->where('Eligible_span', $eligible_span);

    // Jika tipe TUKIN, hanya tampilkan dosen PNS
    if ($tipe_sptjm === 'TUKIN') {
      $query->where('Jenis', 'PNS');
    }


    //filter
    $bulanMap = [
      1 => 'Januari',
      2 => 'Februari',
      3 => 'Maret',
      4 => 'April',
      5 => 'Mei',
      6 => 'Juni',
      7 => 'Juli',
      8 => 'Agustus',
      9 => 'September',
      10 => 'Oktober',
      11 => 'November',
      12 => 'Desember',
    ];
    $namaBulan = [];

    $hasFilter = $request->filled('pencairan_ke', 'bank', 'status_pegawai', 'Eligible_span', 'tunjangan');
    if ($hasFilter) {
      //filtering
      $bulanPendek  = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
      if ($pencairan_ke != "Semua") {
        if ($pencairan_ke <= 12) {
          $query->whereNull("No_sp2d_$pencairan_ke");
        }
        $query->where(function ($q) use ($pencairan_ke, $bulanPendek) {
          foreach ($bulanPendek as $bln) {
            $q->orWhere($bln, $pencairan_ke);
          }
        });
      } else {
        // Jika pencairan_ke = "Semua", tampilkan baris yang memiliki minimal satu
        // nilai TPD{i} > 0 atau TKGB{i} > 0 pada salah satu bulan (OR antar bulan).
        $query->where(function ($q) {
          for ($i = 1; $i <= 12; $i++) {
            if ($i === 1) {
              // Untuk iterasi pertama gunakan where agar tidak menghasilkan OR di awal
              $q->where("TPD$i", ">", 0)
                ->orWhere("TKGB$i", ">", 0);
            } else {
              $q->orWhere("TPD$i", ">", 0)
                ->orWhere("TKGB$i", ">", 0);
            }
          }
        });
      }

      if ($bank != "Semua") {
        $query->where('Bank', $bank);
      }

      if ($status_pegawai != "Semua" && $tipe_sptjm !== 'TUKIN') {
        $query->where('jenis', $status_pegawai);
      }

      if ($tunjangan != "Semua") {
        if ($tunjangan === "tkgb1") {
          $query->whereIn('Jabatan12', ['Guru Besar', 'Guru Besar 1050']);
          // Jika pencairan_ke spesifik (1..12), cari TKGB pada bulan itu.
          if ($pencairan_ke != "Semua") {
            $query->where(function ($q) use ($bulanPendek, $pencairan_ke) {
              for ($i = 1; $i <= 12; $i++) {
                $bulanField = $bulanPendek[$i - 1];
                $q->orWhere(function ($sub) use ($bulanField, $i, $pencairan_ke) {
                  $sub->where($bulanField, '=', $pencairan_ke)
                    ->where("TKGB" . $i, '>', 0);
                });
              }
            });
          } else {
            // Jika pencairan_ke = Semua, tampilkan bila ada TKGB > 0 di salah satu bulan
            $query->where(function ($q) {
              for ($i = 1; $i <= 12; $i++) {
                if ($i === 1) {
                  $q->where("TKGB" . $i, '>', 0);
                } else {
                  $q->orWhere("TKGB" . $i, '>', 0);
                }
              }
            });
          }
        }

        if ($tunjangan === "tpd1") {
          if ($pencairan_ke != "Semua") {
            $query->where(function ($q) use ($bulanPendek, $pencairan_ke) {
              for ($i = 1; $i <= 12; $i++) {
                $bulanField = $bulanPendek[$i - 1];
                $q->orWhere(function ($sub) use ($bulanField, $i, $pencairan_ke) {
                  $sub->where($bulanField, '=', $pencairan_ke)
                    ->where("TPD" . $i, '>', 0);
                });
              }
            });
          } else {
            // Jika pencairan_ke = Semua, tampilkan bila ada TPD > 0 di salah satu bulan
            $query->where(function ($q) {
              for ($i = 1; $i <= 12; $i++) {
                if ($i === 1) {
                  $q->where("TPD" . $i, '>', 0);
                } else {
                  $q->orWhere("TPD" . $i, '>', 0);
                }
              }
            });
          }
        }
      }
    }

    // Exclude identifiers already processed in r_proses_cair.
    // - If pencairan_ke is specific => exclude processed for that pencairan_ke.
    // - If pencairan_ke = Semua => exclude any already processed (so they don't reappear).
    $this->applyExcludeProcessedToQuery($query, (string) $pencairan_ke, (string) $eligible_span, (string) $tipe_sptjm);

    $cursor = $query->cursor();
    $data = []; // Do not pass the large dataset to the view

    // Build rekapitulasi grup untuk proses (sebelumnya dihitung di view)
    $rekap = [];
    $batas = 4985000000;
    $blnPendek = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
    $tunjanganReq = $request->input('tunjangan');
    $pencairanReq = $request->input('pencairan_ke');

    $addToRekap = function (string $bankLabel, string $statusPegawai, string $tunjanganLabel, float $addKotor, float $addPajak, float $addBersih, string $identifier) use (&$rekap, $batas) {
      $key = $bankLabel . '|' . $statusPegawai . '|' . $tunjanganLabel;
      if (!isset($rekap[$key])) {
        $rekap[$key] = [];
        $rekap[$key][] = [
          'bank' => $bankLabel,
          'status_pegawai' => $statusPegawai,
          'tunjangan' => $tunjanganLabel,
          'jmlh_dosen' => 0,
          'total_kotor_semua' => 0,
          'total_pajak_semua' => 0,
          'total_bersih_semua' => 0,
          'nidns' => [],
        ];
      }

      $index = count($rekap[$key]) - 1;
      if (($rekap[$key][$index]['total_kotor_semua'] + $addKotor) > $batas) {
        $rekap[$key][] = [
          'bank' => $bankLabel,
          'status_pegawai' => $statusPegawai,
          'tunjangan' => $tunjanganLabel,
          'jmlh_dosen' => 0,
          'total_kotor_semua' => 0,
          'total_pajak_semua' => 0,
          'total_bersih_semua' => 0,
          'nidns' => [],
        ];
        $index++;
      }

      $rekap[$key][$index]['jmlh_dosen']++;
      $rekap[$key][$index]['total_kotor_semua'] += $addKotor;
      $rekap[$key][$index]['total_pajak_semua'] += $addPajak;
      $rekap[$key][$index]['total_bersih_semua'] += $addBersih;
      $rekap[$key][$index]['nidns'][] = $identifier;
    };

    foreach ($cursor as $item) {
      $totalKotorTPD = 0;
      $totalKotorTKGB = 0;
      $totalPajakTPD = 0;
      $totalPajakTKGB = 0;
      $totalBersihTPD = 0;
      $totalBersihTKGB = 0;

      for ($i = 1; $i <= 12; $i++) {
        $bulanField = $blnPendek[$i - 1];
        $TPDField = 'TPD' . $i;
        $TKGBField = 'TKGB' . $i;
        $pajakTPDField = 'nilaiPajakTPD' . $i;
        $pajakTKGBField = 'nilaiPajakTKGB' . $i;
        $bersihTPDField = 'bersihTPD' . $i;
        $bersihTKGBField = 'bersihTKGB' . $i;

        $valTPD = (float) ($item->$TPDField ?? 0);
        $valTKGB = (float) ($item->$TKGBField ?? 0);
        $valPajakTPD = (float) ($item->$pajakTPDField ?? 0);
        $valPajakTKGB = (float) ($item->$pajakTKGBField ?? 0);
        $valBersihTPD = (float) ($item->$bersihTPDField ?? 0);
        $valBersihTKGB = (float) ($item->$bersihTKGBField ?? 0);

        if ($pencairanReq === 'Semua' || ($item->$bulanField ?? null) == $pencairanReq) {
          // Always compute both; grouping decision is handled after looping
          $totalKotorTPD += $valTPD;
          $totalKotorTKGB += $valTKGB;
          $totalPajakTPD += $valPajakTPD;
          $totalPajakTKGB += $valPajakTKGB;
          $totalBersihTPD += $valBersihTPD;
          $totalBersihTKGB += $valBersihTKGB;
        }
      }

      // Normalisasi bank agar beda case tidak jadi grup berbeda (bni/BNi/BNI => BNI)
      $bankNormalized = strtoupper(trim((string) ($item->Bank ?? '')));
      $bankLabel = $bankNormalized !== '' ? $bankNormalized : '-';

      $statusPegawai = $item->Jenis ?? '-';
      // identifier: prefer NIDN, fallback to NUPTK when NIDN is empty
      $identifier = trim((string) ($item->NIDN ?? '')) ?: trim((string) ($item->NUPTK ?? ''));

      // Build groups based on tunjangan filter:
      // - tpd1 => only TPD
      // - tkgb1 => only TKGB
      // - Semua => create BOTH groups (TPD + TKGB)
      if ($tunjanganReq === 'tpd1') {
        $addToRekap($bankLabel, $statusPegawai, 'TPD', $totalKotorTPD, $totalPajakTPD, $totalBersihTPD, $identifier);
      } elseif ($tunjanganReq === 'tkgb1') {
        $addToRekap($bankLabel, $statusPegawai, 'TKGB', $totalKotorTKGB, $totalPajakTKGB, $totalBersihTKGB, $identifier);
      } else {
        // Semua
        if ($totalKotorTPD > 0 || $totalPajakTPD > 0 || $totalBersihTPD > 0) {
          $addToRekap($bankLabel, $statusPegawai, 'TPD', $totalKotorTPD, $totalPajakTPD, $totalBersihTPD, $identifier);
        }
        if ($totalKotorTKGB > 0 || $totalPajakTKGB > 0 || $totalBersihTKGB > 0) {
          $addToRekap($bankLabel, $statusPegawai, 'TKGB', $totalKotorTKGB, $totalPajakTKGB, $totalBersihTKGB, $identifier);
        }
      }
    }

    return view('admin.rekap-usulan-eligible', [
      'data' => $data,
      'rekap' => $rekap,
      'hasFilter' => $hasFilter,
      'filter' => $request->all(),
      'namaBulan' => $namaBulan,
      'bulanMap' => $bulanMap,
      'success' => 'Data berhasil di dapatkan!'
    ]);
  }

  // AJAX endpoint untuk DataTables (server-side)
  public function data(Request $request)
  {
    $pencairan_ke = $request->pencairan_ke ?? 'Semua';
    $bank = $request->bank ?? 'Semua';
    $status_pegawai = $request->status_pegawai ?? 'Semua';
    $eligible_span = $request->Eligible_span ?? 'YA';
    $tunjangan = $request->tunjangan ?? 'Semua';
    $tipe_sptjm = $request->input('tipe_sptjm', 'SPTJM');

    // determine month from session (fallback to 12)
    $bulanSession = (int) session('bulan') ?: 12;
    if ($bulanSession < 1 || $bulanSession > 12) {
      $bulanSession = 12;
    }

    // base query
    $query = DB::table('s_transaksi_2')
      ->select('*')
      ->where('Aktif', '1')
      ->where('Tahun_Versi', session('tahun'))
      ->where('Eligible_span', $eligible_span);

    // Jika tipe TUKIN, hanya tampilkan dosen PNS
    if ($tipe_sptjm === 'TUKIN') {
      $query->where('Jenis', 'PNS');
    }

    // apply same filtering rules as index
    $bulanPendek  = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
    if ($pencairan_ke != "Semua") {
      if ($pencairan_ke <= 12) {
        $query->whereNull("No_sp2d_$pencairan_ke");
      }
      $query->where(function ($q) use ($pencairan_ke, $bulanPendek) {
        foreach ($bulanPendek as $bln) {
          $q->orWhere($bln, $pencairan_ke);
        }
      });
    } else {
      $query->where(function ($q) {
        for ($i = 1; $i <= 12; $i++) {
          if ($i === 1) {
            $q->where("TPD$i", ">", 0)
              ->orWhere("TKGB$i", ">", 0);
          } else {
            $q->orWhere("TPD$i", ">", 0)
              ->orWhere("TKGB$i", ">", 0);
          }
        }
      });
    }

    // Exclude identifiers already processed in r_proses_cair (see index() comment)
    $this->applyExcludeProcessedToQuery($query, (string) $pencairan_ke, (string) $eligible_span, (string) $tipe_sptjm);

    if ($bank != "Semua") {
      $query->where('Bank', $bank);
    }

    if ($status_pegawai != "Semua" && $tipe_sptjm !== 'TUKIN') {
      $query->where('jenis', $status_pegawai);
    }

    if ($tunjangan != "Semua") {
      if ($tunjangan === "tkgb1") {
        $query->whereIn('Jabatan12', ['Guru Besar', 'Guru Besar 1050']);
        if ($pencairan_ke != "Semua") {
          $query->where(function ($q) use ($bulanPendek, $pencairan_ke) {
            for ($i = 1; $i <= 12; $i++) {
              $bulanField = $bulanPendek[$i - 1];
              $q->orWhere(function ($sub) use ($bulanField, $i, $pencairan_ke) {
                $sub->where($bulanField, '=', $pencairan_ke)
                  ->where("TKGB" . $i, '>', 0);
              });
            }
          });
        } else {
          $query->where(function ($q) {
            for ($i = 1; $i <= 12; $i++) {
              if ($i === 1) {
                $q->where("TKGB" . $i, '>', 0);
              } else {
                $q->orWhere("TKGB" . $i, '>', 0);
              }
            }
          });
        }
      }

      if ($tunjangan === "tpd1") {
        if ($pencairan_ke != "Semua") {
          $query->where(function ($q) use ($bulanPendek, $pencairan_ke) {
            for ($i = 1; $i <= 12; $i++) {
              $bulanField = $bulanPendek[$i - 1];
              $q->orWhere(function ($sub) use ($bulanField, $i, $pencairan_ke) {
                $sub->where($bulanField, '=', $pencairan_ke)
                  ->where("TPD" . $i, '>', 0);
              });
            }
          });
        } else {
          $query->where(function ($q) {
            for ($i = 1; $i <= 12; $i++) {
              if ($i === 1) {
                $q->where("TPD" . $i, '>', 0);
              } else {
                $q->orWhere("TPD" . $i, '>', 0);
              }
            }
          });
        }
      }
    }

    // global search (DataTables)
    if ($request->filled('search') && isset($request->search['value']) && $request->search['value'] !== '') {
      $search = $request->search['value'];
      $query->where(function ($q) use ($search) {
        $q->where('NIDN', 'like', "%{$search}%")
          ->orWhere('Sertifikat_Dosen', 'like', "%{$search}%")
          ->orWhere('Nama', 'like', "%{$search}%");
      });
    }

    // records total / filtered
    $recordsTotalQuery = DB::table('s_transaksi_2')
      ->where('Aktif', '1')
      ->where('Tahun_Versi', session('tahun'))
      ->where('Eligible_span', $eligible_span);
    if ($tipe_sptjm === 'TUKIN') {
      $recordsTotalQuery->where('Jenis', 'PNS');
    }
    $recordsTotal = $recordsTotalQuery->count();

    $recordsFiltered = $query->count();

    // ordering (simple mapping)
    $orderCol = $request->input('order.0.column');
    $orderDir = $request->input('order.0.dir', 'asc');
    $columnMap = [
      0 => 'NIDN',
      1 => 'NUPTK',
      2 => 'Sertifikat_Dosen',
      3 => 'Nama',
      4 => 'Jabatan12',
      5 => 'Gol12',
      6 => 'Tahun12',
      7 => 'Jenis',
      8 => 'Bank',
      9 => 'Eligible_span',
      10 => 'Aktif',
      11 => 'Tahun_Versi'
    ];
    if (isset($columnMap[$orderCol])) {
      $query->orderBy($columnMap[$orderCol], $orderDir);
    }

    $start = intval($request->input('start', 0));
    $length = intval($request->input('length', 25));

    $rows = $query->offset($start)->limit($length)->get();

    $blnPendek = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
    $out = [];
    foreach ($rows as $item) {
      $row = [];
      $row[] = (string) $item->NIDN;
      // show NUPTK (or - if missing)
      $row[] = (string) ($item->NUPTK ?? '-');
      $row[] = (string) $item->Sertifikat_Dosen;
      $row[] = (string) $item->Nama;

      if ($bulanSession >= 12) {
        $row[] = (string) $item->Jabatan12;
        $row[] = (string) $item->Gol12;
        $row[] = (string) $item->Tahun12;
      } else {
        $fieldJabatan = 'Jabatan' . $bulanSession;
        $fieldGol = 'Gol' . $bulanSession;
        $fieldTahun = 'Tahun' . $bulanSession;
        $row[] = (string) ($item->$fieldJabatan ?? '-');
        $row[] = (string) ($item->$fieldGol ?? '-');
        $row[] = (string) ($item->$fieldTahun ?? '-');
      }

      $row[] = (string) $item->Jenis;
      $row[] = (string) $item->Bank;
      $row[] = (string) $item->Eligible_span;
      $row[] = (string) ($item->Aktif == 1 ? 'Aktif' : 'Tidak Aktif');
      $row[] = (string) $item->Tahun_Versi;

      // months
      $totalKotorTPD = 0;
      $totalKotorTKGB = 0;
      $totalPajakTPD = 0;
      $totalPajakTKGB = 0;
      $totalBersihTPD = 0;
      $totalBersihTKGB = 0;

      for ($i = 1; $i <= 12; $i++) {
        $bulanField = $blnPendek[$i - 1];
        $TPDField = 'TPD' . $i;
        $TKGBField = 'TKGB' . $i;
        $pajakTPDField = 'nilaiPajakTPD' . $i;
        $pajakTKGBField = 'nilaiPajakTKGB' . $i;
        $bersihTPDField = 'bersihTPD' . $i;
        $bersihTKGBField = 'bersihTKGB' . $i;

        $valTPD = (float) ($item->$TPDField ?? 0);
        $valTKGB = (float) ($item->$TKGBField ?? 0);
        $valPajakTPD = (float) ($item->$pajakTPDField ?? 0);
        $valPajakTKGB = (float) ($item->$pajakTKGBField ?? 0);
        $valBersihTPD = (float) ($item->$bersihTPDField ?? 0);
        $valBersihTKGB = (float) ($item->$bersihTKGBField ?? 0);

        $displayGaji = 0;
        if ($pencairan_ke == "Semua" || ($item->$bulanField ?? null) == $pencairan_ke) {
          if ($tunjangan == "tpd1") {
            $displayGaji = $valTPD;
            $totalKotorTPD += $displayGaji;
            $totalPajakTPD += $valPajakTPD;
            $totalBersihTPD += $valBersihTPD;
          } elseif ($tunjangan == "tkgb1") {
            $displayGaji = $valTKGB;
            $totalKotorTKGB += $displayGaji;
            $totalPajakTKGB += $valPajakTKGB;
            $totalBersihTKGB += $valBersihTKGB;
          } else {
            $displayGaji = $valTPD + $valTKGB;
            $totalPajakTPD += $valPajakTPD;
            $totalPajakTKGB += $valPajakTKGB;
            $totalBersihTPD += $valBersihTPD;
            $totalBersihTKGB += $valBersihTKGB;
          }
        }

        $row[] = (string) number_format($displayGaji, 0, ',', '.');
      }

      $row[] = (string) number_format($totalKotorTPD ?? 0, 0, ',', '.');
      $row[] = (string) number_format($totalKotorTKGB ?? 0, 0, ',', '.');
      $row[] = (string) number_format($totalPajakTPD ?? 0, 0, ',', '.');
      $row[] = (string) number_format($totalPajakTKGB ?? 0, 0, ',', '.');
      $row[] = (string) number_format($totalBersihTPD ?? 0, 0, ',', '.');
      $row[] = (string) number_format($totalBersihTKGB ?? 0, 0, ',', '.');
      $row[] = (string) $item->No_Rek;
      $row[] = (string) $item->NPWP;

      $out[] = $row;
    }

    return response()->json([
      'draw' => intval($request->input('draw')),
      'recordsTotal' => $recordsTotal,
      'recordsFiltered' => $recordsFiltered,
      'data' => $out,
    ]);
  }

  //kode baru 1
  public function proses(Request $request)
  {
    // Avoid timeouts for large processing
    @set_time_limit(0);
    @ini_set('max_execution_time', '0');
    DB::disableQueryLog();

    $rekap = json_decode($request->rekap_json, true);
    $tipe_sptjm = (string) ($request->tipe_sptjm ?? 'SPTJM');
    $pencairan_ke = (string) $request->pencairan_ke;
    $eligible_span = (string) $request->eligible_span;
    $tahun = session('tahun');

    if (!$tahun) {
      return redirect()->back()->with('error', 'Tahun versi tidak ditemukan di sesi. Silakan login ulang.');
    }

    if (!is_array($rekap) || empty($rekap)) {
      return redirect()->back()->with('error', 'Data rekap tidak valid.');
    }

    // If pencairan_ke = Semua, do not store "Semua".
    // Instead, split by actual pencairan_ke values found in s_transaksi_2 month columns.
    if ($pencairan_ke === 'Semua') {
      $bulanPendek = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
      $batas = 4985000000;

      // Build allowed identifier sets per (bank|status|tunjangan)
      $allowedAny = [];
      $allowedByGroup = []; // [groupKey => [id => true]]
      $banks = [];
      $jenisPegawai = [];
      foreach ($rekap as $r) {
        $bankLabel = strtoupper(trim((string) ($r['bank'] ?? '')));
        $statusPegawai = (string) ($r['status_pegawai'] ?? '-');
        $tunjLabel = (string) ($r['tunjangan'] ?? '-'); // expected: TPD / TKGB

        $banks[$bankLabel] = true;
        $jenisPegawai[$statusPegawai] = true;

        $gk = $bankLabel . '|' . $statusPegawai . '|' . $tunjLabel;
        if (!isset($allowedByGroup[$gk])) {
          $allowedByGroup[$gk] = [];
        }

        if (!empty($r['nidns']) && is_array($r['nidns'])) {
          foreach ($r['nidns'] as $ident) {
            $ident = trim((string) $ident);
            if ($ident === '') continue;
            $allowedAny[$ident] = true;
            $allowedByGroup[$gk][$ident] = true;
          }
        }
      }

      // Build processed identifiers set per pencairan_ke for this year + eligible_span
      $processedByPencairan = [];
      try {
        $rows = DB::table('r_proses_cair')
          ->where('tahun', $tahun)
          ->where('eligible_span', $eligible_span)
          ->where('tipe_sptjm', $tipe_sptjm)
          ->get(['pencairan_ke', 'nidns']);

        foreach ($rows as $row) {
          $pk = (string) ($row->pencairan_ke ?? '');
          if ($pk === '') continue;
          if (!isset($processedByPencairan[$pk])) {
            $processedByPencairan[$pk] = [];
          }
          $ids = $this->parseProcessedIdentifiers([(string) ($row->nidns ?? '')]);
          foreach ($ids as $id) {
            $processedByPencairan[$pk][$id] = true;
          }
        }
      } catch (\Throwable $e) {
        // ignore
      }

      // Accumulator for inserts with batas splitting
      $acc = []; // [$penc|$bank|$status|$tunj => [segments...]]
      $addAcc = function (string $penc, string $bankLabel, string $statusPegawai, string $tunjLabel, string $identifier, float $kotor, float $pajak, float $bersih) use (&$acc, $batas, $eligible_span, $tipe_sptjm) {
        $key = $penc . '|' . $bankLabel . '|' . $statusPegawai . '|' . $tunjLabel;
        if (!isset($acc[$key])) {
          $acc[$key] = [];
          $acc[$key][] = [
            'tahun' => session('tahun'),
            'pencairan_ke' => $penc,
            'status_pegawai' => $statusPegawai,
            'jenis' => $tunjLabel,
            'bank' => $bankLabel,
            'eligible_span' => $eligible_span,
            'tipe_sptjm' => $tipe_sptjm,
            'jumlah_kotor' => 0,
            'jumlah_pajak' => 0,
            'jumlah_bersih' => 0,
            'nidns' => [],
            'seen' => [],
          ];
        }

        $idx = count($acc[$key]) - 1;
        if (($acc[$key][$idx]['jumlah_kotor'] + $kotor) > $batas) {
          $acc[$key][] = [
            'tahun' => session('tahun'),
            'pencairan_ke' => $penc,
            'status_pegawai' => $statusPegawai,
            'jenis' => $tunjLabel,
            'bank' => $bankLabel,
            'eligible_span' => $eligible_span,
            'tipe_sptjm' => $tipe_sptjm,
            'jumlah_kotor' => 0,
            'jumlah_pajak' => 0,
            'jumlah_bersih' => 0,
            'nidns' => [],
            'seen' => [],
          ];
          $idx++;
        }

        if (!isset($acc[$key][$idx]['seen'][$identifier])) {
          $acc[$key][$idx]['seen'][$identifier] = true;
          $acc[$key][$idx]['nidns'][] = $identifier;
        }
        $acc[$key][$idx]['jumlah_kotor'] += $kotor;
        $acc[$key][$idx]['jumlah_pajak'] += $pajak;
        $acc[$key][$idx]['jumlah_bersih'] += $bersih;
      };

      // Single scan (cursor) over s_transaksi_2 for this year
      $q = DB::table('s_transaksi_2')
        ->where('Aktif', '1')
        ->where('Tahun_Versi', $tahun)
        ->where('Eligible_span', $eligible_span)
        ->select(array_merge(['NIDN', 'NUPTK', 'Bank', 'Jenis'], $bulanPendek, [
          // amounts
          'TPD1','TPD2','TPD3','TPD4','TPD5','TPD6','TPD7','TPD8','TPD9','TPD10','TPD11','TPD12',
          'TKGB1','TKGB2','TKGB3','TKGB4','TKGB5','TKGB6','TKGB7','TKGB8','TKGB9','TKGB10','TKGB11','TKGB12',
          'nilaiPajakTPD1','nilaiPajakTPD2','nilaiPajakTPD3','nilaiPajakTPD4','nilaiPajakTPD5','nilaiPajakTPD6','nilaiPajakTPD7','nilaiPajakTPD8','nilaiPajakTPD9','nilaiPajakTPD10','nilaiPajakTPD11','nilaiPajakTPD12',
          'nilaiPajakTKGB1','nilaiPajakTKGB2','nilaiPajakTKGB3','nilaiPajakTKGB4','nilaiPajakTKGB5','nilaiPajakTKGB6','nilaiPajakTKGB7','nilaiPajakTKGB8','nilaiPajakTKGB9','nilaiPajakTKGB10','nilaiPajakTKGB11','nilaiPajakTKGB12',
          'bersihTPD1','bersihTPD2','bersihTPD3','bersihTPD4','bersihTPD5','bersihTPD6','bersihTPD7','bersihTPD8','bersihTPD9','bersihTPD10','bersihTPD11','bersihTPD12',
          'bersihTKGB1','bersihTKGB2','bersihTKGB3','bersihTKGB4','bersihTKGB5','bersihTKGB6','bersihTKGB7','bersihTKGB8','bersihTKGB9','bersihTKGB10','bersihTKGB11','bersihTKGB12',
        ]));

      // apply optional filters to reduce cursor output
      if (!empty($banks)) {
        $q->whereIn('Bank', array_keys($banks));
      }
      if (!empty($jenisPegawai)) {
        $q->whereIn('Jenis', array_keys($jenisPegawai));
      }

      foreach ($q->cursor() as $row) {
        $identifier = trim((string) ($row->NIDN ?? '')) ?: trim((string) ($row->NUPTK ?? ''));
        if ($identifier === '' || !isset($allowedAny[$identifier])) {
          continue;
        }

        $bankLabel = strtoupper(trim((string) ($row->Bank ?? '')));
        $statusPegawai = (string) ($row->Jenis ?? '-');

        // Determine which tunjangan groups this row belongs to
        $tunjGroups = [];
        foreach (['TPD', 'TKGB'] as $tlabel) {
          $gk = $bankLabel . '|' . $statusPegawai . '|' . $tlabel;
          if (isset($allowedByGroup[$gk]) && isset($allowedByGroup[$gk][$identifier])) {
            $tunjGroups[] = $tlabel;
          }
        }
        if (empty($tunjGroups)) {
          continue;
        }

        // Aggregate per pencairan_ke for this dosen
        $perPenc = []; // [penc => ['TPD'=>[k,p,b], 'TKGB'=>[k,p,b]]]
        for ($i = 1; $i <= 12; $i++) {
          $bulanField = $bulanPendek[$i - 1];
          $penc = trim((string) ($row->$bulanField ?? ''));
          if ($penc === '') {
            continue;
          }

          $valTPD = (float) ($row->{'TPD' . $i} ?? 0);
          $valTKGB = (float) ($row->{'TKGB' . $i} ?? 0);
          $valPajakTPD = (float) ($row->{'nilaiPajakTPD' . $i} ?? 0);
          $valPajakTKGB = (float) ($row->{'nilaiPajakTKGB' . $i} ?? 0);
          $valBersihTPD = (float) ($row->{'bersihTPD' . $i} ?? 0);
          $valBersihTKGB = (float) ($row->{'bersihTKGB' . $i} ?? 0);

          if (!isset($perPenc[$penc])) {
            $perPenc[$penc] = [
              'TPD' => [0, 0, 0],
              'TKGB' => [0, 0, 0],
            ];
          }

          $perPenc[$penc]['TPD'][0] += $valTPD;
          $perPenc[$penc]['TPD'][1] += $valPajakTPD;
          $perPenc[$penc]['TPD'][2] += $valBersihTPD;

          $perPenc[$penc]['TKGB'][0] += $valTKGB;
          $perPenc[$penc]['TKGB'][1] += $valPajakTKGB;
          $perPenc[$penc]['TKGB'][2] += $valBersihTKGB;
        }

        foreach ($perPenc as $penc => $totalsByType) {
          foreach ($tunjGroups as $tlabel) {
            // skip already processed for this pencairan_ke
            if (isset($processedByPencairan[$penc]) && isset($processedByPencairan[$penc][$identifier])) {
              continue;
            }

            $t = $totalsByType[$tlabel] ?? [0, 0, 0];
            $kotor = (float) $t[0];
            $pajak = (float) $t[1];
            $bersih = (float) $t[2];
            if ($kotor == 0 && $pajak == 0 && $bersih == 0) {
              continue;
            }
            $addAcc($penc, $bankLabel, $statusPegawai, $tlabel, $identifier, $kotor, $pajak, $bersih);
          }
        }
      }

      $toInsert = [];
      foreach ($acc as $segments) {
        foreach ($segments as $seg) {
          $nidns = $seg['nidns'] ?? [];
          $toInsert[] = [
            'tahun' => $seg['tahun'],
            'pencairan_ke' => $seg['pencairan_ke'],
            'status_pegawai' => $seg['status_pegawai'],
            'jenis' => $seg['jenis'],
            'bank' => $seg['bank'],
            'eligible_span' => $seg['eligible_span'],
            'tipe_sptjm' => $seg['tipe_sptjm'],
            'jumlah_kotor' => $seg['jumlah_kotor'],
            'jumlah_pajak' => $seg['jumlah_pajak'],
            'jumlah_bersih' => $seg['jumlah_bersih'],
            'nidns' => implode(',', $nidns),
            'created_at' => now(),
          ];
        }
      }

      if (empty($toInsert)) {
        return redirect()->back()->with('error', 'Tidak ada data baru untuk diproses (semua sudah diproses / kosong).');
      }

      foreach (array_chunk($toInsert, 200) as $chunk) {
        DB::table('r_proses_cair')->insert($chunk);
      }

      return redirect()->back()->with('success', 'Data berhasil diproses per pencairan ke yang tersedia.');
    }

    // Normal path: pencairan_ke spesifik
    $data = [];
    foreach ($rekap as $r) {
      $mappedIds = [];
      if (!empty($r['nidns']) && is_array($r['nidns'])) {
        $seen = [];
        foreach ($r['nidns'] as $ident) {
          $ident = trim((string) $ident);
          if ($ident === '') {
            continue;
          }
          if (!isset($seen[$ident])) {
            $seen[$ident] = true;
            $mappedIds[] = $ident;
          }
        }
      }

      $data[] = [
        "tahun" => $tahun,
        "pencairan_ke" => $pencairan_ke,
        "status_pegawai" => $r['status_pegawai'],
        "jenis" => $r['tunjangan'],
        "bank" => $r['bank'],
        "eligible_span" => $eligible_span,
        "tipe_sptjm" => $tipe_sptjm,
        "jumlah_kotor" => $r['total_kotor'],
        "jumlah_pajak" => $r['total_pajak'],
        "jumlah_bersih" => $r['total_bersih'],
        "nidns" => implode(',', $mappedIds),
        "created_at" => now(),
      ];
    }

    foreach (array_chunk($data, 200) as $chunk) {
      DB::table('r_proses_cair')->insert($chunk);
    }

    return redirect()->back()->with('success', 'Data berhasil di proses.');
  }
}
